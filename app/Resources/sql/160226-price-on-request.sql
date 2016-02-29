
ALTER TABLE `talent`
	ALTER `price` DROP DEFAULT;
ALTER TABLE `talent`
	CHANGE COLUMN `price` `price` DECIMAL(10,2) NULL AFTER `description`,
	ADD COLUMN `request_price` SMALLINT(6) NOT NULL DEFAULT '0' AFTER `accept`;
ALTER TABLE `talent_inquiry`
	ALTER `price` DROP DEFAULT;
ALTER TABLE `talent_inquiry`
	CHANGE COLUMN `price` `price` DECIMAL(10,2) NULL AFTER `to_at`;
