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
 *
 * @author	Kasper Skårhøj <kasper@typo3.com>
 */
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 *
 *
 *   62: class tx_mininews_pi1 extends tslib_pibase 
 *   72:     function main($content,$conf)	
 *   97:     function listView($content,$conf)	
 *  175:     function makelist($res)	
 *  193:     function makeListItem()	
 *  209:     function makefrontpagelist($res)	
 *  227:     function makeFrontPageListItem()	
 *  243:     function singleView($content,$conf)	
 *  267:     function getFieldContent($fN)	
 *
 * TOTAL FUNCTIONS: 8
 * (This index is automatically created/updated by the extension "extdeveval")
 *
 */

require_once(PATH_tslib."class.tslib_pibase.php");



/**
 * Plugin 'Mininews' for the "mininews" extension
 * 
 * @author	Kasper Skårhøj (kasper@typo3.com)
 * @package TYPO3
 * @subpackage tx_mininews
 */
class tx_mininews_pi1 extends tslib_pibase {
	var $prefixId = "tx_mininews_pi1";		// Same as class name
	var $scriptRelPath = "pi1/class.tx_mininews_pi1.php";	// Path to this script relative to the extension dir.
	var $extKey = "mininews";	// The extension key.

	/**
	 * @param	[type]		$content: ...
	 * @param	[type]		$conf: ...
	 * @return	[type]		...
	 */
	function main($content,$conf)	{
		switch((string)$conf["CMD"])	{
			case "singleView":
				list($t) = explode(":",$this->cObj->currentRecord);
				$this->internal["currentTable"]=$t;
				$this->internal["currentRow"]=$this->cObj->data;
				return $this->pi_wrapInBaseClass($this->singleView($content,$conf));
			break;
			default:
				if (strstr($this->cObj->currentRecord,"tt_content"))	{
					$conf["pidList"] = $this->cObj->data["pages"];
					$conf["recursive"] = $this->cObj->data["recursive"];
				}
				return $this->pi_wrapInBaseClass($this->listView($content,$conf));
			break;
		}
	}

	/**
	 * [Describe function...]
	 * 
	 * @param	[type]		$content: ...
	 * @param	[type]		$conf: ...
	 * @return	[type]		...
	 */
	function listView($content,$conf)	{
		$this->conf=$conf;		// Setting the TypoScript passed to this function in $this->conf
		$FP = $this->conf["CMD"]=="FP" ? 1 : $this->cObj->data["tx_mininews_frontpage_list"];
		$lConf = $this->conf[$FP?"frontPage.":"listView."];	// Local settings for the listView function
		$this->pi_loadLL();		// Loading the LOCAL_LANG values
		
		if ($this->piVars["showUid"])	{	// If a single element should be displayed:
			$this->internal["currentTable"] = "tx_mininews_news";
			$this->internal["currentRow"] = $this->pi_getRecord("tx_mininews_news",$this->piVars["showUid"]);
			
			$content = $this->singleView($content,$conf).$lConf["HR_code"].'
					<P>'.$this->pi_list_linkSingle($this->pi_getLL("back","Back"),0).'</P>';
			return $content;
		} else {
/*			$items=array(
				"1"=> $this->pi_getLL("list_mode_1","Mode 1"),
				"2"=> $this->pi_getLL("list_mode_2","Mode 2"),
				"3"=> $this->pi_getLL("list_mode_3","Mode 3"),
			);
	*/		if (!isset($this->piVars["pointer"]))	$this->piVars["pointer"]=0;
#			if (!isset($this->piVars["mode"]))	$this->piVars["mode"]=1;
	
				// Initializing the query parameters:
			list($this->internal["orderBy"],$this->internal["descFlag"]) = explode(":","datetime:1");	//explode(":",$this->piVars["sort"]);
			$this->internal["results_at_a_time"]=t3lib_div::intInRange($lConf["results_at_a_time"],0,1000,3);		// Number of results to show in a listing.
			$this->internal["maxPages"]=t3lib_div::intInRange($lConf["maxPages"],0,1000,2);		// The maximum number of "pages" in the browse-box: "Page 1", "Page 2", etc.
			$this->internal["searchFieldList"]="title,teaser,full_text";
			$this->internal["orderByList"]="datetime,title";
			
			$addWhere = $FP?" AND front_page>0 ":"";
			
				// Get number of records:
			$query = $this->pi_list_query("tx_mininews_news",1,$addWhere);
			$res = mysql(TYPO3_db,$query);
			if (mysql_error())	debug(array(mysql_error(),$query));
			list($this->internal["res_count"]) = mysql_fetch_row($res);
	
				// Make listing query, pass query to MySQL:
			$query = $this->pi_list_query("tx_mininews_news",0,$addWhere);
#debug($query);
#debug($this->internal);
			$res = mysql(TYPO3_db,$query);
			if (mysql_error())	debug(array(mysql_error(),$query));
			$this->internal["currentTable"] = "tx_mininews_news";
	
				// Put the whole list together:
			$fullTable="";	// Clear var;
		#	$fullTable.=t3lib_div::view_array($this->piVars);	// DEBUG: Output the content of $this->piVars for debug purposes. REMEMBER to comment out the IP-lock in the debug() function in t3lib/config_default.php if nothing happens when you un-comment this line!
				
				// Adds the mode selector.
#			$fullTable.=$this->pi_list_modeSelector($items);	
			
			if ($FP)	{
				$this->pi_tmpPageId = intval($this->cObj->data["pages"]);
					// Adds the whole list table
				$fullTable.=$this->makefrontpagelist($res);
			} else {
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
	 * [Describe function...]
	 * 
	 * @param	[type]		$res: ...
	 * @return	[type]		...
	 */
	function makelist($res)	{
		$items=Array();
			// Make list table rows
		while($this->internal["currentRow"] = mysql_fetch_assoc($res))	{
			$items[]=$this->makeListItem();
		}
	
		$out = '<DIV'.$this->pi_classParam("listrow").' style="margin-top: 5px;">
			'.implode(chr(10),$items).'
			</DIV>';
		return $out;
	}

	/**
	 * [Describe function...]
	 * 
	 * @return	[type]		...
	 */
	function makeListItem()	{
		$out='
				'.($this->internal["currentRow"]["datetime"] && !$this->conf["listView."]["disableDateDisplay"] ? '<P'.$this->pi_classParam("listrowField-datetime").'>'.$this->getFieldContent("datetime").'</P>':'').'
				<P'.$this->pi_classParam("listrowField-title").'>'.$this->pi_list_linkSingle($this->getFieldContent("title"),$this->internal["currentRow"]["uid"],1).'</P>
				<P'.$this->pi_classParam("listrowField-teaser").'>'.$this->pi_list_linkSingle(nl2br(trim(t3lib_div::fixed_lgd($this->getFieldContent("teaser_list"),$this->conf["listView."]["teaserLgd"]))),$this->internal["currentRow"]["uid"],1).'</P>
			';
		$out = $this->pi_getEditIcon($out,"datetime,title,teaser,full_text","Edit news item");
		return $out;
	}

	/**
	 * [Describe function...]
	 * 
	 * @param	[type]		$res: ...
	 * @return	[type]		...
	 */
	function makefrontpagelist($res)	{
		$items=Array();
			// Make list table rows
		while($this->internal["currentRow"] = mysql_fetch_assoc($res))	{
			$items[]=$this->makeFrontPageListItem();
		}
	
		$out = '<DIV'.$this->pi_classParam("fp_listrow").' style="margin-top: 5px;">
			'.implode(chr(10),$items).'
			</DIV>';
		return $out;
	}

	/**
	 * [Describe function...]
	 * 
	 * @return	[type]		...
	 */
	function makeFrontPageListItem()	{
		$out='
			<P'.$this->pi_classParam("fp_listrowField-datetime").'>'.$this->getFieldContent("datetime").'</P>
			<P'.$this->pi_classParam("fp_listrowField-title").'>'.$this->pi_list_linkSingle($this->getFieldContent("title"),$this->internal["currentRow"]["uid"],1).'</P>
			<P'.$this->pi_classParam("fp_listrowField-teaser").'>'.nl2br(trim(t3lib_div::fixed_lgd($this->getFieldContent("teaser_list"),$this->conf["frontPage."]["teaserLgd"])))." ".$this->pi_list_linkSingle($this->pi_getLL("teaser_readMore"),$this->internal["currentRow"]["uid"],1).'</P>
			';
		return $out;
	}

	/**
	 * [Describe function...]
	 * 
	 * @param	[type]		$content: ...
	 * @param	[type]		$conf: ...
	 * @return	[type]		...
	 */
	function singleView($content,$conf)	{
		$this->conf=$conf;
		$this->pi_loadLL();
	
			// This sets the title of the page for use in indexed search results:
		if ($this->internal["currentRow"]["title"])	$GLOBALS["TSFE"]->indexedDocTitle=$this->internal["currentRow"]["title"];

		$content='<DIV'.$this->pi_classParam("singleView").' style="margin-top: 5px;">
				'.($this->internal["currentRow"]["datetime"] && !$this->conf["singleView."]["disableDateDisplay"] ? '<P'.$this->pi_classParam("singleViewField-datetime").'>'.$this->getFieldContent("datetime").'</P>':'').'
				<H2>'.$this->pi_getEditIcon($this->getFieldContent("title"),"datetime,title").'</H2>
				<P'.$this->pi_classParam("singleViewField-teaser").'>'.$this->pi_getEditIcon(nl2br($this->getFieldContent("teaser")),"teaser").'</P>
				'.$this->pi_getEditIcon($this->getFieldContent("full_text"),"full_text").'
			</DIV>'.
		$this->pi_getEditPanel();
	
		return $content;
	}

	/**
	 * [Describe function...]
	 * 
	 * @param	[type]		$fN: ...
	 * @return	[type]		...
	 */
	function getFieldContent($fN)	{
		switch($fN) {
			case "uid":
				return $this->pi_list_linkSingle($this->internal["currentRow"][$fN],$this->internal["currentRow"]["uid"],1);	// The "1" means that the display of single items is CACHED! Set to zero to disable caching.
			break;
			case "datetime":
				return strftime(($this->conf["CMD"]=="singleView"? $this->conf["dateFormat"] : $this->conf["dateTimeFormat"]),$this->internal["currentRow"]["datetime"]);
			break;
			case "title":
					// This will wrap the title in a link.
				return $this->internal["currentRow"]["title"];
			break;
			case "full_text":
				return $this->pi_RTEcssText($this->internal["currentRow"]["full_text"]);
			break;
			case "teaser_list":
				$retVal = trim($this->internal["currentRow"]["teaser"]) ? $this->internal["currentRow"]["teaser"] : strip_tags($this->internal["currentRow"]["full_text"]);
				return trim($retVal)?$retVal:"&nbsp;";
			break;
			default:
				return $this->internal["currentRow"][$fN];
			break;
		}
	}
}



if (defined("TYPO3_MODE") && $TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/mininews/pi1/class.tx_mininews_pi1.php"])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/mininews/pi1/class.tx_mininews_pi1.php"]);
}

?>