CREATE TABLE IF NOT EXISTS `video` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` int(11) NOT NULL,
  `video_id` varchar(20) NOT NULL,
  `embed_url` varchar(100) NOT NULL,
  `original_url` varchar(100) NOT NULL,
  `thumbnail_url` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modified_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

/*!40000 ALTER TABLE `video` DISABLE KEYS */;
INSERT INTO `video` (`id`, `type`, `video_id`, `embed_url`, `original_url`, `thumbnail_url`, `created_at`, `modified_at`) VALUES
	(2, 2, 'EYPapE-3FRw', 'https://www.youtube.com/embed/EYPapE-3FRw', 'https://www.youtube.com/watch?v=EYPapE-3FRw', 'https://i.ytimg.com/vi/EYPapE-3FRw/default.jpg', '2016-02-04 11:00:20', '2016-02-04 11:00:20');
/*!40000 ALTER TABLE `video` ENABLE KEYS */;

ALTER TABLE `talent`
	ADD COLUMN `video_id` INT NULL DEFAULT NULL AFTER `rating`,
	ADD CONSTRAINT `FK_talent_video` FOREIGN KEY (`video_id`) REFERENCES `video` (`id`) ON UPDATE CASCADE ON DELETE NO ACTION;

