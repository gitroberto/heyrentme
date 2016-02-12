
ALTER TABLE `equipment_image`
	ADD COLUMN `main` SMALLINT NOT NULL DEFAULT '0' AFTER `image_id`;
