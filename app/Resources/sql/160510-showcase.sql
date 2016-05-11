
ALTER TABLE `equipment`
	ADD COLUMN `showcase_start` SMALLINT(6) NULL DEFAULT '0' AFTER `service`,
	ADD COLUMN `showcase_category` SMALLINT(6) NULL DEFAULT '0' AFTER `showcase_start`;

ALTER TABLE `talent`
	ADD COLUMN `showcase_start` SMALLINT(6) NULL DEFAULT '0' AFTER `request_price`,
	ADD COLUMN `showcase_category` SMALLINT(6) NULL DEFAULT '0' AFTER `showcase_start`;
