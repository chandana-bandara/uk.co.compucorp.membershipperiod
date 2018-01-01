DROP TABLE IF EXISTS `civicrm_membership_period_detail`;


-- /*******************************************************
-- *
-- * civicrm_membership_period_detail
-- *
-- * Records the membership period related information (membership id, contribution id, number of terms, start date, end date, membership duration unit, membership duration interval, time stamp) when creating or renewing a membership.
-- *
-- *******************************************************/
CREATE TABLE `civicrm_membership_period_detail` (
	`id` int unsigned NOT NULL AUTO_INCREMENT  COMMENT 'Unique Membership Period Detail ID',
	`membership_id` int unsigned NOT NULL   COMMENT 'FK to Membership',
	`currency_code` text NOT NULL   COMMENT 'Currency code e.g. USD',
	`contribution_id` int unsigned NULL  DEFAULT NULL COMMENT 'FK to Contribution',
	`membership_type_id` int unsigned NULL  DEFAULT NULL COMMENT 'FK to Membership Type',
	`number_of_periods` int unsigned NOT NULL   COMMENT 'Number of membership periods this membership commencement/renewal corresponds to',
	`start_date` date NOT NULL   COMMENT 'Starting date of the related membership period(s)',
	`end_date` date NOT NULL   COMMENT 'Ending date of the related membership period(s)',
	`membership_duration_unit` text NOT NULL   COMMENT 'Membership duration unit. E.g. day, week, month, year',
	`membership_duration_interval` int unsigned NOT NULL   COMMENT 'Membership duration interval. E.g. 1(year), 60(days)',
	`created_at` timestamp NOT NULL   COMMENT 'Timestamp of the operation' 
	,
	PRIMARY KEY (`id`),
	CONSTRAINT FK_civicrm_membership_period_detail_membership_id FOREIGN KEY (`membership_id`) REFERENCES `civicrm_membership`(`id`) ON DELETE CASCADE,
	CONSTRAINT FK_civicrm_membership_period_detail_contribution_id FOREIGN KEY (`contribution_id`) REFERENCES `civicrm_contribution`(`id`) ON DELETE SET NULL,
	CONSTRAINT FK_civicrm_membership_period_detail_membership_type_id FOREIGN KEY (`membership_type_id`) REFERENCES `civicrm_membership_type`(`id`) ON DELETE SET NULL  
)  ENGINE=InnoDB DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci  ;

