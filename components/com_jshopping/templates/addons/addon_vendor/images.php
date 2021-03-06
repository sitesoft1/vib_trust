<div id="product_images" class="tab-pane <?php if ($this->product->parent_id!=0){?>active<?php }?>">
    <table>
       <tr>
           <?php
              $i = 0;
              $count_in_row = 5;
              if (count($lists['images']))
           ?>
           <?php if (count($lists['images']) > 0) : ?>   
           <?php foreach ($lists['images'] as $image) : ?>
               <td style = "vertical-align: top; text-align: center;">
               <div id="foto_product_<?php print $image->image_id?>">
                   <input type="text" name="old_image_descr[<?php print $image->image_id?>]" value="<?php print htmlspecialchars($image->name);?>" size="22" class="middle" />
                   <div style="height:3px;"></div>  
                   <div style="padding-bottom:5px;padding-right:5px;">
                       <a class="modal" href="<?php echo getPatchProductImage($image->image_name, 'full', 1)?>" rel="{handler: 'image'}">
                       <img style = "cursor:pointer" src = "<?php echo getPatchProductImage($image->image_name, 'thumb', 1)?>" alt = "" />
                       </a>
                   </div>
                   <?php print _JSHOP_VF_ORDERING?>: <input type="text" style = "width: 50px;" name="old_image_ordering[<?php print $image->image_id?>]" value="<?php print $image->ordering;?>" size="3" class="small" />
                   <div style="height:3px;"></div>
                       <input type = "radio" name = "set_main_image" id = "set_main_image_<?php echo $image->image_id?>" value = "<?php echo $image->image_id?>" <?php if ($row->image == $image->image_name) echo 'checked = "checked"';?>/> <label style="min-width: 50px;float:none;" for = "set_main_image_<?php echo $image->image_id?>"><?php echo _JSHOP_VF_SET_MAIN_IMAGE;?></label> 
                       <div class="link_delete_foto"><a href="#" onclick="if (confirm('<?php print _JSHOP_VF_DELETE_IMAGE;?>')) addon_vendor_deleteFotoProduct('<?php echo $image->image_id?>');return false;"><img src="<?php print JURI::root(); ?>administrator/components/com_jshopping/images/publish_r.png"> <?php print _JSHOP_VF_DELETE_IMAGE;?></a></div>             
                   </div>
               </td>
          </div>
          </td>
           <?php if (++$i % $count_in_row == 0) echo '</tr><tr>'; ?>
           <?php endforeach; ?>
           <?php endif; ?>
       </tr>
    </table>

	<div style="height:10px;"></div>
    <div class="col vf_width-65" style="float:left">
       <fieldset class="adminform">
       <legend><?php echo _JSHOP_VF_UPLOAD_IMAGE?></legend>
       <div style="height:4px;"></div>
       <?php for($i = 0; $i < $jshopConfig->product_image_upload_count; $i++) : ?>
           <div style="padding-bottom:6px;">
               <input type="text" name="product_image_descr_<?php print $i;?>" size="35" title="<?php print _JSHOP_TITLE?>" />
               <input type="file" name="product_image_<?php print $i;?>" />
           </div>
       <?php endfor; ?>        
       </fieldset>
    </div>
    
    <div class="col vf_width-35" style="float:left; display:none !important;">
       
    <fieldset class="adminform">
    <legend><?php echo _JSHOP_VF_IMAGE_THUMB_SIZE?></legend>
        <table class="tmiddle"><tr>
        <td><input type = "radio" name = "size_im_product" id = "size_1" checked = "checked" onclick = "setDefaultSize(<?php echo $jshopConfig->image_product_width; ?>,<?php echo $jshopConfig->image_product_height; ?>, 'product')" value = "1" /></td>
        <td><label for = "size_1"><?php echo _JSHOP_VF_IMAGE_SIZE_1;?></label></td>
        </tr></table>
        <table class="tmiddle"><tr>
        <td><input type = "radio" name = "size_im_product" value = "3" id = "size_3" onclick = "setOriginalSize('product')" value = "3"/></td>
        <td><label for = "size_3"><?php echo _JSHOP_VF_IMAGE_SIZE_3;?></label></td>
        </tr></table>
        <table class="tmiddle"><tr>
        <td><input type = "radio" name = "size_im_product" id = "size_2" onclick = "setManualSize('product')" value = "2" /></td>
        <td><label for = "size_2"><?php echo _JSHOP_VF_IMAGE_SIZE_2;?></label></td>
        <td>&nbsp;<?php echo JHTML::tooltip(_JSHOP_VF_IMAGE_SIZE_INFO)?></td>
        </tr></table>            
        <table>
        	<tr><td><?php echo _JSHOP_VF_IMAGE_WIDTH?>:</td><td><input type = "text" style = "width: 100px; margin-left: 5px;" id = "product_width_image" name = "product_width_image" value = "<?php echo $jshopConfig->image_product_width?>" disabled = "disabled" /></td></tr>
        	<tr><td><?php echo _JSHOP_VF_IMAGE_HEIGHT?>:</td><td><input type = "text" style = "width: 100px; margin-left: 5px;" id = "product_height_image" name = "product_height_image" value = "<?php echo $jshopConfig->image_product_height?>" disabled = "disabled" /></td></tr>
        </table>
    </fieldset>
        
    <fieldset class="adminform">
    <legend><?php echo _JSHOP_VF_IMAGE_SIZE ?></legend>            
        <table class="tmiddle"><tr>
        <td><input type = "radio" name = "size_full_product" id = "size_full_1" onclick = "setDefaultSize(<?php echo $jshopConfig->image_product_full_width; ?>,<?php echo $jshopConfig->image_product_full_height; ?>, 'product_full')" value = "1" checked = "checked" /></td>
        <td><label for = "size_full_1"><?php echo _JSHOP_VF_IMAGE_SIZE_1;?></label></td>
        </tr></table>
        <table class="tmiddle"><tr>
        <td><input type = "radio" name = "size_full_product" id = "size_full_3" onclick = "setFullOriginalSize('product_full')" value = "3" /></td>
        <td><label for = "size_full_3"><?php echo _JSHOP_VF_IMAGE_SIZE_3;?></label></td>
        </tr></table>
        <table class="tmiddle"><tr>
        <td><input type = "radio" name = "size_full_product" id = "size_full_2" onclick = "setFullManualSize('product_full')" value = "2"/></td>
        <td><label for = "size_full_2"><?php echo _JSHOP_VF_IMAGE_SIZE_2;?></label></td>
        <td>&nbsp;<?php echo JHTML::tooltip(_JSHOP_VF_IMAGE_SIZE_INFO)?></td>
        </tr></table>            
        <table>
        	<tr><td><?php echo _JSHOP_VF_IMAGE_WIDTH?>:</td><td><input type = "text" style = "width: 100px; margin-left: 5px;" id = "product_full_width_image" name = "product_full_width_image" value = "<?php echo $jshopConfig->image_product_full_width; ?>" disabled = "disabled" /></td></tr>
        	<tr><td><?php echo _JSHOP_VF_IMAGE_HEIGHT?></td><td><input type = "text" style = "width: 100px; margin-left: 5px;" id = "product_full_height_image" name = "product_full_height_image" value = "<?php echo $jshopConfig->image_product_full_height; ?>" disabled = "disabled" /></td></tr>
        </table>
    </fieldset>

    </div>
    <div class="clr"></div>
    <br/>
    <div class="helpbox">
        <div class="head"><?php echo _JSHOP_VF_ABOUT_UPLOAD_FILES;?></div>
        <div class="text">
            <?php print _JSHOP_VF_IMAGE_UPLOAD_EXT_INFO?><br/>
            <?php print sprintf(_JSHOP_VF_SIZE_FILES_INFO, ini_get("upload_max_filesize"), ini_get("post_max_size"));?>
        </div>
    </div>
</div>