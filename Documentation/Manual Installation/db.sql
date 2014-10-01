--
-- Table structures for Version 4.0
--
-- --------------------------------------------------------

--
-- Table structure for table `bundle`
--
CREATE TABLE IF NOT EXISTS `bundle` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `userid` mediumint(9) NOT NULL,
  `date` datetime NOT NULL,
  `access` varchar(10) NOT NULL DEFAULT 'private',
  `view` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `page`
--
CREATE TABLE IF NOT EXISTS `page` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `seo` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `menu` int(11) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;
--
-- Dumping data for table `page`
--
INSERT INTO `page` (`id`, `name`, `seo`, `content`, `menu`) VALUES
(1, 'Terms and Conditions', 'terms', '', 1),
(2, 'Developer', 'developer', 'Please check out the template at http://gempixel.com/shortener/developer.html and copy the template or write your own developer page.', 1);

-- --------------------------------------------------------

--
-- Table structure for table `payment`
--

CREATE TABLE IF NOT EXISTS `payment` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tid` varchar(255) NOT NULL,
  `userid` bigint(20) NOT NULL,
  `status` varchar(255) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `expiry` datetime NOT NULL,
  `data` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Dumping data for table `settings`
--

CREATE TABLE IF NOT EXISTS `settings` (
  `config` varchar(255) NOT NULL,
  `var` text NOT NULL,
  PRIMARY KEY (`config`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`config`, `var`) VALUES
('url', ''),
('title', ''),
('description', ''),
('api', '1'),
('user', '1'),
('sharing', '1'),
('geotarget', '1'),
('adult', '1'),
('maintenance', '0'),
('keywords', ''),
('theme', 'default'),
('apikey', ''),
('ads', '1'),
('captcha', '0'),
('ad728', ''),
('ad468', ''),
('ad300', ''),
('frame', '0'),
('facebook', ''),
('twitter', ''),
('email', ''),
('fb_connect', '0'),
('analytic', ''),
('private', '0'),
('facebook_app_id', ''),
('facebook_secret', ''),
('twitter_key', ''),
('twitter_secret', ''),
('safe_browsing', ''),
('captcha_public', ''),
('captcha_private', ''),
('tw_connect', '0'),
('multiple_domains', '0'),
('domain_names', ''),
('tracking', '0'),
('update_notification', '0'),
('default_lang', 'en'),
('user_activate', '0'),
('domain_blacklist', ''),
('keyword_blacklist', ''),
('user_history', '0'),
('pro_yearly', ''),
('show_media', '0'),
('pro_monthly', ''),
('paypal_email', ''),
('logo', ''),
('timer', ''),
('smtp', ''),
('style', ''),
('font', ''),
('currency', 'USD'),
('news', ''),
('gl_connect', '0'),
('require_registration', '0'),
('phish_api', ''),
('aliases', ''),
('pro', '1'),
('google_cid', ''),
('google_cs', ''),
('public_dir', '0');;
-- --------------------------------------------------------

--
-- Table structure for table `splash`
--


CREATE TABLE IF NOT EXISTS `splash` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userid` bigint(12) NOT NULL,
  `name` varchar(255) NOT NULL,
  `data` text NOT NULL,
  `date` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
-- --------------------------------------------------------

--
-- Table structure for table `stats`
--


CREATE TABLE IF NOT EXISTS `stats` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `short` varchar(255) NOT NULL,
  `urlid` bigint(20) NOT NULL,
  `urluserid` bigint(20) NOT NULL DEFAULT '0',
  `date` datetime NOT NULL,
  `ip` varchar(255) NOT NULL,
  `country` varchar(255) NOT NULL,
  `domain` varchar(50) NOT NULL,
  `referer` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `url`
--
CREATE TABLE IF NOT EXISTS `url` (
  `id` int(20) NOT NULL AUTO_INCREMENT,
  `userid` int(16) NOT NULL DEFAULT '0',
  `alias` varchar(10) NOT NULL,
  `custom` varchar(160) NOT NULL,
  `url` text NOT NULL,
  `location` text NOT NULL,
  `description` text NOT NULL,
  `date` datetime NOT NULL,
  `pass` varchar(255) NOT NULL,
  `click` bigint(20) NOT NULL DEFAULT '0',
  `meta_title` varchar(255) NOT NULL,
  `meta_description` varchar(255) NOT NULL,
  `ads` int(1) NOT NULL DEFAULT '1',
  `bundle` mediumint(9) NOT NULL,
  `public` int(1) NOT NULL DEFAULT '0',
  `archived` int(1) NOT NULL DEFAULT '0',
  `type` varchar(64) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `user`
--
CREATE TABLE IF NOT EXISTS `user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `auth` text NOT NULL,
  `auth_id` varchar(255) NOT NULL,
  `admin` int(1) NOT NULL DEFAULT '0',
  `email` varchar(255) NOT NULL,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `date` datetime NOT NULL,
  `api` varchar(255) NOT NULL,
  `ads` int(1) NOT NULL DEFAULT '1',
  `active` int(1) NOT NULL DEFAULT '1',
  `banned` int(1) NOT NULL DEFAULT '0',
  `public` int(1) NOT NULL DEFAULT '0',
  `domain` varchar(255) NOT NULL,
  `media` int(1) NOT NULL DEFAULT '0',
  `splash_opt` int(1) NOT NULL DEFAULT '0',
  `splash` text NOT NULL,
  `auth_key` varchar(255) NOT NULL,
  `last_payment` datetime NOT NULL,
  `expiration` datetime NOT NULL,
  `pro` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `api` (`api`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
