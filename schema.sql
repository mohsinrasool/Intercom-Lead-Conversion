
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
  `response` text
) ENGINE=MyISAM DEFAULT CHARSET=latin1
