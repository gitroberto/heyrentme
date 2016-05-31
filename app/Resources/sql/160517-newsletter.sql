
CREATE TABLE `subscriber` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`email` VARCHAR(100) NOT NULL,
	`token` VARCHAR(100) NOT NULL,
	`confirmed` SMALLINT(6) NOT NULL DEFAULT '0',
	`unsubscribed` SMALLINT(6) NOT NULL DEFAULT '0',
	`created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`modified_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	`unsubscribed_at` TIMESTAMP NULL DEFAULT NULL,
	PRIMARY KEY (`id`)
)
ENGINE=InnoDB
;

ALTER TABLE `discount_code`
	ADD COLUMN `subscriber_id` INT NULL DEFAULT NULL AFTER `user_id`;

ALTER TABLE `discount_code`
	ADD CONSTRAINT `FK_discount_code_subscriber` FOREIGN KEY (`subscriber_id`) REFERENCES `subscriber` (`id`) ON UPDATE CASCADE;