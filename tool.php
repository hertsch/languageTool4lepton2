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
$file     = NULL;
$strings = array();
$known    = array( 'EN', 'DE', 'IT', 'RU', 'NL', 'NO', );
$filetype = ( isset($_POST['filetype']) && ( $_POST['filetype'] == 'lang' || $_POST['filetype'] == 'script' ) )
	     ? $_POST['filetype']
	     : NULL;

natcasesort($known);

$parser->setGlobals(
	array(
		'ACTION' => ADMIN_URL.'/admintools/tool.php?tool=language_tool',
		'known'  => $known,
	)
);


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

if ( $filetype == 'lang' )
{
	$path .= '/languages';
}

// this is for editing an existing lang file
if ( isset($_POST['langfile']) )
{
	$file = sanitize_path( $path.'/'.$_POST['langfile'].'.php' );
}
elseif ( isset($_POST['source']) )
{
    $file = sanitize_path( $path.'/'.$_POST['source'] );
}

if ( isset($_POST['cancel']) )
{
    echo $parser->get( 'index.lte' );
    exit;
}

if ( isset($_POST['savetrans']) )
{
	$file = sanitize_path( $path.'/'.$_POST['file'] );
	if ( file_exists( $file ) )
	{
	    copy( $file, $file.'.bak' );
	}
	$output = array();
	foreach ( $_POST as $key => $value )
    {
		$index = NULL;
        if ( preg_match( '~^trans_(.+?)$~i', $key, $match ) )
        {
	        $index    = $match[1];
	        $orig     = $_POST['orig_'.$index];
	        if ( preg_match( '~\'~', $orig ) )
	        {
	            $quote = '"';
			}
			else
			{
			    $quote = "'";
			}
			$output[] = "    ".$quote.$orig.$quote." => '".$value."',";
		}
    }
	if ( count($output) )
	{
	    $fh = fopen( $file, 'w' );
	    fwrite( $fh, '<'.'?'.'php'."\n\n" );
	    fwrite( $fh, '// File created/edited using LanguageTool'."\n\n" );
	    fwrite( $fh, '$LANG = array ('."\n" );
	    fwrite( $fh, implode( "\n", $output ) );
	    fwrite( $fh, ');'."\n\n".'?'.'>' );
	    fseek( $fh, ftell( $fh ) );
	    fclose( $fh );
	}
}

// check if file exists
if ( ! empty($file) && file_exists( $file ) ) {
	// do not allow files outside installation dir
	if ( ! preg_match( '~^' . str_replace( '\\', '/', WB_PATH ) . '~i', str_replace( '\\', '/', dirname($file) ) ) ) {
	    $admin->print_error( 'Invalid path!' );
	}
}

if ( $filetype == 'lang' )
{
    $strings = $admin->lang->checkFile( $file, 'LANG', true );
    // remove quotes
	foreach( $strings as $i => $line ) {
	    $strings[$i] = str_replace( array( "'", '"' ), array( '', '' ), $line );
	}
}
elseif ( $filetype == 'script' )
{
	$found = $admin->lang->parseFile( $file );
	if ( is_array($found) && count($found) ) {
	    foreach ( $found as $item ) {
	        $item['text'] = str_replace( array( "'", '"' ), array( '', '' ), $item['text'] );
	        $strings[ htmlentities($item['text']) . '<br /><span style="font-size:small;">(line: ' . $item['line'] . ')</span>' ] = $item['text'];
	    }
	}
}
else
{
	echo $parser->get( 'index.lte' );
	exit;
}

if ( count($strings) ) {
	// remove doubles
	$strings = array_unique($strings);
	// sort strings
	uksort($strings,'strcasecmp');
	echo $parser->get(
		'edit_strings.lte',
		array(
			'strings'  => $strings,
			'filepath' => str_ireplace( str_replace( '\\', '/', WB_PATH ), '.', $file ),
			'file'     => str_ireplace( str_replace( '\\', '/', $path ), '.', $file ),
			'type'     => $_POST['type'],
			'filetype' => $filetype,
			'module'   => ( ( isset($_POST['module']) ) ? $_POST['module'] : NULL ),
   	    )
	);
}
else
{
	echo $parser->get( 'index.lte', array( 'info' => 'No strings available' ) );
    exit;
}
 
?>