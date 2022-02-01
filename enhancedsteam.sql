
CREATE TABLE `currency` (
  `from` char(3) NOT NULL,
  `to`   char(3) NOT NULL,
  `rate` float NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE(`to`, `from`)
) ENGINE=InnoDB;

CREATE TABLE `dlc_categories` (
  `id` tinyint NOT NULL,
  `name` varchar(25) NOT NULL,
  `icon` varchar(25) NOT NULL,
  `description` varchar(180) NOT NULL,
  PRIMARY KEY(`id`)
) ENGINE=InnoDB;

CREATE TABLE `game_dlc` (
  `appid` int NOT NULL,
  `dlc_category` tinyint NOT NULL,
  `score` int NOT NULL,
  PRIMARY KEY (`appid`, `dlc_category`),
  FOREIGN KEY (dlc_category) REFERENCES dlc_categories(id) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE `market_index` (
  `appid` int NOT NULL,
  `last_update` int NOT NULL DEFAULT 0,
  `last_request` int NOT NULL,
  `request_counter` int NOT NULL DEFAULT 0,
  PRIMARY KEY (`appid`),
  INDEX (`last_update` DESC, `appid`)
) ENGINE=InnoDB;

CREATE TABLE `market_data` (
  `hash_name` varchar(255) NOT NULL,
  `appid` int NOT NULL,
  `name` varchar(255) NOT NULL,
  `sell_listings` int NOT NULL DEFAULT 0,
  `sell_price_usd` int NOT NULL,
  `icon_url` text NOT NULL,
  `type` enum('unknown', 'background', 'booster', 'card', 'emoticon', 'item') NOT NULL,
  `rarity` enum('normal', 'uncommon', 'foil', 'rare'),
  `timestamp` int unsigned NOT NULL,
  PRIMARY KEY (`hash_name`),
  INDEX (`appid`)
) ENGINE=InnoDB;


CREATE TABLE IF NOT EXISTS `exfgls` (
  `appid` int(11) NOT NULL,
  PRIMARY KEY(`appid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `features` (
  `id` bigint(20) NOT NULL auto_increment,
  `category` varchar(3) NOT NULL,
  `name` varchar(500) NOT NULL,
  `active` tinyint(1) NOT NULL,
  `screenshot_before` varchar(255) NOT NULL,
  `screenshot_after` varchar(255) NOT NULL,
  `description` longtext NOT NULL,
  PRIMARY KEY(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `game_links` (
  `id` int(11) NOT NULL auto_increment,
  `appid` int(11) NOT NULL,
  `hltb_id` int(11) NOT NULL,
  `steam_id` bigint(20) NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE (`appid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `game_survey` (
  `appid` int NOT NULL,
  `steamid` bigint NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `framerate` char(2),
  `optimized` boolean,
  `lag` boolean,
  `graphics_settings` char(2),
  `bg_sound` boolean,
  `good_controls` boolean,
  PRIMARY KEY (`appid`, `steamid`)
);


CREATE TABLE IF NOT EXISTS `market_data` (
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `game` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `img` varchar(1024) NOT NULL,
  `appid` int(11) NOT NULL,
  `url` varchar(1024) NOT NULL,
  `price` float NOT NULL,
  `quantity` int(11) NOT NULL,
  `type` varchar(255) NOT NULL,
  `modified` varchar(255) NOT NULL,
  `rarity` varchar(255) NOT NULL,
  INDEX(`type`, `title`),
  INDEX(`type`, `appid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `profile_style_users` (
  `id` int(11) NOT NULL auto_increment,
  `steam64` bigint(32) NOT NULL,
  `profile_style` varchar(13) NOT NULL,
  `update_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY(`id`),
  UNIQUE(`steam64`)
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

CREATE TABLE IF NOT EXISTS `similar` (
  `appid` int NOT NULL,
  `data` text NOT NULL,
  `timestamp` int unsigned NOT NULL,
  PRIMARY KEY(`appid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `twitch_token` (
  `token` char(30) NOT NULL,
  `expiry` int NOT NULL,
  INDEX(`expiry`),
  PRIMARY KEY(`token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `session_ids` (
  `session_id` binary(16) NOT NULL,
  `steam_id` varchar(25) NOT NULL,
  `expiry` timestamp NOT NULL,
  PRIMARY KEY(`session_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
