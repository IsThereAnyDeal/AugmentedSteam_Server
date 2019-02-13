SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";

CREATE TABLE IF NOT EXISTS `currency` (
  `from` char(3) NOT NULL,
  `to`   char(3) NOT NULL,
  `rate` float NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE(`to`, `from`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `dlc_category` (
  `id` int(11) NOT NULL auto_increment,
  `category_name` varchar(45) NOT NULL,
  `category_icon` varchar(300) NOT NULL,
  `category_text` varchar(300) NOT NULL,
  PRIMARY KEY(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `early_access` (
  `appid` int(11) NOT NULL,
  PRIMARY KEY(`appid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `exfgls` (
  `appid` int(11) NOT NULL,
  PRIMARY KEY(`appid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `features` (
  `id` bigint(20) NOT NULL auto_increment,
  `category` varchar(3) NOT NULL,
  `name` varchar(500) NOT NULL,
  `active` tinyint(1) NOT NULL,
  `screenshot_before` varchar(255) NOT NULL,
  `screenshot_after` varchar(255) NOT NULL,
  `description` longtext NOT NULL,
  PRIMARY KEY(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `gamedata` (
  `id` int(11) NOT NULL auto_increment,
  `appid` int(11) NOT NULL,
  `dlc_category` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `game_links` (
  `id` int(11) NOT NULL auto_increment,
  `appid` int(11) NOT NULL,
  `hltb_id` int(11) NOT NULL,
  `steam_id` bigint(20) NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE (`appid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `game_survey` (
  `id` bigint(20) NOT NULL auto_increment,
  `appid` int(11) NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `steamid` bigint(11) NOT NULL,
  `mr` varchar(4) NOT NULL,
  `fs` varchar(3) NOT NULL,
  `fr` varchar(2) NOT NULL,
  `gs` varchar(3) NOT NULL,
  `pw` varchar(3) NOT NULL,
  `gc` varchar(6) NOT NULL,
  PRIMARY KEY(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `market_data` (
  `title` varchar(255) NOT NULL,
  `game` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `img` varchar(1024) NOT NULL,
  `appid` int(11) NOT NULL,
  `url` varchar(1024) NOT NULL,
  `price` float NOT NULL,
  `quantity` int(11) NOT NULL,
  `type` varchar(255) NOT NULL,
  `modified` varchar(255) NOT NULL,
  `rarity` varchar(255) NOT NULL,
  INDEX(`type`, `title`),
  INDEX(`type`, `appid`),
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `metacritic` (
  `id` bigint(11) NOT NULL auto_increment,
  `mcurl` varchar(255) NOT NULL,
  `score` float NOT NULL,
  `access_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `opencritic` (
  `appid` int(11) NOT NULL,
  `json` varchar(5000) NOT NULL,
  `access_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY(`appid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `profile_style_users` (
  `id` int(11) NOT NULL auto_increment,
  `steam64` bigint(32) NOT NULL,
  `profile_style` varchar(64) NOT NULL,
  `update_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `profile_style_users_pending` (
  `id` int(11) NOT NULL auto_increment,
  `steam64` bigint(32) NOT NULL,
  `profile_style` varchar(64) NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `ip` varchar(15) NOT NULL,
  PRIMARY KEY(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `profile_users` (
  `id` int(4) NOT NULL auto_increment,
  `steam64` varchar(64) NOT NULL,
  `profile_background_img` varchar(1024) NOT NULL,
  `update_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `appid` varchar(19) NOT NULL,
  PRIMARY KEY(`id`),
  UNIQUE(`steam64`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `steamcharts` (
  `id` bigint(20) NOT NULL auto_increment,
  `appid` int(11) NOT NULL,
  `one_hour` varchar(11) NOT NULL,
  `one_day` varchar(11) NOT NULL,
  `all_time` varchar(11) NOT NULL,
  `access_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `steamcn` (
  `id` bigint(20) NOT NULL auto_increment,
  `appid` int(11) NOT NULL,
  `json` varchar(4096) COLLATE utf8_unicode_ci NOT NULL,
  `access_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `steamrep` (
  `steam64` bigint(20) NOT NULL,
  `rep` varchar(255) NOT NULL,
  `access_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY(`steam64`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `steamspy` (
  `appid` int(11) NOT NULL,
  `owners` varchar(30) NOT NULL,
  `owners_variance` int(11),
  `players_forever` int(11),
  `players_forever_variance` int(11),
  `players_2weeks` int(11),
  `players_2weeks_variance` int(11),
  `average_forever` int(11) NOT NULL,
  `average_2weeks` int(11) NOT NULL,
  `access_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY(`appid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `steam_reviews` (
  `id` bigint(20) NOT NULL auto_increment,
  `appid` int(11) NOT NULL,
  `total` int(11) NOT NULL,
  `pos` int(11) NOT NULL,
  `stm` int(11) NOT NULL,
  `update_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `supporter_badges` (
  `id` int(11) NOT NULL auto_increment,
  `title` varchar(255) NOT NULL,
  `img` varchar(255) NOT NULL,
  PRIMARY KEY(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `supporter_users` (
  `id` int(11) NOT NULL auto_increment,
  `steam_id` varchar(25) NOT NULL,
  `badge_id` int(11) NOT NULL,
  `link` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  PRIMARY KEY(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `supporter_users_pending` (
  `id` bigint(20) NOT NULL auto_increment,
  `email` varchar(255) NOT NULL,
  `steam_id` varchar(25) NOT NULL,
  `steam_name` varchar(255) NOT NULL,
  `real_name` varchar(255) NOT NULL,
  PRIMARY KEY(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
