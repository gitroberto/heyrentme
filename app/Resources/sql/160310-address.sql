
ALTER TABLE `equipment`
	ADD COLUMN `addr_flat_number` VARCHAR(16) NULL DEFAULT NULL AFTER `addr_number`;
ALTER TABLE `talent`
	ADD COLUMN `addr_flat_number` VARCHAR(16) NULL DEFAULT NULL AFTER `addr_number`;
ALTER TABLE `fos_user`
	ADD COLUMN `addr_street` VARCHAR(128) NULL DEFAULT NULL AFTER `third_day_email_sent_at`,
	ADD COLUMN `addr_number` VARCHAR(16) NULL DEFAULT NULL AFTER `addr_street`,
	ADD COLUMN `addr_flat_number` VARCHAR(16) NULL DEFAULT NULL AFTER `addr_number`,
	ADD COLUMN `addr_postcode` VARCHAR(4) NULL DEFAULT NULL AFTER `addr_flat_number`,
	ADD COLUMN `addr_place` VARCHAR(128) NULL DEFAULT NULL AFTER `addr_postcode`;
