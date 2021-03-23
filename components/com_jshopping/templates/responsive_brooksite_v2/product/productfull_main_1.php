<?php defined('_JEXEC') or die('Restricted access');?>
<?php
$product_id = $product->product_id;
$my_db = JFactory::getDBO();
$my_db->setQuery("SELECT `state_id`, `country_id` FROM `#__jshopping_products` WHERE `product_id`='$product_id'");
$row = $my_db->loadObjectList();
$country_id = $row[0]->country_id;
$state_id = $row[0]->state_id;
$my_db->setQuery("SELECT `name_ru-RU` AS `country_name` FROM `#__jshopping_countries` WHERE `country_id`='$country_id'");
$row2 = $my_db->loadObjectList();
$country_name = $row2[0]->country_name;

$my_db->setQuery("SELECT `name_ru-RU` AS `city_name` FROM `#__jshopping_states` WHERE `country_id`='$country_id' AND `state_id`='$state_id'");
$row3 = $my_db->loadObjectList();
$city_name = $row3[0]->city_name;
?>
<form name="product" method="post" action="<?php print $this->action?>" enctype="multipart/form-data" autocomplete="off">
    <?php print $this->_tmp_product_html_start;?>
    <div class="row<?php print $row_postfix?> jshop pm1">
        <div class="<?php print $prefix.$pibw;?> image_middle">
            <?php print $this->_tmp_product_html_before_image;?>
            <?php if ($product->label_id){?>
                <div class="product_label">
                    <?php if ($product->_label_image){?>
                        <img src="<?php print $product->_label_image?>" alt="<?php print htmlspecialchars($product->_label_name)?>" />
                    <?php }else{?>
                        <span class="label_name"><?php print $product->_label_name;?></span>
                    <?php }?>
                </div>
            <?php }?>
            <?php if (count($this->videos)){?>
                <?php foreach($this->videos as $k=>$video){?>
                    <?php if ($video->video_code){ ?>
                    <div style="display:none" class="video_full" id="hide_video_<?php print $k?>"><?php echo $video->video_code?></div>
                    <?php } else { ?>
                    <a style="display:none" class="video_full" id="hide_video_<?php print $k?>" href=""></a>
                    <?php } ?>
                <?php } ?>
            <?php }?>

            <span id='list_product_image_middle'>
            <?php print $this->_tmp_product_html_body_image?>
            <?php if(!count($this->images)){?>
                <img id = "main_image" src = "<?php print $this->image_product_path?>/<?php print $this->noimage?>" alt = "<?php print htmlspecialchars($this->product->name)?>" />
            <?php }?>
            <?php foreach($this->images as $k=>$image){?>
            <a class="lightbox" id="main_image_full_<?php print $image->image_id?>" href="<?php print $this->image_product_path?>/<?php print $image->image_full;?>" <?php if ($k!=0){?>style="display:none"<?php }?> title="<?php print htmlspecialchars($image->_title)?>">
                <img id = "main_image_<?php print $image->image_id?>" class="main_image" src = "<?php print $this->image_product_path?>/<?php print $image->image_name;?>" alt="<?php print htmlspecialchars($image->_title)?>" title="<?php print htmlspecialchars($image->_title)?>" />
                <div class="text_zoom">
                    <img src="<?php print $this->path_to_image?>search.png" alt="zoom" /> <?php print _JSHOP_ZOOM_IMAGE?>
                </div>
            </a>
            <?php }?>
            </span>
            <?php print $this->_tmp_product_html_after_image;?>

            <div class = "jshop_img_description">
                <?php print $this->_tmp_product_html_before_image_thumb;?>
                <span id='list_product_image_thumb'>
                <?php if ( (count($this->images)>1) || (count($this->videos) && count($this->images)) ) {?>
                    <?php foreach($this->images as $k=>$image){?>
                        <img class="jshop_img_thumb" src="<?php print $this->image_product_path?>/<?php print $image->image_thumb?>" alt="<?php print htmlspecialchars($image->_title)?>" title="<?php print htmlspecialchars($image->_title)?>" onclick="showImage(<?php print $image->image_id?>)" />
                    <?php }?>
                <?php }?>
                </span>
                <?php print $this->_tmp_product_html_after_image_thumb;?>
                <?php if (count($this->videos)){?>
                    <?php foreach($this->videos as $k=>$video){?>
                        <?php if ($video->video_code) { ?>
                        <a href="#" id="video_<?php print $k?>" onclick="showVideoCode(this.id);return false;"><img class="jshop_video_thumb" src="<?php print $this->video_image_preview_path."/"; if ($video->video_preview) print $video->video_preview; else print 'video.gif'?>" alt="video" /></a>
                        <?php } else { ?>
                        <a href="<?php print $this->video_product_path?>/<?php print $video->video_name?>" id="video_<?php print $k?>" onclick="showVideo(this.id, '<?php print $this->config->video_product_width;?>', '<?php print $this->config->video_product_height;?>'); return false;"><img class="jshop_video_thumb" src="<?php print $this->video_image_preview_path."/"; if ($video->video_preview) print $video->video_preview; else print 'video.gif'?>" alt="video" /></a>
                        <?php } ?>
                    <?php } ?>
                <?php }?>
                <?php print $this->_tmp_product_html_after_video;?>
            </div>
        
            <?php print $this->_tmp_product_html_before_demofiles; ?>
            <div id="list_product_demofiles"><?php include(dirname(__FILE__)."/demofiles.php");?></div>
            <?php print $this->_tmp_product_html_after_demofiles; ?>
            <?php if ($on_mod_inproducts && !in_array($this->product->product_id, $prod_mod_exclude_arr) && !in_array($this->category_id, $categ_mod_exclude_arr)) { print '<div class="dopmod_inproducts">'.$dopmod_inproducts.'</div>'; } ?>
        </div>
        <!--END SPAN IMAGE (LEFT)-->
        
        <!--PRODUCT CAPTION (SECOND BLOCK)-->
        <div class="<?php print $prefix.$ptbw;?> product_caption">
            <?php include(dirname(__FILE__)."/ratingandhits.php");?>
            <h1><?php print $this->product->name?></h1>
            
            <?php if ($this->config->show_product_code && $this->product->getEan()){?> <span class="jshop_code_prod"><?php print _JSHOP_EAN?>: <span id="product_code"><?php print $this->product->getEan();?></span></span><?php }?><?php if ($this->config->show_product_code && $this->product->getEan() && $this->config->manufacturer_code_in_product_detail && $this->product->getManufacturerCode()) print ",";?>
            
            <?php if ($this->config->manufacturer_code_in_product_detail && $this->product->getManufacturerCode()){?> <span class="jshop_manufacturer_prod"><?php print _JSHOP_MANUFACTURER_CODE?>: <span id="manufacturer_code"><?php print $this->product->getManufacturerCode();?></span></span><?php }?>
            <?php if ($on_list_categories=="1") include(dirname(__FILE__)."/_list_categories.php"); ?>
            
            <hr />
            <p class="list-categories mt-10 mb-10"><small>Регион: <?php echo $country_name; ?>, г. <?php echo $city_name; ?></small></p>
            <hr />

            <?php if ($ssdp) {?>
                <div class="pfm_short_description">
                    <?php print $this->product->short_description;?>
                </div>
                <?php if ($this->product->short_description) {?>
                    <hr/> 
                <?php } ?>
            <?php }?>

            <?php if ($this->config->product_show_manufacturer && $this->product->manufacturer_info->name!=""){?>
                <div class="manufacturer_name mf_id_<?php print $this->product->manufacturer_info->id ?>">
                    <?php print _JSHOP_MANUFACTURER?>: <span><?php print $this->product->manufacturer_info->name?></span>
                </div>
            <?php }?>
            <?php if ($this->config->product_show_manufacturer_logo && $this->product->manufacturer_info->manufacturer_logo!=""){?>
                <div class="manufacturer_logo mf_id_<?php print $this->product->manufacturer_info->id ?>">
                    <a href="<?php print SEFLink('index.php?option=com_jshopping&controller=manufacturer&task=view&manufacturer_id='.$this->product->product_manufacturer_id, 2);?>">
                        <img src="<?php print $this->config->image_manufs_live_path."/".$this->product->manufacturer_info->manufacturer_logo?>" alt="<?php print htmlspecialchars($this->product->manufacturer_info->name);?>" title="<?php print htmlspecialchars($this->product->manufacturer_info->name);?>" border="0" />
                    </a>
                </div>
            <?php }?>
        
            <?php if ($this->product->vendor_info){?>
                <div class="vendorinfo vendor_id_<?php print $this->product->vendor_info->id ?>">
                    <?php print _JSHOP_VENDOR?>: <span><?php print $this->product->vendor_info->shop_name?> <?php print $this->product->vendor_info->l_name." ".$this->product->vendor_info->f_name;?></span> 
                    <?php if ($this->config->product_show_vendor_detail){?>
                        <a rel="tooltip" data-placement="top" data-original-title="<?php print _JSHOP_ABOUT_VENDOR?>" href="<?php print $this->product->vendor_info->urlinfo?>"><?php print $this->_tmp_vendor_icon;?></a>
                    <?php }?> 
                    <a rel="tooltip" data-placement="top" data-original-title="<?php print _JSHOP_VIEW_OTHER_VENDOR_PRODUCTS?>" href="<?php print $this->product->vendor_info->urllistproducts?>"><?php print $this->_tmp_vendor_icon_products;?></a>
                </div>
            <?php }?>
        
            <?php if (($this->config->product_show_manufacturer && $this->product->manufacturer_info->name!="") || $this->product->vendor_info) {print "<hr/>";} ?>
        
            <!--EXTRA FIELDS-->
            <?php if ($show_tabs_ef!="1" || $on_tabs!="1"){
                include(dirname(__FILE__)."/_extrafield_".$templ_type.".php");
            }?>
            <!--END EXTRA FIELDS-->
            
            <!--ATTRIBUTES-->
            <?php include(dirname(__FILE__)."/_attributes.php");?>
            <?php if (count($this->attributes) || count($this->product->freeattributes)) {print "<hr/>";}?>
            <!--END ATTRIBUTES-->

            <?php if ($this->product->_display_price){?>
                <div class="prod_price">
                    <span id="block_price"><?php print formatprice($this->product->getPriceCalculate())?><?php print $this->product->_tmp_var_price_ext;?> </span class="_prod_price_text"> / <?php print $this->product->product_basic_price_unit_name;?>
                </div>
            <?php }?>
            <?php print $this->product->_tmp_var_bottom_price;?>

            <?php if ($this->product->product_old_price > 0){?>
                <div class="old_price">
                    <?php // print _JSHOP_OLD_PRICE?> <span class="old_price" id="old_price"><?php print formatprice($this->product->product_old_price)?><?php print $this->product->_tmp_var_old_price_ext;?></span>
                </div>
                <div class="clearfix"></div>	
            <?php }?>
            <?php if ($this->config->show_tax_in_product && $this->product->product_tax > 0){?>
                <span class="taxinfo"><?php print productTaxInfo($this->product->product_tax);?>;</span>
            <?php }?>
            <?php if ($this->config->show_plus_shipping_in_product){?>
                <span class="plusshippinginfo"><?php print sprintf(_JSHOP_PLUS_SHIPPING, $this->shippinginfo);?></span>
            <?php }?>
            <div class="clearfix"></div>
    
            <div class="product-full-caption">
                <?php if ($this->product->product_is_add_price){?>
                <div class="price_prod_qty_list_head"><?php print _JSHOP_PRICE_FOR_QTY?></div>
                    <table class="table price_prod_qty_list">
                    <?php foreach($this->product->product_add_prices as $k=>$add_price){?>
                        <tr>
                            <td class="qty_from" <?php if ($add_price->product_quantity_finish==0){?>colspan="3"<?php } ?>>
                                <?php if ($add_price->product_quantity_finish==0) print _JSHOP_FROM?>
                                <?php print $add_price->product_quantity_start?> <?php print $this->product->product_add_price_unit?>
                            </td>
                            <?php if ($add_price->product_quantity_finish > 0){?>
                            <td class="qty_line"> - </td>
                            <?php } ?>
                            <?php if ($add_price->product_quantity_finish > 0){?>
                            <td class="qty_to">
                                <?php print $add_price->product_quantity_finish?> <?php print $this->product->product_add_price_unit?>
                            </td>
                            <?php } ?>
                            <td class="qty_price">            
                                <span id="pricelist_from_<?php print $add_price->product_quantity_start?>"><?php print formatprice($add_price->price)?><?php print $add_price->ext_price?></span> <span class="per_piece">/ <?php print $this->product->product_add_price_unit?></span>
                            </td>
                        </tr>
                    <?php }?>
                    </table>
                <?php }?>
                
                <?php if ($this->product->product_price_default > 0 && $this->config->product_list_show_price_default){?>
                    <div class="default_price"><?php print _JSHOP_DEFAULT_PRICE?>: <span id="pricedefault"><?php print formatprice($this->product->product_price_default)?></span></div>
                <?php }?>       
                
                <!--<?php if ($this->product->product_basic_price_show){?>
                    <div class="prod_base_price"><?php print _JSHOP_BASIC_PRICE?>: <span id="block_basic_price"><?php print formatprice($this->product->product_basic_price_calculate)?></span> / <?php print $this->product->product_basic_price_unit_name;?></div>
                <?php }?>-->
                <?php if ($this->config->product_show_weight && $this->product->product_weight > 0){?>
                    <div class="productweight"><?php print _JSHOP_WEIGHT?>: <span id="block_weight"><?php print formatweight($this->product->getWeight())?></span></div>
                <?php }?>
            
                <?php if ($this->config->product_show_qty_stock){?>
                    <div class="qty_in_stock"><?php print _JSHOP_QTY_IN_STOCK?>: <span id="product_qty"><?php print sprintQtyInStock($this->product->qty_in_stock);?></span></div>
                <?php }?>
                
                <?php if ($this->product->delivery_time != ''){?>
                    <div class="deliverytime" <?php if ($product->hide_delivery_time){?>style="display:none"<?php }?>><?php print _JSHOP_DELIVERY_TIME?>: <span><?php print $this->product->delivery_time?></span></div>
                <?php }?>
            </div>
    
            <?php print $this->_tmp_product_html_before_buttons;?>
            <div class="clearfix"></div>
            <?php if (!$this->hide_buy){?>
                <div class="prod_buttons" style="<?php print $this->displaybuttons?>">
                    <div class="buttons">
                    <div class="input-append">
                        <button type="submit" class="btn button buy" value="<?php print _JSHOP_ADD_TO_CART?>" onclick="jQuery('#to').val('cart');"><i class="fa fa-shopping-cart">&nbsp;</i><?php print _JSHOP_ADD_TO_CART?>&nbsp;</button>
                    </div>
                        <?php print $this->_tmp_qty_unit_pre;?>
                        <input type="text" name="quantity" id="quantity" onkeyup="reloadPrices();" class="btn quantity inputbox" value="<?php print $this->default_count_product?>" /><?php print $this->_tmp_qty_unit_after;?>
                        <?php print $this->_tmp_qty_unit;?>
                        <?php print $this->_tmp_wlist;?>
                        <?php print $this->_tmp_product_html_buttons;?>
                    </div>
                    <div id="jshop_image_loading" style="display:none"></div>
                </div>
            <?php }?>
            <?php print $this->_tmp_product_html_after_buttons;?>
    
            <?php if (!$this->config->hide_text_product_not_available){ ?>
                <div class = "not_available" id="not_available"><?php print $this->available?></div>
            <?php }?> 
        </div>
        <!--END PRODUCT CAPTION (SECOND BLOCK)-->
        
        <!--THIRD BLOCK-->
        <?php if ($ppbw!="0" || $doptext_inproducts!="") {?>
            <div class="<?php print $prefix.$ppbw;?> third_extblock">
                <?php print $thirdblock_text; ?>
                <?php if ($on_mod_inproducts) { print $doptext_inproducts;} ?>
            </div>
        <?php }?>
        <!--END THIRD BLOCK-->

    </div>

    <input type="hidden" name="to" id='to' value="cart" />
    <input type="hidden" name="product_id" id="product_id" value="<?php print $this->product->product_id?>" />
    <input type="hidden" name="category_id" id="category_id" value="<?php print $this->category_id?>" />
</form>