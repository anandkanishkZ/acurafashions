-- =========================================================================
-- v998: Offline / Manual Payment Methods system
-- Adds the table the ManualPaymentMethod model, checkout payment_info
-- partial, and CheckoutController's already-working manual-payment branch
-- all reference but which was never shipped, plus the two orders columns
-- (manual_payment, manual_payment_data) that same working code reads and
-- writes. Also seeds a real offline_payment addon row (off by default).
-- =========================================================================

CREATE TABLE `manual_payment_methods` (
  `id` int(11) NOT NULL,
  `type` varchar(30) NOT NULL DEFAULT 'custom',
  `heading` varchar(255) NOT NULL,
  `photo` int(11) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `bank_info` longtext DEFAULT NULL,
  `status` tinyint(4) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

ALTER TABLE `manual_payment_methods`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `manual_payment_methods`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `orders`
  ADD COLUMN `manual_payment` tinyint(4) NOT NULL DEFAULT 0 AFTER `payment_status`,
  ADD COLUMN `manual_payment_data` longtext DEFAULT NULL AFTER `manual_payment`;

-- Real `offline_payment` addon row so the existing addon_is_activated()
-- gates (checkout UI, admin sidenav, order-details "Make Payment" button)
-- have something real to check. Off by default; admin turns it on once
-- at least one payment method is configured.
INSERT INTO `addons` (`name`, `unique_identifier`, `version`, `activated`, `created_at`, `updated_at`)
SELECT 'Offline Payment System', 'offline_payment', '1.0', 0, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM `addons` WHERE `unique_identifier` = 'offline_payment');
