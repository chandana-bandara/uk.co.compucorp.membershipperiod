<?php

use Civi\Test\HeadlessInterface;
use Civi\Test\HookInterface;
use Civi\Test\TransactionalInterface;

use CRM_Membershipperiod_BAO_MembershipPeriodMembershipDuration as MembershipDuration;
use CRM_Membershipperiod_BAO_MembershipPeriodMembershipPeriodType as MembershipPeriodType;
/**
* This test checks proper functionality of the Gerformember api call. The test creates memberships with and without a contribution as weill
* as with single and multiple term creation/renewals.
*
* Tips:
*  - With HookInterface, you may implement CiviCRM hooks directly in the test class.
*    Simply create corresponding functions (e.g. "hook_civicrm_post(...)" or similar).
*  - With TransactionalInterface, any data changes made by setUp() or test****() functions will
*    rollback automatically -- as long as you don't manipulate schema or truncate tables.
*    If this test needs to manipulate schema or truncate tables, then either:
*       a. Do all that using setupHeadless() and Civi\Test.
*       b. Disable TransactionalInterface, and handle all setup/teardown yourself.
*
* @group headless
*/
class api_v3_MembershipPeriodDetail_GetformemberTest extends \PHPUnit_Framework_TestCase implements HeadlessInterface, HookInterface, TransactionalInterface {


  protected $testOrganizationContactId;
  protected $testIndividualContactId;
  protected $testMembershipId;

  protected $membershipTypeIds = array();

  protected $membershipDueFinancialTypeId = 2;
  protected $cashPaymentInstrumentId = 3;


  /* The tests need an organization, individual before testing the membershipperiod. */
  protected $testOrganizationParams = array(
    'contact_type' => 'Organization',
    'organization_name' => 'Test Organization'
  );

  protected $testIndividualParams = array(
    'contact_type' => 'Individual',
    'first_name' => 'Test',
    'last_name' => 'Individual'
  );

  protected $testMembershipTypeParams = array(
    'rolling_1_year' => array(
      'domain_id' => 1, // Default
      'member_of_contact_id' => null, // Needs to fill in run time
      'financial_type_id' => 2, 
      'duration_unit' => MembershipDuration::YEAR,
      'duration_interval' => 1,
      'period_type' => MembershipPeriodType::ROLLING,
      'name' => "Rolling 1 year membership",
    ),
    'fixed_1_year' => array(
      'domain_id' => 1, // Default
      'member_of_contact_id' => null, // Needs to fill in run time
      'financial_type_id' => 2, 
      'duration_unit' => MembershipDuration::YEAR,
      'duration_interval' => 1,
      'period_type' => MembershipPeriodType::FIXED,
      'name' => "Fixed 1 year membership",
    )
  );

  protected $testMembershipParams = array(
    'contact_id' => null, 
    'membership_type_id' => null,
    
  );


  public function setUpHeadless() {
// Civi\Test has many helpers, like install(), uninstall(), sql(), and sqlFile().
// See: https://github.com/civicrm/org.civicrm.testapalooza/blob/master/civi-test.md
    return \Civi\Test::headless()
    ->installMe(__DIR__)
    ->apply();
  }


  public function setUp() {
    parent::setUp();
    /* Create a test organization */
    $result = civicrm_api3('Contact', 'create', array(
      'contact_type' => $this->testOrganizationParams['contact_type'],
      'organization_name' => $this->testOrganizationParams['organization_name']
    ));

    $this->testOrganizationContactId = $result['id'];
    /* Create a test individual */
    $result = civicrm_api3('Contact', 'create', array(
      'contact_type' => $this->testIndividualParams['contact_type'],
      'first_name' => $this->testIndividualParams['first_name'],
      'last_name' => $this->testIndividualParams['last_name'],
      'employer_id' => $this->testOrganizationContactId
    ));

    $this->testIndividualContactId = $result['id'];
    /* For this test, we create several membership types */

    /* Create membership types */

    foreach ($this->testMembershipTypeParams as $k => $v){
      $this->testMembershipTypeParams[$k]['member_of_contact_id'] = $this->testIndividualContactId;
      $result = civicrm_api3('MembershipType', 'create', $this->testMembershipTypeParams[$k]);

      $this->membershipTypeIds[$k] = $result["id"];
    }
    
    /* Create a test membership for the individual */
    $result = civicrm_api3('Membership', 'create', array(
      'membership_type_id' => $this->membershipTypeIds['rolling_1_year'],
      'contact_id' => $this->testIndividualContactId
    ));

    $membershipId = $result["id"];

    $result = civicrm_api3('Membership', 'getsingle', array(
      'id' => $membershipId
    ));

    $this->testMembershipId = $result["id"];
  }

  public function tearDown() {
    parent::tearDown();
  }

  public function testCreateMembershipWithoutContribution(){
    /* Create 1 year rolling membership period detail */
    $joinDate = '20171231203200';
    $membershipStartDate = '20171231000000';
    $membershipEndDate = '20181230000000';
    $membershipTypeId = $this->membershipTypeIds['rolling_1_year'];
    $contributionAmount = 0;

    $membershipCreateResult = $this->createMembership($joinDate, $membershipTypeId, $membershipStartDate, $membershipEndDate, $contributionAmount);

    $membership = $membershipCreateResult['membership'];
    $membershipId = $membership->id; 

    $membershipPeriodDetails = $membershipCreateResult['periods'];

    $this->assertEquals(1, count($membershipPeriodDetails));
    $this->assertEquals(1, $membershipPeriodDetails[0]['number_of_periods']);
    $this->assertEquals('2017-12-31', $membershipPeriodDetails[0]['start_date']);
    $this->assertEquals('2018-12-30', $membershipPeriodDetails[0]['end_date']);
    $this->assertEquals(MembershipDuration::YEAR, $membershipPeriodDetails[0]['membership_duration_unit']);
    $this->assertEquals(1, $membershipPeriodDetails[0]['membership_duration_interval']);
    $this->assertEquals($this->testMembershipTypeParams['rolling_1_year']['name'], $membershipPeriodDetails[0]['membership_type_id.name']);
    $this->assertEquals(false,$membershipPeriodDetails[0]['shared_contribution']);
    $this->assertEquals(false, @$membershipPeriodDetails[0]['contribution_id.total_amount']);

    return $membershipId; 
  }

  public function testCreateMembershipWithContribution(){
    /* Create 1 year rolling membership period detail */
    $amount = 1000;
    $joinDate = '20171231203200';
    $membershipStartDate = '20171231000000';
    $membershipEndDate = '20181230000000';
    $membershipTypeId = $this->membershipTypeIds['rolling_1_year'];
    $contributionAmount = 1000;

    $membershipCreateResult = $this->createMembership($joinDate, $membershipTypeId, $membershipStartDate, $membershipEndDate, $contributionAmount);

    $membership = $membershipCreateResult['membership'];
    $membershipId = $membership->id;

    $membershipPeriodDetails = $membershipCreateResult['periods'];

    $this->assertEquals(1, count($membershipPeriodDetails));
    $this->assertEquals(1, $membershipPeriodDetails[0]['number_of_periods']);
    $this->assertEquals('2017-12-31', $membershipPeriodDetails[0]['start_date']);
    $this->assertEquals('2018-12-30', $membershipPeriodDetails[0]['end_date']);
    $this->assertEquals(MembershipDuration::YEAR, $membershipPeriodDetails[0]['membership_duration_unit']);
    $this->assertEquals(1, $membershipPeriodDetails[0]['membership_duration_interval']);
    $this->assertEquals($this->testMembershipTypeParams['rolling_1_year']['name'], $membershipPeriodDetails[0]['membership_type_id.name']);
    $this->assertEquals(false,$membershipPeriodDetails[0]['shared_contribution']);
    $this->assertEquals($amount, $membershipPeriodDetails[0]['contribution_id.total_amount']);
    return $membershipId; 
  }

  public function testPeriodDetailsRolling1Year1Term(){
    $membershipId = $this->testMembershipId;
    /* Create a new membership period */
    $amount = 1000;
    $contribution = $this->createContribution($this->testIndividualContactId, $amount);
    $contributionId = $contribution['id'];
    $numberOfTerms = 1;
    $membershipTypeId = $this->membershipTypeIds['rolling_1_year'];

    $startDate = "20181230000000";
    $endDate = "20191229000000";

    $membershipDurationUnit = MembershipDuration::YEAR;
    $membershipDurationInterval = 1;

    $membershipPeriodDetails = $this->createNewMembershipPeriodAndGetDetails($membershipId, $contributionId,$numberOfTerms,$membershipTypeId,$startDate, $endDate, $membershipDurationUnit, $membershipDurationInterval);

    $this->assertEquals(1, count($membershipPeriodDetails));
    $this->assertEquals(1, $membershipPeriodDetails[0]['number_of_periods']);
    $this->assertEquals('2018-12-30', $membershipPeriodDetails[0]['start_date']);
    $this->assertEquals('2019-12-29', $membershipPeriodDetails[0]['end_date']);
    $this->assertEquals(MembershipDuration::YEAR, $membershipPeriodDetails[0]['membership_duration_unit']);
    $this->assertEquals(1, $membershipPeriodDetails[0]['membership_duration_interval']);
    $this->assertEquals($this->testMembershipTypeParams['rolling_1_year']['name'], $membershipPeriodDetails[0]['membership_type_id.name']);
    $this->assertEquals(false,$membershipPeriodDetails[0]['shared_contribution']);
    $this->assertEquals($amount, $membershipPeriodDetails[0]['contribution_id.total_amount']);
  }


  public function testPeriodDetailsRolling1Year2Terms(){
    $membershipId = $this->testMembershipId;
    /* Create a new membership period */
    $amount = 1000;
    $contribution = $this->createContribution($this->testIndividualContactId, $amount);
    $contributionId = $contribution['id'];
    $numberOfTerms = 2;
    $membershipTypeId = $this->membershipTypeIds['rolling_1_year'];

    $startDate = "20181230000000";
    $endDate = "20201229000000";

    $membershipDurationUnit = MembershipDuration::YEAR;
    $membershipDurationInterval = 1;

    $membershipPeriodDetails = $this->createNewMembershipPeriodAndGetDetails($membershipId, $contributionId,$numberOfTerms,$membershipTypeId,$startDate, $endDate, $membershipDurationUnit, $membershipDurationInterval);

    $this->assertEquals(2, count($membershipPeriodDetails));
    
    $this->assertEquals(2, $membershipPeriodDetails[0]['number_of_periods']);
    $this->assertEquals('2018-12-30', $membershipPeriodDetails[0]['start_date']);
    $this->assertEquals('2019-12-29', $membershipPeriodDetails[0]['end_date']);
    $this->assertEquals(MembershipDuration::YEAR, $membershipPeriodDetails[0]['membership_duration_unit']);
    $this->assertEquals(1, $membershipPeriodDetails[0]['membership_duration_interval']);
    $this->assertEquals($this->testMembershipTypeParams['rolling_1_year']['name'], $membershipPeriodDetails[0]['membership_type_id.name']);
    $this->assertEquals(true,$membershipPeriodDetails[0]['shared_contribution']);
    $this->assertEquals(1000, $membershipPeriodDetails[0]['contribution_id.total_amount']);

    $this->assertEquals(2, $membershipPeriodDetails[1]['number_of_periods']);
    $this->assertEquals('2019-12-30', $membershipPeriodDetails[1]['start_date']);
    $this->assertEquals('2020-12-29', $membershipPeriodDetails[1]['end_date']);
    $this->assertEquals(MembershipDuration::YEAR, $membershipPeriodDetails[1]['membership_duration_unit']);
    $this->assertEquals(1, $membershipPeriodDetails[1]['membership_duration_interval']);
    $this->assertEquals($this->testMembershipTypeParams['rolling_1_year']['name'], $membershipPeriodDetails[1]['membership_type_id.name']);
    $this->assertEquals(true,$membershipPeriodDetails[1]['shared_contribution']);
    $this->assertEquals($amount, $membershipPeriodDetails[1]['contribution_id.total_amount']);
  }


  public function testPeriodDetailsFixed1Year1Term(){
    $membershipId = $this->testMembershipId;
    /* Create a new membership period */
    $amount = 1000;
    $contribution = $this->createContribution($this->testIndividualContactId, $amount);
    $contributionId = $contribution['id'];
    $numberOfTerms = 1;
    $membershipTypeId = $this->membershipTypeIds['fixed_1_year'];

    $startDate = "20181230000000";
    $endDate = "20191229000000";

    $membershipDurationUnit = MembershipDuration::YEAR;
    $membershipDurationInterval = 1;

    $membershipPeriodDetails = $this->createNewMembershipPeriodAndGetDetails($membershipId, $contributionId,$numberOfTerms,$membershipTypeId,$startDate, $endDate, $membershipDurationUnit, $membershipDurationInterval);

    $this->assertEquals(1, count($membershipPeriodDetails));
    
    $this->assertEquals(1, $membershipPeriodDetails[0]['number_of_periods']);
    $this->assertEquals('2018-12-30', $membershipPeriodDetails[0]['start_date']);
    $this->assertEquals('2019-12-29', $membershipPeriodDetails[0]['end_date']);
    $this->assertEquals(MembershipDuration::YEAR, $membershipPeriodDetails[0]['membership_duration_unit']);
    $this->assertEquals(1, $membershipPeriodDetails[0]['membership_duration_interval']);
    $this->assertEquals($this->testMembershipTypeParams['fixed_1_year']['name'], $membershipPeriodDetails[0]['membership_type_id.name']);
    $this->assertEquals(false,$membershipPeriodDetails[0]['shared_contribution']);
    $this->assertEquals($amount, $membershipPeriodDetails[0]['contribution_id.total_amount']);
  }

  public function testPeriodDetailsFixed1Year2Terms(){
    $membershipId = $this->testMembershipId;
    /* Create a new membership period */
    $amount = 1000;
    $contribution = $this->createContribution($this->testIndividualContactId, $amount);
    $contributionId = $contribution['id'];
    $numberOfTerms = 2;
    $membershipTypeId = $this->membershipTypeIds['fixed_1_year'];

    $startDate = "20181230000000";
    $endDate = "20201229000000";

    $membershipDurationUnit = MembershipDuration::YEAR;
    $membershipDurationInterval = 1;

    $membershipPeriodDetails = $this->createNewMembershipPeriodAndGetDetails($membershipId, $contributionId,$numberOfTerms,$membershipTypeId,$startDate, $endDate, $membershipDurationUnit, $membershipDurationInterval);

    $this->assertEquals(2, count($membershipPeriodDetails));
    
    $this->assertEquals(2, $membershipPeriodDetails[0]['number_of_periods']);
    $this->assertEquals('2018-12-30', $membershipPeriodDetails[0]['start_date']);
    $this->assertEquals('2019-12-29', $membershipPeriodDetails[0]['end_date']);
    $this->assertEquals(MembershipDuration::YEAR, $membershipPeriodDetails[0]['membership_duration_unit']);
    $this->assertEquals(1, $membershipPeriodDetails[0]['membership_duration_interval']);
    $this->assertEquals($this->testMembershipTypeParams['fixed_1_year']['name'], $membershipPeriodDetails[0]['membership_type_id.name']);
    $this->assertEquals(true,$membershipPeriodDetails[0]['shared_contribution']);
    $this->assertEquals($amount, $membershipPeriodDetails[0]['contribution_id.total_amount']);

    $this->assertEquals(2, $membershipPeriodDetails[1]['number_of_periods']);
    $this->assertEquals('2019-12-30', $membershipPeriodDetails[1]['start_date']);
    $this->assertEquals('2020-12-29', $membershipPeriodDetails[1]['end_date']);
    $this->assertEquals(MembershipDuration::YEAR, $membershipPeriodDetails[1]['membership_duration_unit']);
    $this->assertEquals(1, $membershipPeriodDetails[1]['membership_duration_interval']);
    $this->assertEquals($this->testMembershipTypeParams['fixed_1_year']['name'], $membershipPeriodDetails[1]['membership_type_id.name']);
    $this->assertEquals(true,$membershipPeriodDetails[1]['shared_contribution']);
    $this->assertEquals($amount, $membershipPeriodDetails[1]['contribution_id.total_amount']);
  }

  /**
   * Creates a Membership (including contribution) and membership period detail
   * @param  string $joinDate           yyyymmddhhiiss format
   * @param  int $membershipTypeId      Membership type ID
   * @param  [type] $startDate          yyyymmddhhiiss format
   * @param  [type] $endDate            yyyymmddhhiiss format
   * @param  string $contributionAmount Contribution amount
   * @return array                      Returns array with membership, contribution and period details
   */
  protected function createMembership($joinDate, $membershipTypeId, $startDate, $endDate, $contributionAmount=0){
    $retArr = array(
      'membership' => array(),
      'contribution' => array(),
      'periods' => array()
    );

    $membershipCreateParams = array(
      'total_amount' => $contributionAmount,
      'contact_id' => $this->testIndividualContactId,
      'financial_type_id' => $this->membershipDueFinancialTypeId, 
      'payment_instrument_id' => $this->cashPaymentInstrumentId,
      'contribution_status_id' => 1,
      'receive_date' => $joinDate,
      'processPriceSet' =>1,
      'action' => 1,
      'membership_type_id' => $membershipTypeId,
      'join_date' => $joinDate,
      'start_date' => $startDate,
      'end_date' => $endDate
    );

    $ids = array();
    $result = CRM_Member_BAO_Membership::create($membershipCreateParams,$ids, TRUE);

    $retArr['membership'] = $result;

    $membershipId = $result->id;
    $contributionId = null;

    if ($contributionAmount>0){
      $contribution =civicrm_api3('Contribution', 'getsingle', array(
        'contact_id' => $this->testIndividualContactId,
        'options' => array('limit' => 1, 'sort' => "id DESC"),
      ));
      $retArr['contribution'] = $result;
      $contributionId = $contribution["contribution_id"];
    }

    $numberOfTerms = 1;
    $membershipDurationUnit = MembershipDuration::YEAR;
    $membershipDurationInterval = 1;

    $membershipPeriodDetails = $this->createNewMembershipPeriodAndGetDetails($membershipId, $contributionId, $numberOfTerms,$membershipTypeId,$startDate, $endDate, $membershipDurationUnit, $membershipDurationInterval);
    
    $retArr['periods'] = $membershipPeriodDetails;
    return $retArr;
  }

  private function createContribution($contactId, $contributionAmount){
    $financialType = $this->membershipDueFinancialTypeId;
    $result = civicrm_api3('Contribution', 'create', array(
      'financial_type_id' => $financialType,
      'total_amount' => $contributionAmount,
      'contact_id' => $this->testIndividualContactId
    ));

    return $result;
  }

  private function createNewMembershipPeriodAndGetDetails($membershipId, $contributionId=null, $numberOfTerms, $membershipTypeId, $startDate, $endDate, $membershipDurationUnit, $membershipDurationInterval){

    $currencyCode = "USD";
    /* Create the membership period */
    $result = civicrm_api3('MembershipPeriodDetail', 'create', array(
      'membership_id' => $membershipId,
      'contribution_id' => $contributionId,
      'currency_code' => $currencyCode,
      'membership_type_id' => $membershipTypeId,
      'number_of_periods' => $numberOfTerms,
      'start_date' => $startDate,
      'end_date' => $endDate,
      'membership_duration_unit' => $membershipDurationUnit,
      'membership_duration_interval' => $membershipDurationInterval
    ));

    /* Retrieve membership period details */
    $result = civicrm_api3('MembershipPeriodDetail', 'getformember', array(
      'membership_id' => $membershipId,
    ));

    $membershipPeriodDetails = $result['values'];
    return $membershipPeriodDetails;
  }
}
