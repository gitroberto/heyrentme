
ALTER TABLE `talent`
	ALTER `price` DROP DEFAULT;
ALTER TABLE `talent`
	CHANGE COLUMN `price` `price` DECIMAL(10,2) NULL AFTER `description`,
	ADD COLUMN `request_price` SMALLINT(6) NOT NULL DEFAULT '0' AFTER `accept`;
