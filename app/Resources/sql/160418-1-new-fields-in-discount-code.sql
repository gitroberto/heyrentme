ALTER TABLE `discount_code`
	ADD COLUMN `expires_at` TIMESTAMP NULL DEFAULT NULL AFTER `user_id`,
	ADD COLUMN `value` INT not NULL DEFAULT '5' AFTER `expires_at`;