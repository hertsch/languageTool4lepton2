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

$parser->setPath( dirname(__FILE__).'/templates' );
$loader = $parser->getLoader();
$loader->addDirectory(WB_PATH.'/modules/lib_dwoo/dwoo/plugins/lepton/');

$path    = NULL;
$strings = array();
$type    = ( isset($_POST['filetype']) && ( $_POST['filetype'] == 'lang' || $_POST['filetype'] == 'script' ) )
	     ? $_POST['filetype']
	     : NULL;

if ( isset($_POST['type']) ) {
	if ( $_POST['type'] == 'module' ) {
	    if ( isset($_POST['module']) ) {
	        if ( is_dir( sanitize_path( WB_PATH.'/modules/'.$_POST['module'] ) ) ) {
	    		$path = sanitize_path( WB_PATH.'/modules/'.$_POST['module'] );
			}
		}
	}
	elseif (  $_POST['type'] == 'core' ) {
	    $path = WB_PATH;
	}
}

if ( $type == 'lang' )
{
	$path .= '/languages';
}

$file = $path.'/EN.php';

// this is for editing an existing lang file
if ( isset($_POST['langfile']) )
{
	$file = sanitize_path( $path.'/'.$_POST['langfile'].'.php' );
}
elseif ( isset($_POST['source']) )
{
    $file = sanitize_path( $path.'/'.$_POST['source'] );
}

// check if file exists
if ( file_exists( $file ) ) {
	// do not allow files outside installation dir
	if ( ! preg_match( '~^' . str_replace( '\\', '/', WB_PATH ) . '~i', str_replace( '\\', '/', dirname($file) ) ) ) {
	    $admin->print_error( 'Invalid path!' );
	}
}
else {
    $admin->print_error( 'No such file' );
}

if ( $type == 'lang' )
{
    $strings = $admin->lang->checkFile( $file, 'LANG', true );
    // remove quotes
	foreach( $strings as $i => $line ) {
	    $strings[$i] = str_replace( array( "'", '"' ), array( '', '' ), $line );
	}
}
elseif ( $type == 'script' ) {
	$found = $admin->lang->parseFile( $file );
	if ( is_array($found) && count($found) ) {
	    foreach ( $found as $item ) {
	        $item['text'] = str_replace( array( "'", '"' ), array( '', '' ), $item['text'] );
	        $strings[ $item['text'] . '<br /><span style="font-size:small;">(line: ' . $item['line'] . ')</span>' ] = $item['text'];
	    }
	}
}
else {
	echo $parser->get( 'index.lte', array( 'ACTION' => ADMIN_URL.'/admintools/tool.php?tool=language_tool' ) );
}

if ( count($strings) ) {
	// remove doubles
	$strings = array_unique($strings);
	// sort strings
	uksort($strings,'strcasecmp');
	echo $parser->get( 'edit_strings.lte', array( 'strings' => $strings, 'file' => str_ireplace( str_replace( '\\', '/', WB_PATH ), '.', $file ) ) );
}
 
?>