
CREATE TABLE `currency` (
  `from`      char(3) NOT NULL,
  `to`        char(3) NOT NULL,
  `rate`      float NOT NULL,
  `timestamp` int unsigned NOT NULL,
  UNIQUE(`to`, `from`),
  INDEX(`timestamp`)
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
  `appname` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `sell_listings` int NOT NULL DEFAULT 0,
  `sell_price_usd` int NOT NULL,
  `img` text NOT NULL,
  `url` varchar(255) NOT NULL,
  `type` enum('unknown', 'background', 'booster', 'card', 'emoticon', 'item') NOT NULL,
  `rarity` enum('normal', 'uncommon', 'foil', 'rare'),
  `timestamp` int unsigned NOT NULL,
  PRIMARY KEY (`hash_name`),
  INDEX (`appid`)
) ENGINE=InnoDB;

CREATE TABLE `users_profiles` (
  `steam64` bigint unsigned NOT NULL,
  `bg_img` text,
  `bg_appid` int,
  `style` varchar(20),
  `update_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY(`steam64`)
) ENGINE=InnoDB;

CREATE TABLE `badges` (
  `id` tinyint unsigned NOT NULL,
  `title` varchar(40) NOT NULL,
  `img` varchar(30) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;

CREATE TABLE `users_badges` (
  `steam64` bigint unsigned NOT NULL,
  `badge_id` tinyint unsigned NOT NULL,
  PRIMARY KEY (`steam64`, `badge_id`),
  FOREIGN KEY (badge_id) REFERENCES badges(id) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE `steamrep` (
  `steam64` bigint unsigned NOT NULL,
  `rep` varchar(100),
  `timestamp` int unsigned NOT NULL,
  `checked` tinyint unsigned NOT NULL,
  PRIMARY KEY (`steam64`),
  INDEX (`checked`, `timestamp`)
) ENGINE=InnoDB;

CREATE TABLE `cache` (
  `key` int NOT NULL,
  `field` varchar(50) NOT NULL,
  `data` longblob,
  `expiry` int unsigned NOT NULL,
  PRIMARY KEY (`key`, `field`),
  INDEX (`expiry`)
);

CREATE TABLE `exfgls` (
  `appid` int NOT NULL,
  `excluded` tinyint NOT NULL,
  `checked` tinyint NOT NULL,
  `timestamp` int unsigned NOT NULL,
  PRIMARY KEY(`appid`)
) ENGINE=InnoDB;

CREATE TABLE `hltb` (
  `id` int NOT NULL,
  `appid` int,
  `main` int,
  `extra` int,
  `complete` int,
  `found_timestamp` int unsigned NOT NULL DEFAULT 0,
  `checked_timestamp` int unsigned,
  PRIMARY KEY (`id`),
  INDEX (`appid`)
) ENGINE=InnoDb;

CREATE TABLE `sessions` (
  `token` char(10) NOT NULL,
  `hash` char(64) NOT NULL,
  `steam_id` bigint NOT NULL,
  `expiry` int unsigned NOT NULL,
  PRIMARY KEY(`token`)
) ENGINE=InnoDB;
