
ALTER TABLE `category`
	ADD COLUMN `big_image_id` INT NULL AFTER `image_id`,
	ADD CONSTRAINT `FK_category_image_2` FOREIGN KEY (`big_image_id`) REFERENCES `image` (`id`) ON UPDATE CASCADE ON DELETE SET NULL;
