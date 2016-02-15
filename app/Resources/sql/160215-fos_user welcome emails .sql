ALTER TABLE `fos_user`
	ADD COLUMN `second_day_email_sent_at` TIMESTAMP NULL AFTER `rating`,
	ADD COLUMN `third_day_email_sent_at` TIMESTAMP NULL AFTER `second_day_email_sent_at`;
SELECT `DEFAULT_COLLATION_NAME` FROM `information_schema`.`SCHEMATA` WHERE `SCHEMA_NAME`='heyrentme';
SHOW TABLE STATUS FROM `heyrentme`;
SHOW FUNCTION STATUS WHERE `Db`='heyrentme';
SHOW PROCEDURE STATUS WHERE `Db`='heyrentme';
SHOW TRIGGERS FROM `heyrentme`;
SELECT *, EVENT_SCHEMA AS `Db`, EVENT_NAME AS `Name` FROM information_schema.`EVENTS` WHERE `EVENT_SCHEMA`='heyrentme';
SHOW CREATE TABLE `heyrentme`.`fos_user`;
/* £adowanie sesji "SebaServer" */
SHOW CREATE TABLE `heyrentme`.`fos_user`;