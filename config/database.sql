-- **********************************************************
-- *                                                        *
-- * IMPORTANT NOTE                                         *
-- *                                                        *
-- * Do not import this file manually but use the TYPOlight *
-- * install tool to create and maintain database tables!   *
-- *                                                        *
-- **********************************************************


-- 
-- Table `tl_iso_config`
-- 

CREATE TABLE `tl_iso_config` (
  `enableInventory` char(1) NOT NULL default '',
  `warehouses` blob NULL,
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

-- 
-- Table `tl_iso_warehouse`
--

CREATE TABLE `tl_iso_warehouses` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `pid` int(10) unsigned NOT NULL default '0',
  `tstamp` int(10) unsigned NOT NULL default '0',
  `name` varchar(255) NOT NULL default '',
  `defaultWarehouse` char(1) NOT NULL default '',
  `description` text NULL,
  `company` varchar(255) NOT NULL default '',
  `firstname` varchar(255) NOT NULL default '',
  `lastname` varchar(255) NOT NULL default '',
  `email` varchar(255) NOT NULL default '',
  `phone` varchar(64) NOT NULL default '',
  `street_1` varchar(255) NOT NULL default '',
  `street_2` varchar(255) NOT NULL default '',
  `street_3` varchar(255) NOT NULL default '',
  `postal` varchar(32) NOT NULL default '',
  `city` varchar(255) NOT NULL default '',
  `subdivision` varchar(10) NOT NULL default '',
  `country` varchar(32) NOT NULL default '',
  PRIMARY KEY  (`id`),
  KEY `pid` (`pid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

-- 
-- Table `tl_iso_inventory`
--

CREATE TABLE `tl_iso_inventory` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `pid` int(10) unsigned NOT NULL default '0',
  `tstamp` int(10) unsigned NOT NULL default '0',
  `product_id` int(10) unsigned NOT NULL default '0',
  `quantity` int(10) NOT NULL default '0',
  `frombatch` char(1) NOT NULL default '',
  PRIMARY KEY  (`id`),
  KEY `pid` (`pid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

-- 
-- Table `tl_iso_products`
--

CREATE TABLE `tl_iso_products` (
  `qtyonhand` int(10) NOT NULL default '0',
  `qtybackordered` int(10) NOT NULL default '0',
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

-- 
-- Table `tl_iso_producttypes`
--

CREATE TABLE `tl_iso_producttypes` (
  `lowthreshold` int(10) unsigned NOT NULL default '0',
  `allowbackorders` char(1) NOT NULL default '',
  `hidezeroinvproducts` char(1) NOT NULL default '',
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------
