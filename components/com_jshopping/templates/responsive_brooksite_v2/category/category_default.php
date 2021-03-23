<?php defined('_JEXEC') or die('Restricted access');
include(dirname(__FILE__)."/../plugin_params.php");
if ($on_seo_pagination=="1") {
    include(dirname(__FILE__)."/_pagination_seo.php");
}
if ($param_showdescrimg!=1 || !$this->category->category_image) {
    $col=$prefix."12";
} else {
    $col=$prefix."9";
}
?>
<div class="jshop rp-brooksite" id="comjshop">
	<?php print $this->category->_tmp_category_html_before_name;?>
    <h1><?php print $this->category->name?></h1>
    <?php print $this->category->_tmp_category_html_after_name;?>
    
    <?php if ($param_showdescr==1 && $param_showdescrbottom!=1) { ?>
        <div class="row<?php print $row_postfix?> margin-bot jshop-category">
        <?php if ($param_showdescrimg==1 && $this->category->category_image) { ?>
            <div class="<?php print $prefix?>3 image text-center">
            <img class = "jshop_img" src = "<?php print $this->image_category_path;?>/<?php print $this->category->category_image;?>" alt="<?php print htmlspecialchars($this->category->name);?>" title="<?php print htmlspecialchars($this->category->name);?>" />
            </div>
        <?php } ?>
        <div class="<?php print $col;?>">
        <?php print $this->category->description?>
        </div>
        </div>
    <?php } ?>
    
    
    <?php print $this->category->_tmp_category_html_after_description;?>
				<div class="jshop_list_category">
    <?php if (count($this->categories)) : ?>
        <div class = "jshop list_category">
            <?php foreach($this->categories as $k=>$category) : ?>
                <?php if ($k % $this->count_category_to_row == 0) : ?>
                    <div class = "row<?php print $row_postfix?>">
                <?php endif; ?>
                <div class = "<?php print $prefix.(($this->count_category_to_row > 0) ? 12 / $this->count_category_to_row : '1'); ?>">
                    <div class="jshop_categ category">
                    <div class = "image">
                        <a href = "<?php print $category->category_link;?>"><img class="jshop_img" src="<?php print $this->image_category_path;?>/<?php if ($category->category_image) print $category->category_image; else print $this->noimage;?>" alt="<?php print htmlspecialchars($category->name)?>" title="<?php print htmlspecialchars($category->name)?>" /></a>
                    </div>
                    <div class = "cat_short">
                       <h2><a class = "product_link" href = "<?php print $category->category_link?>"><?php print $category->name?></a></h2>
                       <p class = "category_short_description"><?php print $category->short_description?></p>
                    </div>
                    </div>
                </div>
                <?php if ($k % $this->count_category_to_row == $this->count_category_to_row - 1) : ?>
                    <div class = "clearfix"></div>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
            <?php if ($k % $this->count_category_to_row != $this->count_category_to_row - 1) : ?>
                <div class = "clearfix"></div>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
    </div>
    <?php include(dirname(__FILE__)."/products.php");?>
    <?php print $this->category->_tmp_category_html_after_products;?>
    
    <?php if ($param_showdescr==1 && $param_showdescrbottom==1) { ?>
        <div class="row<?php print $row_postfix?> jshop-category">
        <?php if ($param_showdescrimg==1 && $this->category->category_image) { ?>
            <div class="<?php print $prefix?>3 image text-center">
            <img class = "jshop_img" src = "<?php print $this->image_category_path;?>/<?php print $this->category->category_image;?>" alt="<?php print htmlspecialchars($this->category->name);?>" title="<?php print htmlspecialchars($this->category->name);?>" />
            </div>
        <?php } ?>
        <div class="<?php print $col;?>">
        <?php print $this->category->description?>
        </div>
        </div>
    <?php } ?>
    
</div>