
ALTER TABLE `equipment`
	ADD COLUMN `featured` SMALLINT(6) NULL DEFAULT '0' AFTER `showcase_equipment`;

ALTER TABLE `talent`
	ADD COLUMN `featured` SMALLINT(6) NULL DEFAULT '0' AFTER `showcase_talent`;
