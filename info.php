<?php

/**
 *
 * @module          admin langs
 * @author          Ralf Hertsch, Bianka Martinovic
 * @copyright       2011, Ralf Hertsch, Bianka Martinovic
 * @link            http://www.LEPTON-cms.org
 * @license         copyright, all rights reserved
 * @license_terms   please see info.php of this module
 * @version         $Id: info.php 1240 2011-10-21 12:24:40Z frankh $
 *
 */

 // include class.secure.php to protect this file and the whole CMS!
if (defined('WB_PATH')) {
	include(WB_PATH.'/framework/class.secure.php');
} else {
	$root = "../";
	$level = 1;
	while (($level < 10) && (!file_exists($root.'/framework/class.secure.php'))) {
		$root .= "../";
		$level += 1;
	}
	if (file_exists($root.'/framework/class.secure.php')) {
		include($root.'/framework/class.secure.php');
	} else {
		trigger_error(sprintf("[ <b>%s</b> ] Can't include class.secure.php!", $_SERVER['SCRIPT_NAME']), E_USER_ERROR);
	}
}
// end include class.secure.php

$module_directory	= 'language_tool';
$module_name		= 'LanguageTool';
$module_function	= 'tool';
$module_version		  = '0.3';
$module_platform	  = '2.x';
$module_author		= 'Ralf Hertsch, Bianka Martinovic';
$module_license		= 'copyright, all rights reserved';
$module_license_terms	= 'usage only with written permission, use with LEPTON core is allowed';
$module_description	  = 'Allows you to edit existing language files and parse your scripts for translate() calls';
$module_guid		= "ba58b61b-6c75-44f2-9e35-535d87384fef";


?>
