<?php
# TYPO3 CVS ID: $Id$
if (!defined ("TYPO3_MODE")) 	die ("Access denied.");

$TCA["tx_mininews_news"] = Array (
	"ctrl" => $TCA["tx_mininews_news"]["ctrl"],
	"interface" => Array (
		"showRecordFieldList" => "hidden,starttime,endtime,fe_group,datetime,title,teaser,full_text,front_page"
	),
	"feInterface" => $TCA["tx_mininews_news"]["feInterface"],
	"columns" => Array (
		"hidden" => Array (		
			"exclude" => 1,	
			"label" => $LANG_GENERAL_LABELS["hidden"],
			"config" => Array (
				"type" => "check",
				"default" => "0"
			)
		),
		"starttime" => Array (		
			"exclude" => 1,	
			"label" => $LANG_GENERAL_LABELS["starttime"],
			"config" => Array (
				"type" => "input",
				"size" => "8",
				"max" => "20",
				"eval" => "date",
				"default" => "0",
				"checkbox" => "0"
			)
		),
		"endtime" => Array (		
			"exclude" => 1,	
			"label" => $LANG_GENERAL_LABELS["endtime"],
			"config" => Array (
				"type" => "input",
				"size" => "8",
				"max" => "20",
				"eval" => "date",
				"checkbox" => "0",
				"default" => "0",
				"range" => Array (
					"upper" => mktime(0,0,0,12,31,2020),
					"lower" => mktime(0,0,0,date("m")-1,date("d"),date("Y"))
				)
			)
		),
		"fe_group" => Array (		
			"exclude" => 1,	
			"label" => $LANG_GENERAL_LABELS["fe_group"],
			"config" => Array (
				"type" => "select",	
				"items" => Array (
					Array("", 0),
					Array($LANG_GENERAL_LABELS["hide_at_login"], -1),
					Array($LANG_GENERAL_LABELS["any_login"], -2),
					Array($LANG_GENERAL_LABELS["usergroups"], "--div--")
				),
				"foreign_table" => "fe_groups"
			)
		),
		"datetime" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:mininews/locallang_db.xml:tx_mininews_news.datetime:ESQ",
			"config" => Array (
				"type" => "input",
				"size" => "12",
				"max" => "20",
				"eval" => "datetime",
				"checkbox" => "0",
				"default" => "0"
			)
		),
		"title" => Array (		
			"exclude" => 0,		
			"label" => "LLL:EXT:mininews/locallang_db.xml:tx_mininews_news.title:ESQ",
			"config" => Array (
				"type" => "input",	
				"size" => "30",	
				"eval" => "required",
			)
		),
		"teaser" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:mininews/locallang_db.xml:tx_mininews_news.teaser:ESQ",
			"config" => Array (
				"type" => "text",
				"cols" => "30",	
				"rows" => "5",
			)
		),
		"full_text" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:mininews/locallang_db.xml:tx_mininews_news.full_text:ESQ",
			"config" => Array (
				"type" => "text",
				"cols" => "30",
				"rows" => "5",
				"wizards" => Array(
					"_PADDING" => 2,
					"RTE" => Array(
						"notNewRecords" => 1,
						"RTEonly" => 1,
						"type" => "script",
						"title" => "LLL:EXT:mininews/locallang_db.xml:tx_mininews_news.full_text.W.RTE",
						"icon" => "wizard_rte2.gif",
						"script" => "wizard_rte.php",
					),
				),
			)
		),
		"front_page" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:mininews/locallang_db.xml:tx_mininews_news.front_page:ESQ",
			"config" => Array (
				"type" => "check",
				"default" => 1,
			)
		),
	),
	"types" => Array (
		"0" => Array("showitem" => "hidden;;1;;1-1-1, datetime, title;;;;2-2-2, teaser;;;;3-3-3, full_text;;;richtext[cut|copy|paste|formatblock|textcolor|bold|italic|underline|left|center|right|orderedlist|unorderedlist|outdent|indent|link|table|image|line|chMode]:rte_transform[mode=ts_css|imgpath=uploads/tx_mininews/rte/], front_page")
	),
	"palettes" => Array (
		"1" => Array("showitem" => "starttime, endtime, fe_group")
	)
);
?>