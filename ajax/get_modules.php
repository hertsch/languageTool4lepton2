<?php

header( "Cache-Control: no-cache, must-revalidate" );
header( "Pragma: no-cache" );
header( "Content-Type: text/html; charset:utf-8;" );

@include dirname(__FILE__).'/../../../config.php';
@include dirname(__FILE__).'/../../../framework/LEPTON/Helper/I18n.php';

$lang    = new LEPTON_Helper_I18n();
$path    = WB_PATH.'/modules';
$dirh    = opendir($path);
$modules = array();

if ( ! $dirh || ! is_resource($dirh) ) {
	echo "<div class=\"error\">kaputt</div>";
}
else {
	while ( ( $dir = readdir($dirh) ) !== false ) {
	    if ( ! preg_match ( '#^\.#', $dir ) ) {
	        if ( is_dir( $path.'/'.$dir.'/languages' ) ) {
	            $modules[] = $dir;
	        }
	    }
	}
}

if ( count( $modules ) ) {
	echo '<label id="langfile-label" for="langfile">',
		 $lang->translate( "Please choose a module" ),
		 ':</label>',
		 '<select name="module" id="module" onchange="lt_get_files();">'."\n",
		 '<option value="">[choose]</option>';
	foreach( $modules as $mod ) {
	    echo "<option value=\"$mod\">$mod</option>\n";
	}
    echo "</select><br />\n";
}

?>