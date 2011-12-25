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
 * Class IsotopeWarehouse
 *
 * @copyright  
 * @author     
 * @package    Model
 */

class IsotopeWarehouse extends Model
{

	/**
	 * Name of the current table
	 * @var string
	 */
  	protected $strTable = 'tl_iso_warehouses';

	/**
	 * Name of the child table
	 * @var string
	 */
	protected $ctable = 'tl_iso_inventory';
	
	/**
	 * Array of rows to be inserted
	 * @var array
	 */
  	protected $arrInsertRows = array();

	/**
	 * Define if data should be threaded as "locked"
	 * @var bool
	 */
	protected $blnLocked = false;

	/**
	 * Configuration
	 * @var array
	 */
	protected $arrSettings = array();

	/**
	 * Record has been modified
	 * @var bool
	 */
	protected $blnModified = false;
  	
  	


	public function __construct()
	{
		parent::__construct();
	
		$this->import('Isotope');	

		// Do not use __destruct, because Database object might be destructed first (see http://dev.contao.org/issues/2236)
		if (!$this->blnLocked)
		{
			register_shutdown_function(array($this, 'saveDatabase'));
		}
	}
	


	/**
	 * Shutdown function to save data if modified
	 */
	public function saveDatabase()
	{
		$this->save();
	}
	
	


	public function __get($strKey)
	{
		switch($strKey)
		{
			case 'blnLocked':
				return $this->blnLocked;

			default:
				return parent::__get($strKey);
		}
	}




	public function __set($strKey, $varValue)
	{
		parent::__set($strKey, $varValue);
	}
	



	/**
	 * Check whether a property is set
	 *
	 * @param string
	 * @return boolean
	 */
	public function __isset($strKey)
	{
		if (isset($this->arrData[$strKey]) || isset($this->arrSettings[$strKey]))
			return true;

		return false;
	}



	/**
	 * Load settings from database field
	 *
	 * @access	public
	 * @param  	string
	 * @param  	mixed
	 * @return 	boolean
	 */
	public function findBy($strRefField, $varRefId)
	{
		if (parent::findBy($strRefField, $varRefId))
		{
			$this->arrSettings = deserialize($this->arrData['settings'], true);

			return true;
		}

		return false;
	}



	/**
	 * Load settings from database field
	 *
	 * @param  object
	 * @param  string
	 * @param  string
	 */
	public function setFromRow(Database_Result $resResult, $strTable, $strRefField)
	{
		parent::setFromRow($resResult, $strTable, $strRefField);

		$this->arrSettings = deserialize($this->arrData['settings'], true);
	}



	/**
	 * Load current warehouse
	 */
	public function initializeWarehouse($intWarehouseId)
	{
		$time = time();
		$this->strHash = $this->Input->cookie($this->strCookie);

		$objWarehouse = $this->Database->prepare("SELECT * FROM {$this->strTable} WHERE id=?")->execute($intWarehouseId);
		
		
		// Create new cart
		if ($objWarehouse->numRows)
		{
			$this->setFromRow($objWarehouse, $this->strTable, 'id');
			$this->tstamp = $time;
		}
		else
		{
			$this->setData(array
			(
				'pid'				=> '0',
				'tstamp'			=> time(),
				'name'				=> '',
				'defaultWarehouse'	=> '',
				'description'		=> '',
				'company'			=> '',
				'firstname'			=> '',
				'lastname'			=> '',
				'email'				=> '',
				'phone'				=> '',
				'street_1'			=> '',
				'street_2'			=> '',
				'street_3'			=> '',
				'postal'			=> '',
				'city'				=> '',
				'subdivision'		=> '',
				'country'			=> 'us',
			));
		}
	}
	


	/**
	 * Update database
	 *
	 * @param  bool
	 * @return int
	 */
	public function save($blnForceInsert=false)
	{
		if ($this->blnModified)
		{
			$this->arrData['tstamp'] = time();
			$this->arrData['settings'] = serialize($this->arrSettings);
		}

		$this->updateInventory();
				

		if ($this->blnRecordExists && $this->blnModified && !$blnForceInsert)
		{
			return parent::save($blnForceInsert);
		}
		elseif ((!$this->blnRecordExists && $this->blnModified) || $blnForceInsert)
		{
			$this->findBy('id', parent::save($blnForceInsert));

			return $this->id;
		}
	}


	/**
	 * Update child table records when dropping this collection.
	 *
	 * @access public
	 * @return int
	 */
	public function delete()
	{
		// HOOK for adding additional functionality when deleting a collection
		if (isset($GLOBALS['ISO_HOOKS']['deleteWarehouse']) && is_array($GLOBALS['ISO_HOOKS']['deleteWarehouse']))
		{
			foreach ($GLOBALS['ISO_HOOKS']['deleteWarehouse'] as $callback)
			{
				$this->import($callback[0]);
				$blnRemove = $this->$callback[0]->$callback[1]($this);

				if ($blnRemove === false)
					return 0;
			}
		}
		
		// This updates tl_iso_products before we remove any rows
		$this->removeInventory();

		$intAffectedRows = parent::delete();

		if ($intAffectedRows > 0)
		{
			$this->Database->prepare("DELETE FROM {$this->ctable} WHERE pid=?")->set()->execute($this->id);
		}

		$this->arrCache = array();
		$this->arrInsertRows = null;

		return $intAffectedRows;
	}
	
	
	
	/** 
	 * Update inventory for a product in this warehouse.
	 * This will create new rows for the child table.
	 *
	 * @access 	public
	 * @param 	integer
	 * @param 	integer
	 * @param 	bool
	 */
	public function update($intProductId, $intQuantity, $blnFromBatch=false)
	{		
		$this->arrInsertRows[] = array
		(
			'pid'			=> $this->id,
			'tstamp'		=> time(),
			'product_id'	=> $intProductId,
			'quantity'		=> $intQuantity,
			'frombatch'		=> $blnFromBatch ? '1' : '',
		);
	}
	
	
	
	/** 
	 * Update inventory for this warehouse.
	 * This will insert new rows into the child table and update `qtyonhand` and `qtybackordered` in tl_iso_products.
	 *
	 * @access 	protected
	 * @param 	array
	 */
	protected function updateInventory($arrRows=null)
	{
		if (!is_array($arrRows))
		{
			$arrRows = $this->arrInsertRows;
		}
			
		if (count($arrRows))
		{
			foreach ($arrRows as $key=>$row)
			{
				try
				{
					$objInvProduct = new IsotopeInventoryProduct($row['product_id']);
					
					$intAddRemoveQty = intval($row['quantity']);
									
				
					// Get the product's low inventory threshold
					$intLowThreshold = $objInvProduct->getLowInventoryThreshold();		
							
				
					// Get current quantity on hand
					$intCurrentQty = $this->getProductInventory($row['product_id']);
					
					
					// Determine the new quantity
					$intNewQty = intval($intCurrentQty) + $intAddRemoveQty;
					
					
					// @todo: Use IsotopeMail to send an email notification about warehouse low inventory
					
					// If inventory for this warehouse will be below zero, set it to 0
					if ($intNewQty < 0)
					{
						$row['quantity'] = $intCurrentQty * -1;
					}
					
										
					// Update the quantity on hand and/or the quantity backordered
					$objInvProduct->updateInventory($intAddRemoveQty);
					
					// Update the inventory table									
					$this->Database->prepare("INSERT INTO {$this->ctable} %s")->set($row)->executeUncached();
					
					
				}			
				catch (Exception $e)
				{
					$this->log("Error updating inventory for warehouse ID {$this->id}: " . $e->getMessage(), __METHOD__, TL_ERROR);
				}
			}
		}
		
		$this->arrInsertRows = array();
	}
	
	
	
	/** 
	 * Remove inventory from tl_iso_products.
	 * This will update `qtyonhand` and `qtybackordered` in tl_iso_products when removing a warehouse.
	 *
	 * @access 	protected
	 */
	protected function removeInventory()
	{
		$objInventory = $this->Database->prepare("SELECT product_id, (SUM(quantity) * -1) AS quantity, '' AS frombatch FROM {$this->ctable} WHERE pid = ? GROUP BY product_id")->executeUncached($this->id);
		
		if ($objInventory->numRows > 0)
		{
			$arrProducts = $objInventory->fetchAllAssoc();
			
			foreach ($arrProducts as $product)
			{
				$this->clearProductInventory($product['id'], false);
			}
		}
	}
		
		
		
	
	/**
	 * Get a product's inventory from its ID (only inventory from this warehouse).
	 *
	 * @access 	public
	 * @param	integer
	 * @return	integer
	 */
	public function getProductInventory($intProductId)
	{
		$objReturnData = $this->Database->prepare("SELECT SUM(quantity) AS `qty_in_stock` FROM {$this->ctable} WHERE pid = ? AND product_id = ?")->executeUncached($this->id, $intProductId);
				
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
	 * Clear product inventory in tl_iso_inventory and tl_iso_products.
	 * This will zero out the quantity in tl_iso_inventory and update `qtyonhand` and `qtybackordered` in tl_iso_products.
	 *
	 * @access 	public
	 * @param	integer
	 */
	public function clearProductInventory($intProductId, $blnFromBatch=false)
	{		
		$intInventory = $this->getProductInventory($intProductId);
		
		if ($intInventory > 0)
		{
			$intInventory *= (-1);
			
			// Zero out the quantities in tl_iso_inventory for this warehouse
			$arrRow = array
			(
				'pid'			=> $this->id,
				'tstamp'		=> time(),
				'product_id'	=> $intProductId,
				'quantity'		=> $intInventory,
				'frombatch'		=> $blnFromBatch ? '1' : '',
			);
			
			$this->Database->prepare("INSERT INTO {$this->ctable} %s")->set($arrRow)->executeUncached();
			
			// Update product quantity
			$objInvProduct = new IsotopeInventoryProduct($intProductId);
			$objInvProduct->updateInventory($intInventory);
		}
	}
	


	/**
	 * Fetch products from database.
	 * 
	 * @access 	public
	 * @param	string
	 * @param	array
	 * @param	string
	 * @param	bool
	 * @param	array
	 * @return 	array
	 */
	public function getProducts($sort, $where = null, $limit = null, $count_only = false, $args=null)
	{
		$arrProducts = array();
		
	 	if ( $count_only )
		{
			$objResult = $this->Database->prepare("SELECT COUNT(*) AS count FROM tl_iso_products WHERE pid=0")->limit(1)->execute();
			return $objResult->count;
		}
		
		if (!count($arrProducts))
		{
			if ( $where )
			{
			  $query .= ' where ' . implode(' AND ', $where );
			  $values = implode(',', $args );
			}
		
			$query .= ' order by ' . $sort;
		
			if ( $limit )
			{
			  $query .= ' limit ' . $limit;
			}
	
			$objItems = $this->Database->prepare("SELECT p.*, (SELECT SUM(quantity) FROM tl_iso_inventory i WHERE i.pid=? AND i.product_id=p.id ORDER BY tstamp DESC LIMIT 1) AS total_quantity FROM tl_iso_products p".$query)->execute($this->Input->get('id'), $values);
			
			while( $objItems->next() )
			{
				$arrData = array();
				
				$objProductData = $this->Database->prepare("SELECT *, (SELECT class FROM tl_iso_producttypes WHERE tl_iso_products.type=tl_iso_producttypes.id) AS product_class FROM tl_iso_products WHERE pid={$objItems->id} OR id={$objItems->id}")->limit(1)->execute();
								
				$strClass = $GLOBALS['ISO_PRODUCT'][$objProductData->product_class]['class'];
				
				try
				{
					$intQuantity = ($objItems->total_quantity ? $objItems->total_quantity : 0);
						
					$blnVariants = ($objItems->pid !=0 ? true : false);
				
					$objProduct = new $strClass($objProductData->row(), $this->blnLocked);
							
				}
				catch (Exception $e)
				{
					$objProduct = new IsotopeProduct(array('id'=>$objItems->id, 'sku'=>$objItems->sku, 'name'=>$objItems->name), $this->blnLocked);
				}
						
				if($objProduct->pid)
					$objProduct->loadVariantData($objProductData->row());
				
				$objProduct->quantity = $intQuantity;
				
				$arrProducts[$objProduct->id] = $objProduct;
			}
		}
		
		return $arrProducts;
		
	}
	
	
	/**
	 * Search for products.
	 * 
	 * @access 	public
	 * @param	array
	 * @param	array
	 * @param	array
	 * @param	integer
	 * @param	bool
	 * @return 	array
	 */
	public function searchProducts( $search = array(), $filter = array(), $search_fields = array(), $limit = 10, $countOnly = false )
	{
		$where = null;
		
		if ( count( $search ) or count( $filter ) )
		{
		  $where   = array();
		  $args  = array();
		
		  foreach ( $search as $key => $value )
		  {
			if ( in_array( $key, $search_fields ) )
			{
			  $where[]   = 'p.' . $key . ' like ?';
			  $args[] = '%' . $value . '%';
			}
		  }
		
		  foreach ( $filter as $key => $value )
		  {
			$where[]   .= 'p.' . $key . ' = ?';
			$args[] = $value;
		  }
		
		}
				
		return $this->getProducts('p.id', $where, $limit, $countOnly, $args);
	}
	
	
	
	
	/** 
	 * TEMPORARILY KEEPING THIS WHILE DEVELOPING THE NEW CLASSES
	 * Update inventory for this warehouse.
	 * This will insert new rows into the child table and update `qtyonhand` and `qtybackordered` in tl_iso_products.
	 *
	 * @access 	protected
	 * @param 	array
	 */
	/*protected function updateInventory($arrRows=null)
	{
		if (!is_array($arrRows))
		{
			$arrRows = $this->arrInsertRows;
		}
			
		if (count($arrRows))
		{
			foreach ($arrRows as $key=>$row)
			{
				try
				{
					// Get the product type's low inventory threshold
					$intLowThreshold = $this->getProductLowInventoryThreshold($row['product_id']);		
							
				
					// Get current quantity on hand
					$intCurrentQty = $this->getProductInventory($row['product_id']);
					
					
					// Determine the new quantity
					$intNewQty = intval($intCurrentQty) + intval($row['quantity']);
					
					
						
					// If the quantity is below the threshold, send an admin email
					if ($intNewQty < $intLowThreshold)
					{
						$strType = 'low';
						// TODO: Use IsotopeMail to send an email notification
						//$this->sendAdminNotification($row['product_id'], $strType);
					}
					
					
					// Product update "set" array
					$arrProdSet = array();
					
					
					// If quantity is below zero, get the amount below zero and set the backordered quantity if backordering is enabled
					if ($intNewQty < 0 && $this->allowProductBackordering($intProductId))
					{
					
						// Set product's inventory equal to zero
						$row['quantity'] = $intCurrentQty * -1;
						
						
						// Set product's backordered quantity
						$arrProdSet['qtybackordered'] = ($intNewQty * -1);
						
						
						// Send admin email regarding backordered quantity
						$strType = 'zero';
						// @todo: Use IsotopeMail to send an email notification
						//$this->sendAdminNotification($row['product_id'], $strType);
					}
					
					
					// Update the inventory table									
					$objResult = $this->Database->prepare("INSERT INTO {$this->ctable} %s")->set($row)->executeUncached();
					
										
					// Update the quantity on hand and/or the quantity backordered
					$arrProdSet['qtyonhand'] = ($intNewQty < 0) ? 0 : $intNewQty;	
									
					$this->Database->prepare("UPDATE `tl_iso_products` %s WHERE id = ?")->set($arrProdSet)->executeUncached($row['product_id']);
					
				}			
				catch (Exception $e)
				{
					$this->log("Error updating inventory for warehouse ID {$this->id}: " . $e->getMessage(), __METHOD__, TL_ERROR);
				}
			}
		}
		
		$this->arrInsertRows = array();
	}*/
	
}

