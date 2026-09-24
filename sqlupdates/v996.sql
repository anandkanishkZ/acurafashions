-- =========================================================================
-- v996: Dynamic OTP system (Bullet SMS gateway) for login & registration
-- Adds the tables the existing otp_system addon code already references
-- (otp_configurations, sms_templates) but which were never shipped, plus a
-- new otp_codes table for expiry/attempt-limited OTP verification, and
-- seeds a real `addons` row so the existing on/off toggle controls
-- something real.
-- =========================================================================

CREATE TABLE `otp_codes` (
  `id` int(11) NOT NULL,
  `phone` varchar(30) NOT NULL,
  `code` varchar(10) NOT NULL,
  `purpose` varchar(30) NOT NULL DEFAULT 'login',
  `attempts` int(11) NOT NULL DEFAULT 0,
  `max_attempts` int(11) NOT NULL DEFAULT 5,
  `expires_at` timestamp NULL DEFAULT NULL,
  `verified_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

ALTER TABLE `otp_codes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `otp_codes_phone_purpose_index` (`phone`, `purpose`);

ALTER TABLE `otp_codes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

CREATE TABLE `sms_templates` (
  `id` int(11) NOT NULL,
  `identifier` varchar(191) NOT NULL,
  `sms_body` text NOT NULL,
  `template_id` varchar(191) DEFAULT NULL,
  `status` tinyint(4) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

ALTER TABLE `sms_templates`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `sms_templates_identifier_unique` (`identifier`);

ALTER TABLE `sms_templates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

INSERT INTO `sms_templates` (`identifier`, `sms_body`, `template_id`, `status`, `created_at`, `updated_at`) VALUES
('phone_number_verification', 'Your [[site_name]] verification code is [[code]]. It expires in [[expiry_minutes]] minutes.', NULL, 1, NOW(), NOW()),
('login_otp', 'Your [[site_name]] login code is [[code]]. It expires in [[expiry_minutes]] minutes.', NULL, 1, NOW(), NOW()),
('order_placement', 'Thank you for your order at [[site_name]]. Your order code is [[order_code]].', NULL, 1, NOW(), NOW()),
('delivery_status_change', 'Your [[site_name]] order [[order_code]] status is now: [[status]].', NULL, 1, NOW(), NOW()),
('payment_status_change', 'Your [[site_name]] order [[order_code]] payment status is now: [[status]].', NULL, 1, NOW(), NOW()),
('assign_delivery_boy', 'A delivery agent has been assigned to your [[site_name]] order [[order_code]].', NULL, 1, NOW(), NOW());

CREATE TABLE `otp_configurations` (
  `id` int(11) NOT NULL,
  `type` varchar(100) NOT NULL,
  `value` tinyint(4) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

ALTER TABLE `otp_configurations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `otp_configurations_type_unique` (`type`);

ALTER TABLE `otp_configurations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

INSERT INTO `otp_configurations` (`type`, `value`, `created_at`, `updated_at`) VALUES
('bullet_sms', 1, NOW(), NOW());

-- Real `otp_system` addon row so the existing addon_is_activated() toggle
-- controls something real. Off by default; admin turns it on explicitly.
INSERT INTO `addons` (`name`, `unique_identifier`, `version`, `activated`, `created_at`, `updated_at`)
SELECT 'OTP System', 'otp_system', '1.0', 0, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM `addons` WHERE `unique_identifier` = 'otp_system');

-- Business settings: Bullet SMS credentials (admin-configurable, empty by
-- default) and OTP behaviour tuning.
INSERT INTO `business_settings` (`type`, `value`, `created_at`, `updated_at`)
SELECT * FROM (SELECT 'otp_bullet_sms_token' AS type, '' AS value, NOW() AS created_at, NOW() AS updated_at) AS tmp
WHERE NOT EXISTS (SELECT 1 FROM `business_settings` WHERE `type` = 'otp_bullet_sms_token');

INSERT INTO `business_settings` (`type`, `value`, `created_at`, `updated_at`)
SELECT * FROM (SELECT 'otp_bullet_sms_sender_id' AS type, '' AS value, NOW() AS created_at, NOW() AS updated_at) AS tmp
WHERE NOT EXISTS (SELECT 1 FROM `business_settings` WHERE `type` = 'otp_bullet_sms_sender_id');

INSERT INTO `business_settings` (`type`, `value`, `created_at`, `updated_at`)
SELECT * FROM (SELECT 'otp_expiry_minutes' AS type, '5' AS value, NOW() AS created_at, NOW() AS updated_at) AS tmp
WHERE NOT EXISTS (SELECT 1 FROM `business_settings` WHERE `type` = 'otp_expiry_minutes');

INSERT INTO `business_settings` (`type`, `value`, `created_at`, `updated_at`)
SELECT * FROM (SELECT 'otp_max_attempts' AS type, '5' AS value, NOW() AS created_at, NOW() AS updated_at) AS tmp
WHERE NOT EXISTS (SELECT 1 FROM `business_settings` WHERE `type` = 'otp_max_attempts');

INSERT INTO `business_settings` (`type`, `value`, `created_at`, `updated_at`)
SELECT * FROM (SELECT 'otp_resend_cooldown_seconds' AS type, '60' AS value, NOW() AS created_at, NOW() AS updated_at) AS tmp
WHERE NOT EXISTS (SELECT 1 FROM `business_settings` WHERE `type` = 'otp_resend_cooldown_seconds');

INSERT INTO `business_settings` (`type`, `value`, `created_at`, `updated_at`)
SELECT * FROM (SELECT 'otp_registration_enabled' AS type, '0' AS value, NOW() AS created_at, NOW() AS updated_at) AS tmp
WHERE NOT EXISTS (SELECT 1 FROM `business_settings` WHERE `type` = 'otp_registration_enabled');

INSERT INTO `business_settings` (`type`, `value`, `created_at`, `updated_at`)
SELECT * FROM (SELECT 'otp_login_enabled' AS type, '0' AS value, NOW() AS created_at, NOW() AS updated_at) AS tmp
WHERE NOT EXISTS (SELECT 1 FROM `business_settings` WHERE `type` = 'otp_login_enabled');
