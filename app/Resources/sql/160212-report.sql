
CREATE TABLE IF NOT EXISTS `report_offer` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `report` varchar(100) NOT NULL DEFAULT '0',
  `message` varchar(500) NOT NULL DEFAULT '0',
  `equipment_Id` int(11) DEFAULT '0',
  `talent_id` int(11) DEFAULT '0',
  `offer_type` tinyint(4) NOT NULL DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `FK_report_offer_equipment` (`equipment_Id`),
  KEY `FK_report_offer_talent` (`talent_id`),
  CONSTRAINT `FK_report_offer_equipment` FOREIGN KEY (`equipment_Id`) REFERENCES `equipment` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `FK_report_offer_talent` FOREIGN KEY (`talent_id`) REFERENCES `talent` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8;
