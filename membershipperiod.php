<?php

require_once 'membershipperiod.civix.php';
use CRM_Membershipperiod_ExtensionUtil as E;

/**
 * Implements hook_civicrm_config().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_config
 */
function membershipperiod_civicrm_config(&$config) {
	_membershipperiod_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_xmlMenu().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_xmlMenu
 */
function membershipperiod_civicrm_xmlMenu(&$files) {
	_membershipperiod_civix_civicrm_xmlMenu($files);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_install
 */
function membershipperiod_civicrm_install() {
	_membershipperiod_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_postInstall().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_postInstall
 */
function membershipperiod_civicrm_postInstall() {
	_membershipperiod_civix_civicrm_postInstall();
}

/**
 * Implements hook_civicrm_uninstall().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_uninstall
 */
function membershipperiod_civicrm_uninstall() {
	_membershipperiod_civix_civicrm_uninstall();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function membershipperiod_civicrm_enable() {
	_membershipperiod_civix_civicrm_enable();
}

/**
 * Implements hook_civicrm_disable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_disable
 */
function membershipperiod_civicrm_disable() {
	_membershipperiod_civix_civicrm_disable();
}

/**
 * Implements hook_civicrm_upgrade().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_upgrade
 */
function membershipperiod_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
	return _membershipperiod_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implements hook_civicrm_managed().
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_managed
 */
function membershipperiod_civicrm_managed(&$entities) {
	_membershipperiod_civix_civicrm_managed($entities);
}

/**
 * Implements hook_civicrm_caseTypes().
 *
 * Generate a list of case-types.
 *
 * Note: This hook only runs in CiviCRM 4.4+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_caseTypes
 */
function membershipperiod_civicrm_caseTypes(&$caseTypes) {
	_membershipperiod_civix_civicrm_caseTypes($caseTypes);
}

/**
 * Implements hook_civicrm_angularModules().
 *
 * Generate a list of Angular modules.
 *
 * Note: This hook only runs in CiviCRM 4.5+. It may
 * use features only available in v4.6+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_angularModules
 */
function membershipperiod_civicrm_angularModules(&$angularModules) {
	_membershipperiod_civix_civicrm_angularModules($angularModules);
}

/**
 * Implements hook_civicrm_alterSettingsFolders().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_alterSettingsFolders
 */
function membershipperiod_civicrm_alterSettingsFolders(&$metaDataFolders = NULL) {
	_membershipperiod_civix_civicrm_alterSettingsFolders($metaDataFolders);
}

// --- Functions below this ship commented out. Uncomment as required. ---


/**
 * Implements hook_civicrm_apiWrappers() to map the class name with entity.
 */
function membershipperiod_civicrm_entityTypes(&$entityTypes) {
	$entityTypes['CRM_Membershipperiod_DAO_MembershipPeriodDetail'] = array(
		'name'  => 'MembershipPeriodDetail',
		'class' => 'CRM_Membershipperiod_DAO_MembershipPeriodDetail',
		'table' => 'civicrm_membership_period_detail',
	);
}

/**
 * Implements hook_civicrm_preProcess().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_preProcess
 */
function membershipperiod_civicrm_preProcess($formName, &$form) {
	/* Taking the url path of the request */
	if (is_a($form, 'CRM_Member_Form_MembershipRenewal')){

		/* The existing implementation before this module implementation overrided the 'end_date' variable in the $form object before executing the postProcess hook. But in order to use it in the postProcess hook, it is required to preverve it. In order to do that, create a new variable called '$form->last_membership_end_date' and store the value. */
		
		$membershipId = $form->getVar('_id');
		$memberDetails =civicrm_api3('Membership', 'getsingle', array(
			'return' => array("end_date"),
			'id' => $membershipId,
		));
		$currentMembershipEndDate = @$memberDetails['end_date'];
		@$form->last_membership_end_date = $currentMembershipEndDate;
	}

	if (is_a($form, 'CRM_Member_Form_MembershipView')){

		$membershipId = CRM_Utils_Request::retrieve('id', 'Positive');

		$membershipPeriods = civicrm_api3('MembershipPeriodDetail', 'getformember', array(
			'sequential' => 1,
			'membership_id' => $membershipId
		));

		$form->assign('membershipPeriods',$membershipPeriods);
	}
}

/**
 * Implements hook_civicrm_postProcess().
 *
 * @param string $formName
 * @param CRM_Core_Form $form
 */
function membershipperiod_civicrm_postProcess($formName, &$form) {
	
	/* Taking the url path of the request */
	$urlPath = @$form->getVar('urlPath');
	$controller = @$urlPath[1];
	$action = @$urlPath[2];

	if (is_a($form, 'CRM_Member_Form_MembershipRenewal') || ( is_a($form,'CRM_Member_Form_Membership') && $action=='add' )) {


		$defaultValues = $form->getVar('_defaultValues');
		$membershipId = $form->getVar('_id');

		$membershipDetails = civicrm_api3('Membership', 'getsingle', array(
			'sequential' => 1,
			'return' => array("membership_type_id", "end_date","contact_id"),
			'id' => $membershipId,
		));

		$membershipTypeId = $membershipDetails['membership_type_id'];
		$membershipTypeDetails = civicrm_api3('MembershipType', 'getsingle', array(
			'sequential' => 1,
			'return' => array("name","duration_unit", "duration_interval"),
			'id' => $membershipTypeId
		));

		$membershipTypeName = $membershipTypeDetails['name'];
		
		$contributionId = null;

		/* Whether or not a contribution is made for the membership */
		$recordContribution = $form->getElement('record_contribution')->getValue();

		if ($recordContribution == true){
			/* Payment and some other related data are stored in $_params variable in the $form object. Here it is used to retirve the contribution ID */
			$objectParams = $form->getVar('_params');

    		/* There are two ways to get the contribution here. The $objectParams has it under the root variable lineItems.
    		Also, it is possible to get the contribution through the invoice ID. The problem with Invoice ID approach is that
    		it seems no invoice ID is recorded in the sign-up level contribution.

    		First, get the contribution ID by accessing the 'lineItems'. In case it is not there, get the contribution ID through the invoice ID */
    		
    		if (isset($objectParams['lineItems'])){
    			foreach ($objectParams['lineItems'] as $key1 => $val1){
    				foreach ($level1 as $key2 => $val2){
    					if ($key2 == 'contribution_id'){
    						$contributionId = $val2;
    						break;
    					}
    				}
    				if (is_numeric($contributionId)) break;
    			}
    		}
    		
    		/*  Only if contribution ID is not assigned above, go for the other approach of finding it from the invoice ID if it is available */
    		if (!is_numeric($contributionId)){
    			/* The invoice ID is assigned to two variables. In case one variable get depricated in future, other will work here. */
    			$invoiceId = (@$objectParams['invoice_id'] != "") ? @$objectParams['invoice_id'] : @$objectParams['invoiceID'];

    			/* Sometimes, invoices are not enabled. In that case, the following will give an exception. Therefore check
    			if the invoice ID is not empty before running the query */
    				if ($invoiceId != ""){
    					$contributionDetails = civicrm_api3('Contribution', 'getsingle', array(
    						'sequential' => 1,
    						'return' => array("id"),
    						'invoice_id' => $invoiceId
    					));

    					$contributionId = $contributionDetails['id'];
    				}
    			}

    			/* If still contribution ID is not found, take the last contribution made by the contact */
    			if (!is_numeric($contributionId)){
    				$contactId = $membershipDetails['contact_id'];

    				$contributionDetails = $result = civicrm_api3('Contribution', 'getsingle', array(
    					'sequential' => 1,
    					'contact_id' => $contactId,
    					'options' => array('limit' => 1, 'sort' => "id DESC"),
    				));

    				$contributionId = $contributionDetails['contribution_id'];
    			}
    		}

    		/* This value is stored in the preProcess hook */
    		$previousMembershipEndDate = @$form->last_membership_end_date;

    		if (trim($previousMembershipEndDate) == ""){
    			/* New membership registration */
    			$newMembershipStartDateObj = new \DateTime($defaultValues['join_date']);
    		} else {
    			/* Membership renewal */
    			$newMembershipStartDateObj = new \DateTime($previousMembershipEndDate);
    			$newMembershipStartDateObj->add(new \DateInterval('P1D'));
    		}

    		$newMembershipStartDate = $newMembershipStartDateObj->format("Y-m-d");

    		$membershipDurationUnit = $membershipTypeDetails['duration_unit'];

    		/* New, membership end date.*/
    		$membershipEndDate = @$membershipDetails['end_date'];
    		
    		$membershipDurationInterval = $membershipTypeDetails['duration_interval'];
    		$numberOfPeriods = $form->getElement('num_terms')->getValue();

    		$currencyCode = @$objectParams['currencyID'];

    		if (trim($currencyCode) == ""){
    			/* If currency code is not available from the form data, get the default currency and store it here */
    			$config = CRM_Core_Config::singleton();
    			$currencyCode =$config->defaultCurrency;
    		}

    		$saveData =  array(
    			'membership_id' => $membershipId,
    			'contribution_id' => $contributionId,
    			'number_of_periods' => $numberOfPeriods,
    			'start_date' => $newMembershipStartDate,
    			'end_date' => $membershipEndDate,
    			'membership_duration_unit' => $membershipDurationUnit,
    			'membership_duration_interval' => $membershipDurationInterval,
    			'membership_type_id' => $membershipTypeId,
    			'currency_code' => $currencyCode
    		);
    		$result = civicrm_api3('MembershipPeriodDetail', 'create', $saveData);

    		if ($result['is_error'] == 0){
    			CRM_Core_Session::setStatus(E::ts('Membership period information successfully saved'), E::ts('Membership Period Information Saved'),'success');
    		} else {
    			CRM_Core_Session::setStatus(E::ts('Could not save membership period information'), E::ts('Error saving Membership Period Information','error'));
    		}
    	}
    }


/**
 * Modifies the membership view popup form to show the membership periods
 * @param  String &$content The existing content of the page/form
 * @param  String $context  Contex of the page/form (e.g. page)
 * @param  String  $tplName  The template file is being altered
 * @param  Object &$object  Object containing form variables, etc...
 * @return void
 */
function membershipperiod_civicrm_alterContent( &$content, $context, $tplName, &$object ) {
	if ($tplName == "CRM/Member/Page/Tab.tpl"){
		$action = @$object->getVar('_action');

		if ($action == 4){
			$membershipId = @$object->getVar('_id');
			$MembershipPeriodDetails = civicrm_api3('MembershipPeriodDetail', 'getformember', array(
				'sequential' => 1,
				'membership_id' => $membershipId,
			));

			$membershipPeriods = $MembershipPeriodDetails['values'];

			$appendText  = '
				
				<div id="crm-membership-details-view" class="crm-container" style="margin-top:5px;">
					<div class="crm-block crm-content-block crm-membership-view-form-block">
						<div class="crm-accordion-wrapper">
							<div class="crm-accordion-header">'.E::ts('Membership Period Details').'</div>
							<div class="crm-accordion-body">
								<table class="selector row-highlight">
									<thead class="sticky">
										<tr>
											<th scope="col">
												'.E::ts('No.').'
											</th>
											<th>
												'.E::ts('Start Date').'
											</th>
											<th>
												'.E::ts('End Date').'
											</th>
											<th>
												'.E::ts('Membership').'
											</th>
											<th>
												'.E::ts('Term Duration').'
											</th>
											<th>
												'.E::ts('Contribution').'
											</th>
											<th>
												'.E::ts('Created').'
											</th>
										</tr>
									</thead>
									<tbody>';

			$sequence = 1;
			$config = CRM_Core_Config::singleton();
			
			if (is_object($config)){
				$defaultCurrencyCode = $config->defaultCurrency;
			} else {
				$defaultCurrencyCode = "USD";
			}
			foreach ($membershipPeriods as $index => $p){

				$multipleDurationIndicator = "";
				if ($p['membership_duration_interval'] >1){
					$multipleDurationIndicator = "s";
				}

				if ($p['currency_code'] !=""){
					$currencyCode = $p['currency_code'];
				} else {
					$currencyCode = $defaultCurrencyCode;
				}
				$currencySymbol =  CRM_Core_DAO::getFieldValue('CRM_Financial_DAO_Currency',$currencyCode,'symbol','name');
				
				/* Whether the contribution made for multiple membership periods */
				$shared = ($p['shared_contribution'] == 1) ? "(Shared)" : "";

				$startDateObj = new \DateTime($p['start_date']);
				$startDate = $startDateObj->format('D jS \of M Y');
				$endDateObj = new \DateTime($p['end_date']);
				$endDate = $endDateObj->format('D jS \of M Y');

				$createdDateObj = new \DateTime($p['created_at']);
				$createdDate = $createdDateObj->format('D jS \of M Y');

				$appendText .='
										<tr class="odd-row">
											<td>'.$sequence.'</td>
											<td>'.$startDate.'</td>
											<td>'.$endDate.'</td>
											<td>'.$p['membership_type_id.name'].'</td>
											<td>'.$p['membership_duration_interval'].' '.$p['membership_duration_unit'].''.$multipleDurationIndicator.' </td>
											<td>
												<a href="/civicrm/contact/view/contribution?reset=1&id='.$p['contribution_id'].'&action=view&context=contribution&selectedChild=contribute" class="action-item crm-hover-button">
												'.$currencySymbol.' '.$p['contribution_id.total_amount'].' '.$shared.'
												</a></td>
											<td>'.$createdDate.'</td>
										</tr>';
				$sequence++;
			}
			$appendText .= '
									</tbody>
								</table>
							</div>
						</div>
					</div>
				</div>

				<script type="text/javascript">
					CRM.$(function(){
						CRM.$("#crm-membership-details-view").appendTo(CRM.$(".crm-accordion-wrapper"));
					});
				</script>
			';

			$content .= $appendText;
		}
	}  
}
