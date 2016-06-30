
ALTER TABLE `talent_inquiry`
        CHANGE COLUMN `from_at` `from_at` DATETIME NULL AFTER `message`,
	CHANGE COLUMN `to_at` `to_at` DATETIME NULL AFTER `from_at`,
	ADD COLUMN `requestPrice` SMALLINT NULL AFTER `uuid`,
	ADD COLUMN `num` INT NULL AFTER `request_price`;
