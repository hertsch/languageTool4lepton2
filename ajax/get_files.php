<?php

header( "Cache-Control: no-cache, must-revalidate" );
header( "Pragma: no-cache" );
header( "Content-Type: text/html; charset:utf-8;" );

@include dirname(__FILE__).'/../../../config.php';
@include dirname(__FILE__).'/../../../framework/functions.php';
@include dirname(__FILE__).'/../../../framework/LEPTON/Helper/I18n.php';
@include dirname(__FILE__).'/../../../framework/LEPTON/Helper/Directory.php';

$dirh         = new LEPTON_Helper_Directory();
$lang         = new LEPTON_Helper_I18n();
$target_langs = array( 'DE', 'EN', 'NL', 'FR', 'NO', 'RU', 'IT', 'PL' );
$subdir       = NULL;
$path         = NULL;
$langs 		  = array();

if ( isset($_GET['type']) )
{
    if ( $_GET['type'] == 'module' )
	{
		$subdir = 'modules';
	}
	elseif ( $_GET['type'] == 'core' )
	{
		$subdir = NULL;
	}
}

if ( isset($_GET['filetype']) )
{

    if ( isset($_GET['module']) && $_GET['module'] != '' && is_dir( $dirh->sanitizePath( LEPTON_PATH.'/'.$subdir.'/'.$_GET['module'] ) ) )
	{
		$subdir .= '/'.$_GET['module'];
	}
	elseif ( isset($_GET['iscore']) )
	{
	    $subdir .= '/framework';
	}
	
	// get existing language files; will be checked for compatibility
    if( $_GET['filetype'] == 'lang' ) {
		$path  = $dirh->sanitizePath( LEPTON_PATH.'/'.$subdir.'/languages' );
		$dirh  = @opendir($path);
		if ( ! $dirh || ! is_resource($dirh) ) {
			echo "<div class=\"error\">Unable to open directory [$path]!</div>";
		}
		else {
			while ( ( $file = readdir($dirh) ) !== false ) {
			    if ( ! preg_match ( '#^\.#', $file ) && $file != 'index.php' ) {
			        if ( is_file( $path.'/'.$file ) ) {
			            // check if this is a valid language file
			            if ( $lang->checkFile( $path.'/'.$file, 'LANG', true ) )
			            {
				            $fname = pathinfo( $file, PATHINFO_FILENAME );
				            if ( $fname != 'index' )
				            {
				            	$langs[] = $fname;
							}
						}
			        }
			    }
			}
		}
	}
	else {
		// get template files
		if ( $_GET['filetype'] == 'tpl' )
		{
		    $path  = $dirh->sanitizePath( LEPTON_PATH.'/'.$subdir.'/templates' );
		    $files = $dirh->getTemplateFiles( $path, $path );
		}
		// get script files
		elseif ( $_GET['filetype'] == 'script' )
		{
		    $path  = $dirh->sanitizePath( LEPTON_PATH.'/'.$subdir );
		    $dirh->setPrefix($path);
		    $dirh->setSkipDirs(array($path.'/languages'));
			$files = $dirh->getPHPFiles( $path, $path );
		}
		if ( count( $files ) ) {
			echo '<label id="source-label" for="source">',
		 		 $lang->translate( "Please choose a file" ),
		 		 ':</label>',
				 '<select name="source" id="source">'."\n"
				 ;
			foreach( $files as $file ) {
			    echo "<option value=\"$file\">$file</option>\n";
			}
		    echo "</select>\n";
		}
	}
}
else {
	$langs =& $target_langs;
}

if ( count( $langs ) ) {
    natcasesort($langs);
	echo '<label id="langfile-label" for="langfile">',
		 $lang->translate( "Please choose a file" ),
		 ':</label>',
		 '<select name="langfile" id="langfile">'."\n";
	foreach( $langs as $lang ) {
	    echo "<option value=\"$lang\">$lang</option>\n";
	}
    echo "</select>\n";
}

?>