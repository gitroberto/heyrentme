
ALTER TABLE `equipment`
	ADD COLUMN `price_week` DECIMAL(10,2) NULL AFTER `uuid`,
	ADD COLUMN `price_month` DECIMAL(10,2) NULL AFTER `price_week`;
