<?php
/***************************************************************
*  Copyright notice
*  
*  (c) 2002 Kasper Skårhøj (kasper@typo3.com)
*  All rights reserved
*
*  This script is part of the Typo3 project. The Typo3 project is 
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
* 
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
* 
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/
/** 
 * Plugin 'Mini news' for the 'mininews' extension.
 *
 * $Id$
 * XHTML compliant
 *
 * @author	Kasper Skårhøj <kasper@typo3.com>
 */
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 *
 *
 *   70: class tx_mininews_pi1 extends tslib_pibase 
 *   89:     function main($content,$conf)	
 *  114:     function listView($content,$conf)	
 *  185:     function makelist($res)	
 *  211:     function makeListItem()	
 *  227:     function makefrontpagelist($res)	
 *  253:     function makeFrontPageListItem()	
 *  269:     function singleView($content,$conf)	
 *  299:     function getFieldContent($fN)	
 *
 * TOTAL FUNCTIONS: 8
 * (This index is automatically created/updated by the extension "extdeveval")
 *
 */

require_once(PATH_tslib.'class.tslib_pibase.php');










/**
 * Plugin 'Mininews' for the "mininews" extension
 * 
 * @author	Kasper Skårhøj (kasper@typo3.com)
 * @package TYPO3
 * @subpackage tx_mininews
 */
class tx_mininews_pi1 extends tslib_pibase {

		// Default plugin variables:
	var $prefixId = 'tx_mininews_pi1';		// Same as class name
	var $scriptRelPath = 'pi1/class.tx_mininews_pi1.php';	// Path to this script relative to the extension dir.
	var $extKey = 'mininews';	// The extension key.






	/**
	 * Main function, called from TypoScript:
	 * 
	 * @param	string		Input content, not used, ignore
	 * @param	array		TypoScript configuration input.
	 * @return	string		HTML content from the extension!
	 */
	function main($content,$conf)	{
		switch((string)$conf['CMD'])	{
			case 'singleView':
				list($t) = explode(':',$this->cObj->currentRecord);
				$this->internal['currentTable']=$t;
				$this->internal['currentRow']=$this->cObj->data;
				return $this->pi_wrapInBaseClass($this->singleView($content,$conf));
			break;
			default:
				if (strstr($this->cObj->currentRecord,'tt_content'))	{
					$conf['pidList'] = $this->cObj->data['pages'];
					$conf['recursive'] = $this->cObj->data['recursive'];
				}
				return $this->pi_wrapInBaseClass($this->listView($content,$conf));
			break;
		}
	}

	/**
	 * Rendering of list view plus single view if requested by showUid piVars
	 * 
	 * @param	string		Input content, not used, ignore
	 * @param	array		TypoScript configuration input.
	 * @return	string		HTML content from the extension!
	 */
	function listView($content,$conf)	{
	
			// Init:
		$this->conf=$conf;		// Setting the TypoScript passed to this function in $this->conf
		$FP = $this->conf['CMD']=='FP' ? 1 : $this->cObj->data['tx_mininews_frontpage_list'];
		$lConf = $this->conf[$FP?'frontPage.':'listView.'];	// Local settings for the listView function
		$this->pi_loadLL();		// Loading the LOCAL_LANG values
		
			// Either render the list or single element:
		if ($this->piVars['showUid'])	{	// If a single element should be displayed:
			$this->internal['currentTable'] = 'tx_mininews_news';
			$this->internal['currentRow'] = $this->pi_getRecord('tx_mininews_news',$this->piVars['showUid']);
			
			$content = $this->singleView($content,$conf).$lConf['HR_code'].'
					<p>'.$this->pi_list_linkSingle($this->pi_getLL('back','Back',TRUE),0).'</p>';
			return $content;
		} else {

			if (!isset($this->piVars['pointer']))	$this->piVars['pointer']=0;
	
				// Initializing the query parameters:
			list($this->internal['orderBy'],$this->internal['descFlag']) = explode(':','datetime:1');	//explode(':',$this->piVars['sort']);
			$this->internal['results_at_a_time']=t3lib_div::intInRange($lConf['results_at_a_time'],0,1000,3);		// Number of results to show in a listing.
			$this->internal['maxPages']=t3lib_div::intInRange($lConf['maxPages'],0,1000,2);		// The maximum number of 'pages' in the browse-box: 'Page 1', 'Page 2', etc.
			$this->internal['searchFieldList']='title,teaser,full_text';
			$this->internal['orderByList']='datetime,title';
			
			$addWhere = $FP?' AND front_page>0 ':'';
			
				// Get number of records:
			$query = $this->pi_list_query('tx_mininews_news',1,$addWhere);
			$res = mysql(TYPO3_db,$query);
			if (mysql_error())	debug(array(mysql_error(),$query));
			list($this->internal['res_count']) = mysql_fetch_row($res);
	
				// Make listing query, pass query to MySQL:
			$query = $this->pi_list_query('tx_mininews_news',0,$addWhere);
			$res = mysql(TYPO3_db,$query);
			if (mysql_error())	debug(array(mysql_error(),$query));
			$this->internal['currentTable'] = 'tx_mininews_news';
	
				// Put the whole list together:
			$fullTable='';	// Clear var;
			
				// Determine mode of listing:
			if ($FP)	{	// Frontpage listing:
				$this->pi_tmpPageId = intval($this->cObj->data['pages']);
					// Adds the whole list table
				$fullTable.=$this->makefrontpagelist($res);
			} else {	// Archive listing:
					// Adds the whole list table
				$fullTable.=$this->makelist($res);
				
					// Adds the search box:
				$fullTable.=$this->pi_list_searchBox();
					
					// Adds the result browser:
				$fullTable.=$this->pi_list_browseresults();
			}
			
				// Returns the content from the plugin.
			return $fullTable;
		}
	}

	/**
	 * Compile the list of items (archive)
	 * 
	 * @param	pointer		MySQL SELECT resource
	 * @return	string		HTML content
	 */
	function makelist($res)	{
		$items=Array();
		
			// Make list table rows
		while($this->internal['currentRow'] = mysql_fetch_assoc($res))	{
			$items[]=$this->makeListItem();
		}
	
		$out = '
		
		<!--
			Archive listing of mininews:
		-->
		<div'.$this->pi_classParam('listrow').' style="margin-top: 5px;">
			'.implode(chr(10),$items).'
		</div>';
		
		return $out;
	}

	/**
	 * Create single list item (frontpage)
	 * 
	 * @return	string		HTML content
	 * @see makelist()
	 */
	function makeListItem()	{
		$out='
				'.($this->internal['currentRow']['datetime'] && !$this->conf['listView.']['disableDateDisplay'] ? '<p'.$this->pi_classParam('listrowField-datetime').'>'.$this->getFieldContent('datetime').'</p>':'').'
				<p'.$this->pi_classParam('listrowField-title').'>'.$this->pi_list_linkSingle($this->getFieldContent('title'),$this->internal['currentRow']['uid'],1).'</p>
				<p'.$this->pi_classParam('listrowField-teaser').'>'.$this->pi_list_linkSingle(nl2br(trim(t3lib_div::fixed_lgd($this->getFieldContent('teaser_list'),$this->conf['listView.']['teaserLgd']))),$this->internal['currentRow']['uid'],1).'</p>
			';
		$out = $this->pi_getEditIcon($out,'datetime,title,teaser,full_text','Edit news item');
		return $out;
	}

	/**
	 * Compile the list of items (archive)
	 * 
	 * @param	pointer		MySQL SELECT resource
	 * @return	string		HTML content
	 */
	function makefrontpagelist($res)	{
		$items=Array();
		
			// Make list table rows
		while($this->internal['currentRow'] = mysql_fetch_assoc($res))	{
			$items[]=$this->makeFrontPageListItem();
		}
	
		$out = '
		
		<!--
			Frontpage listing of mininews:
		-->
		<div'.$this->pi_classParam('fp_listrow').' style="margin-top: 5px;">
			'.implode(chr(10),$items).'
		</div>';
		
		return $out;
	}

	/**
	 * Create single list item (frontpage)
	 * 
	 * @return	string		HTML content
	 * @see makefrontpagelist()
	 */
	function makeFrontPageListItem()	{
		$out='
			<p'.$this->pi_classParam('fp_listrowField-datetime').'>'.$this->getFieldContent('datetime').'</p>
			<p'.$this->pi_classParam('fp_listrowField-title').'>'.$this->pi_list_linkSingle($this->getFieldContent('title'),$this->internal['currentRow']['uid'],1).'</p>
			<p'.$this->pi_classParam('fp_listrowField-teaser').'>'.nl2br(trim(t3lib_div::fixed_lgd($this->getFieldContent('teaser_list'),$this->conf['frontPage.']['teaserLgd']))).' '.$this->pi_list_linkSingle($this->pi_getLL('teaser_readMore','',TRUE),$this->internal['currentRow']['uid'],1).'</p>
			';
		return $out;
	}

	/**
	 * Render single view of a mininews item
	 * 
	 * @param	string		Input content, not used, ignore
	 * @param	array		TypoScript configuration input.
	 * @return	string		HTML content from the extension!
	 */
	function singleView($content,$conf)	{
		$this->conf=$conf;
		$this->pi_loadLL();
	
			// This sets the title of the page for use in indexed search results:
		if ($this->internal['currentRow']['title'])	$GLOBALS['TSFE']->indexedDocTitle=$this->internal['currentRow']['title'];

		$content='
		
		<!--
			Single view of mininews item:
		-->
		<div'.$this->pi_classParam('singleView').' style="margin-top: 5px;">
				'.($this->internal['currentRow']['datetime'] && !$this->conf['singleView.']['disableDateDisplay'] ? '
			<p'.$this->pi_classParam('singleViewField-datetime').'>'.$this->getFieldContent('datetime').'</p>':'').'
			<h2>'.$this->pi_getEditIcon($this->getFieldContent('title'),'datetime,title').'</h2>
			<p'.$this->pi_classParam('singleViewField-teaser').'>'.$this->pi_getEditIcon(nl2br($this->getFieldContent('teaser')),'teaser').'</p>
			'.$this->pi_getEditIcon($this->getFieldContent('full_text'),'full_text').'
		</div>'.
		$this->pi_getEditPanel();
	
		return $content;
	}

	/**
	 * Render the content from a given field, prepared for HTML output.
	 * 
	 * @param	string		Fieldname
	 * @return	string		HTML ready content from the field
	 */
	function getFieldContent($fN)	{
		switch($fN) {
			case 'uid':
				return $this->pi_list_linkSingle($this->internal['currentRow'][$fN],$this->internal['currentRow']['uid'],1);	// The '1' means that the display of single items is CACHED! Set to zero to disable caching.
			break;
			case 'datetime':
				return strftime(($this->conf['CMD']=='singleView'? $this->conf['dateFormat'] : $this->conf['dateTimeFormat']),$this->internal['currentRow']['datetime']);
			break;
			case 'title':
					// This will wrap the title in a link.
				return htmlspecialchars($this->internal['currentRow']['title']);
			break;
			case 'full_text':
				return $this->pi_RTEcssText($this->internal['currentRow']['full_text']);
			break;
			case 'teaser_list':
				$retVal = trim($this->internal['currentRow']['teaser']) ? $this->internal['currentRow']['teaser'] : strip_tags($this->internal['currentRow']['full_text']);
				return trim($retVal)?$retVal:'&nbsp;';
			break;
			default:
				return htmlspecialchars($this->internal['currentRow'][$fN]);
			break;
		}
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/mininews/pi1/class.tx_mininews_pi1.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/mininews/pi1/class.tx_mininews_pi1.php']);
}
?>