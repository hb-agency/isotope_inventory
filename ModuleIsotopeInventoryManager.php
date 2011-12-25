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


class ModuleIsotopeInventoryManager extends BackendModule
{

	protected $strTemplate = 'be_inventory_quantities';


	public function compile()
	{		
		$this->Template = new BackendTemplate('be_inventory_quantities');
		
		$arrSearchFields = array('name', 'description', 'sku');

		$objWarehouse = new IsotopeWarehouse();
		
		if (!$objWarehouse->findBy('id', $this->Input->get('id')))
		{
			$this->redirect( $this->Environment->script . '?act=error' );
		}
				
		$submit         = $this->Input->post('FORM_SUBMIT');
		$session        = $this->Session->getData();
		$name           = 'tl_quantities_' . CURRENT_ID;
						
		// handles search and limit
		if ( $submit == 'tl_filters' )
		{
		  $session[ 'search' ][ $name ][ 'value' ] = '';
		  $session[ 'search' ][ $name ][ 'field' ] = $this->Input->post( 'tl_field', true );
		
		  if ( $this->Input->postRaw( 'tl_value' ) != '' )
		  {
			$session[ 'search' ][ $name ][ 'value' ] = $this->Input->postRaw( 'tl_value' );
		  }
		
		
		  if ( $this->Input->post( 'tl_limit' ) != 'tl_limit' )
		  {
			$session[ 'filter' ][ $name ][ 'limit' ] =  $this->Input->post( 'tl_limit' );
		  }
		
		  else
		  {
			unset( $session[ 'filter' ][ $name ][ 'limit' ] );
		  }
		
		  $this->Session->setData( $session );
		  $this->reload();
		}	
		
		
		// prepare filters
		$search_criteria  = ( strlen( $session[ 'search' ][ $name ][ 'value' ] ) ) ? array( $session[ 'search' ][ $name ][ 'field' ] => $session[ 'search' ][ $name ][ 'value' ] ) : array();
		
		$count            = $objWarehouse->searchProducts( $search_criteria, array(), $arrSearchFields, null, true );
		$limits           = array();
		$perPage          = $GLOBALS[ 'TL_CONFIG' ][ 'resultsPerPage' ];
		
	
		for ( $i = 0; $i < ceil( $count / $perPage ); $i++ )
		{
		  $start = $i * $perPage;
		  $limits[ "$start,$perPage" ] = ( $start + 1 ) . '-' . ( $start + $perPage <= ( $count ) ? $start + $perPage : $count );
		}
		
		if ( ! in_array( $session[ 'filter' ][ $name ][ 'limit' ], array_keys( $limits ) ) )
		{
		  unset( $session[ 'filter' ][ $name ][ 'limit' ] );
		  $this->Session->setData( $session );
		}
		
		$products = array();
		
		$products = $objWarehouse->searchProducts( $search_criteria, array(), $arrSearchFields, $session[ 'filter' ][ $name ][ 'limit' ] );
	
		if(count($products))
		{
			$arrProducts = array();
			$arrQuantities = array();
			
			foreach($products as $i=>$objProduct)
			{	
				if($objProduct->pid!=0)
				{
					$arrQuantities[$objProduct->pid] += $objProduct->quantity;	//establish product quantities by parent id
				}
			}
				
			foreach($products as $i=>$objProduct)
			{
				if($objProduct->pid==0 && array_key_exists($objProduct->id, $arrQuantities))
				{
					$objProduct->total_quantity = $arrQuantities[$objProduct->id];
					$objProduct->has_variants = true;
				}
				$arrProducts[] = $objProduct;
			}
		}

		// handles update
		if ( $submit == 'tl_quantities' )
		{
			$quantities = $this->Input->post( 'quantities' );
	
			$arrInserts = array();
			
			if(count($quantities))
			{	
				
				foreach ( $quantities as $id => $new_value )
				{
					$blnValueFound = false;
					
					if(strlen((string)$new_value)==0)
						continue;
	
					if($this->Input->post('override')=='1')
					{						
						if((string)$new_value == "0")
						{
							$final_value = -1*$products[$id]->quantity;
						}
						else
						{
							$final_value = ((int)$new_value >= $products[$id]->quantity ? abs($products[$id]->quantity-(int)$new_value) : (int)$new_value-$products[$id]->quantity);
						}
					}
					else
					{
						$final_value = $new_value;
					}
										
					$arrInserts[] = '('.$this->Input->get( 'id' ).','.time().','.$id.','.$final_value.')';
				}	
												
				if(count($arrInserts))
				{
					$strInsertStatements = implode(',', $arrInserts);
															
					$this->Database->query( 'insert into tl_iso_inventory (pid,tstamp,product_id,quantity) VALUES '.$strInsertStatements);
				}		
			}
					
			if ( strlen( $this->Input->post( 'saveNclose' ) ) )
			{
			$this->redirect( $this->getReferer( true ) );
			}
			
			else
			{
			$this->redirect( $this->Environment->request );
			}
		}

		$this->loadLanguageFile( 'tl_iso_products' );
		$GLOBALS[ 'TL_CSS' ][]    = 'system/modules/isotope_inventory/html/inventory_quantities.css';
		$this->Template->noProducts = $GLOBALS['TL_LANG']['MSC']['noProducts'];
		$this->Template->products = (count($arrProducts) ? $arrProducts : array());
		$this->Template->href     = $this->getReferer( true );
		$this->Template->title    = specialchars($GLOBALS['TL_LANG']['MSC']['backBT']);
		$this->Template->fields   = $arrSearchFields;
		$this->Template->tl_field = $session[ 'search' ][ $name ][ 'field' ];
		$this->Template->tl_value = $session[ 'search' ][ $name ][ 'value' ];
		$this->Template->limits   = $limits;
		$this->Template->limit    = $session[ 'filter' ][ $name ][ 'limit' ];
		$this->Template->perPage  = $perPage;
		return $this->Template->parse();
	}
		
	
	protected function generateSelectWidget($strName, $arrOptions, $arrLabel)
	{			
		//Select widget info
		$arrData = array
		(
			'label'		=> &$arrLabel,
			'inputType'	=> 'select',
			'options'	=> $arrOptions,
			'eval'		=> array('includeBlankOption'=>true)
		);
		
		$objWidget = new SelectMenu($this->prepareForWidget($arrData,$strName));
		
		return $objWidget->parse();
	}
		
	
	public function generateAjax()
	{		
	
	}
}