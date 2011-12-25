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

// THIS CLASS IS NOT USED ANYMORE, JUST SAVING TEMPORARILY!
class IsotopeInventory extends Model
{

	/**
	 * Name of the current table
	 * @var string
	 */
	protected $strTable = 'tl_iso_inventory';

	/**
	 * Name of the child table
	 * @var string
	 */
	protected $ctable = 'tl_iso_products';

	/**
	 * Name of the parent table
	 * @var string
	 */
	protected $ptable = 'tl_iso_warehouses';

	/**
	 * Define if data should be threaded as "locked"
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

	/**
	 * The current warehouse ID
	 * @var integer
	 */
	protected $intWarehouse;

	/**
	 * The current product ID
	 * @var integer
	 */
	protected $intProductId;
	



	public function __construct($intProductId, $intWarehouseId=0)
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
			case 'productid':
			case 'productId':
				return $this->product_id;

			default:
				return parent::__get($strKey);
		}
	}


	public function __set($strKey, $varValue)
	{
		/*switch( $strKey )
		{
			// Order ID cannot be changed, it is created through IsotopeOrder::generateOrderId on checkout
			case 'order_id':
				throw new Exception('IsotopeOrder order_id cannot be changed trough __set().');
				break;

			default:*/
				parent::__set($strKey, $varValue);
		/*}*/
	}
	


	/**
	 * Check whether a property is set
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
	 * @param  string
	 * @param  mixed
	 * @return boolean
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
	 * Update database with latest product prices and store settings
	 */
	public function save($blnForceInsert=false)
	{
		if ($this->blnModified)
		{
			$this->arrData['tstamp'] = time();
			$this->arrData['settings'] = serialize($this->arrSettings);
		}

		/* TODO: Update tl_iso_products whenever an inventory record is added/updated
		$arrProducts = $this->getProducts();
		if (is_array($arrProducts) && count($arrProducts))
		{
			foreach( $arrProducts as $objProduct )
			{
				$this->Database->prepare("UPDATE {$this->ctable} SET %s WHERE id=?")->set()->execute($this->product_id);
			}
		}*/

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
		if (isset($GLOBALS['ISO_HOOKS']['deleteInventoryRow']) && is_array($GLOBALS['ISO_HOOKS']['deleteInventoryRow']))
		{
			foreach ($GLOBALS['ISO_HOOKS']['deleteInventoryRow'] as $callback)
			{
				$this->import($callback[0]);
				$blnRemove = $this->$callback[0]->$callback[1]($this);

				if ($blnRemove === false)
					return 0;
			}
		}

		$intAffectedRows = parent::delete();

		/* TODO: Update tl_iso_products whenever an inventory record is deleted
		if ($intAffectedRows > 0)
		{
			$this->Database->prepare("UPDATE " . $this->ctable . " %s WHERE id=?")->set()->execute($this->product_id);
		}

		$this->arrCache = array();
		$this->arrProducts = null;*/

		return $intAffectedRows;
	}
	
	
	
}

