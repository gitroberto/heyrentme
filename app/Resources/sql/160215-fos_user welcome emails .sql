ALTER TABLE `fos_user`
	ADD COLUMN `second_day_email_sent_at` TIMESTAMP NULL AFTER `rating`,
	ADD COLUMN `third_day_email_sent_at` TIMESTAMP NULL AFTER `second_day_email_sent_at`;
