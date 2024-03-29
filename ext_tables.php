<?php
# TYPO3 CVS ID: $Id$
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

// Make sure to load all of 'tt_content':
t3lib_div::loadTCA('tt_content');

// ... and finally add the new column definition to the list of fields shown for the mininews plugin:
// (This also removes the presence of the normally shown fields, 'layout' and 'select_key')
$TCA['tt_content']['types']['list']['subtypes_excludelist'][$_EXTKEY.'_pi1']='layout,select_key,pages';
$TCA['tt_content']['types']['list']['subtypes_addlist'][$_EXTKEY.'_pi1']='pi_flexform';
t3lib_extMgm::addPiFlexFormValue($_EXTKEY.'_pi1', 'FILE:EXT:mininews/flexform_ds.xml');

// Now, define a new table for the extension. Name: 'tx_mininews_news'
// Only the 'ctrl' section is defined since the rest of the config is in the 'tca.php' file, loaded dynamically when needed.
$TCA['tx_mininews_news'] = Array (
	'ctrl' => Array (
		'title' => 'LLL:EXT:mininews/locallang_db.xml:tx_mininews_news',
		'label' => 'title',	
		'tstamp' => 'tstamp',
		'crdate' => 'crdate',
		'cruser_id' => 'cruser_id',
		'default_sortby' => 'ORDER BY datetime DESC',	
		'delete' => 'deleted',	
		'enablecolumns' => Array (		
			'disabled' => 'hidden',	
			'starttime' => 'starttime',	
			'endtime' => 'endtime',	
			'fe_group' => 'fe_group',
		),
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
		'iconfile' => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_mininews_news.gif',
	),
	'feInterface' => Array (
		'fe_admin_fieldList' => 'hidden, starttime, endtime, fe_group, datetime, title, teaser, full_text, front_page',
	)
);

// Then, make sure records from this table is allowed on regular pages:
t3lib_extMgm::allowTableOnStandardPages('tx_mininews_news');
// ... and allowed to be added in the 'Insert Record' content element type:
t3lib_extMgm::addToInsertRecords('tx_mininews_news');


t3lib_extMgm::addPlugin(Array('LLL:EXT:mininews/locallang_db.xml:tt_content.list_type', $_EXTKEY.'_pi1'),'list_type');

// Adding datastructure for Mininews:
$GLOBALS['TBE_MODULES_EXT']['xMOD_tx_templavoila_cm1']['staticDataStructures'][]=array(
	'title' => 'Mininews Template',
	'path' => 'EXT:'.$_EXTKEY.'/template_datastructure.xml',
	'icon' => '',
	'scope' => 0,
);	
	
if (TYPO3_MODE=='BE')	$TBE_MODULES_EXT['xMOD_db_new_content_el']['addElClasses']['tx_mininews_pi1_wizicon'] = t3lib_extMgm::extPath($_EXTKEY).'pi1/class.tx_mininews_pi1_wizicon.php';
?>