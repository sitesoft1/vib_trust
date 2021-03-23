<?php defined('_JEXEC') or die('Restricted access');?>
<?php
$product_id = $product->product_id;
$my_db = JFactory::getDBO();
$my_db->setQuery("SELECT `state_id`, `country_id` FROM `#__jshopping_products` WHERE `product_id`='$product_id'");
$row = $my_db->loadObjectList();
if(isset($row[0]->country_id)){
    $country_id = $row[0]->country_id;
}
if(isset($row[0]->state_id)){
    $state_id = $row[0]->state_id;
}

$my_db->setQuery("SELECT `name_ru-RU` AS `country_name` FROM `#__jshopping_countries` WHERE `country_id`='$country_id'");
$row2 = $my_db->loadObjectList();
if(isset($row2[0]->country_name)){
    $country_name = $row2[0]->country_name;
}

$my_db->setQuery("SELECT `name_ru-RU` AS `city_name` FROM `#__jshopping_states` WHERE `country_id`='$country_id' AND `state_id`='$state_id'");
$row3 = $my_db->loadObjectList();
if(isset($row3[0]->city_name)){
    $city_name = $row3[0]->city_name;
}
?>
<div class="product productitem_<?php print $product->product_id;?> row list-row list-product-caption">
    <div class = "<?php print $prefix?>2 image">
        <?php if ($product->image){?>
        <div class="image_block">
            <?php print $product->_tmp_var_image_block;?>
            <?php if ($product->label_id){?>
                <div class="product_label">
                    <?php if ($product->_label_image){?>
                        <img src="<?php print $product->_label_image?>" alt="<?php print htmlspecialchars($product->_label_name)?>" />
                    <?php }else{?>
                        <span class="label_name"><?php print $product->_label_name;?></span>
                    <?php }?>
                </div>
            <?php }?>
            <a href="<?php print $product->product_link?>">
                <img class="jshop_img" src="<?php print $product->image?>" alt="<?php print htmlspecialchars($product->name);?>" title="<?php print htmlspecialchars($product->name);?>" />
            </a>
        </div>
        <?php }?>
        <?php print $product->_tmp_var_bottom_foto;?>
    </div>
    
    <div class="<?php print $prefix?>3">
        <div class="name">
            <h4><a href="<?php print $product->product_link?>" title="<?php print _JSHOP_DETAIL; ?>"><?php print $product->name?> <?php print $product->_tmp_var_more_buttons;?></a></h4>
            <?php if ($this->config->product_list_show_product_code && $product->product_ean){?><span class="jshop_code_prod"><?php print _JSHOP_EAN?>: <span><?php print $product->product_ean;?></span></span><?php }?><?php if ($this->config->product_list_show_product_code && $product->product_ean && $this->config->manufacturer_code_in_product_list && $product->manufacturer_code) print ",";?>
            <?php if ($this->config->manufacturer_code_in_product_list && $product->manufacturer_code){?> <span class="jshop_manufacturer_prod"><?php print _JSHOP_MANUFACTURER_CODE?>: <span id="manufacturer_code"><?php print $product->manufacturer_code;?></span></span><?php }?>
            <?php if(!empty($country_name) and !empty($city_name)){ ?>
                <div style="margin-top: -10px;"><?php echo $country_name; ?>, г. <?php echo $city_name; ?></div>
            <?php } ?>
        </div>
    </div><!--END BLOCK 2 FOR LISTVIEW-->

    <div class="<?php print $prefix?>3"><!--START BLOCK 3 FOR LISTVIEW-->
        <div class="price_block">
        <?php if ($product->product_price_default > 0 && $this->config->product_list_show_price_default){?>
            <div class="default_price"><?php print _JSHOP_DEFAULT_PRICE.": ";?><span><?php print formatprice($product->product_price_default)?></span></div>
        <?php }?>
        <?php if ($product->_display_price){?>
            <span class = "jshop_price">
                <?php if ($this->config->product_list_show_price_description) //print _JSHOP_PRICE.": ";?>
                <?php if ($product->show_price_from) print _JSHOP_FROM." ";?>
                <span><?php print formatprice($product->product_price);?><?php print $product->_tmp_var_price_ext;?></span>
            </span>
        <?php }?>
        <?php print $product->_tmp_var_bottom_price;?>
    
        <?php if ($product->basic_price_info['price_show']){?>
            <div class="base_price"><?php print _JSHOP_BASIC_PRICE?>: <?php if ($product->show_price_from) print _JSHOP_FROM;?> <span><?php print formatprice($product->basic_price_info['basic_price'])?> / <?php print $product->basic_price_info['name'];?></span></div>
        <?php }?>
        </div>
    </div><!--END BLOCK 3 FOR LISTVIEW-->
    
    <div class="<?php print $prefix?>4"><!--START BLOCK 4 FOR LISTVIEW-->
    <div class="buttons">
        <?php print $product->_tmp_var_before_buy_1;?>
        <?php print $product->_tmp_var_before_buy_2;?>
        <?php if ($product->buy_link){?>
            <a <?php if ($btn_buy_text != "1") print 'data-rel="tooltip" data-placement="top" title="'._JSHOP_BUY.'"'; ?> class="btn button_buy" href="<?php print $product->buy_link?>"><i class="fa fa-shopping-cart"></i> <?php if ($btn_buy_text != "1") print _JSHOP_BUY ?></a>
        <?php }?>
        <?php print $product->_tmp_var_after_buy_1;?>
        <br/>
        <?php print $product->_tmp_var_after_buy_2;?>
        <?php print $product->_tmp_var_buttons;?>
        <?php print $product->_tmp_var_after_buy_3;?>
        <?php print $product->_tmp_var_after_buy_4;?>
        </div>
        <?php print $product->_tmp_var_more_def_buttons;?>
        <?php print $product->_tmp_var_bottom_buttons;?>
    </div><!--END BLOCK FOR LISTVIEW-->
</div>