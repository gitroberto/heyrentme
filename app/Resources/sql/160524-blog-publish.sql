
ALTER TABLE `blog`
	ADD COLUMN `published` SMALLINT NOT NULL DEFAULT '0' AFTER `uuid`;

-- update blog set published = 1;
