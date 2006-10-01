<?php
/***************************************************************
*  Copyright notice
*  
*  (c) 2002-2004 Kasper Skårhøj (kasper@typo3.com)
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
 * @author	Dmitry Dulepov <typo3@accio.lv>
 */
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 *
 *
 *   73: class tx_mininews_pi1 extends tslib_pibase 
 *   93:     function main($content,$conf)
 *  118:     function listView()
 *  209:     function templaVoilaList($res)
 *  271:     function makelist($res)
 *  297:     function makeListItem()
 *  313:     function makefrontpagelist($res)
 *  367:     function makeFrontPageListItem()
 *  383:     function singleView()
 *  430:     function getFieldContent($fN)
 *
 * TOTAL FUNCTIONS: 9
 * (This index is automatically created/updated by the extension "extdeveval")
 *
 */

require_once(PATH_tslib.'class.tslib_pibase.php');
if (t3lib_extMgm::isLoaded('templavoila'))	{
	require_once(t3lib_extMgm::extPath('templavoila') . 'class.tx_templavoila_htmlmarkup.php');
}









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

		// TemplaVoila specific:
	var $TA = false;		// If TemplaVoila is used and a TO record is found, this array will be loaded with Template Array.
	var $TMPLobj = false;	// Template Object

	var	$disablePrefixComment = false;
	var	$sectionName;

	/**
	 * Main function, called from TypoScript:
	 * 
	 * @param	string		Input content, not used, ignore
	 * @param	array		TypoScript configuration input.
	 * @return	string		HTML content from the extension!
	 */
	function main($content, $conf)	{
		// initialize internal variables
		$this->init($conf);

		if ($conf['CMD'] == 'singleView') {
			// This option is available from typoscript, normally not used, kept for compatiility
			list($t) = explode(':', $this->cObj->currentRecord);
			$this->internal['currentTable'] = $t;
			$this->internal['currentRow'] = $this->cObj->data;
			return $this->pi_wrapInBaseClass($this->singleView());
		}

		// Normal processing
		return $this->pi_wrapInBaseClass($this->listView());
	}

	/**
	 * Initializes plugin's configuration.
	 *
	 * @param	array	$conf	Configuration from TypoScript
	 */
	function init($conf) {
		$this->conf = $conf;		// Setting the TypoScript passed to this function in $this->conf

		// Flexforms first
		$this->pi_initPIflexForm();

		// Load and initialize TemplaVoila TO (if any)
		if (t3lib_extMgm::isLoaded('templavoila'))	{
			$field_templateObject = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'field_templateObject');
			if (intval($field_templateObject))	{
				$this->TMPLobj = t3lib_div::makeInstance('tx_templavoila_htmlmarkup');
				$this->TA = $this->TMPLobj->getTemplateArrayForTO(intval($field_templateObject));
				if (is_array($this->TA))	{
					$this->TMPLobj->setHeaderBodyParts($this->TMPLobj->tDat['MappingInfo_head'],$this->TMPLobj->tDat['MappingData_head_cached']);
				}
			}
		}

		// various parameters
		$this->fetchConfigValue('field_storagePage', 'pidList');
		$this->fetchConfigValue('field_mode', 'CMD');
		$this->sectionName = ($this->conf['CMD'] == 'FP' ? 'frontPage.' : 'listView.');
		$this->fetchConfigValue('field_recursive', 'recursive');
		$this->fetchConfigValue('field_disableSearch', 'listView.|disableSearch');
		$this->fetchConfigValue('field_results', $this->sectionName . '|results_at_a_time');

		if (isset($GLOBALS['TSFE']->config['disablePrefixComment'])) {
			$this->disablePrefixComment = intval($GLOBALS['TSFE']->config['disablePrefixComment']);
		}

		// Language labels
		$this->pi_loadLL();
	}

	/**
	 * Fetches configuration value from flexform. If value exists, value in
	 * <code>$this->conf</code> is replaced with this value.
	 * 
	 * @param	string	$flexformValue	Key to flexform
	 * @param	string	$confValue	Key to configuration. If <code>|</code> is found, the first part is section name, second is key
	 * @return	void
	 */
	function fetchConfigValue($flexformValue, $confValue) {
		$value = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], $flexformValue);
		if (!is_null($value) && $value != '') {
			if (strchr($confValue, '|')) {
				list($section, $confValue) = explode('|', $confValue);
				$this->conf[$section][$confValue] = $value;
			}
			else {
				$this->conf[$confValue] = $value;
			}
		}
	}

	/**
	 * Rendering of list view plus single view if requested by showUid piVars
	 * 
	 * @return	string		HTML content from the extension!
	 */
	function listView()	{
			// Init:
		$FP = ($this->conf['CMD'] == 'FP');
		$lConf = $this->conf[$this->sectionName];	// Local settings for the listView function

			// Either render the list or single element:
		if ($this->piVars['showUid'])	{	// If a single element should be displayed:
			$this->internal['currentTable'] = 'tx_mininews_news';
			$this->internal['currentRow'] = $this->pi_getRecord('tx_mininews_news',$this->piVars['showUid']);

            $content = $this->singleView();
            if (!is_array($this->TA))   {   // !TemplaVoila:
                $content .= $lConf['HR_code'] . '<p>' . $this->pi_list_linkSingle($this->pi_getLL('archive', 'News archive', TRUE), 0) . '</p>';
            }
			return $content;
		} else {

			if (!isset($this->piVars['pointer'])) {
				$this->piVars['pointer'] = 0;
			}

				// Initializing the query parameters:
			list($this->internal['orderBy'], $this->internal['descFlag']) = explode(':','datetime:1');	//explode(':',$this->piVars['sort']);
			$this->internal['maxPages'] = t3lib_div::intInRange($lConf['maxPages'],0,1000,2);		// The maximum number of 'pages' in the browse-box: 'Page 1', 'Page 2', etc.
			$this->internal['searchFieldList'] = 'title,teaser,full_text';
			$this->internal['orderByList'] = 'datetime,title';
			$this->internal['dontLinkActivePage'] = true;

			$addWhere = $FP ? ' AND front_page>0 ' : '';

				// Get total number of news for page browser:
			$query = $this->pi_list_query('tx_mininews_news', true, $addWhere);

			$res = $GLOBALS['TYPO3_DB']->sql_query($query);
			$row = $GLOBALS['TYPO3_DB']->sql_fetch_row($res);
			$GLOBALS['TYPO3_DB']->sql_free_result($res);
			$this->internal['res_count'] = $row[0];

				// Make listing query, pass query to MySQL:
			$this->internal['results_at_a_time'] = t3lib_div::intInRange($lConf['results_at_a_time'], 0, 1000, 3);		// Number of results to show in a listing.
			$query = $this->pi_list_query('tx_mininews_news', false, $addWhere);
			$res = $GLOBALS['TYPO3_DB']->sql_query($query);
			$this->internal['currentTable'] = 'tx_mininews_news';

				// Put the whole list together:
			$fullTable = '';	// Clear var;

				// Determine mode of listing:
			if ($FP)	{	// Frontpage listing:
                $this->pi_tmpPageId = intval($this->conf['pidList']);
					// Adds the whole list table
				$fullTable .= $this->makefrontpagelist($res);
			} else {	// Archive listing:

				if (is_array($this->TA))	{	// TemplaVoila:
					$fullTable .= $this->templaVoilaList($res);
				} else {
						// Adds the whole list table
					$fullTable .= $this->makelist($res);

						// Adds the search box:
					if (!$lConf['disableSearch']) {
						$fullTable .= $this->pi_list_searchBox();
					}

					if ($this->internal['res_count'] > $lConf['results_at_a_time']) {
							// Adds the result browser:
						$this->pi_alwaysPrev = -1;
						$fullTable .= $this->pi_list_browseresults();
					}
				}
			}
			$GLOBALS['TYPO3_DB']->sql_free_result($res);

				// Returns the content from the plugin.
			return $fullTable;
		}
	}

	/**
	 * Compile the list of items (archive), using TemplaVoila
	 * 
	 * @param	pointer		MySQL SELECT resource
	 * @return	string		HTML content
	 */
	function templaVoilaList($res)	{

			// Create list of elements:
		$elements='';
		while($this->internal['currentRow'] = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res))	{
            $link = $this->pi_list_linkSingle('', $this->internal['currentRow']['uid'], true, array(), true);
			$elements.=$this->TMPLobj->mergeDataArrayToTemplateArray(
				$this->TA['sub']['sArchive']['sub']['field_archiveListing']['sub']['element_even'],
				array(
					'field_date' => $this->getFieldContent('datetime'),
					'field_header' => $this->pi_list_linkSingle($this->getFieldContent('title'),$this->internal['currentRow']['uid'],1),
					'field_teaser' => nl2br(trim(t3lib_div::fixed_lgd($this->getFieldContent('teaser_list'),$this->conf['frontPage.']['teaserLgd']))),
					'field_link' => $link,
					'field_link2' => $link
				)
			);
		}

			// Initializing variables:
		$pointer = $this->piVars['pointer'];
		$count = $this->internal['res_count'];
		$results_at_a_time = t3lib_div::intInRange($this->internal['results_at_a_time'],1,1000);
		$maxPages = t3lib_div::intInRange($this->internal['maxPages'],1,100);
		$max = t3lib_div::intInRange(ceil($count/$results_at_a_time),1,$maxPages);
		$pointer = intval($pointer);
		$links = array();

		$br_elements = '';
		for ($a = 0; $a < $max; $a++)	{
			$br_elements .= $this->TMPLobj->mergeDataArrayToTemplateArray(
				$this->TA['sub']['sArchive']['sub']['field_browseBox_cellsContainer']['sub'][$pointer ==  $a?'field_browseBox_cellHighlighted' : 'field_browseBox_cellNormal'],
				array(
					'field_url' => $this->pi_linkTP_keepPIvars_url(array('pointer'=>($a?$a:'')), $this->pi_isOnlyFields($this->pi_isOnlyFields)),
					'field_label' => $this->pi_getLL('pi_list_browseresults_page') . ' ' .  ($a + 1),
				)
			);
		}

		$pR1 = $pointer*$results_at_a_time + 1;
		$pR2 = $pointer*$results_at_a_time + $results_at_a_time;
		$rangeLabel = $pR1 . '-' . min(array($this->internal['res_count'], $pR2));

			// Wrap the elements in their containers:
		$out = $this->TMPLobj->mergeDataArrayToTemplateArray(
				$this->TA['sub']['sArchive'],
				array(
					'field_archiveListing' => $elements,
					'field_browseBox_cellsContainer' => $br_elements,
					'field_searchBox_sword' => htmlspecialchars($this->piVars['sword']),
					'field_searchBox_submitUrl' => htmlspecialchars(t3lib_div::getIndpEnv('REQUEST_URI')),
					'field_browseBox_displayRange' => $rangeLabel,
					'field_browseBox_displayCount' => $this->internal['res_count']
				)
			);

		return $out;
	}

	/**
	 * Compile the list of items (archive)
	 * 
	 * @param	pointer		MySQL SELECT resource
	 * @return	string		HTML content
	 */
	function makelist($res)	{
		$items = array();

			// Make list table rows
		$first = true;
		$count = $GLOBALS['TYPO3_DB']->sql_num_rows($res); $cur = 1;
		while($this->internal['currentRow'] = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res))	{
			$items[] = $this->makeListItem($first, $cur == $count);
			$first = false; $cur++;
		}

		$out = ($this->disablePrefixComment ? '' : '

		<!--
			Archive listing of mininews:
		-->
		') . '<div'.$this->pi_classParam('listrow').'>
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
	function makeListItem($first, $last)	{
		$out='
				'.($this->internal['currentRow']['datetime'] && !$this->conf['listView.']['disableDateDisplay'] ? '<p'.$this->pi_classParam('listrowField-datetime').'>'.$this->getFieldContent('datetime').'</p>':'').'
				<p'.$this->pi_classParam('listrowField-title').'>'.$this->pi_list_linkSingle($this->getFieldContent('title'),$this->internal['currentRow']['uid'],1).'</p>
				<p'.$this->pi_classParam('listrowField-teaser').'>'.$this->pi_list_linkSingle(nl2br(trim(t3lib_div::fixed_lgd($this->getFieldContent('teaser_list'),$this->conf['listView.']['teaserLgd']))),$this->internal['currentRow']['uid'],1).'</p>
			';
		if ($first) {
			$out = '<div' . $this->pi_classParam('listrow-first') . '>' . $out . '</div>';
		}
		else if ($last) {
			$out = '<div' . $this->pi_classParam('listrow-last') . '>' . $out . '</div>';
		}
		else {
			$out = '<div' . $this->pi_classParam('listrow-normal') . '>' . $out . '</div>';
		}
		return $this->pi_getEditIcon($out,'datetime,title,teaser,full_text','Edit news item');
	}

	/**
	 * Compile the list of items (archive)
	 * 
	 * @param	pointer		MySQL SELECT resource
	 * @return	string		HTML content
	 */
	function makefrontpagelist($res)	{

			// Detecting template engine:
		if (is_array($this->TA))	{	// TemplaVoila:
				// Create list of elements:
			$elements = '';
			while($this->internal['currentRow'] = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res))	{
                $this->pi_tmpPageId = $this->internal['currentRow']['pid'];
                $link = $this->pi_list_linkSingle('', $this->internal['currentRow']['uid'], true, array(), true);
				$elements .= $this->TMPLobj->mergeDataArrayToTemplateArray(
					$this->TA['sub']['sFrontpage']['sub']['field_fpListing']['sub']['element_even'],
					array(
						'field_date' => $this->getFieldContent('datetime'),
                        'field_header' => $this->getFieldContent('title'),
						'field_teaser' => nl2br(trim(t3lib_div::fixed_lgd($this->getFieldContent('teaser_list'),$this->conf['frontPage.']['teaserLgd']))),
						'field_url' => $link,
						'field_url2' => $link
					)
				);
			}

				// Wrap the elements in their container:
			$out = $this->TMPLobj->mergeDataArrayToTemplateArray(
					$this->TA['sub']['sFrontpage'],
					array(
						'field_fpListing' => $elements,
					)
				);
		} else {	// Default:
			$items = array();

				// Make list table rows
			$first = true;
			$count = $GLOBALS['TYPO3_DB']->sql_num_rows($res); $cur = 1;
			while($this->internal['currentRow'] = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res))	{
                $this->pi_tmpPageId = $this->internal['currentRow']['pid'];
				$items[] = $this->makeFrontPageListItem($first, $cur == $count);
				$first = false; $cur++;
			}

			$out = ($this->disablePrefixComment ? '' : '

			<!--
				Frontpage listing of mininews:
			-->
			') . '<div'.$this->pi_classParam('fp_listrow').' style="margin-top: 5px;">
				'.implode(chr(10),$items).'
			</div>';
		}

		return $out;
	}

	/**
	 * Create single list item (frontpage)
	 * 
	 * @param $first <code>true</code> if first item
	 * @return	string		HTML content
	 * @see makefrontpagelist()
	 */
	function makeFrontPageListItem($first, $last)	{
		$out = '
			<p'.$this->pi_classParam('fp_listrowField-datetime').'>'.$this->getFieldContent('datetime').'</p>
			<p'.$this->pi_classParam('fp_listrowField-title').'>'.$this->pi_list_linkSingle($this->getFieldContent('title'),$this->internal['currentRow']['uid'],1).'</p>
			<p'.$this->pi_classParam('fp_listrowField-teaser').'>'.nl2br(trim(t3lib_div::fixed_lgd($this->getFieldContent('teaser_list'),$this->conf['frontPage.']['teaserLgd']))).'
			<span'.$this->pi_classParam('fp_listrowField-more-link').'>'.$this->pi_list_linkSingle($this->pi_getLL('teaser_readMore','',TRUE),$this->internal['currentRow']['uid'],1).'</span></p>
			';
		if ($first) {
			$out = '<div' . $this->pi_classParam('fp_listrow-first') . '>' . $out . '</div>';
		}
		else if ($last) {
			$out = '<div' . $this->pi_classParam('fp_listrow-last') . '>' . $out . '</div>';
		}
		else {
			$out = '<div' . $this->pi_classParam('fp_listrow-normal') . '>' . $out . '</div>';
		}

		return $out;
	}

	/**
	 * Render single view of a mininews item
	 * 
	 * @return	string		HTML content from the extension!
	 */
	function singleView()	{

			// This sets the title of the page for use in indexed search results:
		if ($this->internal['currentRow']['title'])	$GLOBALS['TSFE']->indexedDocTitle=$this->internal['currentRow']['title'];


			// Detecting template engine:
		if (is_array($this->TA))	{	// TemplaVoila:

				// Create list of elements:
			$content = $this->TMPLobj->mergeDataArrayToTemplateArray(
				$this->TA['sub']['sSingle'],
				array(
					'field_date' => ($this->internal['currentRow']['datetime'] && !$this->conf['singleView.']['disableDateDisplay'] ? $this->getFieldContent('datetime') : ''),
					'field_header' => $this->pi_getEditIcon($this->getFieldContent('title'),'datetime,title'),
					'field_teaser' => $this->pi_getEditIcon(nl2br($this->getFieldContent('teaser')),'teaser'),
					'field_bodytext' => $this->pi_getEditIcon($this->getFieldContent('full_text'),'full_text'),
					'field_url' => $this->pi_list_linkSingle('l', 0, false, array(), true)
				)
			);
		} else {	// Default:

			$content = ($this->disablePrefixComment ? '' : '

			<!--
				Single view of mininews item:
			-->
			') . '<div'.$this->pi_classParam('singleView').'>
					'.($this->internal['currentRow']['datetime'] && !$this->conf['singleView.']['disableDateDisplay'] ? '
				<p'.$this->pi_classParam('singleViewField-datetime').'>'.$this->getFieldContent('datetime').'</p>':'').'
				<h2>'.$this->pi_getEditIcon($this->getFieldContent('title'),'datetime,title').'</h2>
				<p'.$this->pi_classParam('singleViewField-teaser').'>'.$this->pi_getEditIcon(nl2br($this->getFieldContent('teaser')),'teaser').'</p>
				'.$this->pi_getEditIcon($this->getFieldContent('full_text'),'full_text').'
			</div>';
		}

		return $content . $this->pi_getEditPanel();
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