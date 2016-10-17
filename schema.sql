
/* Leads table for the cron to convert all the leads to users */
CREATE TABLE `leads_new` (
  `nid` bigint(20) NOT NULL AUTO_INCREMENT,
  `id` varchar(100) NOT NULL,
  `user_id` varchar(100) DEFAULT NULL,
  `email` varchar(200) DEFAULT NULL,
  `name` varchar(100) DEFAULT NULL,
  `anonymous` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL COMMENT 'Creation time of Intercom',
  `contact_id` varchar(100) DEFAULT 'NULL' COMMENT 'Contact Id to which it is converted',
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Creation time of this record',
  `response` text,
  `request` text,
  `error` varchar(500) DEFAULT NULL,
  `adjustment` int(1) DEFAULT NULL,
  `in_process` tinyint(4) DEFAULT NULL COMMENT '0 = needs processing, 1=in processing, 2 = processed',
  PRIMARY KEY (`nid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1

/* Log table of the cron for on the fly conversion */
CREATE TABLE `cron_log` (
  `id` varchar(100) NOT NULL,
  `user_id` varchar(100) DEFAULT NULL,
  `email` varchar(200) DEFAULT NULL,
  `name` varchar(100) DEFAULT NULL,
  `anonymous` tinyint(1) DEFAULT '0',
  `created_at` bigint(20) DEFAULT NULL COMMENT 'Creation time of Intercom',
  `contact_id` varchar(100) DEFAULT 'NULL' COMMENT 'Contact Id to which it is converted',
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Creation time of this record',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1