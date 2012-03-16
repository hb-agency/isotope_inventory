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
 * Config
 */
$GLOBALS['TL_DCA']['tl_iso_products']['config']['onload_callback'][] = array('tl_iso_inventory_products', 'loadInvntoryCSS');



/**
 * List
 */
$GLOBALS['TL_DCA']['tl_iso_products']['list']['operations']['inventory_history'] = array
(
	'label'				=> &$GLOBALS['TL_LANG']['tl_iso_products']['inventory_history'],
	'href'				=> 'key=inventory_history',
	'icon'				=> 'system/modules/isotope_inventory/html/inventory-maintenance-icon.png',
	'attributes'		=> 'class="isotope-tools"',
	'button_callback'	=> array('tl_iso_inventory_products', 'inventoryHistoryButton'),
);

$GLOBALS['TL_DCA']['tl_iso_products']['list']['global_operations']['inventory_history'] = array
(
	'label'				=> &$GLOBALS['TL_LANG']['tl_iso_products']['inventory_history'],
	'href'				=> 'key=inventory_history',
	'class'				=> 'header_inventory_history isotope-tools',
	'attributes'		=> 'onclick="Backend.getScrollOffset();"',
);


/**
 * Fields
 */
$GLOBALS['TL_DCA']['tl_iso_products']['fields']['inventory'] = array
(
	'label'					=> &$GLOBALS['TL_LANG']['tl_iso_products']['inventory'],
	'input_field_callback'	=> array('InventoryBackend', 'generateInventoryWizard'),
	'attributes'			=> array('legend'=>'inventory_legend', 'fixed'=>true, 'variant_fixed'=>true),
);


class tl_iso_inventory_products extends tl_iso_products
{

	/**
	 * Load the inventory CSS file
	 */
	public function loadInvntoryCSS()
	{
		$GLOBALS['TL_CSS'][] = 'system/modules/isotope_inventory/html/inventory_history.css';
	}

	/**
	 * Hide inventory history button if inventory is not enabled
	 */
	public function inventoryHistoryButton($row, $href, $label, $title, $icon, $attributes)
	{
		$objConfig = $this->Database->prepare("SELECT * FROM tl_iso_config WHERE id=?")->execute($this->Isotope->Config->id);
		
		if ($objConfig->enableInventory == '1')
			return '<a href="'.$this->addToUrl($href.'&amp;id='.$row['id']).'" title="'.specialchars($title).'"'.$attributes.'>'.$this->generateImage($icon, $label).'</a> ';
		else
			return '';

	}
	
	
}
