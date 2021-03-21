    <?php
        $row = $this->product;
        $lists = $this->lists;
        $tax_value = $this->tax_value;
        $jshopConfig = JSFactory::getConfig();
        JHTML::_('behavior.tooltip');
        JHTML::_('behavior.modal', 'a.modal');
        
        $dispatcher = JDispatcher::getInstance();
    ?>
    <div class="jshop_vendor">
    
    <script type="text/javascript">
    var lang_delete = "<?php print _JSHOP_DELETE; ?>";
    var baseurl = '<?php print JURI::base()?>';
    </script>
    
    <form action = "<?php print SEFLink("index.php?option=com_jshopping&controller=addon_vendor&task=save_product", 1)?>" method = "post" enctype = "multipart/form-data" name = "adminForm" id="addon_vendor_edit_form">
    <div class="toolbarbox">
        <div class="title">
            <?php 
            if ($row->product_id){
                print _JSHOP_VF_EDIT_PRODUCT;
            }else{
                print _JSHOP_ADD_PRODUCT;
            }
            ?>
        </div>
        <div class="toolbar-list">        
            <a class="toolbar" onclick="av_submitbutton('save');return false;" href="#">
                <span class="icon-32-save"> </span>
                <?php print _JSHOP_SAVE;?>
            </a>
            <?php if ($this->product->parent_id == 0) :?>
            <a class="toolbar" onclick="av_cancel();return false;" href="#">
                <span class="icon-32-cancel"> </span>
                <?php print _JSHOP_CANCEL;?>
            </a>
            <?php endif;?>
        </div>
    </div>
    
    <ul class="nav nav-tabs">
        <?php if ($this->product->parent_id==0){?>
        <?php $i=0; foreach($this->languages as $lang){ $i++;?>
            <li <?php if ($i==1){?>class="active"<?php }?>>
                <a href="#<?php print $lang->lang.'-page'?>" data-toggle="tab">
                    <?php echo _JSHOP_DESCRIPTION?><?php if ($this->multilang){?> (<?php print $lang->lang?>)<img class="tab_image" src="administrator/components/com_jshopping/images/flags/<?php print $lang->lang?>.gif" /><?php }?>
                </a>
            </li>        
        <?php } ?>
        <li><a href="#main-page" data-toggle="tab"><?php echo _JSHOP_VF_INFO_PRODUCT;?></a></li>
        <?php } ?>
        
        <?php if ($this->vndconfig->show_attributes && $this->product->parent_id==0){?>
            <li><a href="#attribs-page" data-toggle="tab"><?php echo _JSHOP_VF_ATTRIBUTES;?></a></li>
        <?php }?>
        <?php if ($this->vndconfig->show_freeattributes && $this->product->parent_id==0){?>
            <li><a href="#product_freeattribute" data-toggle="tab"><?php echo _JSHOP_VF_FREE_ATTRIBUTES;?></a></li>
        <?php }?>
        <?php if ($this->vndconfig->show_product_image){?>
            <li <?php if ($this->product->parent_id!=0){?>class="active"<?php }?>><a href="#product_images" data-toggle="tab"><?php echo _JSHOP_VF_PRODUCT_IMAGES;?></a></li>
        <?php }?>
        <?php if ($this->vndconfig->show_product_video && $this->product->parent_id==0){?>
            <li><a href="#product_videos" data-toggle="tab"><?php echo _JSHOP_VF_PRODUCT_VIDEOS;?></a></li>
        <?php }?>
        <?php if ($this->vndconfig->show_product_related && $this->product->parent_id==0){?>
            <li><a href="#product_related" data-toggle="tab"><?php echo _JSHOP_VF_PRODUCT_RELATED;?></a></li>
        <?php }?>
        <?php if ($this->vndconfig->show_product_files){?>
            <li><a href="#product_files" data-toggle="tab"><?php echo _JSHOP_VF_FILES;?></a></li>
        <?php }?>
        <?php if ($this->vndconfig->show_product_extra_field && $this->product->parent_id==0){?>
            <li><a href="#product_extra_fields" data-toggle="tab"><?php echo _JSHOP_VF_EXTRA_FIELDS;?></a></li>
        <?php } ?>
        <?php 
            if ($this->product->parent_id == 0){
                $dispatcher->trigger('onDisplayProductEditTabsEndTab', array(&$row, &$lists, &$tax_value));
            } else {
                $dispatcher->trigger('onDisplayExtAttributProductEditTabsEndTab', array(&$row, &$lists, &$tax_value));	
            }
        ?>
        
        <li><a href="#acab" data-toggle="tab">ACAB!!!</a></li>
        
    </ul>
    
    <div id="editdata-document" class="tab-content">
    <?php
       if ($this->product->parent_id == 0){
           include(dirname(__FILE__)."/description.php");
           include(dirname(__FILE__)."/info.php");
       }
       if ($this->vndconfig->show_attributes && $this->product->parent_id==0) {
           include(dirname(__FILE__)."/attribute.php");
       }
       if ($this->vndconfig->show_freeattributes && $this->product->parent_id==0){
           include(dirname(__FILE__)."/freeattribute.php");
       }
       if ($this->vndconfig->show_product_image){
           include(dirname(__FILE__)."/images.php");
       }
       if ($this->vndconfig->show_product_video && $this->product->parent_id==0) {
           include(dirname(__FILE__)."/videos.php");
       }
       if ($this->vndconfig->show_product_related && $this->product->parent_id==0) {
           include(dirname(__FILE__)."/related.php");
       }
       if ($this->vndconfig->show_product_files) {
           include(dirname(__FILE__)."/files.php");
       }
       if ($this->vndconfig->show_product_extra_field && $this->product->parent_id==0){
           include(dirname(__FILE__)."/extrafields.php");
       }
       if ($this->product->parent_id == 0){
           $dispatcher->trigger('onDisplayProductEditTabsEnd', array(&$pane, &$row, &$lists, &$tax_value, &$currency));
       } else {
           $dispatcher->trigger('onDisplayExtAttributProductEditTabsEnd', array(&$pane, &$row, &$lists, &$tax_value, &$currency));
       }
    ?>
        <div id="acab" class="tab-pane">
            ACAB 123!
        </div>
        
    </div>
       <input type = "hidden" name = "task" value = "save_product" />
       <input type = "hidden" name = "current_cat" value = "<?php echo JRequest::getVar('current_cat', 0)?>" />
       <input type = "hidden" name = "product_id" value = "<?php echo $row->product_id?>" />             
       <input type = "hidden" name = "parent_id" value = "<?php echo $row->parent_id?>" />
        
        <!-- NEXT BUTTONS -->
        <div class="next_buttons">
            <strong><a onclick="next_step()" href="" data-toggle="tab">Далее >></a></strong>
        </div>
        <!-- NEXT BUTTON END -->
        
    </form>
    
    
    </div>


        <div class="toolbar-list">        
            <a class="toolbar" onclick="av_submitbutton('save');return false;" href="#">
                <span class="icon-32-save"> </span>
                <?php print _JSHOP_SAVE;?>
            </a>
            <?php if ($this->product->parent_id == 0) :?>
            <a class="toolbar" onclick="av_cancel();return false;" href="#">
                <span class="icon-32-cancel"> </span>
                <?php print _JSHOP_CANCEL;?>
            </a>
            <?php endif;?>
        </div>

    <script type = "text/javascript">
        
        function next_step(){
           jQuery('.nav-tabs > li.active').next().find('a').trigger('click');
            //let test = jQuery('.nav-tabs > li.active').next().find('a').attr('href');
            //jQuery('.nav-tabs > li.active > a').next().trigger('click');
            //jQuery('.nav-tabs > li > a[href="#attribs-page"]').trigger('click');
            //let attribs = jQuery('.nav-tabs > li > a[href="#attribs-page"]').text();
            //console.log(attribs);
        }
        
    var product_price_precision = <?php print intval($jshopConfig->product_price_precision)?>;
    function av_submitbutton(pressbutton){
        if (pressbutton == 'save' || pressbutton == 'apply'){
            <?php if ($this->vndconfig->show_product_image){?>
            if (isEmpty($F_('product_width_image')) && isEmpty($F_('product_height_image'))){
               alert ('<?php echo _JSHOP_VF_WRITE_SIZE_BAD?>');
            } else 
            <?php }?>
            <?php if ($this->product->parent_id==0){?>    
                if (jQuery('#addon_vendor_edit_form #category_id').val() == null){
                    alert ('<?php echo _JSHOP_VF_WRITE_SELECT_CATEGORY?>');
                } else { 
                    jQuery('#addon_vendor_edit_form').submit();
                }
            <?php } else {?>
                jQuery('#addon_vendor_edit_form').submit();
            <?php }?>
        } else {
            jQuery('#addon_vendor_edit_form').submit();
        }
    }

    function av_cancel(){
        location.href='<?php print SEFLink("index.php?option=com_jshopping&controller=addon_vendor&task=products", 1, 1);?>';
    }

    function showHideAddPrice(){
         $_('tr_add_price').style.display = ($_('product_is_add_price').checked)  ? ('') : ('none');
    }
    <?php if ($this->product->parent_id==0){?>
        showHideAddPrice();
    <?php }?>
    </script>