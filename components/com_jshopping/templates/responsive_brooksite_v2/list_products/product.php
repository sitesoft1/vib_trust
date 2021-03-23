<?php defined('_JEXEC') or die('Restricted access');
include(dirname(__FILE__)."/../plugin_params.php");
$stv = $session->get('toView','');
print $product->_tmp_var_start;
if ($ualayout && $ualayout=="mobile" && $mobile_list_view) {
	include(dirname(__FILE__)."/mobileview.php");
} else {
	if ($stv=="toGrid" || !$stv){
		include(dirname(__FILE__)."/gridview.php");
	}
	if ($stv=="toList"){
		include(dirname(__FILE__)."/listview.php");
	}
	if ($stv=="toSlim"){
		include(dirname(__FILE__)."/slimview.php");
	}
}
print $product->_tmp_var_end;?>