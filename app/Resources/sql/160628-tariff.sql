
ALTER TABLE `talent_inquiry`
        CHANGE COLUMN `from_at` `from_at` DATETIME NULL AFTER `message`,
	CHANGE COLUMN `to_at` `to_at` DATETIME NULL AFTER `from_at`,
	ADD COLUMN `requestPrice` SMALLINT NULL AFTER `uuid`,
	ADD COLUMN `num` INT NULL AFTER `request_price`;

ALTER TABLE `talent_booking`
	ALTER `price` DROP DEFAULT;
ALTER TABLE `talent_booking`
	CHANGE COLUMN `price` `price` DECIMAL(10,2) NULL AFTER `modified_at`;
ALTER TABLE `talent_booking`
	CHANGE COLUMN `total_price` `total_price` DECIMAL(10,2) NULL AFTER `price`;

