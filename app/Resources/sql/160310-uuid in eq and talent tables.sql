ALTER TABLE `equipment`
	ADD COLUMN `uuid` CHAR(36) NULL DEFAULT null AFTER `accept`;

ALTER TABLE `talent`
	ADD COLUMN `uuid` CHAR(36) NULL DEFAULT null AFTER `accept`;

