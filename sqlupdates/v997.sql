-- =========================================================================
-- v997: Fix phone-only users being stuck in a verification redirect loop
-- Adds users.phone_verified_at, which the `verified` route middleware and
-- VerificationController now check for phone-only accounts (email is null)
-- instead of always requiring email_verified_at, which such accounts can
-- never satisfy.
-- =========================================================================

ALTER TABLE `users`
  ADD COLUMN `phone_verified_at` timestamp NULL DEFAULT NULL AFTER `email_verified_at`;

-- Backfill: any phone-only account that has already cleared its
-- verification_code (i.e. already completed OTP verification under the
-- old, broken flow) is treated as verified now, so existing users are not
-- forced to re-verify.
UPDATE `users`
SET `phone_verified_at` = `updated_at`
WHERE `email` IS NULL
  AND `phone` IS NOT NULL
  AND `verification_code` IS NULL
  AND `phone_verified_at` IS NULL;
