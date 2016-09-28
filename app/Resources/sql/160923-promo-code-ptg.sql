
ALTER TABLE `promo_code`
	ADD COLUMN `type` SMALLINT(6) NOT NULL AFTER `status`;
