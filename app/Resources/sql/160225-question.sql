
CREATE TABLE `equipment_question` (
	`id` INT NOT NULL AUTO_INCREMENT,
	`equipment_id` INT NOT NULL,
	`user_id` INT NOT NULL,
	`message` TEXT NOT NULL,
	`reply` TEXT NULL,
	`created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`modified_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	PRIMARY KEY (`id`),
	CONSTRAINT `FK_equipment_question_equipment` FOREIGN KEY (`equipment_id`) REFERENCES `equipment` (`id`) ON UPDATE CASCADE ON DELETE NO ACTION,
	CONSTRAINT `FK_equipment_question_fos_user` FOREIGN KEY (`user_id`) REFERENCES `fos_user` (`id`) ON UPDATE CASCADE ON DELETE NO ACTION
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB
;

CREATE TABLE `talent_question` (
	`id` INT NOT NULL AUTO_INCREMENT,
	`talent_id` INT NOT NULL,
	`user_id` INT NOT NULL,
	`message` TEXT NOT NULL,
	`reply` TEXT NULL,
	`created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`modified_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	PRIMARY KEY (`id`),
	CONSTRAINT `FK_talent_question_talent` FOREIGN KEY (`talent_id`) REFERENCES `talent` (`id`) ON UPDATE CASCADE ON DELETE NO ACTION,
	CONSTRAINT `FK_talent_question_fos_user` FOREIGN KEY (`user_id`) REFERENCES `fos_user` (`id`) ON UPDATE CASCADE ON DELETE NO ACTION
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB
;
