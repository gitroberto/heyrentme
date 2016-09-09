
CREATE TABLE `talent_tariff` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`talent_id` INT(11) NOT NULL,
	`type` INT(11) NOT NULL,
	`price` DECIMAL(10,2) NULL DEFAULT NULL,
	`min_num` INT(11) NULL DEFAULT NULL,
	`discount` SMALLINT(6) NULL DEFAULT NULL,
	`discount_min_num` INT(11) NULL DEFAULT NULL,
	`discount_price` DECIMAL(10,2) NULL DEFAULT NULL,
	`duration` INT(11) NULL DEFAULT NULL,
	`request_price` SMALLINT(6) NULL DEFAULT NULL,
	`position` INT(11) NULL DEFAULT NULL,
	`created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`modified_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	PRIMARY KEY (`id`),
	INDEX `FK_talent_tariff_talent` (`talent_id`),
	CONSTRAINT `FK_talent_tariff_talent` FOREIGN KEY (`talent_id`) REFERENCES `talent` (`id`) ON UPDATE CASCADE ON DELETE CASCADE
)
COLLATE='latin1_general_ci'
ENGINE=InnoDB
;
