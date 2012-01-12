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

global
	$filetype,
	$file,
	$type,
	$basepath,
	$infile;

$basepath = NULL;
$path     = NULL;
$file     = NULL;
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

if ( isset($_REQUEST['ltsubmit']) ) {

	$strings        = array();
	$targetstrings  = array();
	$sourcestrings  = array();
	$lines          = array();

	$filetype 		= ( isset($_POST['filetype']) && $_POST['filetype'] != '' )
	            	? $_POST['filetype']
	            	: 'lang';
	$type     		= ( isset($_POST['type']) && $_POST['type'] != '' )
	            	? $_POST['type']
	            	: 'module';
	$file     		= ( isset($_POST['source']) && $_POST['source'] != '' )
	            	? $_POST['source']
	            	: (
						( isset($_POST['langfile']) && $_POST['langfile'] != '' )
						? $_POST['langfile']
						: 'DE'
				  	  );

	$basepath = sanitize_path( LEPTON_PATH.'/'.($type=='module'?'modules/'.$_POST['module']:'framework').'/'.($filetype=='lang'?'languages':'') );
    $infile   = sanitize_path( $basepath.'/'.$file );

	if ( isset($_POST['savetrans']) ) {
		lt_save_trans();
	}

    // validate path
	if ( ! empty($infile) && file_exists( $infile ) ) {
		// do not allow files outside installation dir
		if ( ! preg_match( '~^' . str_replace( '\\', '/', LEPTON_PATH ) . '~i', str_replace( '\\', '/', dirname($infile) ) ) ) {
		    $admin->print_error( 'Invalid path!' );
		}
	}
    
	if ( ! file_exists( $infile ) ) {
	    $parser->setGlobals(
			array( 'info' => $admin->lang->translate('The file does not exist!') )
		);
	}

	// handle existing language file
	if ( $filetype == 'lang' )
	{
	    $strings = $admin->lang->checkFile( $infile, 'LANG', true );
	    // remove quotes
		foreach( $strings as $i => $line ) {
		    $strings[$i] = str_replace( array( "'", '"' ), array( '', '' ), $line );
		}
	}
	// handle template file
	elseif ( $filetype == 'tpl' )
	{
	    $infile = sanitize_path( LEPTON_PATH.'/'.($type=='module'?'modules/'.$_POST['module']:'framework').'/templates/'.$file );
	    // let the I18n helper save all translations
		LEPTON_Helper_I18n::$_store_translations = true;
		LEPTON_Helper_I18n::$_translated         = array();
		// let Dwoo parse the file; this will call the translate() method
	    $parser->get( $infile );
		// now we have all translated messages
		foreach( LEPTON_Helper_I18n::$_translated as $item )
		{
		    $strings[] = array( 'plain' => $item );
		}
	}
	// handle script file
	elseif ( $filetype == 'script' )
	{
		$found = $admin->lang->parseFile( $infile );
		if ( is_array($found) && count($found) ) {
		    foreach ( $found as $item ) {
		        $item['text'] = str_replace( array( "'", '"' ), array( '', '' ), $item['text'] );
		        $strings[$item['line']]
		            = array(
		                'html'  => htmlentities($item['text']),
		                'plain' => $item['text']
					  );
				$lines[$item['text']] = $item['line'];
		    }
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
		
		$matches = $admin->lang->getMatchCount();
		if ( $matches == 0 ) {
		    $matches = count(LEPTON_Helper_I18n::$_translated);
		}
		
		echo $parser->get(
			'edit_strings.lte',
			array_merge(
			    $_POST,
				array(
					'allstrings'    => $allstrings,
					'lines'         => $lines,
					'targetstrings' => $targetstrings,
					'missingright'  => $missing_right,
					'missingleft'   => $missing_left,
					'filepath' 		=> str_ireplace( str_replace( '\\', '/', LEPTON_PATH ), '.', $infile ),
					'file'          => $file,
					'filetype' 		=> $filetype,
					'module'   		=> ( ( isset($_POST['module']) )     ? $_POST['module']     : NULL ),
					'targetlang'    => ( ( isset($_POST['targetlang']) ) ? $_POST['targetlang'] : NULL ),
					'count'         => $matches,
		   	    )
			)
		);
	}
	else
	{
		echo $parser->get( 'index.lte', array( 'info' => 'No strings available' ) );
	}

}
else {
	echo $parser->get( 'index.lte' );
}


function lt_save_trans()
{

	global
		$filetype,
		$file,
		$type,
		$basepath,
		$infile;
		
	$path    = sanitize_path( LEPTON_PATH.'/'.($type=='module'?'modules/'.$_POST['module']:'framework').'/languages' );
	$outfile = sanitize_path( $path.'/'.((isset($_POST['outlang'])&&$_POST['outlang']!='')?$_POST['outlang']:$_POST['targetlang']).'.php.test' );
	$isnew   = true;
	if ( file_exists( $outfile ) )
	{
//	    copy( $outfile, $outfile.'.bak' );
		$isnew = false;
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
			$output[] = "    ".$quote.$orig.$quote."\n        => '".$value."',";
		}
    }

	if ( count($output) )
	{
	    $fh = fopen( $outfile, 'w' );
	    fwrite( $fh, '<'.'?'.'php'."\n\n" );
	    if ( $isnew ) {
	        fwrite( $fh, lt_print_header( $_POST['targetlang'] ) );
		}
	    fwrite( $fh, '// File created/edited using LanguageTool'."\n\n" );
	    fwrite( $fh, '$LANG = array ('."\n" );
	    fwrite( $fh, implode( "\n", $output ) );
	    fwrite( $fh, "\n".');'."\n\n".'?'.'>' );
	    fseek( $fh, ftell( $fh ) );
	    fclose( $fh );
	}

}

function lt_print_header( $lang ) {
	return "/**
 *
 * @language        $lang
 * @created         LEPTON Language Tool
 * @license         copyright, all rights reserved
 * @version         \$Id\$
 *
 */
";
}
