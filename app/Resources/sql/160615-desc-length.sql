
ALTER TABLE `equipment`
	CHANGE COLUMN `description` `description` VARCHAR(2500) NULL DEFAULT NULL AFTER `name`;

ALTER TABLE `talent`
	CHANGE COLUMN `description` `description` VARCHAR(2500) NULL DEFAULT NULL AFTER `name`;
