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


class ModuleIsotopeProductListInventory extends ModuleIsotopeProductList
{

	/**
	 * Template
	 * @var string
	 */
	protected $strTemplate = 'mod_iso_productlist';

	protected $strOrderBySQL = 'c.sorting';
	
	protected $strFilterSQL;
	
	protected $strSearchSQL;
	
	protected $arrParams;
	
	
	/**
	 * Display a wildcard in the back end
	 * @return string
	 */
	public function generate()
	{		
		if (TL_MODE == 'BE')
		{
			$objTemplate = new BackendTemplate('be_wildcard');
			$objTemplate->wildcard = '### ISOTOPE ECOMMERCE: INVENTORY PRODUCT LIST ###';
			$objTemplate->title = $this->headline;
			$objTemplate->id = $this->id;
			$objTemplate->link = $this->name;
			$objTemplate->href = $this->Environment->script.'?do=modules&amp;act=edit&amp;id=' . $this->id;
			
			return $objTemplate->parse();
		}

		return parent::generate();
	}


	/**
	 * Fill the object's arrProducts array
	 * @return array
	 */
	protected function findProducts($arrCacheIds=null)
	{
		$arrIds = $this->findCategoryProducts($this->iso_category_scope, $this->iso_list_where);
		
		if (is_array($arrCacheIds) && count($arrCacheIds))
		{
			$arrIds = array_intersect($arrIds, $arrCacheIds);
			
			foreach($arrIds as $k=>$id)
			{		
				$objProductTypeData = $this->getProductTypeData($id);
				
				$objQuantity = $this->Database->prepare("SELECT SUM(quantity) as qty FROM tl_iso_inventory WHERE product_id=?")->execute($id);
				
				if($objQuantity->qty < 1 && $objProductTypeData->hidezeroinvproducts == '1')
				{
					unset($arrIds[$k]);
				}
			}
		}
		
		$strSQL = $this->Isotope->getProductSelect();
		$strSQL .= " WHERE p1.published='1' AND p1.language='' AND p1.id IN (" . implode(',', $arrIds) . ")";
		
		$objProductData = $this->Database->execute($strSQL);

		list($arrFilters, $arrSorting) = $this->getFiltersAndSorting();

		return $this->getProducts($objProductData, true, $arrFilters, $arrSorting);
	}
	

	
	private function getProductTypeData($intProductId)
	{
		return $this->Database->prepare("SELECT pt.* FROM tl_iso_products p INNER JOIN tl_iso_producttypes pt ON p.type = pt.id WHERE p.id = ?")->limit(1)->execute($intProductId);
	}


}

