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


class InventoryFrontend extends IsotopeFrontend
{
	
	public function __construct()
	{
		parent::__construct();
	}
	
	
	/**
	 * Callback for isoButton Hook.
	 */
	public function defaultButtons($arrButtons)
	{
		$arrButtons['update'] = array('label'=>$GLOBALS['TL_LANG']['MSC']['buttonLabel']['update']);
		$arrButtons['add_to_cart'] = array('label'=>$GLOBALS['TL_LANG']['MSC']['buttonLabel']['add_to_cart'], 'callback'=>array('InventoryFrontend', 'addToCart'));
		
		return $arrButtons;
	}
	
	
	/**
	 * Callback for add_to_cart button
	 *
	 * @access	public
	 * @param	object
	 * @return	void
	 */
	public function addToCart($objProduct, $objModule=null)
	{
		$intQuantity = ($objModule->iso_use_quantity && intval($this->Input->post('quantity_requested')) > 0) ? intval($this->Input->post('quantity_requested')) : 1;
		
		if ($this->Isotope->Config->enableInventory)
		{
			$objWarehouse = $this->getWarehouse();
			
			$objInvProduct = $this->getInventoryProduct($objProduct->id);
			
			$intInventory = $objInvProduct->getInventory();
						
			$blnAllowBackorders = $objInvProduct->allowBackordering();
						
			if ($intQuantity > $intInventory && !$blnAllowBackorders)
			{
				$intQuantity = $intInventory;
				$_SESSION['ISO_ERROR']['quantity'] = $GLOBALS['TL_LANG']['MSC']['quantity_error'];
			}		
		}	
				
		if ($this->Isotope->Cart->addProduct($objProduct, $intQuantity) !== false)
		{
			$_SESSION['ISO_CONFIRM'][] = $GLOBALS['TL_LANG']['MSC']['addedToCart'];
			$this->jumpToOrReload($objModule->iso_addProductJumpTo);
		}
	}
	
	
	/**
	 * Callback for add product to Collection
	 *
	 * @access	public
	 * @param	object
	 * @param	int
	 * @param	object
	 * @return	int
	 */
	public function addProductToCollection($objProduct, $intQuantity, $objCollection)
	{		
		if (!$this->Isotope->Config->enableInventory)
			return $intQuantity;
			
		$totalQuantity = $intQuantity;
		
		$objItem = $this->Database->prepare("SELECT * FROM {$objCollection->ctable} WHERE pid={$objCollection->id} AND product_id={$objProduct->id} AND product_options='".serialize($objProduct->getOptions(true))."'")->limit(1)->execute();
		
		if ($objItem->numRows)
		{
			$totalQuantity += $objItem->product_quantity;
		}
		
		$objWarehouse = $this->getWarehouse();
		
		$objInvProduct = $this->getInventoryProduct($objProduct->id);
		
		$intInventory = $objInvProduct->getInventory();
					
		$blnAllowBackorders = $objInvProduct->allowBackordering();
		
		if ($totalQuantity > $intInventory && !$blnAllowBackorders)
		{
			$intQuantity = ($totalQuantity - $intInventory) < $intQuantity ? ($intQuantity - ($totalQuantity - $intInventory)) : 0;
			$_SESSION['ISO_ERROR']['quantity'] = $GLOBALS['TL_LANG']['MSC']['quantity_error'];
		}
		
		return $intQuantity;	
	}
	
	/**
	 * Callback for add product to Collection
	 *
	 * @access	public
	 * @param	object
	 * @param	int
	 * @param	object
	 * @return	void
	 */
	public function updateProductInCollection($objProduct, $arrSet, $objCollection)
	{
		if (!$this->Isotope->Config->enableInventory)
			return $arrSet;
				
		$objWarehouse = $this->getWarehouse();
		
		$objInvProduct = $this->getInventoryProduct($objProduct->id);
		
		$intInventory = $objInvProduct->getInventory();
					
		$blnAllowBackorders = $objInvProduct->allowBackordering();
		
		if ($arrSet['product_quantity'] > $intInventory && !$blnAllowBackorders)
		{
			$arrSet['product_quantity'] = $intInventory;
			$_SESSION['ERROR']['quantity'] = $GLOBALS['TL_LANG']['MSC']['quantity_error'];
		}
		
		return $arrSet;	
	}
	
	
	/** 
	 * hook function to update product inventory levels
	 * @access public
	 * @param object
	 * @param object
	 * @return boolean
	 */
	public function updateInventory($objOrder, $arrItems)
	{	
		if (!$this->Isotope->Config->enableInventory)
			return;
	
		$arrProducts = $objOrder->getProducts();
				
		if (is_array($arrProducts) && count($arrProducts))
		{
			$arrRows = array();
					
			$objWarehouse = $this->getWarehouse();
			
			foreach($arrProducts as $i=>$objProduct)
			{
				$objWarehouse->update($objProduct->id, (-1)*$objProduct->quantity_requested);
			}		
		}
	}
	
	
	/**
	 * Get a warehouse object
	 *
	 * @access	protected
	 * @return	object
	 */
	protected function getWarehouse()
	{		
		$arrWarehouses = deserialize($this->Isotope->Config->warehouses, true);
			
		//TODO: determine closest eligible warehouse the items should be shipped from
		/*
			
		*/
		
		//For now just grab the first warehouse...
		$intWarehouseId = $arrWarehouses[0];
		
		$objWarehouse = new IsotopeWarehouse();
		$objWarehouse->initializeWarehouse($intWarehouseId);
		
		return $objWarehouse;
	}
	
	
	/**
	 * Get an inventory product object
	 *
	 * @access	protected
	 * @param	integer
	 * @return	object
	 */
	protected function getInventoryProduct($intProductId)
	{
		$objInvProduct = new IsotopeInventoryProduct($intProductId);
		
		return $objInvProduct;
	}
	
	
	
	/** 
	 * Check availability of a given product based on backorder setting and inventory level
	 * @access public
	 * @param integer $intProductId
	 */
	public function checkProductAvailability($intProductId)
	{
		$objQuantity = $this->Database->execute("SELECT SUM(quantity) as quantity_in_stock FROM tl_iso_inventory WHERE product_id=$intProductId");
		
		if(!$objQuantity->numRows)	//if no record then assume available?
			return true;
		
		if($objQuantity->quantity_in_stock<=0)
			return false;
	}
	
	
	
	/**
	 * Add quantity as variant attribute for a product
	 * @param	array
	 * @param	array
	 * @param	object
	 */
	public function addAttributes(&$arrAttributes, &$arrVariantAttributes, $objProduct)
	{
		$arrAttributes[] = 'quantity';
		$arrVariantAttributes[] = 'quantity';
	}
	
	
}

