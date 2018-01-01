<?php

use CRM_Membershipperiod_ExtensionUtil as E;

/**
 * MembershipPeriodDetail.create API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 * @return void
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC/API+Architecture+Standards
 */
function _civicrm_api3_membership_period_detail_create_spec(&$spec) {
  $spec['membership_id']['api.required'] = 1;
  $spec['number_of_periods']['api.required'] = 1;
  $spec['start_date']['api.required'] = 1;
  $spec['end_date']['api.required'] = 1;
  $spec['membership_duration_unit']['api.required'] = 1;
  $spec['membership_duration_interval']['api.required'] = 1;
  $spec['membership_type_id']['api.required'] = 1;
  $spec['currency_code']['api.required'] = 1;
}

/**
 * MembershipPeriodDetail.create API
 *
 * @param array $params
 * @return array API result descriptor
 * @throws API_Exception
 */
function civicrm_api3_membership_period_detail_create($params) {
	return _civicrm_api3_basic_create(_civicrm_api3_get_BAO(__FUNCTION__), $params);
}

/**
 * MembershipPeriodDetail.delete API
 *
 * @param array $params
 * @return array API result descriptor
 * @throws API_Exception
 */
function civicrm_api3_membership_period_detail_delete($params) {
	return _civicrm_api3_basic_delete(_civicrm_api3_get_BAO(__FUNCTION__), $params);
}

/**
 * MembershipPeriodDetail.get API
 *
 * @param array $params
 * @return array API result descriptor
 * @throws API_Exception
 */
function civicrm_api3_membership_period_detail_get($params) {
	return _civicrm_api3_basic_get(_civicrm_api3_get_BAO(__FUNCTION__), $params);
}