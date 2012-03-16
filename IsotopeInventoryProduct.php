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


class IsotopeInventoryProduct extends IsotopeProduct
{

	/**
	 * Name of the inventory table
	 * @var string
	 */
	protected $itable = 'tl_iso_inventory';
	
	
	
	/**
	 * Constructor that allows us to build the object from an ID or an array.
	 *
	 * @param  mixed
	 * @param  array
	 * @param  boolean
	 * @return int
	 */
	public function __construct($varData, $arrOptions=null, $blnLocked=false)
	{
		$this->import('Database');
		
		if (!is_array($varData) && is_numeric($varData))
		{
			$objRow = $this->Database->prepare("SELECT * FROM tl_iso_products WHERE id = ?")->limit(1)->execute($varData);
			
			if ($objRow->numRows > 0)
			{
				$varData = $objRow->row();
			}
		}
		
		parent::__construct($varData, $arrOptions, $blnLocked);
	}
	
		
	/**
	 * Get a property
	 * @return mixed
	 */
	public function __get($strKey)
	{	
		return parent::__get($strKey);
	}
	
	
	/**
	 * Set a property
	 */
	public function __set($strKey, $varValue)
	{
		parent::__set($strKey, $varValue);
	}
		
	
	
	/**
	 * Get product type row as DB result.
	 *
	 * @access 	protected
	 * @return 	object
	 */
	protected function getProductTypeData()
	{	
		return $this->Database->prepare("SELECT pt.* FROM tl_iso_products p INNER JOIN tl_iso_producttypes pt ON p.type = pt.id WHERE p.id = ?")->limit(1)->execute($this->id);
	}
		
		
		
	
	/**
	 * Get the current inventory.
	 *
	 * @access 	public
	 * @return	integer
	 */
	public function getInventory()
	{
		$objReturnData = $this->Database->prepare("SELECT SUM(quantity) AS `qty_in_stock` FROM {$this->itable} WHERE product_id = ?")->executeUncached($this->id);
				
		if ($objReturnData->numRows == 0)
		{
			return 0;		
		}
		else
		{
			return intval($objReturnData->qty_in_stock);
		}
	}
	
		
	
	/**
	 * Check whether or not backordering is allowed for this product.
	 *
	 * @access 	public
	 * @return	bool
	 */
	public function allowBackordering()
	{
		$objReturnData = $this->getProductTypeData();
		
		if ($objReturnData->numRows == 0 || $objProductTypeData->allowbackorders == null)
		{
			return false;
		}
		
		if ($objProductTypeData->allowbackorders == '1')
		{
			return true;
		}		
		
		return false;
	}
	
		
	
	/**
	 * Gets the low inventory threshold.
	 *
	 * @access 	public
	 * @return	integer
	 */
	public function getLowInventoryThreshold()
	{
		$objReturnData = $this->getProductTypeData();
		
		if ($objReturnData->numRows == 0 || $objReturnData->lowthreshold == null || $objReturnData->lowthreshold == 0)
		{
			return 3;
		}
		else
		{
			return intval($objReturnData->lowthreshold);
		}
	}
	
	
	
	/** 
	 * Update inventory.
	 * This will update `qtyonhand` and `qtybackordered`.
	 *
	 * @access 	protected
	 * @param 	array
	 * @return	array
	 */
	public function updateInventory($intQuantity)
	{	
		try
		{
			$arrReturn = array('sendEmail'=>false, 'newQty'=>0);
			
			// Get the product type's low inventory threshold
			$intLowThreshold = $this->getLowInventoryThreshold();		
					
		
			// Get current quantity on hand
			$intCurrentQty = $this->getInventory();
			
			
			// Determine the new quantity
			$intNewQty = intval($intCurrentQty) + intval($intQuantity);			
			$arrReturn['newQty'] = $intNewQty;
			
				
			// If the quantity is below the threshold, send an admin email
			if ($intNewQty < $intLowThreshold)
			{
				$arrReturn['sendEmail'] = true;
			}
			
			
			// Product update "set" array
			$arrProdSet = array();
			
			
			// If quantity is below zero, get the amount below zero and set the backordered quantity if backordering is enabled
			if ($intNewQty < 0 && $this->allowBackordering())
			{				
				
				// Set product's backordered quantity
				$arrProdSet['qtybackordered'] = ($intNewQty * -1);
			}
			
			// Don't let the qty on hand go below zero
			$arrProdSet['qtyonhand'] = ($intNewQty < 0) ? 0 : $intNewQty;
							
			// Update the quantity on hand and possibly the quantity backordered
			$this->Database->prepare("UPDATE `tl_iso_products` %s WHERE id = ?")->set($arrProdSet)->executeUncached($this->id);
			
			return $arrReturn;
			
		}			
		catch (Exception $e)
		{
			$this->log("Error updating inventory for product \"{$this->name}\": " . $e->getMessage(), __METHOD__, TL_ERROR);
		}
	}	
	
	
}

