
ALTER TABLE `equipment`
	ALTER `value` DROP DEFAULT,
	ALTER `deposit` DROP DEFAULT;
ALTER TABLE `equipment`
	CHANGE COLUMN `value` `value` DECIMAL(10,2) NULL AFTER `price`,
	CHANGE COLUMN `deposit` `deposit` DECIMAL(10,2) NULL AFTER `discount`,
	ADD COLUMN `service` SMALLINT NULL DEFAULT '0' AFTER `price_month`;
