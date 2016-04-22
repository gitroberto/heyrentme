
ALTER TABLE `equipment_question`
	ALTER `user_id` DROP DEFAULT;
ALTER TABLE `equipment_question`
	CHANGE COLUMN `user_id` `user_id` INT(11) NULL AFTER `equipment_id`,
	ADD COLUMN `name` VARCHAR(100) NULL AFTER `user_id`,
	ADD COLUMN `email` VARCHAR(100) NULL AFTER `name`;

ALTER TABLE `talent_question`
	ALTER `user_id` DROP DEFAULT;
ALTER TABLE `talent_question`
	CHANGE COLUMN `user_id` `user_id` INT(11) NULL AFTER `talent_id`,
	ADD COLUMN `name` VARCHAR(100) NULL AFTER `user_id`,
	ADD COLUMN `email` VARCHAR(100) NULL AFTER `name`;
