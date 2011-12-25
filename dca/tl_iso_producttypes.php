<?php if (!defined('TL_ROOT')) die('You can not access this file directly!');

/**
 * TYPOlight Open Source CMS
 * Copyright (C) 2005-2010 Leo Feyer
 *
 * This program is free software: you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation, either
 * version 3 of the License, or (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 * 
 * You should have received a copy of the GNU Lesser General Public
 * License along with this program. If not, please visit the Free
 * Software Foundation website at <http://www.gnu.org/licenses/>.
 *
 * PHP version 5
 * @copyright  Winans Creative 2009, Intelligent Spark 2010, iserv.ch GmbH 2010
 * @author     Fred Bliss <fred.bliss@intelligentspark.com>
 * @author     Andreas Schempp <andreas@schempp.ch>
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */


/**
 * Palettes
 */
foreach ($GLOBALS['TL_DCA']['tl_iso_producttypes']['palettes'] as $key=>$palette)
{
	if ($key != '__selector__' && stripos($palette, '{attributes_legend}') !== false)
	{
		$GLOBALS['TL_DCA']['tl_iso_producttypes']['palettes'][$key] = str_replace('{attributes_legend}', '{inventory_legend:hide},lowthreshold,allowbackorders,hidezeroinvproducts;{attributes_legend}', $palette);
	}
}



/** 
 * Fields
 */
$GLOBALS['TL_DCA']['tl_iso_producttypes']['fields']['lowthreshold'] = array
(
	'label'					=> &$GLOBALS['TL_LANG']['tl_iso_producttypes']['lowthreshold'],
	'exclude'				=> true,
	'inputType'				=> 'text',
	'default'				=> '0',
	'eval'					=> array('rgxp'=>'digit')
);

$GLOBALS['TL_DCA']['tl_iso_producttypes']['fields']['allowbackorders'] = array
(
	'label'					=> &$GLOBALS['TL_LANG']['tl_iso_producttypes']['allowbackorders'],
	'exclude'				=> true,
	'inputType'				=> 'checkbox',
	'eval'					=> array('tl_class'=>'w50'),
);

$GLOBALS['TL_DCA']['tl_iso_producttypes']['fields']['hidezeroinvproducts'] = array
(
	'label'					=> &$GLOBALS['TL_LANG']['tl_iso_producttypes']['hidezeroinvproducts'],
	'exclude'				=> true,
	'inputType'				=> 'checkbox',
	'eval'					=> array('tl_class'=>'w50'),
);



/** 
 * Legends
 */
$GLOBALS['TL_LANG']['tl_iso_producttypes']['inventory_legend'] = 'Inventory';

