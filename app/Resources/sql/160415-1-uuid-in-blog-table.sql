ALTER TABLE `blog`
	ADD COLUMN `uuid` CHAR(36) NULL DEFAULT NULL AFTER `modified_at`;
