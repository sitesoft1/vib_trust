<div id="main-page" class="tab-pane"> 
    <div class="col100">
     <table class="jshop" width="90%">
     
     <tr hidden>
       <td class="key" style="width:180px;">
         <?php echo _JSHOP_VF_PUBLISH;?>
       </td>
       <td>
         <!--<input type = "checkbox" name = "vendor_publish" id = "vendor_publish" value = "1" <?php if ($row->vendor_publish) echo 'checked = "checked"'?> />-->
         <input type = "checkbox" name = "vendor_publish" id = "vendor_publish" value = "1" <?php if (true) echo 'checked = "checked"'?> />
       </td>
     </tr>
    
     <tr>
       <td class="key">
         <?php echo _JSHOP_VF_PRODUCT_PRICE;?>
       </td>
       <td>
         <input type = "text" name = "product_price" id = "product_price" value = "<?php echo $row->product_price?>" 
         <?php if (!$this->withouttax) : ?>        
            onkeyup = "updatePrice2(<?php print $jshopConfig->display_price_admin;?>)"  
         <?php endif; ?>
         />       
         <?php echo $this->lists['currency']; ?>
       </td>
     </tr>
     
     <?php if ($jshopConfig->admin_show_product_bay_price) { ?>
     <tr>
       <td class="key">
         <?php echo _JSHOP_VF_PRODUCT_BUY_PRICE;?>
       </td>
       <td>
         <input type = "text" name = "product_buy_price" id = "product_buy_price" value = "<?php echo $row->product_buy_price?>" />
       </td>
     </tr>
     <?php } ?>
     
	 <?php if (!$this->withouttax) : ?>
     <tr>     
       <td class="key">
         <?php echo _JSHOP_VF_NAME_TAX;?>
       </td>
       <td>
         <?php echo $lists['tax'];?>
       </td>
     </tr>
     <?php endif; ?>
    
     <tr>
       <td class="key">
         <?php echo _JSHOP_EAN_PRODUCT;?>
       </td>
       <td>
         <input type = "text" name = "product_ean" id = "product_ean" value = "<?php echo $row->product_ean?>" onkeyup="updateEanForAttrib()"; />
       </td>
     </tr>

     <!--<tr>
       <td class="key"><?php echo _JSHOP_VF_URL; ?></td>
       <td>
         <input type = "text" name = "product_url" id = "product_url" value = "<?php echo $row->product_url?>" size="80" />
       </td>
     </tr>-->

     <tr>
         <td class="key">
             Выберите регион: *
         </td>
         <td>
             <?php echo $lists['regions'];?>
         </td>
     </tr>

     <tr>
         <td class="key">
                 Выберите город: *
         </td>
         <td>
             <?php echo $lists['cities'];?>
         </td>
     </tr>
     
     <tr>
       <td class="key">
         <?php echo _JSHOP_VF_CATEGORIES;?>
       </td>
       <td>
         <?php echo $lists['categories'];?>
       </td>
     </tr>
     
     <!-- Характеристики -->
         <script>
             jQuery('#category_id').on("change", function(){
                 let category_id = jQuery(this).val();
                 jQuery.ajax({
                     type: "POST",
                     url: "index.php?option=com_jshopping&controller=addon_vendor&task=get_product_extra_fields",
                     data: "category_id="+category_id,
                     success: function(html){
                         jQuery('#extra_fields_space').html(html);
                     }
                 });
             });
         </script>
     <!-- Характеристики КОНЕЦ -->
     
     <?php if ($jshopConfig->admin_show_delivery_time) { ?>
     <tr>
       <td class="key">
         <?php echo _JSHOP_DELIVERY_TIME;?>
       </td>
       <td>
         <?php echo $lists['deliverytimes'];?>
       </td>
     </tr>
     <?php }?>
     
     <?php if ($jshopConfig->admin_show_product_labels) { ?>
     <tr>
       <td class="key">
         <?php echo _JSHOP_LABEL;?>
       </td>
       <td>
         <?php echo $lists['labels'];?>
       </td>
     </tr>
     <?php }?>
     
     <?php if ($jshopConfig->admin_show_product_basic_price) { ?>

     <tr>
       <td class="key">
       
       </td>
       <td>
	   
 <select name="44" class="inputbox" style="display: none;" size="1">
	<option value="10000" selected="selected">Неограниченно</option>
	<option value="2">Два</option>
</select>
       </td>
     </tr>
       
       <!--
     <tr>
       <td class="key">
         <?php echo _JSHOP_VF_UNIT_MEASURE;?>
       </td>
       <td>
         <?php echo $lists['basic_price_units'];?>
       </td>
     </tr>
         -->
         
     <?php }?>
     <?php $pkey='plugin_template_info'; if ($this->$pkey){ print $this->$pkey;}?>
   </table>
   </div>
   <div class="clr"></div>
</div>