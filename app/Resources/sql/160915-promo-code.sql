
CREATE TABLE `promo_code` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`status` SMALLINT(6) NOT NULL,
	`code` VARCHAR(50) NOT NULL COLLATE 'latin1_general_ci',
	`value` INT(11) NOT NULL,
	`user_id` INT(11) NULL DEFAULT NULL,
	`expires_at` DATETIME NOT NULL,
	`created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`modified_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	PRIMARY KEY (`id`),
	INDEX `FK__fos_user` (`user_id`),
	CONSTRAINT `FK__fos_user` FOREIGN KEY (`user_id`) REFERENCES `fos_user` (`id`)
)
COLLATE='latin1_general_ci'
ENGINE=InnoDB
AUTO_INCREMENT=1
;

ALTER TABLE `equipment_booking`
	ADD COLUMN `promo_code_id` INT(11) NULL DEFAULT NULL AFTER `discount_code_id`,
	ADD CONSTRAINT `FK_equipment_booking_promo_code` FOREIGN KEY (`promo_code_id`) REFERENCES `promo_code` (`id`) ON UPDATE CASCADE ON DELETE NO ACTION;

ALTER TABLE `talent_booking`
	ADD COLUMN `promo_code_id` INT(11) NULL DEFAULT NULL AFTER `discount_code_id`,
	ADD CONSTRAINT `FK_talent_booking_promo_code` FOREIGN KEY (`promo_code_id`) REFERENCES `promo_code` (`id`) ON UPDATE CASCADE ON DELETE NO ACTION;
