<?php defined('_JEXEC') or die('Restricted access');
include(dirname(__FILE__)."/../plugin_params.php");
$to_view=$_POST['toView'];
if (isset($to_view)){
    $session->set('toView',$to_view);
}
$stv=$session->get('toView','');

if ($ualayout && $ualayout=="mobile" && $mobile_list_view) {
    $this->count_product_to_row = $mobile_list_view_count;
    $xs_suffix = 12/$this->count_product_to_row; ?>
    <div class="jshop_grid list_product <?php print $add_mobile_class;?>">
        <?php foreach ($this->rows as $k=>$product) : ?>
            <?php if ($k % $this->count_product_to_row == 0) : ?>
                <div class = "row<?php print $row_postfix?>">
            <?php endif; ?>
            
            <div class = "<?php echo $prefix.(($this->count_product_to_row > 0) ? 12 / $this->count_product_to_row : '1')." ".$prefix_xs.$xs_suffix;?> block_product">
                <?php include(dirname(__FILE__)."/".$product->template_block_product);?>
            </div>
            
            <?php if ($k % $this->count_product_to_row == $this->count_product_to_row - 1) : ?>
                <div class = "clearfix"></div>
                </div>
            <?php endif; ?>
        <?php endforeach; ?>
        <?php if ($k % $this->count_product_to_row != $this->count_product_to_row - 1) : ?>
            <div class = "clearfix"></div>
            </div>
        <?php endif; ?>
    </div>
<?php } else {

if ($stv=="toGrid" || !$stv){ ?>
<div class="jshop_grid list_product <?php print $stv; ?>">
<?php foreach ($this->rows as $k=>$product) : ?>
    <?php if ($k % $this->count_product_to_row == 0) : ?>
        <div class = "row<?php print $row_postfix?>">
    <?php endif; ?>
    
    <div class = "<?php echo $prefix.(($this->count_product_to_row > 0) ? 12 / $this->count_product_to_row : '1')." ".$prefix_xs."12";?> block_product">
        <?php include(dirname(__FILE__)."/".$product->template_block_product);?>
    </div>
    
    <?php if ($k % $this->count_product_to_row == $this->count_product_to_row - 1) : ?>
        <div class = "clearfix"></div>
        </div>
    <?php endif; ?>
<?php endforeach; ?>
<?php if ($k % $this->count_product_to_row != $this->count_product_to_row - 1) : ?>
    <div class = "clearfix"></div>
    </div>
<?php endif; ?>
</div>
<?php } ?>

<?php if ($stv=="toList") { ?>

<div class="jshop_grid list_product <?php print $stv; ?>">
<?php foreach ($this->rows as $k=>$product) : ?>
     <div class = "row<?php print $row_postfix?>">
     <div class = "<?php print $prefix."12"." ".$prefix_xs."12"?> list-block block_product">
        <?php include(dirname(__FILE__)."/".$product->template_block_product);?>
    	</div>
     
    	<div class = "clearfix"></div>
    	</div>
<?php endforeach; ?>
</div>
<?php } ?>

<?php if ($stv=="toSlim") { ?>
<div class="jshop_grid list_product <?php print $stv; ?>">
<?php foreach ($this->rows as $k=>$product) : ?>
     <div class = "row<?php print $row_postfix?>">
     <div class = "<?php print $prefix."12"." ".$prefix_xs."12"?> list-block block_product">
        <?php include(dirname(__FILE__)."/".$product->template_block_product);?>
    	</div>
     
    	<div class = "clearfix"></div>
    	</div>
<?php endforeach; ?>
</div>
<?php }

}
?>