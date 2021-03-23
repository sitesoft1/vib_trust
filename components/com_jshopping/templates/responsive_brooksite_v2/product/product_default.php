<?php defined('_JEXEC') or die('Restricted access');
$product = $this->product;
include(dirname(__FILE__)."/load.js.php");
include(dirname(__FILE__)."/../plugin_params.php");
include_once(dirname(__FILE__)."/__array_list_categories.php");
if ($on_mod_inproducts) {
    include(dirname(__FILE__)."/_addmodule.php");
}

if (!in_array($this->product->product_id, $prod_seo_exclude_arr) && !in_array($this->category_id, $categ_seo_exclude_arr)) {
    $document =  JFactory::getDocument();
    if ($on_seo_title=="1") {
        $get_title = $document->getTitle();
        if ($noempty_seo_exclude!="1" || ($noempty_seo_exclude=="1" && !$get_title)) {
            $document->setTitle($prod_title_prefix." ".htmlspecialchars($cnp)." ".$prod_title_name_prefix." ".htmlspecialchars($this->product->name)." ".$prod_title_suffix);
        }
    }
    if ($on_seo_descr) {
        $get_metadata_decsr = $document->getMetadata('description');
        if ($noempty_seo_exclude!="1" || ($noempty_seo_exclude=="1" && !$get_metadata_decsr)) {
            $manufacturer_seo="";
            if ($on_seo_pm=="1") {
                $manufacturer_seo = $prefix_seo_pm." ".htmlspecialchars($this->product->manufacturer_info->name)." ".$suffix_seo_pm;
            }
            $document->setMetaData('description', trim($prod_descr_prefix." ".rtrim(htmlspecialchars($category_name),", ")." ".$prod_descr_name_prefix." ".htmlspecialchars($this->product->name)." ".$prod_descr_suffix." ".$manufacturer_seo));
        }
    }
}
?>
<div class="jshop productfull rp-brooksite" data-id="<?php print $this->product->product_id;?>">
	<div class="productfull-main">
        <?php
            if ($ualayout && $ualayout=="mobile" && $mobile_card_view) {
                include(dirname(__FILE__)."/productfull_mobile.php");
            } else {
                include(dirname(__FILE__)."/productfull_main_".$templ_type.".php");
            }
        ?>
    </div>

	<?php if ($show_tabs_descr!="1" || $on_tabs!="1"){?>
    <div class="jshop_prod_description">
        <?php print $this->product->description; ?>
    </div>
    <?php print $this->_tmp_product_html_after_description;?>
    <?php } ?>
    
    <?php if ($this->product->product_url!=""){?>
    <div class="prod_url">
        <a target="_blank" href="<?php print $this->product->product_url;?>"><?php print _JSHOP_READ_MORE?></a>
    </div>
    <?php }?>
      
    <?php //Tabs
	if ($on_tabs=="1"){
		$brwser = new JBrowser;
		if ($typeTabs=="Collapse" || $brwser->isMobile()){
			include(dirname(__FILE__)."/collapse.php");
		} else {
			include(dirname(__FILE__)."/tabs.php");
		}
	} else {
		include(dirname(__FILE__)."/no_tabs.php");
	}?>
    
    <?php if ($this->config->product_show_button_back && $ualayout!="mobile"){?>
        <div class="button_back">
        <input type="button" class="btn button" value="&larr; <?php print _JSHOP_BACK;?>" onclick="<?php print $this->product->button_back_js_click;?>" />
        </div>
    <?php }?>
<?php print $this->_tmp_product_html_end;?>
</div>