<?php
use CRM_Membershipperiod_ExtensionUtil as E;
use CRM_Membershipperiod_BAO_MembershipPeriodMembershipDuration as MembershipDuration;

class CRM_Membershipperiod_BAO_MembershipPeriodDetail extends CRM_Membershipperiod_DAO_MembershipPeriodDetail {

  public static $lifetimeMembershipEndDate = "9999-12-31";
  
  /**
   * Create a new MembershipPeriodDetail based on array-data
   *
   * @param array $params key-value pairs
   * @return CRM_Membershipperiod_DAO_MembershipPeriodDetail|NULL
   */
  public static function create($params) {
    $className = 'CRM_Membershipperiod_DAO_MembershipPeriodDetail';
    $entityName = 'MembershipPeriodDetail';

    /* If the membership duration unit is lifetime, the end date of the membership period should be recorded as */
    if ($params['membership_duration_unit'] == MembershipDuration::LIFETIME ){
      $params['end_date'] = self::$lifetimeMembershipEndDate;
    }

    $hook = empty($params['id']) ? 'create' : 'edit';
    CRM_Utils_Hook::pre($hook, $entityName, CRM_Utils_Array::value('id', $params), $params);

    $instance = new $className();
    $instance->copyValues($params);
    $instance->save();

    CRM_Utils_Hook::post($hook, $entityName, $instance->id, $instance);
    return $instance;
  }
}
