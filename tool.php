<?php

/**
 *
 * @module          language_tool
 * @author          Bianka Martinovic
 * @copyright       2012 Bianka Martinovic
 * @link            http://www.LEPTON-cms.org
 * @license         copyright, all rights reserved
 * @license_terms   please see info.php of this module
 * @version         $Id$
 *
 */

$parser->setPath( dirname(__FILE__).'/templates' );
$loader = $parser->getLoader();
$loader->addDirectory(WB_PATH.'/modules/lib_dwoo/dwoo/plugins/lepton/');

$basepath = NULL;
$path     = NULL;
$file     = NULL;
$strings  = array();
$targetstrings = array();
$known    = array( 'EN', 'DE', 'IT', 'RU', 'NL', 'NO', );
$filetype = ( isset($_POST['filetype']) && ( $_POST['filetype'] == 'lang' || $_POST['filetype'] == 'script' ) )
	      ? $_POST['filetype']
	      : NULL;

natcasesort($known);

$parser->setGlobals(
	array(
		'ACTION'   => ADMIN_URL.'/admintools/tool.php?tool=language_tool',
		'leptoken' => ( isset($_REQUEST['leptoken']) ? $_REQUEST['leptoken'] : NULL ),
		'known'    => $known,
	)
);

if ( isset($_POST['type']) ) {
	if ( $_POST['type'] == 'module' ) {
	    if ( isset($_POST['module']) ) {
	        if ( is_dir( sanitize_path( LEPTON_PATH.'/modules/'.$_POST['module'] ) ) ) {
	    		$basepath = sanitize_path( LEPTON_PATH.'/modules/'.$_POST['module'] );
			}
		}
	}
	elseif (  $_POST['type'] == 'core' ) {
	    $basepath = LEPTON_PATH;
	}
}

$path = $basepath;
if ( $filetype == 'lang' )
{
	$path = $basepath.'/languages';
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
	if ( ! preg_match( '~^' . str_replace( '\\', '/', LEPTON_PATH ) . '~i', str_replace( '\\', '/', dirname($file) ) ) ) {
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
	        $strings[$item['line']]
	            = array(
	                'html'  => htmlentities($item['text']),
	                'plain' => $item['text']
				  );
	        //$strings[ htmlentities($item['text']) . '<br /><span style="font-size:small;">(line: ' . $item['line'] . ')</span>' ] = $item['text'];
	    }
	}
	if ( isset($_POST['targetlang']) )
	{
	    $langfile = sanitize_path( $basepath.'/languages/'.$_POST['targetlang'].'.php' );
	    if ( file_exists($langfile) )
	    {
	        $targetstrings = $admin->lang->checkFile( $langfile, 'LANG', true );
		    // remove quotes
			foreach( $targetstrings as $i => $line ) {
			    $targetstrings[$i] = str_replace( array( "'", '"' ), array( '', '' ), $line );
			}
	    }
	}
}
else
{
	echo $parser->get( 'index.lte' );
	exit;
}

if ( count($strings) )
{
	foreach( $strings as $line => $item )
	{
	    $sourcestrings[] = $item['plain'];
	}

	// find missing strings in $targetstrings
	$missing_right = array_diff( $sourcestrings, array_keys($targetstrings) );
	$missing_left  = array_diff( array_keys($targetstrings), $sourcestrings );
	
	// create allstrings array
	$allstrings    = array_merge( $sourcestrings, array_keys($targetstrings) );
	
	$allstrings = array_unique(array_values($allstrings));
	natcasesort($allstrings);

echo "<textarea cols=\"100\" rows=\"20\" style=\"width: 100%;\">";
print_r( $allstrings );
echo "</textarea>";
echo "<textarea cols=\"100\" rows=\"20\" style=\"width: 100%;\">";
print_r( $targetstrings );
echo "</textarea>";
echo "<textarea cols=\"100\" rows=\"20\" style=\"width: 100%;\">";
print_r( array_key_exists( 'Actions', $targetstrings ) );
echo "</textarea>";
	echo $parser->get(
		'edit_strings.lte',
		array(
			'strings'       => $allstrings,
			'targetstrings' => $targetstrings,
			'missingright'  => $missing_right,
			'missingleft'   => $missing_left,
			'filepath' 		=> str_ireplace( str_replace( '\\', '/', LEPTON_PATH ), '.', $file ),
			'file'     		=> str_ireplace( str_replace( '\\', '/', $path ), '.', $file ),
			'type'     		=> $_POST['type'],
			'filetype' 		=> $filetype,
			'module'   		=> ( ( isset($_POST['module']) ) ? $_POST['module'] : NULL ),
   	    )
	);
}
else
{
	echo $parser->get( 'index.lte', array( 'info' => 'No strings available' ) );
    exit;
}
 
?>