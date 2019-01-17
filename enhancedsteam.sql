-- Host: localhost
-- Generation Time: Jan 02, 2019 at 03:15 PM

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

--
-- Database: `enhancedsteam`
--

-- --------------------------------------------------------

--
-- Table structure for table `currency`
--

CREATE TABLE IF NOT EXISTS `currency` (
  `Base` varchar(3) NOT NULL,
  `USD` float DEFAULT NULL,
  `GBP` float DEFAULT NULL,
  `EUR` float NOT NULL,
  `RUB` float NOT NULL,
  `BRL` float NOT NULL,
  `JPY` float NOT NULL,
  `NOK` float NOT NULL,
  `IDR` float NOT NULL,
  `MYR` float NOT NULL,
  `PHP` float NOT NULL,
  `SGD` float NOT NULL,
  `THB` float NOT NULL,
  `VND` float NOT NULL,
  `KRW` float NOT NULL,
  `TRY` float NOT NULL,
  `UAH` float NOT NULL,
  `MXN` float NOT NULL,
  `CAD` float NOT NULL,
  `AUD` float NOT NULL,
  `NZD` float NOT NULL,
  `INR` float NOT NULL,
  `HKD` float NOT NULL,
  `TWD` float NOT NULL,
  `CNY` float NOT NULL,
  `SAR` float NOT NULL,
  `ZAR` float NOT NULL,
  `AED` float NOT NULL,
  `CHF` float NOT NULL,
  `CLP` float NOT NULL,
  `PEN` float NOT NULL,
  `COP` float NOT NULL,
  `UYU` float NOT NULL,
  `ILS` float NOT NULL,
  `PLN` float NOT NULL,
  `ARS` float NOT NULL,
  `CRC` float NOT NULL,
  `KZT` float NOT NULL,
  `KWD` float NOT NULL,
  `QAR` float NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `dlc_category`
--

CREATE TABLE IF NOT EXISTS `dlc_category` (
`id` int(11) NOT NULL,
  `category_name` varchar(45) NOT NULL,
  `category_icon` varchar(300) NOT NULL,
  `category_text` varchar(300) NOT NULL
) ENGINE=MyISAM AUTO_INCREMENT=14 DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `early_access`
--

CREATE TABLE IF NOT EXISTS `early_access` (
  `appid` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `exfgls`
--

CREATE TABLE IF NOT EXISTS `exfgls` (
  `appid` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `features`
--

CREATE TABLE IF NOT EXISTS `features` (
`id` bigint(20) NOT NULL,
  `category` varchar(3) NOT NULL,
  `name` varchar(500) NOT NULL,
  `active` tinyint(1) NOT NULL,
  `screenshot_before` varchar(255) NOT NULL,
  `screenshot_after` varchar(255) NOT NULL,
  `description` longtext NOT NULL
) ENGINE=MyISAM AUTO_INCREMENT=40 DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `gamedata`
--

CREATE TABLE IF NOT EXISTS `gamedata` (
`id` int(11) NOT NULL,
  `appid` int(11) NOT NULL,
  `dlc_category` int(11) NOT NULL DEFAULT '0'
) ENGINE=MyISAM AUTO_INCREMENT=1849 DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `game_links`
--

CREATE TABLE IF NOT EXISTS `game_links` (
`id` int(11) NOT NULL,
  `appid` int(11) NOT NULL,
  `hltb_id` int(11) NOT NULL,
  `steam_id` bigint(20) NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=MyISAM AUTO_INCREMENT=5123 DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `game_survey`
--

CREATE TABLE IF NOT EXISTS `game_survey` (
`id` bigint(20) NOT NULL,
  `appid` int(11) NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `steamid` bigint(11) NOT NULL,
  `mr` varchar(4) NOT NULL,
  `fs` varchar(3) NOT NULL,
  `fr` varchar(2) NOT NULL,
  `gs` varchar(3) NOT NULL,
  `pw` varchar(3) NOT NULL,
  `gc` varchar(6) NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=27730 DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `market_data`
--

CREATE TABLE IF NOT EXISTS `market_data` (
`id` bigint(20) NOT NULL,
  `game` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `img` varchar(1024) NOT NULL,
  `appid` int(11) NOT NULL,
  `url` varchar(1024) NOT NULL,
  `price` float NOT NULL,
  `price_brl` float NOT NULL,
  `price_rub` float NOT NULL,
  `price_gbp` float NOT NULL,
  `price_eur` float NOT NULL,
  `price_jpy` float NOT NULL,
  `price_nok` float NOT NULL,
  `price_idr` float NOT NULL,
  `price_myr` float NOT NULL,
  `price_php` float NOT NULL,
  `price_sgd` float NOT NULL,
  `price_thb` float NOT NULL,
  `price_vnd` float NOT NULL,
  `price_krw` float NOT NULL,
  `price_try` float NOT NULL,
  `price_uah` float NOT NULL,
  `price_mxn` float NOT NULL,
  `price_cad` float NOT NULL,
  `price_aud` float NOT NULL,
  `price_nzd` float NOT NULL,
  `quantity` int(11) NOT NULL,
  `type` varchar(255) NOT NULL,
  `modified` varchar(255) NOT NULL,
  `rarity` varchar(255) NOT NULL
) ENGINE=MyISAM AUTO_INCREMENT=200112 DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `metacritic`
--

CREATE TABLE IF NOT EXISTS `metacritic` (
`id` bigint(11) NOT NULL,
  `mcurl` varchar(255) NOT NULL,
  `score` float NOT NULL,
  `access_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB AUTO_INCREMENT=4077131 DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `opencritic`
--

CREATE TABLE IF NOT EXISTS `opencritic` (
  `appid` int(11) NOT NULL,
  `json` varchar(5000) NOT NULL,
  `access_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `profile_backgrounds`
--

CREATE TABLE IF NOT EXISTS `profile_backgrounds` (
`id` int(4) NOT NULL,
  `url` varchar(500) NOT NULL,
  `smallurl` varchar(500) NOT NULL,
  `name` varchar(100) NOT NULL,
  `type` varchar(255) NOT NULL
) ENGINE=MyISAM AUTO_INCREMENT=1856 DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `profile_style_users`
--

CREATE TABLE IF NOT EXISTS `profile_style_users` (
`id` int(11) NOT NULL,
  `steam64` bigint(32) NOT NULL,
  `profile_style` varchar(64) NOT NULL,
  `update_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB AUTO_INCREMENT=138442 DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `profile_style_users_pending`
--

CREATE TABLE IF NOT EXISTS `profile_style_users_pending` (
`id` int(11) NOT NULL,
  `steam64` bigint(32) NOT NULL,
  `profile_style` varchar(64) NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `ip` varchar(15) NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=269981 DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `profile_users`
--

CREATE TABLE IF NOT EXISTS `profile_users` (
`id` int(4) NOT NULL,
  `steam64` varchar(64) NOT NULL,
  `profile_background_id` int(4) NOT NULL,
  `profile_background_img` varchar(1024) NOT NULL,
  `update_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `appid` varchar(19) NOT NULL
) ENGINE=MyISAM AUTO_INCREMENT=105568 DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `profile_users_pending`
--

CREATE TABLE IF NOT EXISTS `profile_users_pending` (
`id` int(11) NOT NULL,
  `steam64` varchar(64) NOT NULL,
  `es_background` varchar(1024) NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `ip` varchar(15) NOT NULL,
  `appid` varchar(19) NOT NULL
) ENGINE=MyISAM AUTO_INCREMENT=178082 DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `steamcharts`
--

CREATE TABLE IF NOT EXISTS `steamcharts` (
`id` bigint(20) NOT NULL,
  `appid` int(11) NOT NULL,
  `one_hour` varchar(11) NOT NULL,
  `one_day` varchar(11) NOT NULL,
  `all_time` varchar(11) NOT NULL,
  `access_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB AUTO_INCREMENT=130234968 DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `steamcn`
--

CREATE TABLE IF NOT EXISTS `steamcn` (
`id` bigint(20) NOT NULL,
  `appid` int(11) NOT NULL,
  `json` varchar(4096) COLLATE utf8_unicode_ci NOT NULL,
  `access_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB AUTO_INCREMENT=11848794 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `steamrep`
--

CREATE TABLE IF NOT EXISTS `steamrep` (
  `steam64` bigint(20) NOT NULL,
  `rep` varchar(255) NOT NULL,
  `access_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `steamspy`
--

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
  `access_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `steam_reviews`
--

CREATE TABLE IF NOT EXISTS `steam_reviews` (
`id` bigint(20) NOT NULL,
  `appid` int(11) NOT NULL,
  `total` int(11) NOT NULL,
  `pos` int(11) NOT NULL,
  `stm` int(11) NOT NULL,
  `update_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB AUTO_INCREMENT=49069750 DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `supporter_badges`
--

CREATE TABLE IF NOT EXISTS `supporter_badges` (
`id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `img` varchar(255) NOT NULL
) ENGINE=MyISAM AUTO_INCREMENT=10 DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `supporter_users`
--

CREATE TABLE IF NOT EXISTS `supporter_users` (
`id` int(11) NOT NULL,
  `steam_id` varchar(25) NOT NULL,
  `badge_id` int(11) NOT NULL,
  `link` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL
) ENGINE=MyISAM AUTO_INCREMENT=360 DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `supporter_users_pending`
--

CREATE TABLE IF NOT EXISTS `supporter_users_pending` (
`id` bigint(20) NOT NULL,
  `email` varchar(255) NOT NULL,
  `steam_id` varchar(25) NOT NULL,
  `steam_name` varchar(255) NOT NULL,
  `real_name` varchar(255) NOT NULL
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `currency`
--
ALTER TABLE `currency`
 ADD PRIMARY KEY (`Base`);

--
-- Indexes for table `dlc_category`
--
ALTER TABLE `dlc_category`
 ADD PRIMARY KEY (`id`);

--
-- Indexes for table `early_access`
--
ALTER TABLE `early_access`
 ADD PRIMARY KEY (`appid`);

--
-- Indexes for table `exfgls`
--
ALTER TABLE `exfgls`
 ADD PRIMARY KEY (`appid`);

--
-- Indexes for table `features`
--
ALTER TABLE `features`
 ADD PRIMARY KEY (`id`);

--
-- Indexes for table `gamedata`
--
ALTER TABLE `gamedata`
 ADD PRIMARY KEY (`id`);

--
-- Indexes for table `game_links`
--
ALTER TABLE `game_links`
 ADD PRIMARY KEY (`id`), ADD UNIQUE KEY `appid` (`appid`);

--
-- Indexes for table `game_survey`
--
ALTER TABLE `game_survey`
 ADD PRIMARY KEY (`id`);

--
-- Indexes for table `market_data`
--
ALTER TABLE `market_data`
 ADD PRIMARY KEY (`id`);

--
-- Indexes for table `metacritic`
--
ALTER TABLE `metacritic`
 ADD PRIMARY KEY (`id`);

--
-- Indexes for table `opencritic`
--
ALTER TABLE `opencritic`
 ADD PRIMARY KEY (`appid`);

--
-- Indexes for table `profile_backgrounds`
--
ALTER TABLE `profile_backgrounds`
 ADD PRIMARY KEY (`id`);

--
-- Indexes for table `profile_style_users`
--
ALTER TABLE `profile_style_users`
 ADD PRIMARY KEY (`id`);

--
-- Indexes for table `profile_style_users_pending`
--
ALTER TABLE `profile_style_users_pending`
 ADD PRIMARY KEY (`id`);

--
-- Indexes for table `profile_users`
--
ALTER TABLE `profile_users`
 ADD PRIMARY KEY (`id`);

--
-- Indexes for table `profile_users_pending`
--
ALTER TABLE `profile_users_pending`
 ADD PRIMARY KEY (`id`);

--
-- Indexes for table `steamcharts`
--
ALTER TABLE `steamcharts`
 ADD PRIMARY KEY (`id`);

--
-- Indexes for table `steamcn`
--
ALTER TABLE `steamcn`
 ADD PRIMARY KEY (`id`);

--
-- Indexes for table `steamrep`
--
ALTER TABLE `steamrep`
 ADD UNIQUE KEY `steam64` (`steam64`);

--
-- Indexes for table `steamspy`
--
ALTER TABLE `steamspy`
 ADD UNIQUE KEY `appid` (`appid`);

--
-- Indexes for table `steam_reviews`
--
ALTER TABLE `steam_reviews`
 ADD PRIMARY KEY (`id`);

--
-- Indexes for table `supporter_badges`
--
ALTER TABLE `supporter_badges`
 ADD PRIMARY KEY (`id`);

--
-- Indexes for table `supporter_users`
--
ALTER TABLE `supporter_users`
 ADD PRIMARY KEY (`id`);

--
-- Indexes for table `supporter_users_pending`
--
ALTER TABLE `supporter_users_pending`
 ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `dlc_category`
--
ALTER TABLE `dlc_category`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=1;
--
-- AUTO_INCREMENT for table `features`
--
ALTER TABLE `features`
MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=1;
--
-- AUTO_INCREMENT for table `gamedata`
--
ALTER TABLE `gamedata`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=1;
--
-- AUTO_INCREMENT for table `game_links`
--
ALTER TABLE `game_links`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=1;
--
-- AUTO_INCREMENT for table `game_survey`
--
ALTER TABLE `game_survey`
MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=1;
--
-- AUTO_INCREMENT for table `market_data`
--
ALTER TABLE `market_data`
MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=1;
--
-- AUTO_INCREMENT for table `metacritic`
--
ALTER TABLE `metacritic`
MODIFY `id` bigint(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=1;
--
-- AUTO_INCREMENT for table `profile_backgrounds`
--
ALTER TABLE `profile_backgrounds`
MODIFY `id` int(4) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=1;
--
-- AUTO_INCREMENT for table `profile_style_users`
--
ALTER TABLE `profile_style_users`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=1;
--
-- AUTO_INCREMENT for table `profile_style_users_pending`
--
ALTER TABLE `profile_style_users_pending`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=1;
--
-- AUTO_INCREMENT for table `profile_users`
--
ALTER TABLE `profile_users`
MODIFY `id` int(4) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=1;
--
-- AUTO_INCREMENT for table `profile_users_pending`
--
ALTER TABLE `profile_users_pending`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=1;
--
-- AUTO_INCREMENT for table `steamcharts`
--
ALTER TABLE `steamcharts`
MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=1;
--
-- AUTO_INCREMENT for table `steamcn`
--
ALTER TABLE `steamcn`
MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=1;
--
-- AUTO_INCREMENT for table `steam_reviews`
--
ALTER TABLE `steam_reviews`
MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=1;
--
-- AUTO_INCREMENT for table `supporter_badges`
--
ALTER TABLE `supporter_badges`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=1;
--
-- AUTO_INCREMENT for table `supporter_users`
--
ALTER TABLE `supporter_users`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=1;
--
-- AUTO_INCREMENT for table `supporter_users_pending`
--
ALTER TABLE `supporter_users_pending`
MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=1;
