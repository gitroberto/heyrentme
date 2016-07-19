
ALTER TABLE `equipment`
	ALTER `inquiry_cc` DROP DEFAULT;
ALTER TABLE `equipment`
	CHANGE COLUMN `inquiry_cc` `inquiry_email` VARCHAR(100) NULL AFTER `test_drive`;


ALTER TABLE `talent`
	ALTER `inquiry_cc` DROP DEFAULT;
ALTER TABLE `talent`
	CHANGE COLUMN `inquiry_cc` `inquiry_email` VARCHAR(100) NULL AFTER `featured`;

