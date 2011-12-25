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


class InventoryBackend extends IsotopeBackend
{
	
	public function __construct()
	{
		parent::__construct();
	}
	
	
	/**
	 * Generate a section in Product Management to update inventory.
	 * 
	 * @access 	protected
	 * @param	integer
	 * @param	string
	 * @return 	string
	 */
	public function generateInventoryWizard(DataContainer $dc, $xlabel)
	{
		// Load language file for the foreign key table.	@TODO: only if file_exists==true;
		$this->loadLanguageFile('tl_iso_warehouses');

		$intId = $this->Input->get('id');
				
		$arrUnits = array();
				
		$return = $xlabel;
		
		$return .= '<table cellspacing="0" cellpadding="5" id="ctrl_'.$dc->field.'" class="tl_inventorywizard" summary="Inventory wizard">
  <thead>
  <tr>
    <td>'.$GLOBALS['TL_LANG']['tl_iso_products']['warehouse_name'].'</td>
    <td>'.$GLOBALS['TL_LANG']['tl_iso_products']['quantity'].'</td>
    <td>'.$GLOBALS['TL_LANG']['tl_iso_products']['new_quantity'].'</td>
  </tr>
  </thead>
  <tbody>';
		
		// Get current quantites from inventory
		$objQty = $this->Database->query("SELECT id, name, (SELECT SUM(quantity) FROM tl_iso_inventory WHERE tl_iso_inventory.pid=tl_iso_warehouses.id AND tl_iso_inventory.product_id=$intId) AS total_quantity FROM tl_iso_warehouses");

		if(!$objQty->numRows)
		{
			return '<em>'.$GLOBALS['TL_LANG']['MSC']['noWarehouses'].'</em>';
		}
		
		while($objQty->next())
		{
			$arrQty[$objQty->id] = array
			(
				'warehouse_name'	=> $objQty->name,
				'quantity'			=> $objQty->total_quantity
			);
		}
		
		$return .= '<tbody>';
		
		foreach ($arrQty as $key=>$value)
		{
			$return .= '<tr>';
			$return .= '	<td>';
			$return .= '<strong>'.$value['warehouse_name'].'</strong>';
			$return .= '	</td>';
			$return .= '	<td>';
			$return .= $value['quantity'];
			$return .= '	</td>';
			$return .= '	<td>';
			$return .= '<input type="text" name="'.$dc->field.'['.$key.']" id="ctrl_'.$dc->field.'" class="tl_text_4" value="0" onfocus="Backend.getScrollOffset();" />';
			$return .= '	</td>';
			$return .= '</tr>';
		}
		
		$return .= '</tbody>';
		
		if($this->Input->post('FORM_SUBMIT')==$dc->table)
		{
			$arrInserts = array();
			
			$varValue = $this->Input->post($dc->field);

			if(!is_array($varValue) || !count($varValue))
				return $varValue;
				
			foreach($varValue as $i=>$value)
			{
				$arrInserts[] = "(".time().",".$i.",".$dc->id.",".$value.",'')";
			
			}
			
			if(!count($arrInserts))
				return $varValue;
				
			$strInserts = implode(',', $arrInserts);
			
			$this->Database->query("INSERT INTO tl_iso_inventory (tstamp,pid,product_id,quantity,frombatch)VALUES".$strInserts);
		}
		
		return $return . '</table>';
	}
	
	
	/**
	 * Generate a link to edit a product from the backend Warehouses module.
	 * 
	 * @access 	protected
	 * @param	integer
	 * @param	string
	 * @return 	string
	 */
	protected function generateEditLink($intRecordId, $strLinkTitle)
	{
		return '<a href="'.$this->Environment->script.'?do=iso_products&act=edit&id='.$intRecordId.'" title="'.sprintf($GLOBALS['TL_LANG']['tl_iso_products']['edit'][1],$intRecordId).'">'.$strLinkTitle.'</a>';
	}
	
	
}

