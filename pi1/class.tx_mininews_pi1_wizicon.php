<?php
/***************************************************************
*  Copyright notice
*  
*  (c) 2002-2004 Kasper Sk�rh�j (kasper@typo3.com)
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
 * Class that adds the wizard icon.
 *
 * $Id$
 *
 * @author	Kasper Sk�rh�j <kasper@typo3.com>
 */
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 *
 *
 *   55: class tx_mininews_pi1_wizicon 
 *   63:     function proc($wizardItems)	
 *   83:     function includeLocalLang()	
 *
 * TOTAL FUNCTIONS: 2
 * (This index is automatically created/updated by the extension "extdeveval")
 *
 */

 
require_once(PATH_typo3.'sysext/lang/lang.php');

 
/**
 * Class that adds the wizard icon.
 * 
 * @author	Kasper Sk�rh�j (kasper@typo3.com)
 * @package TYPO3
 * @subpackage tx_mininews
 */
class tx_mininews_pi1_wizicon {

	/**
	 * Adds Mininews wizard item to the list in the DB content element wizard.
	 * 
	 * @param	array		Array of wizard items, input
	 * @return	array		Array of wizard items, output (modified)
	 */
	function proc($wizardItems)	{
		global $LANG;

		$LL = $this->includeLocalLang();

		$wizardItems['plugins_tx_mininews_pi1'] = array(
			'icon' => t3lib_extMgm::extRelPath('mininews').'pi1/ce_wiz.gif',
			'title' => $LANG->getLLL('pi1_title',$LL),
			'description' => $LANG->getLLL('pi1_plus_wiz_description',$LL),
			'params' => '&defVals[tt_content][CType]=list&defVals[tt_content][list_type]=mininews_pi1'
		);

		return $wizardItems;
	}

	/**
	 * Include local lang file.
	 * 
	 * @return	array		Local lang array.
	 */
	function includeLocalLang()	{
		if (isset($GLOBALS['LANG'])) {
			$lang = $GLOBALS['LANG'];
		}
		else {
			$lang = t3lib_div::makeInstance('language');
			$lang->init($GLOBALS['BE_USER']->uc['lang']);
		}
		return $lang->readLLFile('EXT:mininews/locallang.xml');
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/mininews/pi1/class.tx_mininews_pi1_wizicon.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/mininews/pi1/class.tx_mininews_pi1_wizicon.php']);
}
?>