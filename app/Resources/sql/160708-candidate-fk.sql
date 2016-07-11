
ALTER TABLE `candidate`
	DROP FOREIGN KEY `FK_candidate_subcategory`;
ALTER TABLE `candidate`
	CHANGE COLUMN `subcategory_id` `subcategory_id` INT(11) NULL DEFAULT '0' AFTER `id`,
	ADD CONSTRAINT `FK_candidate_subcategory` FOREIGN KEY (`subcategory_id`) REFERENCES `subcategory` (`id`) ON UPDATE CASCADE ON DELETE SET NULL;
