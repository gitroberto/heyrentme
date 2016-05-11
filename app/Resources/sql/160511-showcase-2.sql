
ALTER TABLE `equipment`
	CHANGE COLUMN `showcase_category` `showcase_equipment` SMALLINT(6) NULL DEFAULT '0' AFTER `showcase_start`;
ALTER TABLE `talent`
	CHANGE COLUMN `showcase_category` `showcase_talent` SMALLINT(6) NULL DEFAULT '0' AFTER `showcase_start`;
