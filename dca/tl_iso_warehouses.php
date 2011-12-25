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


$this->loadLanguageFile('subdivisions');


/**
 * Table tl_iso_warehouses 
 */
$GLOBALS['TL_DCA']['tl_iso_warehouses'] = array
(

	// Config
	'config' => array
	(
		'dataContainer'               => 'Table',
		/*'switchToEdit'				=> true,
		'enableVersioning'            => true,
		'closed'					  => true,
		'onload_callback' => array
		(
			array('tl_iso_warehouses', 'checkPermission'),
		),
		'ondelete_callback'			  => array
		(
			array('tl_iso_warehouses', 'archiveRecord'),
		),*/
	),

	// List
	'list' => array
	(
		'sorting' => array
		(
			'mode'                    => 1,
			'fields'                  => array('name'),
			'flag'					  => 1,
		),
		'label' => array
		(
			'fields'                  => array('name'),
			'format'                  => '%s',
		),
		'global_operations' => array
		(
			'back' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['MSC']['backBT'],
				'href'                => 'table=',
				'class'               => 'header_back',
				'attributes'          => 'onclick="Backend.getScrollOffset();"',
			),
			/*'all' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['MSC']['all'],
				'href'                => 'act=select',
				'class'               => 'header_edit_all',
				'attributes'          => 'onclick="Backend.getScrollOffset();"',
			)*/
		),
		'operations' => array
		(
			'edit' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_iso_warehouses']['edit'],
				'href'                => 'act=edit',
				'icon'                => 'edit.gif',
			),
			'copy' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_iso_warehouses']['copy'],
				'href'                => 'act=copy',
				'icon'                => 'copy.gif',
			),
			/*'delete' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_iso_warehouses']['delete'],
				'href'                => 'act=delete',
				'icon'                => 'delete.gif',
				'attributes'          => 'onclick="if (!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\')) return false; Backend.getScrollOffset();"',
			),*/
			'show' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_iso_warehouses']['show'],
				'href'                => 'act=show',
				'icon'                => 'show.gif',
			),
			'quantities' => array
			( 
			  'label'               => &$GLOBALS['TL_LANG']['tl_iso_warehouses']['edit'],
			  'href'                => 'key=quantities',
			  'icon'                => 'tablewizard.gif',
			  'button_callback'     => array( 'tl_iso_warehouses', 'getQuantityButton' )
			),
			'import' => array
			(
				'label'				=> &$GLOBALS['TL_LANG']['tl_iso_warehouses']['import'],
			  'href'                => 'key=importinventory',
				'icon'				=> 'system/modules/isotope_inventory/html/inventory_import.png',
			  	'button_callback'   => array('tl_iso_warehouses', 'goToImport')
			),
		)
	),

	// Palettes
	'palettes' => array
	(
		'default'                     => 'general_legend},name,defaultWarehouse;{address_legend},firstname,lastname,email,phone,company,street_1,street_2,street_3,postal,city,subdivision,country',
	),

	// Fields
	'fields' => array
	(
		'name' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_warehouses']['name'],
			'exclude'                 => true,
			'inputType'               => 'text',
			'eval'                    => array('mandatory'=>true, 'unique'=>true, 'maxlength'=>255, 'tl_class'=>'w50'),
		),
		'defaultWarehouse' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_warehouses']['defaultWarehouse'],
			'exclude'                 => true,
			'inputType'               => 'checkbox',
			'eval'						=> array('tl_class'=>'w50'),
		),
		'firstname' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_warehouses']['firstname'],
			'exclude'                 => true,
			'search'                  => true,
			'sorting'                 => true,
			'flag'                    => 1,
			'inputType'               => 'text',
			'eval'                    => array('maxlength'=>255, 'tl_class'=>'w50'),
		),
		'lastname' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_warehouses']['lastname'],
			'exclude'                 => true,
			'search'                  => true,
			'sorting'                 => true,
			'flag'                    => 1,
			'inputType'               => 'text',
			'eval'                    => array('maxlength'=>255, 'tl_class'=>'w50'),
		),
		'company' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_warehouses']['company'],
			'exclude'                 => true,
			'search'                  => true,
			'sorting'                 => true,
			'flag'                    => 1,
			'inputType'               => 'text',
			'eval'                    => array('maxlength'=>255, 'tl_class'=>'w50'),
		),
		'street_1' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_warehouses']['street_1'],
			'exclude'                 => true,
			'search'                  => true,
			'inputType'               => 'text',
			'eval'                    => array('maxlength'=>255, 'tl_class'=>'w50'),
		),
		'street_2' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_warehouses']['street_2'],
			'exclude'                 => true,
			'search'                  => true,
			'inputType'               => 'text',
			'eval'                    => array('maxlength'=>255, 'tl_class'=>'w50'),
		),
		'street_3' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_warehouses']['street_3'],
			'exclude'                 => true,
			'search'                  => true,
			'inputType'               => 'text',
			'eval'                    => array('maxlength'=>255, 'tl_class'=>'w50'),
		),
		'postal' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_warehouses']['postal'],
			'exclude'                 => true,
			'search'                  => true,
			'inputType'               => 'text',
			'eval'                    => array('maxlength'=>32, 'tl_class'=>'w50'),
		),
		'city' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_warehouses']['city'],
			'exclude'                 => true,
			'filter'                  => true,
			'search'                  => true,
			'sorting'                 => true,
			'inputType'               => 'text',
			'eval'                    => array('maxlength'=>255, 'tl_class'=>'w50'),
		),
		'subdivision' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_warehouses']['subdivision'],
			'exclude'                 => true,
			'sorting'                 => true,
			'inputType'               => 'conditionalselect',
			'options'				  => &$GLOBALS['TL_LANG']['DIV'],
			'eval'                    => array('conditionField'=>'country', 'includeBlankOption'=>true, 'tl_class'=>'w50'),
		),
		'country' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_warehouses']['country'],
			'exclude'                 => true,
			'filter'                  => true,
			'sorting'                 => true,
			'inputType'               => 'select',
			'default'				  => $this->User->country,
			'options'                 => $this->getCountries(),
			'eval'                    => array('mandatory'=>true, 'includeBlankOption'=>true, 'tl_class'=>'w50'),
		),
		'phone' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_warehouses']['phone'],
			'exclude'                 => true,
			'search'                  => true,
			'inputType'               => 'text',
			'eval'                    => array('maxlength'=>64, 'rgxp'=>'phone', 'tl_class'=>'w50'),
		),
		'email' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_warehouses']['email'],
			'exclude'                 => true,
			'search'                  => true,
			'inputType'               => 'text',
			'eval'                    => array('maxlength'=>64, 'rgxp'=>'email', 'tl_class'=>'w50'),
		)
	)
);

// Settings for inventory import file tree
$GLOBALS['TL_DCA']['inventory_warehouses']['fields']['inventory_file']['eval']['files'] = true;
$GLOBALS['TL_DCA']['inventory_warehouses']['fields']['inventory_file']['eval']['filesOnly'] = true;
$GLOBALS['TL_DCA']['inventory_warehouses']['fields']['inventory_file']['eval']['fieldType'] = 'radio';
$GLOBALS['TL_DCA']['inventory_warehouses']['fields']['inventory_file']['eval']['extensions'] = 'csv,';


/**
 * tl_iso_warehouses class.
 * 
 * @extends Backend
 */
class tl_iso_warehouses extends Backend
{
	
	public function getQuantityButton(  $row, $href, $label, $title, $icon, $attribute )
    {
		$href .= '&id=' . $row[ 'id' ];
		return '<a href="'.$this->addToUrl($href).'" title="'.specialchars($title).'"'.$attributes.'>'.$this->generateImage($icon, $label).'</a> ';
    }
    
    
    public function goToImport($row, $href, $label, $title, $icon, $attribute)
    {
		$href .= '&id=' . $row[ 'id' ];
		return '<a href="'.$this->addToUrl($href).'" title="'.specialchars($title).'"'.$attributes.'>'.$this->generateImage($icon, $label).'</a> ';
    }
	
	
	public function checkPermission($dc)
	{				
		if (strlen($this->Input->get('act')))
		{
			$GLOBALS['TL_DCA']['tl_iso_warehouses']['config']['closed'] = false;
		}
		
		$this->import('BackendUser', 'User');
		
		// Hide archived (used and deleted) configs
		if ($this->User->isAdmin)
		{
			//$arrConfigs = $this->Database->execute("SELECT id FROM tl_iso_warehouses WHERE archive<2")->fetchEach('id');
		}
		else
		{
			if (!is_array($this->User->iso_configs) || !count($this->User->iso_configs))
			{
				$this->User->iso_configs = array(0);
			}
			
			//$arrConfigs = $this->Database->execute("SELECT id FROM tl_iso_warehouses WHERE id IN ('','" . implode("','", $this->User->iso_configs) . "') AND archive<2")->fetchEach('id');
		}
		
		if (!count($arrConfigs))
		{
			$arrConfigs = array(0);
		}

		$GLOBALS['TL_DCA']['tl_iso_warehouses']['list']['sorting']['root'] = $arrConfigs;

		// Check current action
		switch ($this->Input->get('act'))
		{
			case 'edit':
			case 'copy':
			case 'delete':
			case 'show':
				if (!in_array($this->Input->get('id'), $arrConfigs))
				{
					$this->log('Not enough permissions to '.$this->Input->get('act').' config ID "'.$this->Input->get('id').'"', 'tl_iso_warehouses checkPermission()', TL_ACCESS);
					$this->redirect($this->Environment->script.'?act=error');
				}
				break;

			case 'editAll':
			case 'copyAll':
			case 'deleteAll':
				$session = $this->Session->getData();
				$session['CURRENT']['IDS'] = array_intersect($session['CURRENT']['IDS'], $arrConfigs);
				$this->Session->setData($session);
				break;
		}
	}
	
	
	/**
	 * Record is deleted, archive if necessary
	 */
	public function archiveRecord($dc)
	{
	}

}

