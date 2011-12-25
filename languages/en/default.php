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
 
 
$GLOBALS['TL_LANG']['MSC']['inventoryHistoryLink']			= 'View inventory history for %s';

$GLOBALS['TL_LANG']['MSC']['new_quantity']					= 'New Qty';
$GLOBALS['TL_LANG']['MSC']['inventory_history']				= 'History';
$GLOBALS['TL_LANG']['MSC']['id']							= 'Product ID';

$GLOBALS['TL_LANG']['MSC']['noWarehouses']					= 'No warehouses are found, please create one first to control your inventory.';
$GLOBALS['TL_LANG']['MSC']['noProducts']					= 'No products were found';

$GLOBALS['TL_LANG']['MSC']['inventoryAdminSubject']			= 'Inventory Warning';
$GLOBALS['TL_LANG']['MSC']['inventoryAdminText']['zero']	= 'A product (ID %s, NAME %s) has dropped to a zero level inventory and is no longer being listed. Click here to view the product. %s';
$GLOBALS['TL_LANG']['MSC']['inventoryAdminText']['low']		= 'A product (ID %s, NAME %s) has dropped to a low inventory. Click here to view the product. %s';
$GLOBALS['TL_LANG']['MSC']['quantity_error']				= 'The amount of product you selected is greater than the stock on hand.<br />Your request has been limited to stock on hand.';


/**														
 * Inventory Manager									
 */
$GLOBALS['TL_LANG']['MSC']['manage_quantities']				= 'Manage Inventory';


/**														
 * Inventory Import								
 */
$GLOBALS['TL_LANG']['MSC']['inventory_import_headline']		= 'Inventory Import';
$GLOBALS['TL_LANG']['MSC']['inventory_import_subheadline']	= 'This allows you to import inventory from a CSV file.  Columns should be SKU, then quantity.';
$GLOBALS['TL_LANG']['MSC']['btn_inventory_import']			= 'Import Inventory';
$GLOBALS['TL_LANG']['MSC']['inventory_override_quantites']	= 'Override Quantites Mode';


/**														
 * Inventory History								
 */
$GLOBALS['TL_LANG']['MSC']['inventory_history_headline'] 	= 'Inventory History';
$GLOBALS['TL_LANG']['MSC']['inventory_history_print']		= 'Print to PDF';
$GLOBALS['TL_LANG']['MSC']['btn_inventory_history']			= 'Submit';