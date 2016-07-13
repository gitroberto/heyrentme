
ALTER TABLE `equipment`
	ADD COLUMN `inquiry_cc` SMALLINT(6) NOT NULL DEFAULT '0' AFTER `test_drive`;
ALTER TABLE `talent`
	ADD COLUMN `inquiry_cc` SMALLINT(6) NOT NULL DEFAULT '0' AFTER `featured`;
