<div id="attribs-page" class="tab-pane">
<?php if ( (count($lists['all_independent_attributes']) + count($lists['all_attributes'])) > 0) : ?>
    <script type="text/javascript">
        var lang_error_attribute = "<?php print _JSHOP_VF_ERROR_ADD_ATTRIBUTE; ?>";
        var lang_attribute_exist = "<?php print _JSHOP_VF_ATTRIBUTE_EXIST; ?>";
        var folder_image_attrib = "<?php print $jshopConfig->image_attributes_live_path?>";
        var use_basic_price = "<?php print $jshopConfig->admin_show_product_basic_price?>";
        var use_bay_price = "<?php print $jshopConfig->admin_show_product_bay_price?>";
        var uriRoot = "<?php print JURI::root();?>";
        var attrib_images = new Object();
        <?php if (count($lists['attribs_values']) > 0) : ?>
        <?php foreach($lists['attribs_values'] as $k => $v) : ?>
            attrib_images[<?php print $v->value_id?>] = "<?php print $v->image?>";
        <?php endforeach; ?>
        <?php endif; ?>
    </script>
<?php endif; ?>
         
<?php if (count($lists['all_attributes'])) : ?>
    <script type="text/javascript">
       var attrib_ids = new Array();
       var attrib_exist = new Object();
       <?php $i=0; ?>
       <?php if (count($lists['all_attributes']) > 0) : ?>
       <?php foreach($lists['all_attributes'] as $key => $value) : ?>
           attrib_ids[<?php print $i++;?>] = "<?php echo $value->attr_id ?>";            
       <?php endforeach; ?>
       <?php endif; ?>
       
       <?php
       $attr_tmp_row_num = 0;
       if (count($lists['attribs'])){
           foreach($lists['attribs'] as $k=>$v){
               $attr_tmp_row_num++;
               print "attrib_exist[".$attr_tmp_row_num."]={};\n";
               if (count($lists['all_attributes']) > 0){
                   foreach($lists['all_attributes'] as $key=>$value){
                       $tmp_field = "attr_".$value->attr_id;
                       $tmp_val = $v->$tmp_field;
                       print "attrib_exist[".$attr_tmp_row_num."][".$value->attr_id."]='".$tmp_val."';\n";
                   }
               }
           }
       }
       print "var attr_tmp_row_num = $attr_tmp_row_num;\n";
       ?>
           var product_attr_link = '<?php print SEFLink("index.php?option=com_jshopping&controller=addon_vendor&task=edit_product&tmpl=component&product_attr_id=", 1, 1);?>';
       </script>
        
       <table class = "jshop" id="list_attr_value">
       <thead>
       <tr>
       <?php if (count($lists['all_attributes']) > 0) : ?>
       <?php foreach($lists['all_attributes'] as $key => $value) : ?>
            <th width="120"><?php echo $value->name?></th>
       <?php endforeach; ?>
       <?php endif; ?>
            <th width="120"><?php print _JSHOP_PRICE; ?></th>            
            <th width="120"><?php print _JSHOP_VF_QUANTITY_PRODUCT ?></th>
            <th width="120"><?php print _JSHOP_EAN_PRODUCT ?></th>
            <th width="120"><?php print _JSHOP_VF_PRODUCT_WEIGHT?> (<?php print sprintUnitWeight()?>)</th>
            <?php if ($jshopConfig->admin_show_product_basic_price) : ?>
                <th width="120"><?php print _JSHOP_VF_WEIGHT_VOLUME_UNITS ?></th>
            <?php endif; ?>
            <th width="120"><?php print _JSHOP_OLD_PRICE; ?></th>
            <?php if ($jshopConfig->admin_show_product_bay_price) : ?>
            <th width="120"><?php print _JSHOP_VF_PRODUCT_BUY_PRICE; ?></th>
            <?php endif; ?>
            <th></th>
            <th width="60"><?php print _JSHOP_DELETE?></th>
       </tr>
       </thead>
    
       <?php       
       if (count($lists['attribs'])){
           $attr_tmp_row_num = 0;
           foreach($lists['attribs'] as $k=>$v){
               $attr_tmp_row_num++;
               print "<tr id='attr_row_".$attr_tmp_row_num."'>";
               if (count($lists['all_attributes']) > 0){
               foreach($lists['all_attributes'] as $key=>$value){
                    $tmp_field = "attr_".$value->attr_id;
                    $tmp_val = $v->$tmp_field;
                    $tmp_val_val = $lists['attribs_values'][$tmp_val]->name;
                    $image_ = "";
                    if ($lists['attribs_values'][$tmp_val]->image!=''){
                        $image_ = "<img src='".$jshopConfig->image_attributes_live_path."/".$lists['attribs_values'][$tmp_val]->image."' align='left' hspace='5' width='16' height='16' style='margin-right:5px;' class='img_attrib'>";
                    }
                    print "<td><input type='hidden' name='attrib_id[".$value->attr_id."][]' value='".$tmp_val."'>".$image_.$tmp_val_val."</td>";
               }}
               print "<td><input type='text' name='attrib_price[]' value='".$v->price."'></td>";               
               print "<td><input type='text' name='attr_count[]' value='".$v->count."'></td>";
               print "<td><input type='text' name='attr_ean[]' value='".$v->ean."'></td>";
               print "<td><input type='text' name='attr_weight[]' value='".$v->weight."'></td>";
               if ($jshopConfig->admin_show_product_basic_price){
                   print "<td><input type='text' name='attr_weight_volume_units[]' value='".$v->weight_volume_units."'></td>";
               }
               print "<td><input type='text' name='attrib_old_price[]' value='".$v->old_price."'></td>";
               if ($jshopConfig->admin_show_product_bay_price){
                   print "<td><input type='text' name='attrib_buy_price[]' value='".$v->buy_price."'></td>";
               }
               print '<td>';
               if ($jshopConfig->use_extend_attribute_data){               
                 print "<a class='btn btn-mini' target='_blank' href='".SEFLink("index.php?option=com_jshopping&controller=addon_vendor&task=edit_product?product_attr_id=", 1, 1).$v->product_attr_id."'  onclick='addonVendorEditAttributeExtendParams(".$v->product_attr_id.");return false;'>"._JSHOP_VENDOR_ATTRIBUTE_EXTEND_PARAMS."</a>";
               }
               
               print '</td>';
               print "<td><a href='#' onclick=\"deleteTmpRowAttrib('".$attr_tmp_row_num."');return false;\">
               <img src='". JURI::root() ."administrator/components/com_jshopping/images/publish_r.png' border='0'></a></td>";
               print "</tr>";
           }
       }
       ?>
       </table>    
       <br/>
       <div class="col width-55">
       <fieldset class="adminform" style="margin-left:0px;">
       <legend><?php echo _JSHOP_VF_ADD_ATTRIBUT?></legend>
            <table class="jshop">
            <?php if (count($lists['all_attributes']) > 0) : ?>
            <?php foreach($lists['all_attributes'] as $key => $value) : ?>
                <tr>
                    <td class="key"><?php echo $value->name?></td>
                    <td><?php echo $value->values_select;?></td>
                </tr>    
            <?php endforeach; ?>
            <?php endif; ?>
            <tr>
                <td class="key"><?php print _JSHOP_PRICE;?>*</td>
                <td><input type="text" id="attr_price" value="<?php echo $row->product_price?>" style="width:100px;"></td>
            </tr>
            <tr>
                <td class="key"><?php print _JSHOP_VF_QUANTITY_PRODUCT?>*</td>
                <td><input type="text" id="attr_count"  style="width:100px;" value="1"></td> 
            </tr>
            <tr>
                <td class="key"><?php print _JSHOP_EAN_PRODUCT?></td>
                <td><input type="text" id="attr_ean" style="width:100px;" value="<?php echo $row->product_ean?>"></td>
            </tr>
            <tr>
                <td class="key"><?php print _JSHOP_VF_PRODUCT_WEIGHT?></td>
                <td><input type="text" id="attr_weight" style="width:100px;" value="<?php echo $row->product_weight?>"> <?php print sprintUnitWeight();?></td>
            </tr>
            <?php if ($jshopConfig->admin_show_product_basic_price) : ?>
            <tr>
                <td class="key"><?php print _JSHOP_VF_WEIGHT_VOLUME_UNITS?></td>
                <td><input type="text" id="attr_weight_volume_units" style="width:100px;" value="<?php echo $row->weight_volume_units?>"></td>
            </tr>
            <?php endif; ?>
            <tr>
                <td class="key"><?php print _JSHOP_OLD_PRICE;?></td>
                <td><input type="text" id="attr_old_price" value="<?php echo $row->product_old_price?>" style="width:100px;"></td>
            </tr>
            <?php if ($jshopConfig->admin_show_product_bay_price) : ?>
            <tr>
                <td class="key"><?php print _JSHOP_VF_PRODUCT_BUY_PRICE;?></td>
                <td><input type="text" id="attr_buy_price" value="<?php echo $row->product_buy_price?>" style="width:100px;"> </td>
            </tr>
            <?php endif; ?>
            <tr>
                <td></td>
                <td>
                <div style="width:100px;text-align:right;">
                <input type = "button" onclick = "addVendorAttributValue()" value = "<?php echo _JSHOP_VF_ADD?>" />
                </div>
                </td>
            </tr>            
            </table>
        </fieldset>    
       </div>
       <div class="clr"></div>
       <br/>
   <?php endif; ?>

   <?php if (count($lists['all_independent_attributes'])) : ?>
       <?php foreach($lists['all_independent_attributes'] as $ind_attr) : ?> 
           <div style="padding-top:20px;">
           <table class = "jshop" id="list_attr_value_ind_<?php print $ind_attr->attr_id?>">
           <thead>
           <tr>
               <th width="150"><?php print $ind_attr->name?></th>
               <th width="120"><?php print _JSHOP_VF_PRICE_MODIFICATION?></th>
               <th width="120"><?php print _JSHOP_PRICE; ?></th>
               <th><?php print _JSHOP_DELETE?></th>
           </tr>
           </thead>        
           <?php if (isset($lists['ind_attribs_gr'][$ind_attr->attr_id]) && is_array($lists['ind_attribs_gr'][$ind_attr->attr_id])) : ?>
               <?php foreach($lists['ind_attribs_gr'][$ind_attr->attr_id] as $ind_attr_val) : ?>
               <tr id='attr_ind_row_<?php print $ind_attr_val->attr_id?>_<?php print $ind_attr_val->attr_value_id?>'>
                   <td>
                   <?php if ($lists['attribs_values'][$ind_attr_val->attr_value_id]->image != '') : ?>
                       <img src='<?php print $jshopConfig->image_attributes_live_path."/".$lists['attribs_values'][$ind_attr_val->attr_value_id]->image?>' align='left' hspace='5' width='16' height='16' style='margin-right:5px;' class='img_attrib'>
                   <?php endif; ?>
                   <input type='hidden' id='attr_ind_<?php print $ind_attr_val->attr_id?>_<?php print $ind_attr_val->attr_value_id?>' name='attrib_ind_id[]' value='<?php print $ind_attr_val->attr_id?>'>
                   <input type='hidden' name="attrib_ind_value_id[]" value='<?php print $ind_attr_val->attr_value_id?>'>
                   <?php print $lists['attribs_values'][$ind_attr_val->attr_value_id]->name;?>
                   </td>
                   <td><input type='text' style = "width: 110px; margin-left: 5px; margin-right: 5px;" name='attrib_ind_price_mod[]' value='<?php print $ind_attr_val->price_mod?>'></td>
                   <td><input type='text' style = "width: 110px; margin-left: 5px; margin-right: 5px;" name='attrib_ind_price[]' value='<?php print $ind_attr_val->addprice?>'></td>
                   <td><a href='#' onclick="jQuery('#attr_ind_row_<?php print $ind_attr_val->attr_id?>_<?php print $ind_attr_val->attr_value_id?>').remove();return false;"><img src='<?php print JURI::root(); ?>administrator/components/com_jshopping/images/publish_r.png' border='0'></a></td>
               </tr>
            <?php endforeach; ?>
        <?php endif; ?>
        </table>
        </div>
        
        <div style="padding-top:5px;">
        <table cellpadding="4">
        <tr>
            <td width="150"><?php print $ind_attr->values_select;?></td>
            <td width="120"><?php print $ind_attr->price_modification_select;?></td>
            <td width="120"><input type="text" id="attr_ind_price_tmp_<?php print $ind_attr->attr_id?>" style = "width: 110px; margin-left: 5px; margin-right: 5px;" value="0"></td>
            <td><?php print $ind_attr->submit_button;?></td>
        </tr>
        </table>
        </div>
    <?php endforeach; ?>
    
   <br/><br/>
   <?php endif; ?>
</div>
