

CREATE TABLE `equipment_subcategory` (
	`equipment_id` INT NOT NULL,
	`subcategory_id` INT NOT NULL,
	CONSTRAINT `FK_this_equipment` FOREIGN KEY (`equipment_id`) REFERENCES `equipment` (`id`) ON UPDATE CASCADE ON DELETE CASCADE,
	CONSTRAINT `FK_this_subcategory` FOREIGN KEY (`subcategory_id`) REFERENCES `subcategory` (`id`) ON UPDATE CASCADE ON DELETE CASCADE
)
COLLATE='latin1_general_ci'
ENGINE=InnoDB
;

insert into equipment_subcategory (equipment_id, subcategory_id)
	select id, subcategory_id from equipment;

ALTER TABLE `equipment`
	DROP COLUMN `subcategory_id`,
	DROP FOREIGN KEY `FK_equipment_subcategory`;


CREATE TABLE `talent_subcategory` (
	`talent_id` INT NOT NULL,
	`subcategory_id` INT NOT NULL,
	CONSTRAINT `FK_this_talent` FOREIGN KEY (`talent_id`) REFERENCES `talent` (`id`) ON UPDATE CASCADE ON DELETE CASCADE,
	CONSTRAINT `FK_this_subcategory2` FOREIGN KEY (`subcategory_id`) REFERENCES `subcategory` (`id`) ON UPDATE CASCADE ON DELETE CASCADE
)
COLLATE='latin1_general_ci'
ENGINE=InnoDB
;

insert into talent_subcategory (talent_id, subcategory_id)
	select id, subcategory_id from talent;

ALTER TABLE `talent`
	DROP COLUMN `subcategory_id`,
	DROP FOREIGN KEY `talent_ibfk_2`;
