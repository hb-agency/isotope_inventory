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
 * @copyright  Winans Creative 2009, Intelligent Spark 2010, iserv.ch GmbH 2010, Olivier El Mekki, 2010
 * @author     Fred Bliss <fred.bliss@intelligentspark.com>
 * @author     Andreas Schempp <andreas@schempp.ch>   
 * @author     Olivier El Mekki 
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */


class ModuleIsotopeInventoryImport extends BackendModule
{

	/**
	 * Name of the template
	 * @var string
	 */
	protected $strTemplate = 'be_inventory_import';
	
	/**
	 * Name of the override checkbox widget
	 * @var string
	 */
	protected $strOverrideMode = 'override_mode';
	
	/**
	 * Name of the file tree widget
	 * @var string
	 */
	protected $strFileTree = 'inventory_file';
	
	/**
	 * Form ID
	 * @var string
	 */
	protected $strFormId = 'tl_iso_warehouses';
	
	/**
	 * Name of the hidden FORM_SUBMIT input
	 * @var string
	 */
	protected $strFormSubmit = 'tl_iso_inventory_import';
	


	public function compile()
	{	
		$this->import('Files');
		$this->loadDataContainer('tl_iso_warehouses');
		$this->Template = new BackendTemplate($this->strTemplate);
		
		// Override mode checkbox data
		$arrOverrideModeData = array
		(
			'id'		=> $this->strOverrideMode,
			'name'		=> $this->strOverrideMode,
		);
		
		// Create override mode checkbox
		$objCheckBox = new CheckBox($arrOverrideModeData);
		$objCheckBox->options = array(array('label'=>$GLOBALS['TL_LANG']['MSC']['inventory_override_quantites'], 'value'=>'1'));
				
		// Create the file tree widget
		$objFileTree = new FileTree($this->prepareForWidget($GLOBALS['TL_DCA'][$this->strFormId]['fields'][$this->strFileTree], $this->strFileTree, null, $this->strFileTree, $this->strFormId));
		
		// Import scripts
		$GLOBALS['TL_CSS'][] 			= 'system/modules/isotope_inventory/html/inventory_import.css';
		
		// Set template values
		$this->Template->importMessage 	= '';	
		$this->Template->href     		= $this->getReferer( true );
		$this->Template->title    		= specialchars($GLOBALS['TL_LANG']['MSC']['backBT']);
		$this->Template->headline  		= specialchars($GLOBALS['TL_LANG']['MSC']['inventory_import_headline']);
		$this->Template->subheadline	= specialchars($GLOBALS['TL_LANG']['MSC']['inventory_import_subheadline']);
		
		$this->Template->formId			= $this->strFormId;
		$this->Template->formSubmit		= $this->strFormSubmit;
		$this->Template->enctype 		= 'application/x-www-form-urlencoded';
		$this->Template->fileTreeId		= $this->strFileTree;
		$this->Template->overrideMode	= $objCheckBox->generate();
		$this->Template->fileTree		= $objFileTree->generate();
		$this->Template->formAction 	= ampersand($this->Environment->request, ENCODE_AMPERSANDS);
		$this->Template->ajaxAction 	= 'startImport';
		$this->Template->ajaxMethod 	= 'start';
		$this->Template->submit			= 'import_submit';
		$this->Template->submitLabel	= $GLOBALS['TL_LANG']['MSC']['btn_inventory_import'];
		
		if ($this->Input->post('FORM_SUBMIT') == $this->strFormSubmit && $this->Input->post($this->strFileTree) != '')
		{
			$this->importInventoryFromCsv();
		}
		
		return $this->Template->parse();
	}
	
	

	/**
	 * Import inventory values from a CSV
	 * Only works with 2 columns - SKU/quantity
	 *
	 * @access	protected
	 */
	protected function importInventoryFromCsv()
	{
		if (!strlen($this->Input->post($this->strFileTree)))
		{
			//$_SESSION['TL_ERROR'][] = $GLOBALS['TL_LANG']['ERR']['all_fields'];
			$_SESSION['TL_ERROR'][] = $GLOBALS['TL_LANG']['ERR']['noCSVFile'];
			$this->reload();
			return;		
		}	
		
		$strFileName = $this->Input->post($this->strFileTree);
		
		// Folders cannot be imported
		if (is_dir(TL_ROOT . '/' . $strFileName))
		{
			$_SESSION['TL_ERROR'][] = sprintf($GLOBALS['TL_LANG']['ERR']['importFolder'], basename($strFileName));
			return;
		}

		$objFile = new File($strFileName);

		// Check the file extension
		if ($objFile->extension != 'csv')
		{
			$_SESSION['TL_ERROR'][] = sprintf($GLOBALS['TL_LANG']['ERR']['filetype'], $objFile->extension);
			return;
		}

		$strFile = $objFile->getContent();
		//$strFile = str_replace("\r", '', $strFile);
		$strName = preg_replace('/\.csv$/i', '', basename($strFileName));
				
		$strSeparator = ',';

		// open file
		if (!($csvFile = fopen(TL_ROOT .'/'. $strFileName, 'r'))) 
		{
			$_SESSION['TL_ERROR'][] = $GLOBALS['TL_LANG']['ERR']['noCSVData'];
			$this->reload();
			return;
		}		
		
		$addcount = 0;
		
		$objWarehouse = new IsotopeWarehouse();
		$objWarehouse->initializeWarehouse($this->Input->get('id'));

		// keep fetching a line from the file until end of file
		while ($arrRow = fgetcsv($csvFile, 4096, $strSeparator)) 
		{									
			if ($this->importInventoryRow($arrRow, $objWarehouse))
				$addcount++;
		}
		
		fclose($csvFile);
			
		$this->Template->importMessage = 'Successfully imported ' . $addcount . ' rows from ' . htmlentities($strFileName);	

		$_SESSION['TL_CONFIRM'][] = sprintf($GLOBALS['TL_LANG']['ERR']['importSuccess'], $addcount);
	}
	
	
	
	/**
	 * Import an inventory row - checks for override mode
	 *
	 * @access	protected
	 * @param	array
	 * @param	object
	 * @return	boolean
	 */
	protected function importInventoryRow($arrRow, &$objWarehouse)	
	{
		// Get product ID and make sure it's a valid product
		$intProductId = $this->getProductIdFromSku($arrRow[0]);
		
		if ($intProductId == 0 || !is_numeric($arrRow[1]))
			return false;
		
		// If override mode, add a row to reset the inventory
		if (strlen($this->Input->post($this->strOverrideMode)) && $this->Input->post($this->strOverrideMode) == '1')
		{
			$objWarehouse->clearProductInventory($intProductId, true);
		}
		
		$objWarehouse->update($intProductId, $arrRow[1], true);	
				
		return true;
	}
	
	
	
	/**
	 * Gets a product's ID from the sku
	 *
	 * @access	protected
	 * @param	string
	 * @return	integer
	 */
	protected function getProductIdFromSku($strSku)
	{
		if (strlen(trim($strSku)) == 0)
		{
			return 0;
		}
			
		$objProductId = $this->Database->prepare("SELECT id FROM tl_iso_products WHERE sku = ?")->execute($strSku);
		
		if ($objProductId->numRows == 0)
		{
			return 0;
		}
		else
		{
			return $objProductId->id;
		}
	}
	
}

