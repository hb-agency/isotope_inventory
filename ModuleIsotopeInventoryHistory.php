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


class ModuleIsotopeInventoryHistory extends BackendModule
{

	/**
	 * Name of the template
	 * @var string
	 */
	protected $strTemplate = 'be_inventory_history';

	/**
	 * Files directory
	 * @var string
	 */
	protected $strFilesDir = 'system/html/';

	/**
	 * Filename prefix
	 * @var string
	 */
	protected $strFilePrefix = 'iso_inv_';

	/**
	 * Name of the "Print History" checkbox
	 * @var string
	 */
	protected $strPrintHistory = 'print_history';
		
	/**
	 * Form ID
	 * @var string
	 */
	protected $strFormId = 'import_inventory_form';
	
	/**
	 * Name of the hidden FORM_SUBMIT input
	 * @var string
	 */
	protected $strFormSubmit = 'tl_iso_inventory_import';
	


	public function compile()
	{
		$this->Template = new BackendTemplate($this->strTemplate);
		
		// Import scripts
		$GLOBALS['TL_CSS'][] 			= 'system/modules/isotope_inventory/html/inventory_history.css';
		$GLOBALS['TL_CSS'][] 			= 'plugins/calendar/calendar.css';
		$GLOBALS['TL_CSS'][] 			= 'plugins/calendar/css/calendar.css';
		
		// Set template values	
		$this->Template->href     		= $this->getReferer( true );
		$this->Template->title    		= specialchars($GLOBALS['TL_LANG']['MSC']['backBT']);
		
		$this->Template->datefilter		= 'From:';
		$this->Template->printHistory  	= $this->strPrintHistory;
		$this->Template->printLabel  	= $GLOBALS['TL_LANG']['MSC']['inventory_history_print'];
		$this->Template->formId			= $this->strFormId;
		$this->Template->formSubmit		= $this->strFormSubmit;
		$this->Template->enctype 		= 'application/x-www-form-urlencoded';
		$this->Template->formAction 	= ampersand($this->Environment->request, ENCODE_AMPERSANDS);
		$this->Template->dateFromVal 	= strlen($this->Input->post('tstamp_from')) ? $this->Input->post('tstamp_from') : '';
		$this->Template->dateToVal	 	= strlen($this->Input->post('tstamp_to')) ? $this->Input->post('tstamp_to') : '';
		$this->Template->submit			= 'import_submit';
		
		if ($this->Input->post('FORM_SUBMIT') == $this->strFormSubmit)
		{
			$this->Template->graph = $this->generateGraph();
		}
		
		return $this->Template->parse();
	}
	
	

	/**
	 * Build the graph and display it
	 *
	 * @access	protected
	 */
	protected function generateGraph()
	{
		$arrIds = $this->getProductIds();
		$arrResults = $this->getResults($arrIds);
		
		if (count($arrResults) == 0)
			return '';
			
		$arrResults = $this->getFormattedResults($arrResults);
		$strGraph = $this->generateLineChart($arrResults);
		
		// THIS IS WHERE ADAM LEFT OFF		
		if (strlen($this->Input->post($this->strPrintHistory)) && $this->Input->get($this->strPrintHistory) == '1')
			$this->generatePDF();
		
		return $strGraph;
	}
	
	

	/**
	 * Get the IDs of the products to display
	 *
	 * @access	protected
	 */
	protected function getProductIds()
	{	
		$arrIds = array();
				
		if (!strlen($this->Input->get('id')))
		{
			$arrIds = is_array($_SESSION['BE_DATA']['CURRENT']['IDS']) ? $_SESSION['BE_DATA']['CURRENT']['IDS'] : array();
		}
		else
		{
			$arrIds[] = $this->Input->get('id');
		}
		
		return $arrIds;
	}
	
	

	/**
	 * Get the product inventory data
	 *
	 * @param	array
	 * @return	array
	 */
	protected function getResults($arrIds)
	{
		if (count($arrIds) == 0)
			return array();
			
		$strDateFrom = strtotime(strlen($this->Input->post('tstamp_from')) ? $this->Input->post('tstamp_from') : '1901-01-01');
		$strDateTo = strtotime(strlen($this->Input->post('tstamp_to')) ? $this->Input->post('tstamp_to') : '2038-01-01');
				
		$strQuery = "SELECT prod.id AS `product_id`,
						prod.sku AS `product_sku`,
						prod.name AS `product_name`,
						inv.id AS `inventory_id`,
						inv.tstamp AS `inventory_tstamp`,
						inv.quantity AS `inventory_quantity`
					FROM tl_iso_products prod
					INNER JOIN tl_iso_inventory inv
						ON prod.id = inv.product_id
					WHERE prod.id IN ('" . implode("','", $arrIds) . "')
						AND inv.tstamp BETWEEN ? AND ?
					ORDER BY prod.id, inv.tstamp, inv.id";

		// Execute query
		$objResults = $this->Database->prepare($strQuery)->execute($strDateFrom, $strDateTo);

		if ($objResults->numRows < 1)
		{
			return array();
		}
		
		return $objResults->fetchAllAssoc();
	}
	
	
	protected function getFormattedResults($arrResults)
	{
		$strPreviousProductId = '';
		$arrProductData = array();
		$intInventory = 0;
		
		// Loop through the results and build a new item in $arrProductData for each product
		foreach ($arrResults as $arrResult)
		{
			$strCurrentProductId = $arrResult['product_id'];
			
			if ($strCurrentProductId != $strPreviousProductId)
			{
				// Added the "id_" prefix to prevent any confusion
				$arrProductData['id_' . $strCurrentProductId] = array
				(
					'product_label'			=> $arrResult['product_sku'],
					'product_quantities'	=> array(),
					'product_history'		=> array(),
				);
				
				$intInventory = intval($arrResult['inventory_quantity']);
			}
			else
			{
				$intInventory += intval($arrResult['inventory_quantity']);
			}
			
			// Add the data to the "product_history" array
			$arrProductData['id_' . $strCurrentProductId]['product_history'][] = $this->parseDate($GLOBALS['TL_CONFIG']['dateFormat'], $arrResult['inventory_tstamp']);
			$arrProductData['id_' . $strCurrentProductId]['product_quantities'][] = $intInventory;
						
			$strPreviousProductId = $strCurrentProductId;
		}
		
		// Remove any products with only one entry (won't show up on line graph)
		foreach ($arrProductData as $key=>$productData)
		{
			if (count($productData['product_quantities']) < 2)
				unset($arrProductData[$key]);
		}

		return $arrProductData;
	}
	
	
	
	/**
	 * Generate the collection using a template. Useful for PDF output.
	 *
	 * @param  string
	 * @return string
	 */
	public function getPdfTemplate($strTemplate=null, $blnResetConfig=true)
	{
		$strArticle = $this->replaceInsertTags($this->getTemplateContent(true));
		$strArticle = html_entity_decode($strArticle, ENT_QUOTES, $GLOBALS['TL_CONFIG']['characterSet']);
		$strArticle = $this->convertRelativeUrls($strArticle, '', true);

		// Remove form elements and JavaScript links
		$arrSearch = array
		(
			'@<form.*</form>@Us',
			'@<a [^>]*href="[^"]*javascript:[^>]+>.*</a>@Us'
		);

		$strArticle = preg_replace($arrSearch, '', $strArticle);

		// Handle line breaks in preformatted text
		$strArticle = preg_replace_callback('@(<pre.*</pre>)@Us', 'nl2br_callback', $strArticle);

		// Default PDF export using TCPDF
		$arrSearch = array
		(
			'@<span style="text-decoration: ?underline;?">(.*)</span>@Us',
			'@(<img[^>]+>)@',
			'@(<div[^>]+block[^>]+>)@',
			'@[\n\r\t]+@',
			'@<br /><div class="mod_article@',
			'@href="([^"]+)(pdf=[0-9]*(&|&amp;)?)([^"]*)"@'
		);

		$arrReplace = array
		(
			'<u>$1</u>',
			'<br />$1',
			'<br />$1',
			' ',
			'<div class="mod_article',
			'href="$1$4"'
		);

		$strArticle = preg_replace($arrSearch, $arrReplace, $strArticle);

		return $strArticle;
	}


	public function generatePDF($strTemplate=null, $pdf=null, $blnOutput=true)
	{
		if (!is_object($pdf))
		{
			// TCPDF configuration
			$l['a_meta_dir'] = 'ltr';
			$l['a_meta_charset'] = $GLOBALS['TL_CONFIG']['characterSet'];
			$l['a_meta_language'] = $GLOBALS['TL_LANGUAGE'];
			$l['w_page'] = 'page';

			// Include library
			require_once(TL_ROOT . '/system/config/tcpdf.php');
			require_once(TL_ROOT . '/plugins/tcpdf/tcpdf.php');

			// Create new PDF document
			$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true);

			// Set document information
			$pdf->SetCreator(PDF_CREATOR);
			$pdf->SetAuthor(PDF_AUTHOR);

// @todo $objInvoice is not defined
//			$pdf->SetTitle($objInvoice->title);
//			$pdf->SetSubject($objInvoice->title);
//			$pdf->SetKeywords($objInvoice->keywords);

			// Remove default header/footer
			$pdf->setPrintHeader(false);
			$pdf->setPrintFooter(false);

			// Set margins
			$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);

			// Set auto page breaks
			$pdf->SetAutoPageBreak(true, PDF_MARGIN_BOTTOM);

			// Set image scale factor
			$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

			// Set some language-dependent strings
			$pdf->setLanguageArray($l);

			// Initialize document and add a page
			$pdf->AliasNbPages();

			// Set font
			$pdf->SetFont(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN);
		}

		// Start new page
		$pdf->AddPage();

		// Write the HTML content
		$pdf->writeHTML($this->getPdfTemplate(), true, 0, true, 0);

		if ($blnOutput)
		{
			// Close and output PDF document
			// @todo $strInvoiceTitle is not defined
			$pdf->lastPage();
			$pdf->Output(standardize(ampersand('inventory_report_' . time(), false), true) . '.pdf', 'D');
			//$pdf->Output(standardize(ampersand('cbreport', false), true) . '.pdf', 'I');  // For debugging

			// Stop script execution
			exit;
		}

		return $pdf;
	}
	
	protected function generateLineChart($arrChartData=array(), $blnPrintPDF=false)
	{
		if (count($arrChartData) == 0)
			return '';
			
		if (!defined('USE_CACHE'))
			define("USE_CACHE",true);
		if (!defined('READ_CACHE'))
			define("READ_CACHE",true);
		
		define("CACHE_DIR",TL_ROOT . '/' . $this->strFilesDir);
		
		include_once (TL_ROOT . "/plugins/jpgraph/src/jpgraph.php");
		include_once (TL_ROOT . "/plugins/jpgraph/src/jpgraph_line.php");
				 
		// File info
		$strCacheFileName = $this->strFilePrefix . (string)time() . rand(100,1000) . '.jpg';
		$strCacheFilePath = $this->strFilesDir . $strCacheFileName;
		
		 
		// Create the graph and specify the scale for both Y-axis
		$graph = new Graph(700, 500, $strCacheFileName, 0, 0);    
		$graph->SetScale("textlin");
		$graph->SetShadow();
		 
		// Adjust the margin
		$graph->img->SetMargin(40,40,20,70);
		
		// Begin setting label text and positions
		$tickPositions = array();
		$tickLabels = array();	
		
		foreach ($arrChartData as $chartData)
		{
			$ydata = $chartData['product_quantities'];
			$lineplot = new LinePlot($ydata);	
		 
			// Add the plot to the graph
			$graph->Add($lineplot);	
		 
			// Set the legends for the plots
			$lineplot->SetLegend($chartData['product_label']);
			
			for ($i = 0; $i < count($chartData['product_history']); $i++)
			{
				$tickPositions[$i] = $i;
				$tickLabels[$i] = $chartData['product_history'][$i];			
			}
		}
		 
		//$graph->title->Set("Example 6.1");
		//$graph->xaxis->title->Set("X-title");
		//$graph->yaxis->title->Set("Y-title");
		 
		$graph->title->SetFont(FF_FONT1,FS_BOLD);
		$graph->yaxis->title->SetFont(FF_FONT1,FS_BOLD);
		$graph->xaxis->title->SetFont(FF_FONT1,FS_BOLD);
 
		// Now set the tic positions
		$graph->xaxis->SetMajTickPositions($tickPositions,$tickLabels);
		 
		// Adjust the legend position
		$graph->legend->SetLayout(LEGEND_HOR);
		$graph->legend->Pos(0.4,0.95,"center","bottom");
		 
		// Display the graph
		$graph->Stroke();
		
		// Get the image to display
		$strPath = $strCacheFilePath;
		
		if (!is_file($strCacheFilePath) && is_file(TL_ROOT . '/' . $strCacheFilePath))
		{
			$strPath = TL_ROOT . '/' . $strCacheFilePath;
		}
		
		// Get the cached image		
		if (is_dir(CACHE_DIR) && is_file($strPath))
		{			
			$objFile = new File($strCacheFilePath);
			
			if ($objFile->isGdImage)
			{
				return $strCacheFilePath;
			}
		}		
		
		return '';
	}
	
	public function deleteOldImages()
	{
		try
		{
			$this->log('Starting to remove temp images from ' . $this->strFilesDir, __METHOD__, TL_GENERAL);
			
			$this->import('Files');
			
			if (!is_dir(TL_ROOT . '/' . $this->strFilesDir))
				return;
				
			$arrFiles = scan(TL_ROOT . '/' . $this->strFilesDir);
			
			if (is_array($arrFiles) && count($arrFiles))
			{
				foreach ($arrFiles as $file)
				{
					if (stripos($file, $this->strFilePrefix) === false)
						continue;						
		
					// Get whichever path is necessary to find the file
					$strPath = $this->strFilesDir . $file;
					
					if (!is_file($this->strFilesDir . $file) && is_file(TL_ROOT . '/' . $this->strFilesDir . $file))
					{
						$strPath = TL_ROOT . '/' . $this->strFilesDir . $file;
					}
						
					if (is_file($strPath))
					{
						$this->Files->delete($strPath);
					}
				}
			}
		}
		catch (Exception $e)
		{
			$this->log('Problem removing temp images from ' . $this->strFilesDir . ' : ' . $e->getMessage(), __METHOD__, TL_ERROR);
		}	
	}
	
}

