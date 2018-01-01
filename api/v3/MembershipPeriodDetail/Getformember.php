<?php
use CRM_Membershipperiod_ExtensionUtil as E;
use CRM_Membershipperiod_BAO_MembershipPeriodMembershipDuration as MembershipDuration;
use CRM_Membershipperiod_BAO_MembershipPeriodDetail as MembershipPeriodDetail;
/**
 * MembershipPeriodDetail.Getformember API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 * @return void
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC/API+Architecture+Standards
 */
function _civicrm_api3_membership_period_detail_getformember_spec(&$spec) {
  // $spec['magicword']['api.required'] = 1;
  $spec['membership_id']['api.required'] = 1;
}

/**
 * MembershipPeriodDetail.Getformember API
 *
 * @param array $params
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws API_Exception
 *
function civicrm_api3_membership_period_detail_Getformember($params) {
  if (array_key_exists('magicword', $params) && $params['magicword'] == 'sesame') {
    $returnValues = array(
      // OK, return several data rows
      12 => array('id' => 12, 'name' => 'Twelve'),
      34 => array('id' => 34, 'name' => 'Thirty four'),
      56 => array('id' => 56, 'name' => 'Fifty six'),
    );
    // ALTERNATIVE: $returnValues = array(); // OK, success
    // ALTERNATIVE: $returnValues = array("Some value"); // OK, return a single value

    // Spec: civicrm_api3_create_success($values = 1, $params = array(), $entity = NULL, $action = NULL)
    return civicrm_api3_create_success($returnValues, $params, 'NewEntity', 'NewAction');
  }
  else {
    throw new API_Exception( 'Everyone knows that the magicword is "sesame"', 1234);
  }
} */


/**
 * MembershipPeriodDetail.get API
 * Required parameters : membership_id
 * @param array $params
 * @return array API result descriptor
 * @throws API_Exception
 */
function civicrm_api3_membership_period_detail_getformember($params) {
  $membershipId = $params['membership_id'];

  $periodDetails = civicrm_api3('MembershipPeriodDetail', 'get', array(
    'sequential' => 1,
    'membership_id' => $membershipId,
    'return' => array(
      "contribution_id.total_amount",
      "id", 
      "membership_id", 
      "number_of_periods", 
      "start_date", 
      "end_date", 
      "membership_duration_unit", 
      "membership_duration_interval",
      "created_at", 
      "contribution_id",
      "currency_code",
      "membership_id",
      "membership_type_id.name"),
    'options' => array('sort' => "start_date ASC")
  ));

  $periods = array();

  foreach ($periodDetails['values'] as $p){

    $numberOfPeriods = $p['number_of_periods'];

    /* Whether the member create/renew membership for multiple terms. In that case, the contribution is common for all the terms */
    $sharedContribution = false; 
    
    if ($numberOfPeriods == 1){
      /*  Membership is for only 1 period. Directly add it to the final list */
      $p['shared_contribution'] = $sharedContribution;

      if ($p['end_date'] == MembershipPeriodDetail::$lifetimeMembershipEndDate ){
        $p['end_date'] = "-";
      }
      $periods [] = $p;

    } else {
      /*  Membership is for more than 1 period. Calculate all the intermediate membership periods
      and add them to the final list */
      $sharedContribution = true;
      $startDate = $p['start_date'];
      $finalEndDate = $p['end_date'];

      $contributionId = $p['contribution_id'];
      $membershipDurationUnit = $p['membership_duration_unit'];
      $membershipDurationInterval = $p['membership_duration_interval'];
      $createdAt = $p['created_at'];
      $contributionAmount = $p['contribution_id.total_amount'];

      try {
        for ($i=0; $i < $numberOfPeriods; $i++){

          $startDateObj = new \DateTime($startDate);
          $dateInterval = "";

          switch ($membershipDurationUnit){
            case MembershipDuration::YEAR : 
            $dateInterval = 'P'.$membershipDurationInterval.'Y'; break;
            case MembershipDuration::MONTH : 
            $dateInterval = 'P'.$membershipDurationInterval.'M'; break;
            case MembershipDuration::DAY :
            $dateInterval = 'P'.$membershipDurationInterval.'D'; break;
            default:
            throw new \Exception(E::ts('Could not determine membership duration unit in membership period details'));
          }

          $tmpEndDate = $startDateObj->add(new \DateInterval($dateInterval));
          $tmpEndDate->sub(new \DateInterval('P1D'));
          $endDate =$tmpEndDate->format('Y-m-d');

          $tmpPeriod = $p;
          $tmpPeriod['start_date'] = $startDate;
          $tmpPeriod['end_date'] = $endDate;

          $tmpPeriod['contribution_id'] = $contributionId;
          $tmpPeriod['contribution_id.total_amount'] = $contributionAmount;
          $tmpPeriod['shared_contribution'] = $sharedContribution;

          $periods []= $tmpPeriod;

          $tmpEndDate->add(new \DateInterval('P1D'));
          $startDate = $tmpEndDate->format('Y-m-d'); /* Current period's end date is the start date for the next period. Still inside the loop. */
        }

        /* After calculating all intermediate periods, the end dates must be tally */
        if ($finalEndDate != $endDate)
        {
          throw new Exception (E::ts("Untallied end dates for membership periods. Please check the membership period details to rectify the problem "). $finalEndDate.' != '. $endDate);
        }
      } catch (\Exception $e){
        return civicrm_api3_create_error($e->getMessage());
      }
    }
  }
  return civicrm_api3_create_success($periods, $params, '', 'getformember');
}



