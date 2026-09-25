-- Icons for the in-house addons on the Addons page (paths are relative to public/, see static_asset()).
UPDATE `addons` SET `image` = 'assets/img/addons/otp_system.svg' WHERE `unique_identifier` = 'otp_system';
UPDATE `addons` SET `image` = 'assets/img/addons/offline_payment.svg' WHERE `unique_identifier` = 'offline_payment';
