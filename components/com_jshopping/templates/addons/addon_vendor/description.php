<?php
$i=0;
if (count($this->languages) > 0){
    foreach($this->languages as $lang){
        $i++;
        $name = "name_".$lang->language;
        $alias = "alias_".$lang->language;
        $description = "description_".$lang->language;
        $short_description = "short_description_".$lang->language;
        $meta_title = "meta_title_".$lang->language;
        $meta_keyword = "meta_keyword_".$lang->language;
        $meta_description = "meta_description_".$lang->language;
?>
    <div id="<?php print $lang->lang.'-page'?>" class="tab-pane<?php if ($i==1){?> active<?php }?>">
        <div class="col100">
        <table class="jshop" >
        <tr>
            <td class="key" style="width:180px;"><?php echo _JSHOP_TITLE;?>: *</td>
            <td><input type = "text" class = "inputbox required product_name" size = "55" name="<?php echo $name?>" value = "<?php echo $row->$name?>" /></td>
        </tr>


        <tr>
            <td class="key"><?php echo _JSHOP_VF_SHORT_DESCRIPTION;?></td>
            <td><textarea name = "<?php print $short_description;?>" cols = "55" rows="5"><?php echo $row->$short_description ?></textarea></td>
        </tr>
        <tr>
            <td class="key"><?php echo _JSHOP_DESCRIPTION;?></td>
            <td>
               <?php
                   $editor = JFactory::getEditor();
                   print $editor->display('description'.$lang->id,  $row->$description , '100%', '350', '75', '20', false ) ;
               ?>
            </td>
        </tr>

        <?php $pkey='plugin_template_description_'.$lang->language; if ($this->$pkey){ print $this->$pkey;}?>
        </table>
        </div>
        <div class="clr"></div>
    </div>        
   <?php }} ?>