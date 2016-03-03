
ALTER TABLE `image`
	ADD COLUMN `thumbnail_path` VARCHAR(128) NULL DEFAULT NULL AFTER `original_path`;
