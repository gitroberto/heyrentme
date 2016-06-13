
ALTER TABLE `equipment`
	CHANGE COLUMN `price` `price` DECIMAL(10,2) NULL AFTER `description`;
ALTER TABLE `equipment_inquiry`
	CHANGE COLUMN `price` `price` DECIMAL(10,2) NULL AFTER `to_at`;
ALTER TABLE `equipment_booking`
	CHANGE COLUMN `price` `price` DECIMAL(10,2) NULL AFTER `modified_at`;
ALTER TABLE `equipment_booking`
	CHANGE COLUMN `total_price` `total_price` DECIMAL(10,2) NULL AFTER `deposit`;
