ALTER TABLE `talent`
	CHANGE COLUMN `description` `description` VARCHAR(10000) NULL DEFAULT NULL AFTER `name`;
	
ALTER TABLE `equipment`
	CHANGE COLUMN `description` `description` VARCHAR(10000) NULL DEFAULT NULL AFTER `name`;