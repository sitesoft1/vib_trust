<?php
/**
* @version      2.7.9 13.03.2020
* @author       Garry
* @copyright    Copyright (C)All rights reserved.
* @license      GNU GPL v3
*/

defined( '_JEXEC' ) or die( 'Restricted access' );
jimport('joomla.application.component.controller');
require_once (JPATH_COMPONENT_ADMINISTRATOR.'/functions.php');

class JshoppingControllerAddon_vendor extends JControllerLegacy{
    private $vendorId;
    private $va_template = 'addons';
    private $alias = 'addon_vendor';
     
    function __construct($config = array()){
        parent::__construct( $config );
        JSFactory::loadExtLanguageFile('addon_vendor');
        JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR.'/components/com_jshopping/models');
        
    	$document = JFactory::getDocument();
    	$document->addCustomTag('<script type = "text/javascript" src = "'.JURI::root().'components/com_jshopping/js/addon_vendor.js"></script>');
     	$document->addCustomTag('<link type = "text/css" rel = "stylesheet" href = "'.JURI::root().'components/com_jshopping/css/addon_vendor.css" />');  
        $document->addCustomTag('<link type = "text/css" rel = "stylesheet" href = "'.JURI::root().'administrator/components/com_jshopping/css/style.css" />');
        $document->addCustomTag('<script type = "text/javascript" src = "'.JURI::root().'administrator/components/com_jshopping/js/functions.js"></script>');
    
        $user =  JFactory::getUser();
        $vendors = $this->getModel("vendors");    
        $this->vendorId = $vendors->getIdVendorForUserId($user->id);
        
        $this->registerTask("paymentAdd", "paymentEdit");
        $this->registerTask("paymentUnpublish", "paymentPublish");
        $this->registerTask("paymentOrderUp", "paymentReorder");
        $this->registerTask("paymentOrderDown", "paymentReorder");
        $this->registerTask("shippingAdd", "shippingEdit");
        $this->registerTask("shippingUnpublish", "shippingPublish");
        $this->registerTask("shippingOrderUp", "shippingReorder");
        $this->registerTask("shippingOrderDown", "shippingReorder");
        $this->registerTask("shippingsPriceAdd", "shippingsPriceEdit");
    }
    
    function display($cachable = false, $urlparams = false){
        $user = JFactory::getUser();
        $addon_vendor = $this->getModel("addon_vendor");
        $vndConfig = $addon_vendor->getVendorsConfig();

        setMetaData(_JSHOP_VENDOR_MENU, _JSHOP_VENDOR_MENU, _JSHOP_VENDOR_MENU);
        appendPathWay(_JSHOP_VENDOR_MENU);    
        
        $text = '';
        $menu = array();
        if ($user->id){
            $vendor = $addon_vendor->getVendorByUser();
            if (!empty($vendor) && !$vendor->publish){
                JError::raiseWarning("", _JSHOP_YOUR_SELLER_ACCOUNT_IS_BLOCKED);
            } else if ($addon_vendor->checkUserVendor($user->id)){
                $menu = $addon_vendor->getVendorMenu(true, $vndConfig);
            } else if ($vndConfig->allow_become_a_seller && !$addon_vendor->checkUserVendor($user->id)) {
                $text = _JSHOP_BECOME_A_SELLER_LABEL;
                $menu = $addon_vendor->getVendorMenu(false, $vndConfig);
            } else {
                $this->setRedirect(SEFLink("index.php", 1, 1));
            }
        } else {
            $this->setRedirect(SEFLink("index.php?option=com_jshopping&controller=user&task=login", 1, 1));
        }
 
        $view_name = "addon_vendor";
        $view_config = array("template_path" => JPATH_COMPONENT."/templates/".$this->va_template."/".$view_name);
        $view = $this->getView($view_name, 'html', '', $view_config);
        $view->setLayout("vendor_menu");
        $view->assign('menu', $menu);
        $view->assign('message', $text);         
        $view->display();          
    }
    
    function becomeaseller(){
        $user = JFactory::getUser();
        $addon_vendor = JModelLegacy::getInstance('addon_vendor', 'JshoppingModel');
        
        if (!$user->id){
            $this->setRedirect(SEFLink("index.php?option=com_jshopping&controller=user&task=register", 1, 1));
        } else {
            $addon_vendor = $this->getModel("addon_vendor");
            $addon_vendor->becomeaseller();
            $this->setRedirect(SEFLink("index.php?option=com_jshopping&controller=addon_vendor", 1, 1));
        }
    }
    
    function _getAllCategoriesLevel($parentId, $currentOrdering = 0){
        $_categories = $this->getModel('categories');
        $rows = $_categories->getSubCategories($parentId, "ordering");
        $first[] = JHTML::_('select.option', '0',_JSHOP_VF_ORDERING_FIRST,'ordering','name');//category_ordering
        if (count($rows) > 0){
            $rows = array_merge($first,$rows);
            $currentOrdering = (!$currentOrdering) ? ($rows[count($rows) - 1]->ordering) : ($currentOrdering);//
            return (JHTML::_('select.genericlist', $rows,'ordering','class="inputbox" size="1"','ordering','name',$currentOrdering));//category_ordering
        }
    }
    
    function save_category(){
        $addon_vendor = $this->getModel("addon_vendor");
        $addon_vendor->checkUserSeller();
        $addon_vendor->checkTasksAccess('addCategory');
        
        $jshopConfig = JSFactory::getConfig();
        
        require_once ($jshopConfig->path.'lib/image.lib.php');
        require_once ($jshopConfig->path.'lib/uploadfile.class.php');
        
        $_alias = $this->getModel('alias');
        
        $category = JTable::getInstance("category","jshop");
        if (!$_POST["category_id"]){
            $_POST['category_add_date'] = date("Y-m-d H:i:s");
        }
        if (!isset($_POST['category_publish'])){
            $_POST['category_publish'] = 0;
        }
        
        $post = JRequest::get('post');
        $post['category_id'] = 0;
        $_lang = $this->getModel('languages');                  
        $languages = $_lang->getAllLanguages(1);   
        if (count($languages) > 0)
        foreach($languages as $lang){
            $post['name_'.$lang->language] = trim($post['name_'.$lang->language]);
            if ($post['name_'.$lang->language] == '' || empty($post['products_page']) || empty($post['products_row'])){
                JError::raiseWarning('', _JSHOP_VF_ERROR_BIND);
                $this->setRedirect(SEFLink("index.php?option=com_jshopping&controller=addon_vendor&task=add_category", 1, 1));
                return 0;
            }
            if ($jshopConfig->create_alias_product_category_auto && $post['alias_'.$lang->language]=="") $post['alias_'.$lang->language] = $post['name_'.$lang->language];
            $post['alias_'.$lang->language] = JApplication::stringURLSafe($post['alias_'.$lang->language]);
            if ($post['alias_'.$lang->language]!="" && !$_alias->checkExistAlias1Group($post['alias_'.$lang->language], $lang->language, $post['category_id'], 0)){
                $post['alias_'.$lang->language] = "";
                JError::raiseWarning("",_JSHOP_VF_ERROR_ALIAS_ALREADY_EXIST);
                $this->setRedirect(SEFLink("index.php?option=com_jshopping&controller=addon_vendor&task=add_category", 1, 1));
                return 0;
            }
            $post['description_'.$lang->language] = JRequest::getVar('description'.$lang->id,'','post',"string", 2);
            $post['short_description_'.$lang->language] = JRequest::getVar('short_description_'.$lang->language,'','post',"string", 2);
        }
        
        if(!$category->bind($post)) {
            JError::raiseWarning("",_JSHOP_VF_ERROR_BIND);
            $this->setRedirect(SEFLink("index.php?option=com_jshopping&controller=addon_vendor&task=add_category", 1, 1));
            return 0;
        }
       
        $upload = new UploadFile($_FILES['category_image']);
        $upload->setAllowFile(array('jpeg','jpg','gif','png'));
        $upload->setDir($jshopConfig->image_category_path);
        
        if ($upload->upload()){            
            $name = $upload->getName();
            @chmod($jshopConfig->image_category_path."/".$name, 0777);
            
            if ($post['size_im_category'] < 3){
                if($post['size_im_category'] == 1){
                    $category_width_image = $jshopConfig->image_category_width; 
                    $category_height_image = $jshopConfig->image_category_height;
                }else{
                    $category_width_image = JRequest::getInt('category_width_image'); 
                    $category_height_image = JRequest::getInt('category_height_image');
                }

                $path_full = $jshopConfig->image_category_path."/".$name;
                $path_thumb = $jshopConfig->image_category_path."/".$name;

                if (!ImageLib::resizeImageMagic($path_full, $category_width_image, $category_height_image, $jshopConfig->image_cut, $jshopConfig->image_fill, $path_thumb, $jshopConfig->image_quality, $jshopConfig->image_fill_color)) {
                    JError::raiseWarning("",_JSHOP_VF_ERROR_CREATE_THUMBAIL);
                    saveToLog("error.log", "SaveCategory - Error create thumbail");
                }
                @chmod($jshopConfig->image_category_path."/".$name, 0777);    
                unset($img);
            }
            
            $category->category_image = $name;
            
        }else{            
            if ($upload->getError() != 4){
                JError::raiseWarning("", _JSHOP_VF_ERROR_UPLOADING_IMAGE);
                saveToLog("error.log", "SaveCategory - Error upload image. code: ".$upload->getError());
            }
        }

        $this->_reorderCategory($category);
         
        if (!$category->store()) {
            JError::raiseWarning("",_JSHOP_VF_ERROR_SAVE_DATABASE);
            $this->setRedirect(SEFLink("index.php?option=com_jshopping&controller=addon_vendor&task=add_category", 1, 1));
            return 0;
        }
        
        $this->setRedirect(SEFLink('index.php?option=com_jshopping&controller=addon_vendor', 1, 1), _JSHOP_VF_CATEGORY_SUCC_ADDED);     
    }        

    function _reorderCategory(&$category) {
        $addon_vendor = $this->getModel("addon_vendor");
        $addon_vendor->checkUserSeller();    	
        $db = JFactory::getDBO();
        $query = "UPDATE `#__jshopping_categories` SET `ordering` = ordering + 1
                    WHERE `category_parent_id` = '" . $db->escape($category->category_parent_id) . "' AND `ordering` > '" . $db->escape($category->ordering) . "'";
        $db->setQuery($query);
        $db->query();
        $category->ordering++;
    }
    
    public function attributes(){
        $addon_vendor = $this->getModel("addon_vendor");
        $addon_vendor->checkUserSeller();     
        $addon_vendor->checkTasksAccess('addAttr');
        
    	$attributes = JModelLegacy::getInstance("attribut", "JshoppingModel");
    	$attributesvalue = JModelLegacy::getInstance("attributValue", "JshoppingModel");
        $rows = $attributes->getAllAttributes(0);
        foreach ($rows as $key => $value){
            $rows[$key]->values = splitValuesArrayObject( $attributesvalue->getAllValues($rows[$key]->attr_id), 'name');
            $rows[$key]->count_values = count($attributesvalue->getAllValues($rows[$key]->attr_id));
        }
        
        $view_name = "addon_vendor";
        $view_config = array("template_path"=>JPATH_COMPONENT."/templates/".$this->va_template."/".$view_name);
        $view = $this->getView($view_name, 'html', '', $view_config);
        $view->setLayout("attributes");
        $view->assign('rows', $rows);
        $view->display();
    }
    
    public function add_attribut(){
        $addon_vendor = $this->getModel("addon_vendor");
        $addon_vendor->checkUserSeller();     
        $addon_vendor->checkTasksAccess('addAttr');
    
        $_lang = JModelLegacy::getInstance("languages", "JshoppingModel");
        $languages = $_lang->getAllLanguages(1);
        $multilang = count($languages)>1;
	
        $types[] = JHTML::_('select.option', '1','Select','attr_type_id','attr_type');
        $types[] = JHTML::_('select.option', '2','Radio','attr_type_id','attr_type');
        $type_attribut = JHTML::_('select.genericlist', $types, 'attr_type','class = "inputbox" size = "1"','attr_type_id','attr_type', 1);

        $all = array();
        $all[] = JHTML::_('select.option', 1, _JSHOP_ALL, 'id','value');
        $all[] = JHTML::_('select.option', 0, _JSHOP_VF_SELECTED, 'id','value');
        $lists['allcats'] = JHTML::_('select.radiolist', $all, 'allcats','onclick="PFShowHideSelectCats()"','id','value', 1);
        
        $categories = buildTreeCategory(0,1,0);
        //$lists['categories'] = JHTML::_('select.genericlist', $categories,'category_id[]','class="inputbox" size="10" multiple = "multiple"','category_id','name', array());
        $lists['categories'] = JHTML::_('select.genericlist', $categories,'category_id[]','class="inputbox" size="1"','category_id','name', array());
	    
        $view_name = "addon_vendor";
        $view_config = array("template_path"=>JPATH_COMPONENT."/templates/".$this->va_template."/".$view_name);
        $view = $this->getView($view_name, 'html', '', $view_config);
        
        $view->setLayout("attributes_edit");
        $view->assign('type_attribut', $type_attribut);
        $view->assign('languages', $languages);
        $view->assign('multilang', $multilang);
        $view->assign('lists', $lists);
        $view->display();
    }
    
    public function save_attributes(){
        $addon_vendor = $this->getModel("addon_vendor");
        $addon_vendor->checkUserSeller();     
        $addon_vendor->checkTasksAccess('addAttr');
        
        $db = JFactory::getDBO(); 
        
        $attribut = JTable::getInstance('attribut', 'jshop');    
        $post = JRequest::get("post");
        $post['independent'] = 1;
        $post['attr_id'] = 0;
        
        $query = "SELECT MAX(attr_ordering) AS attr_ordering FROM `#__jshopping_attr`";
        $db->setQuery($query);
        $row = $db->loadObject();
        $post['attr_ordering'] = $row->attr_ordering + 1;
        
        $_lang = $this->getModel('languages');                  
        $languages = $_lang->getAllLanguages(1);   
        if (count($languages) > 0)
        foreach($languages as $lang){
            $post['name_'.$lang->language] = trim($post['name_'.$lang->language]);
            if ($post['name_'.$lang->language] == ''){
                JError::raiseWarning('', _JSHOP_VF_ERROR_BIND);
                $this->setRedirect(SEFLink("index.php?option=com_jshopping&controller=addon_vendor&task=add_attribut", 1, 1));
                return 0;
            }
        }
        
        if (!$attribut->bind($post) || ($post['attr_type'] != 1 && $post['attr_type'] != 2) || ($post['allcats'] != 0 && $post['allcats'] != 1)) {
            JError::raiseWarning("",_JSHOP_VF_ERROR_BIND);
            $this->setRedirect(SEFLink('index.php?option=com_jshopping&controller=addon_vendor'));
            return 0;
        }
        
        if (isset($post['category_id'])) 
            $categorys = $post['category_id'];
        else
            $categorys = '';
        
        if (!is_array($categorys)) $categorys = array();
        
        $attribut->setCategorys($categorys);

        if (!$attribut->store()) {
            JError::raiseWarning("",_JSHOP_ERROR_SAVE_DATABASE);
            $this->setRedirect(SEFLink('index.php?option=com_jshopping&controller=addon_vendor'));
            return 0;
        }

        $query="ALTER TABLE `#__jshopping_products_attr` ADD `attr_".$attribut->attr_id."` INT(11) NOT NULL";
        $db->setQuery($query);
        $db->query();

        $this->setRedirect(SEFLink('index.php?option=com_jshopping&controller=addon_vendor&task=attributes', 1, 1), _JSHOP_VF_ATTRIBUTE_SAVED);
    }
    
    public function attributesvalues(){
        $addon_vendor = $this->getModel("addon_vendor");
        $addon_vendor->checkUserSeller();     
        $addon_vendor->checkTasksAccess('addAttr');
        
        $attr_id = JRequest::getInt("attr_id");
        $jshopConfig = JSFactory::getConfig();
        
		$attributValues = JModelLegacy::getInstance("AttributValue", "JshoppingModel");
		$rows = $attributValues->getAllValues($attr_id);
		$attribut = JModelLegacy::getInstance("attribut", "JshoppingModel");
		$attr_name = $attribut->getName($attr_id);
		
        $view_name = "addon_vendor";
        $view_config = array("template_path"=>JPATH_COMPONENT."/templates/".$this->va_template."/".$view_name);
        $view = $this->getView($view_name, 'html', '', $view_config);
        $view->setLayout("attributesvalues");
        $view->assign('rows', $rows);        
        $view->assign('attr_id', $attr_id);
        $view->assign('config', $jshopConfig);
        $view->assign('attr_name', $attr_name);
		
		$view->display(); 
    }
    
    public function addattrvalue(){
        $addon_vendor = $this->getModel("addon_vendor");
        $addon_vendor->checkUserSeller();     
        $addon_vendor->checkTasksAccess('addAttr');
        
		$attr_id = JRequest::getInt("attr_id");
		$jshopConfig = JSFactory::getConfig();	
        
        $_lang = $this->getModel("languages");
        $languages = $_lang->getAllLanguages(1);
        $multilang = count($languages)>1;
		
		$view_name = "addon_vendor";
        $view_config = array("template_path"=>JPATH_COMPONENT."/templates/".$this->va_template."/".$view_name);
        $view = $this->getView($view_name, 'html', '', $view_config);
        $view->setLayout("addattrvalue");		      
        $view->assign('attr_id', $attr_id);        
        $view->assign('config', $jshopConfig);
        $view->assign('languages', $languages);
        $view->assign('multilang', $multilang);

		$view->display();
    }
    
    public function saveattrvalue(){
        $addon_vendor = $this->getModel("addon_vendor");
        $addon_vendor->checkUserSeller();     
        $addon_vendor->checkTasksAccess('addAttr');
        
        $jshopConfig = JSFactory::getConfig();
        require_once ($jshopConfig->path.'lib/uploadfile.class.php');
        
        $db = JFactory::getDBO();
		$attr_id = JRequest::getInt("attr_id");
        
        $post = JRequest::get("post");
        $post['value_id'] = 0;
        $attributValue = JTable::getInstance('attributValue', 'jshop');

        $_lang = $this->getModel('languages');
        $languages = $_lang->getAllLanguages(1);

        if (count($languages) > 0)
        foreach($languages as $lang){
            $post['name_'.$lang->language] = trim($post['name_'.$lang->language]);
            if ($post['name_'.$lang->language] == ''){
                JError::raiseWarning('', _JSHOP_VF_ERROR_BIND);
                $this->setRedirect(SEFLink("index.php?option=com_jshopping&controller=addon_vendor&task=attributesvalues&attr_id=".$attr_id, 1, 1));
                return 0;
            }
        }
        
        $upload = new UploadFile($_FILES['image']);
        $upload->setAllowFile(array('jpeg','jpg','gif','png'));
        $upload->setDir($jshopConfig->image_attributes_path);
        $upload->setFileNameMd5(0);
        $upload->setFilterName(1);
        if ($upload->upload()) {
            $post['image'] = $upload->getName();
            @chmod($jshopConfig->image_attributes_path."/".$post['image'], 0777);
        } else {
            if ($upload->getError() != 4){
                JError::raiseWarning("", _JSHOP_VF_ERROR_UPLOADING_IMAGE);
                saveToLog("error.log", "SaveAttributeValue - Error upload image. code: ".$upload->getError());
            }
        }
        
        $query = "SELECT MAX(value_ordering) AS value_ordering FROM `#__jshopping_attr_values` where attr_id='".$db->escape($attr_id)."'";
        $db->setQuery($query);
        $row = $db->loadObject();
        $post['value_ordering'] = $row->value_ordering + 1;
        
        if (!$attributValue->bind($post) || !$attr_id) {
            JError::raiseWarning("",_JSHOP_VF_ERROR_BIND);
            $this->setRedirect("index.php?option=com_jshopping&controller=addon_vendor&task=attributesvalues&attr_id=".$attr_id);
            return 0;
        }
                
        if (!$attributValue->store()) {
            JError::raiseWarning("",_JSHOP_VF_ERROR_SAVE_DATABASE);
            $this->setRedirect("index.php?option=com_jshopping&controller=addon_vendor&task=attributesvalues&attr_id=".$attr_id);
            return 0;
        }
                
		$this->setRedirect(SEFLink('index.php?option=com_jshopping&controller=addon_vendor&task=attributesvalues&attr_id='.$attr_id, 1, 1), _JSHOP_VF_ATTRIBUTE_VALUE_SAVED);
    }
    
    public function add_category(){
        $addon_vendor = $this->getModel("addon_vendor");
        $addon_vendor->checkUserSeller();     
        $addon_vendor->checkTasksAccess('addCategory');
        
        setMetaData(_JSHOP_ADD_CATEGORY, _JSHOP_ADD_CATEGORY, _JSHOP_ADD_CATEGORY);
        appendPathWay(_JSHOP_ADD_CATEGORY);        
        
        $_lang = $this->getModel('languages');                
        $languages = $_lang->getAllLanguages(1);
        $multilang = count($languages) > 1;
        
        $parentid = JRequest::getInt("catid");
        $rows = $this->_getAllCategoriesLevel($parentid);
        $lists['templates'] = getTemplates('category', '');
        $lists['onelevel'] = $rows;    
        
        $parentTop = new stdClass();
        $parentTop->category_id = 0;
        $parentTop->name = _JSHOP_VF_TOP_LEVEL;
        $categories = buildTreeCategory(0,1,0);
        array_unshift($categories, $parentTop);
        
        $lists['treecategories'] = JHTML::_('select.genericlist', $categories,'category_parent_id','class="inputbox" id = "ordering" size="1" onchange = "changeVendorCategory()"','category_id','name', $parentid);
        $lists['parentid'] = $parentid;

        $accessgroups = getAccessGroups();
        $lists['access'] = JHTML::_('select.genericlist', $accessgroups, 'access','class = "inputbox" size = "1"','id','title', '');

        $view_name = "addon_vendor";
        $view_config = array("template_path"=>JPATH_COMPONENT."/templates/".$this->va_template."/".$view_name);
        $view = $this->getView($view_name, 'html', '', $view_config);
        $view->setLayout("new_category");    
        
        $view->assign('lists', $lists);
        $view->assign('languages', $languages);
        $view->assign('multilang', $multilang);
        $view->display();
	}
    
    function cashing_delete(){
        $addon_vendor = $this->getModel("addon_vendor");
        $addon_vendor->checkUserSeller();
		$addon_vendor->checkTasksAccess('cashing');
        
        $db = JFactory::getDBO();
        $text = array();
        $cid = JRequest::getVar('cid');
        
        if ($this->vendorId && count($cid) > 0){
            foreach ($cid as $key => $value){			
                $query = "select * from #__jshopping_vendors_cashing where id='".$db->escape($value)."' AND vendor_id = ".$this->vendorId ." AND status <> '1'";
                $db->setQuery($query);
                $list = $db->loadObjectList();
                if (count($list) > 0){            	
                    $sum = $list[0]->sum + $list[0]->commission;

                    $query = "DELETE FROM `#__jshopping_vendors_cashing` WHERE `id` = '" . $db->escape($value) . "' AND vendor_id = ".$this->vendorId;
                    $db->setQuery($query);
                    $db->query();

                        $query = "SELECT * from #__jshopping_vendors_cashing WHERE id='".$db->escape($value)."'  AND vendor_id = ".$this->vendorId;
                        $db->setQuery($query);
                        $list = $db->loadObjectList();
                        if (count($list) <=0 ){	            
                                $query = "UPDATE `#__jshopping_vendors`
                                           SET `balance` = ROUND(`balance`, 2) + ". round($sum, 2) ." WHERE `id` = ".$db->escape($this->vendorId);
                                $db->setQuery($query);
                                $db->query();		            		
                        }	            	
                }
            }
            $text[]= sprintf(_JSHOP_VENDOR_CASHING_DELETED, $value)."<br />";
       	}
        $this->setRedirect(SEFLink("index.php?option=com_jshopping&controller=addon_vendor&task=cashing", 1, 1), implode("</li><li>",$text));		
    }    
    
    function calc_comm(){
        $addon_vendor = $this->getModel("addon_vendor");
        $addon_vendor->checkUserSeller(); 
        $vndConfig = $addon_vendor->getVendorsConfig();
        $vendor = $addon_vendor->getVendorByUser();
        $cashingSum = JRequest::getVar('cashingSum');
        $payment_id = JRequest::getVar('payment_id');
        $payment = $addon_vendor->getPayment($payment_id);
        
        if ($payment->commission_type == 1) 
            $commission = round($cashingSum * ($payment->commission / 100), 2);
        else
            $commission = $payment->commission;
        
        $jshop_commission = (round($cashingSum, 2) > $vndConfig->max_money_cashing && $vndConfig->max_money_cashing > 0) ? _JSHOP_COMMISSION . ": <span class = 'min_max_cashing_sum' style = 'color: red'>" . $commission . " " .getMainCurrencyCode() . "</span>" : _JSHOP_COMMISSION . ": <span class = 'min_max_cashing_sum'>" . $commission . " " .getMainCurrencyCode() . "</span>";
        $jshop_vendor_balance = (round(($vendor->balance - $cashingSum), 2) < 0) ? _JSHOP_VENDOR_BALANCE . ": <span class = 'min_max_cashing_sum' style = 'color: red'>" . round(($vendor->balance - $cashingSum), 2) . " " .getMainCurrencyCode() . "</span>" : _JSHOP_VENDOR_BALANCE . ": <span class = 'min_max_cashing_sum'>" . round(($vendor->balance - $cashingSum), 2) . " " .getMainCurrencyCode() . "</span>";
        
        $jshop_cashing = ((round($cashingSum, 2) > $vndConfig->max_money_cashing && $vndConfig->max_money_cashing > 0) || round($cashingSum - $commission, 2) <= 0 || round($cashingSum - $commission, 2) < $vndConfig->min_money_cashing) ? _JSHOP_CASHING . ": <span class = 'min_max_cashing_sum' style = 'color: red'>" . round($cashingSum - $commission, 2) . " " .getMainCurrencyCode() . "</span>" : _JSHOP_CASHING . ": <span class = 'min_max_cashing_sum'>" . round($cashingSum - $commission, 2) . " " .getMainCurrencyCode() . "</span>";;
        
        $result = array(
                    'commission' => $jshop_commission,
                    'balance' => $jshop_vendor_balance,
                    'viewCashingSum' => $jshop_cashing,
                    'vndBalance' => round(($vendor->balance - $cashingSum), 2),
                    'vndCommission' => (float)$commission,
                    'min_cashing_sum' => (float)$vndConfig->min_money_cashing,
                    'max_cashing_sum' => (float)$vndConfig->max_money_cashing,
                    'cashingSum' => $cashingSum - $commission
                    
        );
        print (json_encode($result));
        die;
    }
    
    public function new_cashing(){
        $addon_vendor = $this->getModel("addon_vendor");
        $addon_vendor->checkUserSeller();
        $addon_vendor->checkTasksAccess('cashing');
        
        setMetaData(_JSHOP_CASHING, _JSHOP_CASHING, _JSHOP_CASHING);
        appendPathWay(_JSHOP_CASHING);  
        
        $document = JFactory::getDocument();
    	$document->addCustomTag('<script type = "text/javascript">
                                    var url_calc_comm = "'. SEFLink("index.php?option=com_jshopping&controller=addon_vendor&task=calc_comm", 1, 1) .'";
                                </script>');
        $vndConfig = $addon_vendor->getVendorsConfig();
        $jshopConfig = JSFactory::getConfig();
        $vnd = $addon_vendor->getVendorByUser();
        if ($vnd->balance <= 0)
            $this->setRedirect(SEFLink("index.php?option=com_jshopping&controller=addon_vendor&task=cashing", 1, 1));	
        $payments = $addon_vendor->getVendorsPayments();
        
        $payments_fields = $addon_vendor->getVendorsPaymentsFields();
        
        $view_name = "addon_vendor";
        $view_config = array("template_path" => JPATH_COMPONENT."/templates/".$this->va_template."/".$view_name);
        $view = $this->getView($view_name, 'html', '', $view_config);
        $view->setLayout("new_cashing");

        $view->assign('vnd', $vnd);
        $view->assign('config', $jshopConfig); 
        $view->assign('payments', $payments); 
        $view->assign('paymentsFields', $payments_fields);    
        $view->assign('vndConfig', $vndConfig);  
        $view->display();          
    }
    
    function cashing(){   
        $addon_vendor = $this->getModel("addon_vendor");
        $addon_vendor->checkUserSeller();
		$addon_vendor->checkTasksAccess('cashing');
        
        $mainframe = JFactory::getApplication();		    
        
        setMetaData(_JSHOP_CASHING, _JSHOP_CASHING, _JSHOP_CASHING);
        appendPathWay(_JSHOP_CASHING);        
        $jshopConfig = JSFactory::getConfig();
        $vnd = $addon_vendor->getVendorByUser();

        $context = "jshoping.vendors_cashing_list";

        $limit = $mainframe->getUserStateFromRequest( $context.'limit', 'limit', $mainframe->getCfg('list_limit'), 'int' );
        $limitstart = $mainframe->getUserStateFromRequest($context.'limitstart', 'limitstart', 0, 'int' );
        $total = $addon_vendor->getCountAllCashing($this->vendorId);
        
        jimport('joomla.html.pagination');
        $pagination = new JPagination($total, $limitstart, $limit);
        
        $rows = $addon_vendor->getAllCashing($this->vendorId, $pagination->limitstart, $pagination->limit);        
      
        $view_name = "addon_vendor";
        $view_config = array("template_path"=>JPATH_COMPONENT."/templates/".$this->va_template."/".$view_name);
        $view = $this->getView($view_name, 'html', '', $view_config);
        $view->setLayout("cashing");
        $view->assign('rows', $rows);
        $view->assign('vnd', $vnd);
        $view->assign('pagination', $pagination);
        $view->assign('config', $jshopConfig);		 
        $view->display();      	
    }
    
    function save_cashing(){
        $addon_vendor = $this->getModel("addon_vendor");
        $addon_vendor->checkUserSeller();
        $addon_vendor->checkTasksAccess('cashing');
        
        $vndConfig = $addon_vendor->getVendorsConfig();
        $jshopConfig = JSFactory::getConfig();
        
        $post = JRequest::get('post');
        $post['vendor_id'] = $this->vendorId; 
        $vnd = $addon_vendor->getVendorByUser();
     
        $payment = $addon_vendor->getPayment($post['cashing_payment']);
   
        if (substr_count($post['cashingSumm'], ',')) $post['cashingSumm'] = str_replace(',', '.', $post['cashingSumm']);
        $post['cashingSumm'] = round($post['cashingSumm'], 2);
        if ($payment->commission_type == 1) 
            $post['commission'] = round($post['cashingSumm'] * ($payment->commission / 100), 2);
        else
            $post['commission'] = $payment->commission;

        if ((round(($vnd->balance - $post['cashingSumm']), 2) < 0) || ($post['cashingSumm'] - $post['commission'] <= 0) || ($post['cashingSumm'] - $post['commission'] < $vndConfig->min_money_cashing) || ($vndConfig->max_money_cashing > 0 && ($post['cashingSumm'] > $vndConfig->max_money_cashing))){
            JError::raiseWarning("",_JSHOP_ERROR_UNABLE_CASHING_THE_MONEY);
            $this->setRedirect(SEFLink("index.php?option=com_jshopping&controller=addon_vendor&task=new_cashing", 1, 1));
            return 0;
        }
        
        if (!isset($post['cashing_payment'])){
            JError::raiseWarning("",_JSHOP_SELECT_PAYMENT_SYSTEM); 
            $this->setRedirect(SEFLink("index.php?option=com_jshopping&controller=addon_vendor&task=new_cashing", 1, 1));
            return 0;
        } else {
            if (!$addon_vendor->validateCashingForm($post)){
                JError::raiseWarning("",_JSHOP_EMPTY_REQUIRED_FIELDS); 
                $this->setRedirect(SEFLink("index.php?option=com_jshopping&controller=addon_vendor&task=new_cashing", 1, 1));
                return 0;    
            }
        } 
        
        if (!$addon_vendor->saveCashing($post)){
            JError::raiseWarning("",_JSHOP_VF_ERROR_SAVE_DATABASE); 
            $this->setRedirect(SEFLink("index.php?option=com_jshopping&controller=addon_vendor&task=new_cashing", 1, 1));
            return 0;        
        } 
        
        $post['payment'] = $addon_vendor->getPayment($post['cashing_payment']);
        
        if (count($post['paymentFields']) > 0)
            foreach ($post['paymentFields'] as $id => $options){
                $post['paymentFields'][$id]['title'] = $addon_vendor->getPaymentField($id, $post['cashing_payment']);
            }
        
        $mainframe = JFactory::getApplication();
        $mailfrom = $mainframe->getCfg( 'mailfrom' );
        $fromname = $mainframe->getCfg( 'fromname' );

        $view_name = "addon_vendor";
        $view_config = array("template_path"=>JPATH_COMPONENT."/templates/".$this->va_template."/".$view_name);
        $view = $this->getView($view_name, 'html', '', $view_config);
        $view->setLayout("admin_notify_cashing");
        
        $view->assign('post', $post);
        $view->assign('vendor', $vnd);

        $message = $view->loadTemplate();
        
        $subject = _JSHOP_NEW_CASHING_REQUEST . ". ". _JSHOP_VENDOR . ": " . $vnd->f_name . " " . $vnd->l_name;
        
        JFactory::getMailer()->sendMail($mailfrom, $fromname, $jshopConfig->contact_email, $subject, $message, 1);	
        
        $this->setRedirect(SEFLink("index.php?option=com_jshopping&controller=addon_vendor&task=cashing", 1, 1), _JSHOP_CASHING_SAVED);  
    } 
	
    function orders(){
        $addon_vendor = $this->getModel("addon_vendor");
        $addon_vendor->checkUserSeller();
        $addon_vendor->checkTasksAccess('orders');
        
        appendPathWay(_JSHOP_MY_ORDERS);
        
        setMetaData(_JSHOP_MY_ORDERS, _JSHOP_MY_ORDERS, _JSHOP_MY_ORDERS);
        
        $orders = $addon_vendor->getAllOrders($this->vendorId);
		foreach($orders as $key => $value){
            $orders[$key]->order_href = SEFLink('index.php?option=com_jshopping&controller=addon_vendor&task=order&order_id='.$value->order_id, 1);
        }

        $vndConfig = $addon_vendor->getVendorsConfig();
        $view_name = "addon_vendor";
        $view_config = array("template_path"=>JPATH_COMPONENT."/templates/".$this->va_template."/".$view_name);
        $view = $this->getView($view_name, 'html', '', $view_config);
        $view->setLayout("orders");
        $view->assign('orders', $orders);
        $view->assign('config', $vndConfig);
        $view->assign('filter_html', $addon_vendor->getFilterHtml());
        $view->display();   
	}
	
    function order(){
        $addon_vendor = $this->getModel("addon_vendor");
        $addon_vendor->checkUserSeller();
        $addon_vendor->checkTasksAccess('orders');
        
        $vndConfig = $addon_vendor->getVendorsConfig();
        
        $jshopConfig = JSFactory::getConfig();

        $lang = JSFactory::getLang();
        
        appendPathWay(_JSHOP_MY_ORDERS, SEFLink('index.php?option=com_jshopping&controller=addon_vendor&task=orders', 1));
        
        setMetaData(_JSHOP_MY_ORDERS, _JSHOP_MY_ORDERS, _JSHOP_MY_ORDERS);
        
        $order_id = JRequest::getInt('order_id');

        $order = $addon_vendor->getOrder($order_id);        
        
        $order_commission = $addon_vendor->getOrderCommission($order_id, $this->vendorId);
        
        appendPathWay(_JSHOP_ORDER_NUMBER.": ".$order->order_number);
        
        $order->items = $addon_vendor->getOrderItems($order_id, $this->vendorId);
        if (count($order->items)==0){
            JError::raiseError( 500, "Error order number. You are not the owner of this order");
        }
        
        $country = JTable::getInstance('country', 'jshop');
        $country->load($order->country);
        $field_country_name = $lang->get("name");
        $order->country = $country->$field_country_name;
        
        $d_country = JTable::getInstance('country', 'jshop');
        $d_country->load($order->d_country);
        $field_country_name = $lang->get("name");
        $order->d_country = $d_country->$field_country_name;
        
        $jshopConfig->user_field_client_type[0] = "";
        $order->client_type_name = $jshopConfig->user_field_client_type[$order->client_type];
        
        $deliverytimes = JSFactory::getAllDeliveryTime();
        if (isset($deliverytimes[$order->delivery_times_id])){
            $order->delivery_time_name = $deliverytimes[$order->delivery_times_id];
            if ($order->delivery_time_name == ""){
                $order->delivery_time_name = $order->delivery_time;
            }
        } else {
            $order->delivery_time_name = $order->delivery_time;
        }
        
        
        $tmp_fields = $jshopConfig->getListFieldsRegister();
        $config_fields = $tmp_fields["address"];
        $count_filed_delivery = 0;
        foreach($config_fields as $k => $v){
            if (substr($k, 0, 2)=="d_" && $v['display']==1) $count_filed_delivery++;
        }
        
        if ($jshopConfig->order_display_new_digital_products){
            $product = JTable::getInstance('product', 'jshop');
            foreach($order->items as $k => $v){
                $product->product_id = $v->product_id;
                $product->setAttributeActive(unserialize($v->attributes));
                $files = $product->getSaleFiles();
                $order->items[$k]->files = serialize($files);
            }
        }

        $order_status_modify = $vndConfig->order_status_modify && $order->vendor_id!=-1;

        $lists = array();
        if ($order_status_modify){
            $lists['status'] = JHTML::_('select.genericlist', JshopHelpersSelectOptions::getOrderStatus(),'order_status','class = "inputbox" onchange="this.form.submit()"','status_id','name', $order->order_status);
        }
        
        $view_name = "addon_vendor";
        $view_config = array("template_path"=>JPATH_COMPONENT."/templates/".$this->va_template."/".$view_name);
        $view = $this->getView($view_name, getDocumentType(), '', $view_config);
        $view->setLayout("order");
        $view->assign('order', $order);
        $view->assign('config', $jshopConfig);
        $view->assign('config_fields', $config_fields);
        $view->assign('vndConfig', $vndConfig);
        $view->assign('count_filed_delivery', $count_filed_delivery);
        $view->assign('order_commission', $order_commission);
        $view->assign('lists', $lists);
        $view->assign('order_status_modify', $order_status_modify);
        $view->display();
    }
	
    public function products(){
        $addon_vendor = $this->getModel("addon_vendor");
        $addon_vendor->checkUserSeller();
        $addon_vendor->checkTasksAccess('myProducts');
        
        setMetaData(_JSHOP_MY_PRODUCTS, _JSHOP_MY_PRODUCTS, _JSHOP_MY_PRODUCTS);
        appendPathWay(_JSHOP_MY_PRODUCTS);
		
		$vendorInf = $addon_vendor->getVendorByUser();
        
        $mainframe = JFactory::getApplication();
        $jshopConfig = JSFactory::getConfig();        
        $products = $this->getModel("products");     
        
        $context = "jshoping.vendors_products_list";
        $limit = $mainframe->getUserStateFromRequest($context.'limit', 'limit', $mainframe->getCfg('list_limit'), 'int' );
        
        $limitstart = $mainframe->getUserStateFromRequest($context.'limitstart', 'limitstart', 0, 'int' );
        $filter_order = $mainframe->getUserStateFromRequest($context.'filter_order', 'filter_order', "product_id", 'cmd');
        $filter_order_Dir = $mainframe->getUserStateFromRequest($context.'filter_order_Dir', 'filter_order_Dir', "asc", 'cmd');
        
        if (isset($_GET['category_id']) && $_GET['category_id']==="0"){
            $mainframe->setUserState( $context.'category_id', 0);
            $mainframe->setUserState( $context.'manufacturer_id', 0);
            $mainframe->setUserState( $context.'label_id', 0);
            $mainframe->setUserState( $context.'publish', 0);
            $mainframe->setUserState( $context.'text_search', '');
        } 
        //print_r($filter_order);die;
        $category_id = $mainframe->getUserStateFromRequest( $context.'category_id', 'category_id', 0, 'int' );
        
        $manufacturer_id = $mainframe->getUserStateFromRequest( $context.'manufacturer_id', 'manufacturer_id', 0, 'int' );
        $label_id = $mainframe->getUserStateFromRequest( $context.'label_id', 'label_id', 0, 'int' );
        $publish = $mainframe->getUserStateFromRequest( $context.'publish', 'publish', 0, 'int' );
        $text_search = $mainframe->getUserStateFromRequest( $context.'text_search', 'text_search', '');

        $filter = array(
                    "category_id" => $category_id, 
                    "manufacturer_id" => $manufacturer_id, 
                    "label_id" => $label_id, 
                    "publish" => $publish, 
                    "text_search" => $text_search,
                    "vendor_id" => $this->vendorId
        );
        $total = $products->getCountAllProducts($filter);

        jimport('joomla.html.pagination');
        $pagination = new JPagination($total, $limitstart, $limit);
        
        $rows = $addon_vendor->getAllProducts($filter, $pagination->limitstart, $pagination->limit, $filter_order, $filter_order_Dir);
        
        $parentTop = new stdClass();
        $parentTop->category_id = 0;
        $parentTop->name = _JSHOP_CATEGORY;
        $categories_select = buildTreeCategory(0);
        array_unshift($categories_select, $parentTop);    
        $lists['treecategories'] = JHTML::_('select.genericlist', $categories_select,'category_id','onchange="document.adminForm.submit();" style = "width: 140px;"', 'category_id', 'name', $category_id );

        $manuf1 = array();
        $manuf1[0] = new stdClass();
        $manuf1[0]->manufacturer_id = '0';
        $manuf1[0]->name = _JSHOP_MANUFACTURER;

        $_manufacturer = $this->getModel('manufacturers');
        $manufs = $_manufacturer->getAllManufacturers(0);
        $manufs = array_merge($manuf1, $manufs);
        $lists['manufacturers'] = JHTML::_('select.genericlist', $manufs, 'manufacturer_id','onchange="document.adminForm.submit();" style = "width: 140px;"', 'manufacturer_id', 'name', $manufacturer_id);
        
        // product labels
        if ($jshopConfig->admin_show_product_labels) {
            $_labels = $this->getModel("productLabels");
            $alllabels = $_labels->getList();            
            $first = array();
            $first[] = JHTML::_('select.option', '0', _JSHOP_LABEL, 'id','name');        
            $lists['labels'] = JHTML::_('select.genericlist', array_merge($first, $alllabels), 'label_id','onchange="document.adminForm.submit();" style = "width: 100px;"','id','name', $label_id);
        }
        //
       
        $f_option = array();
        $f_option[] = JHTML::_('select.option', 0, _JSHOP_VF_SHOW, 'id', 'name');
        $f_option[] = JHTML::_('select.option', 1, _JSHOP_VF_PUBLISH, 'id', 'name');
        $f_option[] = JHTML::_('select.option', 2, _JSHOP_VF_UNPUBLISH, 'id', 'name');
        $lists['publish'] = JHTML::_('select.genericlist', $f_option, 'publish', 'onchange="document.adminForm.submit();" style = "width: 100px;"', 'id', 'name', $publish);
      
		$view_name = "addon_vendor";
        $view_config = array("template_path"=>JPATH_COMPONENT."/templates/".$this->va_template."/".$view_name);
        $view = $this->getView($view_name, 'html', '', $view_config);
        $view->setLayout("product_list");
        $view->assign('rows', $rows);
        $view->assign('lists', $lists);
        $view->assign('category_id', $category_id);
        $view->assign('manufacturer_id', $manufacturer_id);
        $view->assign('pagination', $pagination);
        $view->assign('text_search', $text_search);
        $view->assign('config', $jshopConfig);
        $view->assign('filter_order', $filter_order);
        $view->assign('filter_order_Dir', $filter_order_Dir);		 
        $view->assign('vendorInf', $vendorInf);		 
        $view->display();       
    }	    
        
    function edit_product(){
        $addon_vendor = $this->getModel("addon_vendor");
        $addon_vendor->checkUserSeller();
        $addon_vendor->checkTasksAccess('addProduct');
        
        JPluginHelper::importPlugin('jshoppingadmin');
        $dispatcher = JDispatcher::getInstance();
        $dispatcher->trigger('onVendorLoadEditProduct', array());
        
        $lang = JSFactory::getLang(); 
        $jshopConfig = JSFactory::getConfig();        
        
        $product_id = JRequest::getInt('product_id');
        $product_attr_id = JRequest::getInt('product_attr_id');
       
        if ($product_attr_id) {
            $product_attr = JSFactory::getTable('productAttribut', 'jshop');
            $product_attr->load($product_attr_id);

            if ($product_attr->ext_attribute_product_id) {
                $product_id = $product_attr->ext_attribute_product_id;
            } else {
                $product = JSFactory::getTable('product', 'jshop');
                $product->parent_id = $product_attr->product_id;
                $product->store();
                $product_id = $product->product_id;
                $product_attr->ext_attribute_product_id = $product_id;
                $product_attr->store();
            }
        }
		
		if ($product_id && $this->vendorId && !$product_attr_id){
            checkAccessVendorToProduct($this->vendorId, $product_id);         	
        }
        
        if ($product_id > 0) {
            setMetaData(_JSHOP_VF_EDIT_PRODUCT, _JSHOP_VF_EDIT_PRODUCT, _JSHOP_VF_EDIT_PRODUCT);
            appendPathWay(_JSHOP_VF_EDIT_PRODUCT);
        } else {
            setMetaData(_JSHOP_ADD_PRODUCT, _JSHOP_ADD_PRODUCT, _JSHOP_ADD_PRODUCT);
            appendPathWay(_JSHOP_ADD_PRODUCT);
        }

        $vndConfig = $addon_vendor->getVendorsConfig();        
        $products = $this->getModel('products');        
        $product = JTable::getInstance('product', 'jshop');
        $product->load($product_id);
        $post['vendor_id'] = $this->vendorId;
                
        $_productprice = JTable::getInstance('productPrice', 'jshop');
        $product->product_add_prices = $_productprice->getAddPrices($product_id);        
        $product->product_add_prices = array_reverse($product->product_add_prices);
        $name = $lang->get("name");
        $product->name = $product->$name;
        
        $_lang = $this->getModel('languages');		        
        $languages = $_lang->getAllLanguages(1);        
        $multilang = count($languages) > 1;
        
        $nofilter = array();
        JFilterOutput::objectHTMLSafe( $product, ENT_QUOTES, $nofilter);

        $edit = intval($product_id);
        
        if (!$product_id) {
            $rows = array();
            $product->product_quantity = 1;
            $product->product_publish = 1;
        }
        
        $_tax = $this->getModel('taxes');
        $all_taxes = $_tax->getAllTaxes();
        
        if($edit){
            $images = $product->getImages();
            $videos = $product->getVideos();
            $files  = $product->getFiles();            
            $categories_select = $product->getCategories();
            $categories_select_list = array();
            if (count($categories_select) > 0)
            foreach($categories_select as $v){
                $categories_select_list[] = $v->category_id;
            }
            $related_products = $products->getRelatedProducts($product_id);   
        } else {
            $images = array();
            $videos = array();
            $files = array();
            $categories_select_list = array();
            $categories_select = null;
            $related_products = array();
        }
        
        if ($jshopConfig->tax){
            $list_tax = array();
            foreach ($all_taxes as $tax){
                $list_tax[] = JHTML::_('select.option', $tax->tax_id, $tax->tax_name . ' (' . $tax->tax_value . '%)','tax_id','tax_name');
            }
            $withouttax = 0;
        }else{
            $withouttax = 1;
        }
        
        $categories = buildTreeCategory(0);
        if (count($categories) == 0) JError::raiseNotice(0, _JSHOP_PLEASE_ADD_CATEGORY);
        $lists['images'] = $images;
        $lists['videos'] = $videos;
        $lists['files'] = $files;

        $manuf1 = array();
        $manuf1[0] = new stdClass();
        $manuf1[0]->manufacturer_id = '0';
        $manuf1[0]->name = _JSHOP_VF_NONE;

        $_manufacturer =$this->getModel('manufacturers');
        $manufs = $_manufacturer->getAllManufacturers(0);
        $manufs = array_merge($manuf1, $manufs);
        
        //Attributes
        $_attribut = $this->getModel('attribut');
        $list_all_attributes = $_attribut->getAllAttributes(2, $categories_select_list);

        $_attribut_value = $this->getModel('attributValue');
        $lists['attribs'] = $product->getAttributes();
        $lists['ind_attribs'] = $product->getAttributes2();
        $lists['attribs_values'] = $_attribut_value->getAllAttributeValues(2);
        $all_attributes = $list_all_attributes['dependent'];
        
        $lists['ind_attribs_gr'] = array();
        if (count($lists['ind_attribs']) > 0)
        foreach($lists['ind_attribs'] as $v){
            $lists['ind_attribs_gr'][$v->attr_id][] = $v;
        }
          
        $first = array();
        $first[] = JHTML::_('select.option', '0',_JSHOP_SELECT, 'value_id','name');
        
        if (count($all_attributes) > 0)
        foreach ($all_attributes as $key => $value){
            $values_for_attribut = $_attribut_value->getAllValues($value->attr_id);
            $all_attributes[$key]->values_select = JHTML::_('select.genericlist', array_merge($first, $values_for_attribut),'value_id['.$value->attr_id.']','class = "inputbox" size = "5" multiple="multiple" id = "value_id_'.$value->attr_id.'"','value_id','name');
            $all_attributes[$key]->values = $values_for_attribut;
        }  
        
        $lists['all_attributes'] = $all_attributes;
        $product_with_attribute = (count($lists['attribs']) > 0);
        
        //independent attribute
        $all_independent_attributes = $list_all_attributes['independent'];
        
        $price_modification = array();
        $price_modification[] = JHTML::_('select.option', '+','+', 'id','name');
        $price_modification[] = JHTML::_('select.option', '-','-', 'id','name');
        $price_modification[] = JHTML::_('select.option', '*','*', 'id','name');
        $price_modification[] = JHTML::_('select.option', '/','/', 'id','name');
        $price_modification[] = JHTML::_('select.option', '=','=', 'id','name');
        $price_modification[] = JHTML::_('select.option', '%','%', 'id','name');
        
 		if (count($all_independent_attributes) > 0)
        foreach ($all_independent_attributes as $key => $value){
            $values_for_attribut = $_attribut_value->getAllValues($value->attr_id);            
            $all_independent_attributes[$key]->values_select = JHTML::_('select.genericlist', array_merge($first, $values_for_attribut),'attr_ind_id_tmp_'.$value->attr_id.'','class = "inputbox"','value_id','name');
            $all_independent_attributes[$key]->values = $values_for_attribut;
            $all_independent_attributes[$key]->price_modification_select = JHTML::_('select.genericlist', $price_modification,'attr_price_mod_tmp_'.$value->attr_id.'','class = "inputbox" style = "width: 110px; margin-left: 5px; margin-right: 5px;"','id','name');
            $all_independent_attributes[$key]->submit_button = '<input type = "button" onclick = "addVendorAttributValue2('.$value->attr_id.');" value = "'._JSHOP_VF_ADD_ATTRIBUT.'" />';
        }        
        $lists['all_independent_attributes'] = $all_independent_attributes;
        // End work with attributes and values
                                   
        // delivery Times
        if ($jshopConfig->admin_show_delivery_time) {
            $_deliveryTimes = $this->getModel('deliveryTimes');  
            $all_delivery_times = $_deliveryTimes->getDeliveryTimes();                
            $all_delivery_times0 = array();
            $all_delivery_times0[0] = new stdClass();
            $all_delivery_times0[0]->id = '0';
            $all_delivery_times0[0]->name = _JSHOP_VF_NONE;        
            $lists['deliverytimes'] = JHTML::_('select.genericlist', array_merge($all_delivery_times0, $all_delivery_times),'delivery_times_id','class = "inputbox" size = "1"','id','name',$product->delivery_times_id);        
        }
        //
        
        // units
        $_units = $this->getModel('units');
        $allunits = $_units->getUnits();
        if ($jshopConfig->admin_show_product_basic_price) {
                    
            $lists['basic_price_units'] = JHTML::_('select.genericlist', $allunits, 'basic_price_unit_id','class = "inputbox" size = "1"','id','name',$product->basic_price_unit_id);
        }
        if (!$product->add_price_unit_id) $product->add_price_unit_id = $jshopConfig->product_add_price_default_unit;
        $lists['add_price_units'] = JHTML::_('select.genericlist', $allunits, 'add_price_unit_id','class = "inputbox" style = "width: 70px;"','id','name', $product->add_price_unit_id);
        //
       
        // product labels
        if ($jshopConfig->admin_show_product_labels) {
            $_labels = $this->getModel('productLabels');  
            $alllabels = $_labels->getList();
            $first = array();
            $first[] = JHTML::_('select.option', '0',_JSHOP_SELECT, 'id','name');        
            $lists['labels'] = JHTML::_('select.genericlist', array_merge($first, $alllabels), 'label_id','class = "inputbox" size = "1"','id','name',$product->label_id);
        }
        //

        // access rights
        $accessgroups = getAccessGroups();        
        $lists['access'] = JHTML::_('select.genericlist', $accessgroups, 'access','class = "inputbox" size = "1"','id','title', $product->access);

        //currency
        $current_currency = $product->currency_id;
        if (!$current_currency) $current_currency = $jshopConfig->mainCurrency;
        $_currency = $this->getModel('currencies'); 
        $currency_list = $_currency->getAllCurrencies();
        $lists['currency'] = JHTML::_('select.genericlist', $currency_list, 'currency_id','class = "inputbox"','currency_id','currency_code', $current_currency);

        //product extra field
        if ($jshopConfig->admin_show_product_extra_field) {
            $categorys_id = array();
            if (is_array($categories_select) && count($categories_select) > 0){
                foreach($categories_select as $tmp){
                    $categorys_id[] = $tmp->category_id;
                }        
            }
            $tmpl_extra_fields = $this->_getHtmlProductExtraFields($categorys_id, $product);
           
            $lists['extra_fields'] =  $tmpl_extra_fields;
        }
        //
        
        //free attribute
        if ($jshopConfig->admin_show_freeattributes){
            $_freeattributes = $this->getModel('freeattribut');        
            $listfreeattributes = $_freeattributes->getAll();
            $activeFreeAttribute = $product->getListFreeAttributes();
            $listIdActiveFreeAttribute = array();
	
            if (count($activeFreeAttribute) > 0)
            foreach($activeFreeAttribute as $_obj){
                $listIdActiveFreeAttribute[] = $_obj->id;
            } 
            if (count($listfreeattributes) > 0)           
            foreach($listfreeattributes as $k=>$v){
                if (in_array($v->id, $listIdActiveFreeAttribute)){
                    $listfreeattributes[$k]->pactive = 1;
                }
            }
            $lists['freeattributes'] = $listfreeattributes; 
        }
         
        $lists['manufacturers'] = JHTML::_('select.genericlist', $manufs,'product_manufacturer_id','class = "inputbox" size = "1"','manufacturer_id','name',$product->product_manufacturer_id);
        
        $tax_value = 0;
        if (count($all_taxes) > 0)
        foreach ($all_taxes as $tax){
            if ($tax->tax_id == $product->product_tax_id){
                $tax_value = $tax->tax_value;
                break; 
            }
        }
        
        if ($product_id){
            $product->product_price = round($product->product_price, $jshopConfig->product_price_precision);
             if ($jshopConfig->display_price_admin==0){
                $product->product_price2 = round($product->product_price / (1 + $tax_value / 100), $jshopConfig->product_price_precision);
            }else{
                $product->product_price2 = round($product->product_price * (1 + $tax_value / 100), $jshopConfig->product_price_precision);
            }
        } else {
            $product->product_price2 = '';
        }
        
        $category_select_onclick = "";
        if ($vndConfig->show_product_extra_field) $category_select_onclick = 'onclick="addon_vendor_reloadProductExtraField(\''.$product_id.'\')"';
        if ($jshopConfig->tax){
            $lists['tax'] = JHTML::_('select.genericlist', $list_tax,'product_tax_id','class = "inputbox" size = "1" onchange = "updatePrice2('.$jshopConfig->display_price_admin.');"','tax_id','tax_name',$product->product_tax_id);
        }
        //$lists['categories'] = JHTML::_('select.genericlist', $categories,'category_id[]','class="inputbox" size="10" multiple = "multiple" '.$category_select_onclick,'category_id','name',$categories_select);
        $lists['categories'] = JHTML::_('select.genericlist', $categories,'category_id[]','class="inputbox" size="1" '.$category_select_onclick,'category_id','name',$categories_select);
        $lists['templates'] = getTemplates('product', $product->product_template);
        
        $dispatcher->trigger('onVendorBeforeDisplayEditProduct', array(&$product, &$related_products, &$lists, &$listfreeattributes, &$tax_value));
        
		$view_name = "addon_vendor";
        $view_config = array("template_path"=>JPATH_COMPONENT."/templates/".$this->va_template."/".$view_name);
        $view = $this->getView($view_name, 'html', '', $view_config);
        $view->setLayout("default"); 
        $view->assign('product', $product);
        $view->assign('lists', $lists);
        $view->assign('related_products', $related_products);
        $view->assign('edit', $edit);
        $view->assign('product_with_attribute', $product_with_attribute);
        $view->assign('tax_value', $tax_value);
        $view->assign('languages', $languages);
        $view->assign('multilang', $multilang);
        $view->assign('tmpl_extra_fields', $tmpl_extra_fields);
        $view->assign('withouttax', $withouttax);
        $view->assign('listfreeattributes', $listfreeattributes);
        $view->assign('vndconfig', $vndConfig);
        $dispatcher->trigger('onVendorBeforeDisplayEditProductView', array(&$view) );
        $view->display();    
    }
    
    function ocLog($filename, $data, $append=false)
    {
        $MyJConfig = new JConfig();
        if(!$append){
            file_put_contents($MyJConfig->log_path . '/'. $filename . '.txt', var_export($data,true));
        }else{
            file_put_contents($MyJConfig->log_path . '/'. $filename . '.txt', var_export($data,true).PHP_EOL, FILE_APPEND);
        }
        
    }
    
    function get_product_extra_fields(){
        echo 'test get_product_extra_fields 123!!!';
        exit;
    }
    
    function _getHtmlProductExtraFields($categorys, $product){
    
        $this->ocLog('categorys_log', $categorys, false);
        $this->ocLog('product_log', $product, false);
        
        $_productfields = $this->getModel("productFields");
        $list = $_productfields->getList(1);
        
        $_productfieldvalues = $this->getModel("productFieldValues");
        $listvalue = $_productfieldvalues->getAllList();
        $f_option = array();
        $f_option[] = JHTML::_('select.option', 0, " - - - ", 'id', 'name');
        
        $fields = array();
        if (count($list) > 0)
        foreach($list as $v){
            $insert = 0;
            if ($v->allcats==1){
                $insert = 1;
            }else{
                $cats = unserialize($v->cats);
                if (count($categorys) > 0)
                foreach($categorys as $catid){
                    if (in_array($catid, $cats)) $insert = 1;
                }
            }
            
            if ($insert){
                $obj = new stdClass();
                $obj->id = $v->id;
                $obj->name = $v->name;
                $obj->groupname = $v->groupname;
                $tmp = array();
                if (count($listvalue) > 0)
                foreach($listvalue as $lv){
                    if ($lv->field_id == $v->id) $tmp[] = $lv;
                }                
                $name = 'extra_field_'.$v->id;
                if ($v->type==0){
                    if ($v->multilist==1){
                        $attr = 'multiple="multiple" size="10"';
                    }else{
                        $attr = "";
                    }
                    $obj->values = JHTML::_('select.genericlist', array_merge($f_option, $tmp), 'productfields['.$name.'][]', $attr, 'id', 'name', explode(',',$product->$name));
                }else{
                    $obj->values = "<input type='text' name='".$name."' value='".$product->$name."' />";
                }
                $fields[] = $obj;
            }
        }  
        $view_name = "addon_vendor";
        $view_config = array("template_path"=>JPATH_COMPONENT."/templates/".$this->va_template."/".$view_name);
        $view = $this->getView($view_name, 'html', '', $view_config);
        
        $view->setLayout("extrafields_inner");
        $view->assign('fields', $fields);
        $dispatcher = JDispatcher::getInstance();
        $dispatcher->trigger('onVendorBeforeLoadTemplateHtmlProductExtraFields', array(&$view));
        return $view->loadTemplate();     
    }
    
    
    function save_product() {
        $addon_vendor = $this->getModel("addon_vendor");
	    $addon_vendor->checkUserSeller();
        $addon_vendor->checkTasksAccess('addProduct');
        
        JPluginHelper::importPlugin('jshoppingadmin');
        $dispatcher = JDispatcher::getInstance();
        
        $jshopConfig = JSFactory::getConfig();
        require_once($jshopConfig->path.'lib/image.lib.php');
        require_once($jshopConfig->path.'lib/uploadfile.class.php');
       
        $vendorInf = $addon_vendor->getVendorByUser();
        $vndConfig = $addon_vendor->getVendorsConfig();

        $post = JRequest::get('post');
        
        $_language = JFactory::getLanguage();
        
        $_products = $this->getModel("products");
        
        $product = JTable::getInstance('product', 'jshop');

        $_alias = $this->getModel("alias");
        $_lang = $this->getModel("languages");
        
        if ($this->vendorId && $post['product_id'] && !$post['parent_id']){
            checkAccessVendorToProduct($this->vendorId, $post['product_id']);
        }

		$post['product_attr_id'] = null;
        $post['different_prices'] = 0;
        if (isset($post['product_is_add_price']) && $post['product_is_add_price']) $post['different_prices'] = 1;

		$post['product_publish'] = 0;
        if (!isset($post['vendor_publish'])) $post['vendor_publish'] = 0;
        
		if (!$post['product_id']) {
			if ($post['vendor_publish'] && $vendorInf->auto_publish){
                $post['product_publish'] = 1;
            }
            if (!$post['vendor_publish'] && $vendorInf->auto_publish){
                $post['vendor_publish'] = -1;
            }
		} else {
			if ($post['vendor_publish'] && $vndConfig->products_auto_publish_modify){
                $post['product_publish'] = 1;
            }
            if (!$post['vendor_publish'] && $vndConfig->products_auto_publish_modify){
                $post['vendor_publish'] = -1;
            }
		}
		
        if (!isset($post['attrib_buy_price'])) $post['attrib_buy_price'] = null;
        if (!isset($post['product_is_add_price'])) $post['product_is_add_price'] = 0;
        if (!isset($post['unlimited'])) $post['unlimited'] = 0;
        $post['product_price'] = saveAsPrice($post['product_price']);
        $post['product_old_price'] = saveAsPrice($post['product_old_price']);
        if (isset($post['product_buy_price']))
            $post['product_buy_price'] = saveAsPrice($post['product_buy_price']);
        else 
            $post['product_buy_price'] = null;
        $post['product_weight'] = saveAsPrice($post['product_weight']);
        if(!isset($post['related_products'])){
            $post['related_products'] = array();
        }
        if (!$post['product_id']){
            $post['product_date_added'] = date("Y-m-d H:i:s");
        }
        if (!isset($post['attrib_price'])) $post['attrib_price'] = null;
        if (!isset($post['attr_count'])) $post['attr_count'] = null;
        if (!isset($post['attrib_ind_id'])) $post['attrib_ind_id'] = null;
        if (!isset($post['attrib_ind_price'])) $post['attrib_ind_price'] = null;
        if (!isset($post['attrib_ind_price_mod'])) $post['attrib_ind_price_mod'] = null;
        if (!isset($post['freeattribut'])) $post['freeattribut'] = null;
        $post['date_modify'] = date("Y-m-d H:i:s");
        $post['edit'] = intval($post['product_id']);
        if (!isset($post['product_add_discount'])) $post['product_add_discount'] = 0;
        $post['min_price'] = $_products->getMinimalPrice($post['product_price'], $post['attrib_price'], array($post['attrib_ind_id'], $post['attrib_ind_price_mod'], $post['attrib_ind_price']), $post['product_is_add_price'], $post['product_add_discount']);
        
        if ($this->vendorId) $post['vendor_id'] = $this->vendorId;
         
        if (is_array($post['attr_count'])){
            $qty = 0;
            if (count($post['attr_count']) > 0)
            foreach($post['attr_count'] as $_qty) {
                if ($_qty > 0) $qty += $_qty;
            }
            $post['product_quantity'] = $qty;
        }
        
        if (!$post['product_id']){
            $post['commission'] = $vndConfig->product_commission;
            $post['commission_type'] = $vndConfig->product_commission_type;
        }
        
        if ($post['unlimited']){
            $post['product_quantity'] = 1;
        }
        if (isset($post['productfields']) && is_array($post['productfields'])){
            foreach($post['productfields'] as $productfield=>$val){
                if (is_array($val)){
                    $post[$productfield] = implode(',', $val);
                }
            }
        }
        
        if (is_array($post['attrib_price'])){
            if (count(array_unique($post['attrib_price'])) > 1) $post['different_prices'] = 1;
        }
        
        if (is_array($post['attrib_ind_price'])){
            $tmp_attr_ind_price = array();
            if (count($post['attrib_ind_price']) > 0)
            foreach($post['attrib_ind_price'] as $k=>$v){
                $tmp_attr_ind_price[] = $post['attrib_ind_price_mod'][$k].$post['attrib_ind_price'][$k];
            }
            if (count(array_unique($tmp_attr_ind_price))>1) $post['different_prices'] = 1;
        }
                
        $languages = $_lang->getAllLanguages(1);
        if (count($languages) > 0)
        foreach($languages as $lang){
            $post['name_'.$lang->language] = trim($post['name_'.$lang->language]);            
            if ($jshopConfig->create_alias_product_category_auto && $post['alias_'.$lang->language]=="") $post['alias_'.$lang->language] = $post['name_'.$lang->language];
            $post['alias_'.$lang->language] = JApplication::stringURLSafe($post['alias_'.$lang->language]);
            if ($post['alias_'.$lang->language]!="" && !$_alias->checkExistAlias2Group($post['alias_'.$lang->language], $lang->language, $post['product_id'])){
                $post['alias_'.$lang->language] = "";
                JError::raiseWarning("", _JSHOP_VF_ERROR_ALIAS_ALREADY_EXIST);
            }            
            $post['description_'.$lang->language] = JRequest::getVar('description'.$lang->id,'','post',"string", 2);
            $post['short_description_'.$lang->language] = JRequest::getVar('short_description_'.$lang->language,'','post',"string", 2);
        }
        
        $dispatcher->trigger('onVendorBeforeDisplaySaveProduct', array(&$post, &$product) );
        
        if (!$product->bind($post)) {
            JError::raiseWarning("",_JSHOP_VF_ERROR_BIND);
            $this->setRedirect(SEFLink("index.php?option=com_jshopping&controller=addon_vendor&task=edit_product&product_id=".$post['product_id'], 1, 1));
            return 0;
        }
        
        if (($product->min_price==0 || $product->product_price==0) && !$jshopConfig->user_as_catalog && $product->parent_id == 0){
            JError::raiseNotice("", _JSHOP_VF_YOU_NOT_SET_PRICE);    
        }
        
        if (isset($post['set_main_image'])) {
            $image = JTable::getInstance('image', 'jshop');
            $image->load($post['set_main_image']);
            if($image->image_id) {
                $product->image = $image->image_name;
            }
        }
        
        if (!$product->store()){
            JError::raiseWarning("",_JSHOP_VF_ERROR_SAVE_DATABASE."<br>".$product->_error);
            $this->setRedirect(SEFLink("index.php?option=com_jshopping&controller=addon_vendor&task=edit_product&product_id=".$post['product_id'], 1, 1));
            return 0;
        }
        
        $dispatcher->trigger('onVendorAfterSaveProduct', array(&$product));

        $product_id = $product->product_id;

        if ($vndConfig->show_product_video && $product->parent_id==0){
            $_products->uploadVideo($product, $product_id, $post);
        }
        
        if ($vndConfig->show_product_image){
            $_products->uploadImages($product, $product_id, $post);
        }

        if ($vndConfig->show_product_files){
            $_products->uploadFiles($product, $product_id, $post);
        }
        
        if ($vndConfig->show_attributes){
            $_products->saveAttributes($product, $product_id, $post);
        }
        
        if ($vndConfig->show_freeattributes){
            $_products->saveFreeAttributes($product_id, $post['freeattribut']);
        }
        
        if (!isset($post['quantity_start'])) $post['quantity_start'] = null;
        if (!isset($post['quantity_finish'])) $post['quantity_finish'] = null;
        
        if ($post['product_is_add_price']){
            $_products->saveAditionalPrice($product_id, $post['product_add_discount'], $post['quantity_start'], $post['quantity_finish']);
        }
        
        if ($product->parent_id == 0){
            $_products->setCategoryToProduct($product_id, $post['category_id']);
        }
        
        if ($vndConfig->show_product_related){
            $_products->saveRelationProducts($product, $product_id, $post);
        }
        
        $dispatcher->trigger('onVendorAfterSaveProductEnd', array($product->product_id) );
        
        if ($product->parent_id != 0){
            print "<script type='text/javascript'>window.close();</script>";            
            die();
        }

        if ((($vndConfig->informed_about_new_product == 2) || ($vndConfig->informed_about_new_product == 1 && $post['product_publish'] == 0))){
            $mainframe = JFactory::getApplication();
            $mailfrom = $mainframe->getCfg( 'mailfrom' );
            $fromname = $mainframe->getCfg( 'fromname' );

            $currency = JTable::getInstance('currency', 'jshop');
            $currency->load($post['currency_id']);
            $post['currency_code'] = $currency->currency_code;
        
            $view_name = "addon_vendor";
            $view_config = array("template_path"=>JPATH_COMPONENT."/templates/".$this->va_template."/".$view_name);
            $view = $this->getView($view_name, 'html', '', $view_config);
            $view->setLayout("admin_notify");
            $view->assign('post', $post);
            $view->assign('vendor', $vendorInf);
            $view->assign('lang', $_language->getTag());
            
            $message = $view->loadTemplate();
            if (empty($post['product_id'])){
                $subject = _JSHOP_NEW_VENDOR_PRODUCT . ". ". _JSHOP_VENDOR . ": " . $vendorInf->f_name . " " . $vendorInf->l_name;
            } else {
                $subject = _JSHOP_EDIT_VENDOR_PRODUCT . ". ". _JSHOP_VENDOR . ": " . $vendorInf->f_name . " " . $vendorInf->l_name;
            }
            
            JFactory::getMailer()->sendMail($mailfrom, $fromname, $jshopConfig->contact_email, $subject, $message, 0);	
        }

        $this->setRedirect(SEFLink("index.php?option=com_jshopping&controller=addon_vendor&task=products", 1, 1), _JSHOP_VF_PRODUCT_SAVED);
    }
    
    function remove(){
        $addon_vendor = $this->getModel("addon_vendor");
        $addon_vendor->checkUserSeller();
		    	
        $jshopConfig = JSFactory::getConfig();
        $db = JFactory::getDBO();
        $text = array();
        $cid = JRequest::getVar('cid'); 
        $post['vendor_id'] = $this->vendorId;
        
        if (count($cid) > 0)
        foreach ($cid as $key => $value) {
            
            if ($value && $this->vendorId){
            checkAccessVendorToProduct($this->vendorId, $value);         	
            }            
            
            $product = JTable::getInstance('product', 'jshop');
            $product->load($value);
            if ($product->vendor_id != $this->vendorId) continue;
            $query = "DELETE FROM `#__jshopping_products` WHERE `product_id` = '" . $db->escape($value) . "'";
            $db->setQuery($query);
            $db->query();

            $query = "DELETE FROM `#__jshopping_products_attr` WHERE `product_id` = '" . $db->escape($value) . "'";
            $db->setQuery($query);
            $db->query();
            
            $query = "DELETE FROM `#__jshopping_products_attr2` WHERE `product_id` = '" . $db->escape($value) . "'";
            $db->setQuery($query);
            $db->query();
            
            $query = "DELETE FROM `#__jshopping_products_prices` WHERE `product_id` = '".$db->escape($value)."'";
            $db->setQuery($query);
            $db->query();
            
            $query = "DELETE FROM `#__jshopping_products_relations` WHERE `product_id` = '" . $db->escape($value) . "' OR `product_related_id` = '" . $db->escape($value) . "'";
            $db->setQuery($query);
            $db->query();

            $query = "DELETE FROM `#__jshopping_products_to_categories` WHERE `product_id` = '" . $db->escape($value) . "'";
            $db->setQuery($query);
            $db->query();

            $images = $product->getImages();
            $videos = $product->getVideos();
            $files = $product->getFiles();

            if(count($images)) {
                foreach ($images as $image){
                    $query = "select count(*) as k from #__jshopping_products_images where image_name='".$db->escape($image->image_name)."' and product_id!='".$db->escape($value)."'";                    
                    $db->setQuery($query);
                    if (!$db->loadResult()){
                        @unlink($jshopConfig->image_product_path . "/" . $image->image_thumb);
                        @unlink($jshopConfig->image_product_path . "/" . $image->image_full);
                        @unlink($jshopConfig->image_product_path . "/" . $image->image_name);
                    }
                }
            }
            
            $query = "DELETE FROM `#__jshopping_products_images` WHERE `product_id` = '" . $db->escape($value) . "'";
            $db->setQuery($query);
            $db->query();

            if(count($videos)) {
                foreach ($videos as $video) {
                    $query = "select count(*) as k from #__jshopping_products_videos where video_name='".$db->escape($video->video_name)."' and product_id!='".$db->escape($value)."'";                    
                    $db->setQuery($query);
                    if (!$db->loadResult()){
                        @unlink($jshopConfig->video_product_path . "/" . $video->video_name);
                        if ($video->video_preview){
                            @unlink($jshopConfig->video_product_path . "/" . $video->video_preview);
                        }
                    }
                }
            }
            
            $query = "DELETE FROM `#__jshopping_products_videos` WHERE `product_id` = '" . $db->escape($value) . "'";
            $db->setQuery($query);
            $db->query();
            
            if (count($files)){
                foreach($files as $file){
                    $query = "select count(*) as k from #__jshopping_products_files where demo='".$db->escape($file->demo)."' and product_id!='".$db->escape($value)."'";
                    $db->setQuery($query);
                    if (!$db->loadResult()){
                        @unlink($jshopConfig->demo_product_path."/".$file->demo);
                    }
                    
                    $query = "select count(*) as k from #__jshopping_products_files where file='".$db->escape($file->file)."' and product_id!='".$db->escape($value)."'";
                    $db->setQuery($query);
                    if (!$db->loadResult()){
                        @unlink($jshopConfig->files_product_path."/".$file->file);
                    }            
                }
            }
            
            $query = "DELETE FROM `#__jshopping_products_files` WHERE `product_id` = '" . $db->escape($value) . "'";
            $db->setQuery($query);
            $db->query();
            
            $text[]= sprintf(_JSHOP_VF_PRODUCT_DELETED, $value)."<br>";
        }

        $this->setRedirect(SEFLink("index.php?option=com_jshopping&controller=addon_vendor&task=products", 1, 1), implode("</li><li>",$text));
    }    
    
    function publish(){
        $addon_vendor = $this->getModel("addon_vendor");
	    $addon_vendor->checkUserSeller();
		    	
        $this->_publishProduct(1);
        $this->setRedirect(SEFLink("index.php?option=com_jshopping&controller=addon_vendor&task=products", 1, 1));
    }
    
    function unpublish(){
        $addon_vendor = $this->getModel("addon_vendor");
        $addon_vendor->checkUserSeller();
		    	
        $this->_publishProduct(0);
        $this->setRedirect(SEFLink("index.php?option=com_jshopping&controller=addon_vendor&task=products", 1, 1));
    }
    
	function pause(){
        $addon_vendor = $this->getModel("addon_vendor");
        $addon_vendor->checkUserSeller();
		    	
        $this->_publishProduct2(-1,0);
        $this->setRedirect(SEFLink("index.php?option=com_jshopping&controller=addon_vendor&task=products", 1, 1));
    }
	
	function resume(){
        $addon_vendor = $this->getModel("addon_vendor");
        $addon_vendor->checkUserSeller();
		    	
        $this->_publishProduct2(1,1);
        $this->setRedirect(SEFLink("index.php?option=com_jshopping&controller=addon_vendor&task=products", 1, 1));
    }		    
    
    function _publishProduct($flag) {
        $addon_vendor = $this->getModel("addon_vendor");
	    $addon_vendor->checkUserSeller();
		    	
        $db = JFactory::getDBO();
        $cid = (array)JRequest::getVar('cid');
        $post['vendor_id'] = $this->vendorId;

        foreach ($cid as $key => $value) {
            $query = "UPDATE `#__jshopping_products`
                       SET `vendor_publish` = '" . $db->escape($flag) . "'
                       WHERE `product_id` = '" . $db->escape($value) . "' AND `vendor_id`='".$db->escape($this->vendorId)."' AND `block`='0'";
            $db->setQuery($query);
            $db->query();
        }
    }
	
    function _publishProduct2($flag,$flag2) {
        $addon_vendor = $this->getModel("addon_vendor");
	    $addon_vendor->checkUserSeller();
		    	
        $db = JFactory::getDBO();
        $cid = (array)JRequest::getVar('cid');
        $post['vendor_id'] = $this->vendorId;
		        
        if ($flag2 == 0){$flag2last = 1;}else{$flag2last=0;}
        
        foreach ($cid as $key => $value) {
            $query = "UPDATE `#__jshopping_products`
                       SET `vendor_publish` = '" . $db->escape($flag) . "',
                       `product_publish` = '" . $db->escape($flag2) . "'
                       WHERE `product_id` = '" . $db->escape($value) . "' AND `vendor_id`='".$db->escape($this->vendorId)."' AND `vendor_publish`='".$db->escape((-$flag))."' AND `product_publish`='".$db->escape($flag2last)."' AND `block`='0'";
            $db->setQuery($query);
            $db->query();
        }
    }
    
    function product_extra_fields(){
        $product_id = JRequest::getInt("product_id");
        $cat_id = JRequest::getVar("cat_id");
        $product = JTable::getInstance('product', 'jshop');
        $product->load($product_id);
        
        $categorys = array();
        if (is_array($cat_id)){
            foreach($cat_id as $cid){
                $categorys[] = intval($cid);        
            }
        }        
        
        print $this->_getHtmlProductExtraFields($categorys, $product);
        die();    
    }    

    function search_related(){
        $addon_vendor = $this->getModel("addon_vendor");
	    $addon_vendor->checkUserSeller();
    
        $jshopConfig = JSFactory::getConfig();        
        $products  = $this->getModel("products");
        $text_search = JRequest::getVar("text");
        $limitstart = JRequest::getInt("start");
        $no_id = JRequest::getInt("no_id");
        $limit = 20;
        
        $filter = array(
            "without_product_id" => $no_id, 
            "text_search" => $text_search, 
            "vendor_id" => $this->vendorId,
            "category_id" => 0,
            "manufacturer_id" => 0,
            "label_id" => 0,
            "publish" => 0
        );
        $total = $products->getCountAllProducts($filter);
        $rows = $addon_vendor->getAllProducts($filter, $limitstart, $limit);
        $page = ceil($total/$limit);

		$view_name = "addon_vendor";
        $view_config = array("template_path"=>JPATH_COMPONENT."/templates/".$this->va_template."/".$view_name);
        $view = $this->getView($view_name, 'html', '', $view_config);
        $view->setLayout("product_list_search_related");
        $view->assign('rows', $rows);
        $view->assign('config', $jshopConfig);
        $view->assign('limit', $limit);
        $view->assign('pages', $page);
        $view->assign('no_id', $no_id);
        $view->display();
        die();
    }        
     
    function delete_foto(){
        $addon_vendor = $this->getModel("addon_vendor");
	    $addon_vendor->checkUserSeller();
		    	
        $image_id = JRequest::getInt("id");
        $jshopConfig = JSFactory::getConfig();
        $db = JFactory::getDBO();
        
        $query = "SELECT prod.`vendor_id` FROM `#__jshopping_products_images` AS images
        		  LEFT JOIN `#__jshopping_products` AS prod ON prod.`product_id` = images.`product_id` 
        		  WHERE `image_id` = $db->escape($image_id) LIMIT 1";
        $db->setQuery($query);
        $vendor_id = $db->loadResult();
        
        if ($this->vendorId == $vendor_id){
	        $query = "SELECT * FROM `#__jshopping_products_images` WHERE image_id = '" . $db->escape($image_id) . "'";
	        $db->setQuery($query);
	        $row = $db->loadObject();
	        
	        $query = "DELETE FROM `#__jshopping_products_images` WHERE `image_id` = '" . $db->escape($image_id) . "'";
	        $db->setQuery($query);
	        $db->query();
	
	        $query = "select count(*) as k from #__jshopping_products_images where image_name='".$db->escape($row->image_name)."' and product_id!='".$db->escape($row->product_id)."'";                    
	        $db->setQuery($query);
	        if (!$db->loadResult()){        
	            @unlink(getPatchProductImage($row->image_name,'thumb',2));
	            @unlink(getPatchProductImage($row->image_name,'',2));
	            @unlink(getPatchProductImage($row->image_name,'full',2));
	        }
	        
	        $product = JTable::getInstance('product', 'jshop');
	        $product->load($row->product_id);
	        if ($product->image == $row->image_name){
	            $product->image = '';
	            $list_images = $product->getImages();
	            if (count($list_images)){
	                $product->image = $list_images[0]->image_name;
	            } 
	            $product->store();
	        }
        } else {
        	print "Access denied";
        }
	    die();
    }

    function delete_video(){
        $addon_vendor = $this->getModel("addon_vendor");
	    $addon_vendor->checkUserSeller();
		    	
        $video_id = JRequest::getInt("id");
        $jshopConfig = JSFactory::getConfig();
        $db = JFactory::getDBO();
        
        $query = "SELECT prod.`vendor_id` FROM `#__jshopping_products_videos` AS video
        		  LEFT JOIN `#__jshopping_products` AS prod ON prod.`product_id` = video.`product_id` 
        		  WHERE `video_id` = $video_id LIMIT 1";
        $db->setQuery($query);
        $vendor_id = $db->loadResult();
        
        if ($this->vendorId == $vendor_id){
	        $query = "SELECT * FROM `#__jshopping_products_videos` WHERE video_id = '" . $db->escape($video_id) . "'";
	        $db->setQuery($query);
	        $row = $db->loadObject();
	        
	        $query = "select count(*) from #__jshopping_products_videos where video_name='".$db->escape($row->video_name)."' and product_id!='".$db->escape($row->product_id)."'";                    
	        $db->setQuery($query);
	        if (!$db->loadResult()){
	            @unlink($jshopConfig->video_product_path . "/" . $row->video_name);
	            if ($row->video_preview){
	                @unlink($jshopConfig->video_product_path . "/" . $row->video_preview);
	            }
	        }
	
	        $query = "DELETE FROM `#__jshopping_products_videos` WHERE `video_id` = '" . $db->escape($video_id) . "'";
	        $db->setQuery($query);
	        $db->query();
        } else {
        	print "Access denied";
        }
        die();
    }
    
    function delete_file(){
        $addon_vendor = $this->getModel("addon_vendor");
        $addon_vendor->checkUserSeller();
		    	
        $jshopConfig = JSFactory::getConfig();
        $db = JFactory::getDBO();
        $id = JRequest::getInt("id");
        $type = JRequest::getVar("type");
        
        $query = "SELECT prod.`vendor_id` FROM `#__jshopping_products_files` AS files
        		  LEFT JOIN `#__jshopping_products` AS prod ON prod.`product_id` = files.`product_id` 
        		  WHERE `id` = $id LIMIT 1";
        $db->setQuery($query);
        $vendor_id = $db->loadResult();
        
        if ($this->vendorId == $vendor_id){
	        $query = "SELECT * FROM `#__jshopping_products_files` WHERE `id` = '" . $db->escape($id) . "'";
	        $db->setQuery($query);
	        $row = $db->loadObject();
	        
	        $delete_row = 0;
	                
	        if ($type=="demo"){
	            if ($row->file==""){
	                $query = "DELETE FROM `#__jshopping_products_files` WHERE `id` = '" . $db->escape($id) . "'";
	                $db->setQuery($query);
	                $db->query();
	                $delete_row = 1;
	            }else{
	                $query = "update `#__jshopping_products_files` set `demo`='', demo_descr='' WHERE `id` = '" . $db->escape($id) . "'";
	                $db->setQuery($query);
	                $db->query();
	            }
	            
	            $query = "select count(*) as k from #__jshopping_products_files where demo='".$db->escape($row->demo)."'";
	            $db->setQuery($query);
	            if (!$db->loadResult()){
	                @unlink($jshopConfig->demo_product_path."/".$row->demo);
	            }
	        }
	        
	        if ($type == "file"){
	            if ($row->demo==""){
	                $query = "DELETE FROM `#__jshopping_products_files` WHERE `id` = '" . $db->escape($id) . "'";
	                $db->setQuery($query);
	                $db->query();
	                $delete_row = 1;
	            }else{
	                $query = "update `#__jshopping_products_files` set `file`='', file_descr='' WHERE `id` = '" . $db->escape($id) . "'";
	                $db->setQuery($query);
	                $db->query();
	            }
	            
	            $query = "select count(*) as k from #__jshopping_products_files where file='".$db->escape($row->file)."'";
	            $db->setQuery($query);
	            if (!$db->loadResult()){
	                @unlink($jshopConfig->files_product_path."/".$row->file);
	            }
	        }
	        print $delete_row;
        } else {
        	print "Access denied";
        }
		die();    
    }
  function edit_vendor() {
        $addon_vendor = $this->getModel("addon_vendor");
        $addon_vendor->checkUserSeller();
        $addon_vendor->checkTasksAccess('editVendor');
        
        $lang = JSFactory::getLang(); 
        $jshopConfig = JSFactory::getConfig(); 
        
        $vndConfig = $addon_vendor->getVendorsConfig();  
        $vendorInf = $addon_vendor->getVendorByUser();
        /*
        $vendor = JTable::getInstance('vendor', 'jshop');
        $vendor->load($vendorInf->id); */

        $country = JTable::getInstance('country', 'jshop');
        $list_country = $country->getAllCountries();
        $option_country[] = JHTML::_('select.option', 0, _JSHOP_REG_SELECT, 'country_id', 'name' );
        $option_countryes = array_merge($option_country, $list_country);
        $lists['country'] = JHTML::_('select.genericlist', $option_countryes,'country','class = "inputbox" size = "1"','country_id', 'name',$vendorInf->country );        
        

        //print_r($countries);die;
 
        /*
        $users = $_categories->getAllUsers(); 
        $first = array();
        $first[] = JHTML::_('select.option', '0'," - - - ", 'id','username');        
        $lists['users'] = JHTML::_('select.genericlist', array_merge($first, $users), 'users','style="" onchange="javascript:setUserId(this.value);"','id','username', $vendor->user_id);
        */
     
		$view_name = "addon_vendor";
        $view_config = array("template_path"=>JPATH_COMPONENT."/templates/".$this->va_template."/".$view_name);
        $view = $this->getView($view_name, 'html', '', $view_config);
        $view->setLayout("vendor_edit"); 
        $view->assign('vendor', $vendorInf); 
        $view->assign('lists', $lists);
        $view->assign('vndconfig', $vndConfig);
        $view->display(); 
    }	 
     
    function save_vendor() {
        
        $addon_vendor = $this->getModel("addon_vendor");
	    $addon_vendor->checkUserSeller();
        $addon_vendor->checkTasksAccess('editVendor');
        $vndConfig = $addon_vendor->getVendorsConfig(); 
        $vendorInf = $addon_vendor->getVendorByUser();
        $vendor = JTable::getInstance('vendor', 'jshop');
        
        $post = JRequest::get("post");
        $post['id']=$vendorInf->id;
        //print_r($post);die;
        $vendor->bind($post);
        if($vndConfig->show_vendor_form==1 || $vndConfig->show_vendor_form==2){
        if (!$vendor->check()) {            
            JError::raiseWarning("", $vendor->getError());
             $this->setRedirect(SEFLink("index.php?option=com_jshopping&controller=addon_vendor&task=edit_vendor", 1, 1));
             return 0;
        }
        }
        if(!$vendor->store()) {
            JError::raiseWarning("",_JSHOP_ERROR_SAVE_DATABASE);
            $this->setRedirect(SEFLink("index.php?option=com_jshopping&controller=addon_vendor&task=edit_vendor", 1, 1));
            return 0;
        }
        
        $this->setRedirect(SEFLink("index.php?option=com_jshopping&controller=addon_vendor", 1, 1), _JSHOP_VF_VENDOR_SAVED);
    }    

    public function payments(){
        JSFactory::loadAdminLanguageFile();
        $application = JFactory::getApplication();
        $addonModel = JModelLegacy::getInstance("addon_vendor", "JshoppingModel");
        $addonModel->checkUserSeller();
        $addonModel->checkTasksAccess("payments");
        $ordering = $application->getUserStateFromRequest("com_jshopping.addon_vendors.payments.ordering", "filter_order", "ordering", "cmd");
        $direction = strtoupper($application->getUserStateFromRequest("com_jshopping.addon_vendors.payments.direction", "filter_order_Dir", "ASC", "cmd"));
        $rows = $addonModel->getVendorPayments($this->vendorId, false, $ordering, $direction);
        $view = $this->getView("addon_vendor", "html", "", array("template_path" => JPATH_ROOT."/components/com_jshopping/templates/addons/addon_vendor"));
        $view->setLayout("payments");
        $view->assign("pagination", new JPagination(count($rows), 0, count($rows)));
        $view->assign("rows", $rows);
        $view->assign("filter_order", $ordering);
        $view->assign("filter_order_Dir", $direction);
        appendPathWay(_JSHOP_PAYMENTS);
        $view->display();
    }

    public function paymentEdit(){
        JSFactory::loadAdminLanguageFile();
        $addonModel = JModelLegacy::getInstance("addon_vendor", "JshoppingModel");
        $addonModel->checkUserSeller();
        $addonModel->checkTasksAccess("paymentEdit");
        $cid = JRequest::getVar("cid", array(), "POST", "array");
        $paymentId = (int)(count($cid) ? $cid[0] : JRequest::getInt("payment_id"));
        $addonModel->checkPaymentAccess($paymentId);
        $payment = JTable::getInstance("paymentMethod", "jshop");
        $payment->load($paymentId);
        JFilterOutput::objectHTMLSafe($payment, ENT_QUOTES, array());
        $view = $this->getView("addon_vendor", "html", "", array("template_path" => JPATH_ROOT."/components/com_jshopping/templates/addons/addon_vendor"));
        $view->setLayout("payment");
        $view->assign("payment", $payment);
        $languagesModel = JModelLegacy::getInstance("languages", "JshoppingModel");
        $view->assign("languages", $languagesModel->getAllLanguages(1));
        $view->assign("configHtml", $addonModel->renderPaymentConfig($payment));
        $view->assign("taxOptions", $addonModel->getTaxOptions());        
        $view->assign("paymentPrototypeOptions", $addonModel->getPaymentPrototypeOptions());
        $view->assign("priceTypeOptions", $addonModel->getPriceTypeOptions());
        $paymentsModel = JModelLegacy::getInstance("payments", "JshoppingModel");
        $view->assign("paymentTypeOptions", $paymentsModel->getTypes());
        $view->display();
    }

    public function paymentSave(){
        JSession::checkToken() or die(JText::_('JINVALID_TOKEN'));
        $addonModel = JModelLegacy::getInstance("addon_vendor", "JshoppingModel");
        $paymentId = JRequest::getInt("payment_id");
        $addonModel->checkPaymentAccess($paymentId);
        $post = JRequest::get("post");
        $payment = JTable::getInstance("paymentMethod", "jshop");
        if ($paymentId == 0){
            $prototypePaymentId = JRequest::getInt("prototype_payment_id");
            if ($prototypePaymentId != 0){
                $payment->load($prototypePaymentId);
				$payment->payment_class .= '_' . JFactory::getUser()->id;
                $addonModel->filterEmptyPaymentData($post);
            }else{
                $post['payment_type'] = 1;
            }
        }
        $post['price'] = saveAsPrice($post['price']);
        $payment->bind($post);
        if (isset($post["pm_params"])){
            $parseString = new parseString($post["pm_params"]);
            $payment->payment_params = $parseString->splitParamsToString();
        }
        $error = false;
        if ($payment->check()){
            if ($addonModel->checkVendorPaymentExists($payment, $this->vendorId)){
                $payment->store();
                $addonModel->addPaymentToVendor($this->vendorId, $payment->payment_id);
            }else{
                $error = _JSHOP_VENDOR_PAYMENT_ALREDY_EXISTS_ERROR;
            }
        }else{
            $error = $payment->getError();
        }
        if ($error !== false){
            $this->setRedirect(SEFLink("index.php?option=com_jshopping&controller=addon_vendor&task=paymentEdit&payment_id=".$paymentId, 1, 1), $error, "error");
        }else{
            $this->setRedirect(SEFLink("index.php?option=com_jshopping&controller=addon_vendor&task=payments", 1, 1));
        }
    }

    public function paymentCancel(){
        $this->setRedirect(SEFLink("index.php?option=com_jshopping&controller=addon_vendor&task=payments", 1, 1));
    }    

    public function paymentDelete(){
        JSession::checkToken() or die(JText::_("JINVALID_TOKEN"));
        $addonModel = JModelLegacy::getInstance("addon_vendor", "JshoppingModel");
        $cid = JRequest::getVar("cid", array(), "POST", "array");
        $payment = JTable::getInstance("paymentMethod", "jshop");
        $v2p = JTable::getInstance("VendorsToPayments", "jshop");
        foreach($cid as $paymentId){
            $addonModel->checkPaymentAccess($paymentId);
            if($payment->delete($paymentId)){                
                if($v2p->load(array("payment_id" => $paymentId))){
                    $v2p->delete();
                }
            }else{
                JFactory::getApplication()->enqueueMessage($table->getError(), "error");
            }
        }
        $this->setRedirect(SEFLink("index.php?option=com_jshopping&controller=addon_vendor&task=payments", 1, 1));
    }

    public function paymentPublish(){
        JSession::checkToken() or die(JText::_("JINVALID_TOKEN"));
        $addonModel = JModelLegacy::getInstance("addon_vendor", "JshoppingModel");
        $cid = JRequest::getVar("cid", array(), "POST", "array");
        $task = strtolower(JRequest::getCmd("task"));
        $paymentPublish = (int)($task == "paymentpublish");
        $payment = JTable::getInstance("paymentMethod", "jshop");
        foreach($cid as $paymentId) {
            $addonModel->checkPaymentAccess($paymentId);
            if($payment->load($paymentId)){
                $payment->payment_publish = $paymentPublish;
                $payment->store();
            }
        }
        $this->setRedirect(SEFLink("index.php?option=com_jshopping&controller=addon_vendor&task=payments", 1, 1));
    }

    public function paymentReorder(){
        JSession::checkToken() or die(JText::_("JINVALID_TOKEN"));
        $addonModel = JModelLegacy::getInstance("addon_vendor", "JshoppingModel");
        $cid = JRequest::getVar("cid", array(), "POST", "array");
        $task = strtolower(JRequest::getCmd("task"));
        $delta = $task == "paymentorderup" ? -1 : 1;
        $v2p = JTable::getInstance("VendorsToPayments", "jshop");
        foreach($cid as $paymentId){
            $addonModel->checkPaymentAccess($paymentId);
            if($v2p->load(array("payment_id" => $paymentId, "vendor_id" => $this->vendorId))){
                $v2p->move($delta, array("vendor_id" => $this->vendorId));
            }
        }
        $this->setRedirect(SEFLink("index.php?option=com_jshopping&controller=addon_vendor&task=payments", 1, 1));
    }

    public function paymentSaveOrder(){
        JSession::checkToken() or jexit(JText::_("JINVALID_TOKEN"));
        $addonModel = JModelLegacy::getInstance("addon_vendor", "JshoppingModel");
        $cid = JRequest::getVar("cid", array(), "POST", "array");
        $order = JRequest::getVar("order", array(), "array");
        JArrayHelper::toInteger($cid);
        JArrayHelper::toInteger($order);
        $addonModel->paymentSaveOrder($cid, $order);
        $this->setRedirect(SEFLink("index.php?option=com_jshopping&controller=addon_vendor&task=payments", 1, 1));
    }

    public function shippings(){
        JSFactory::loadAdminLanguageFile();
        $application = JFactory::getApplication();
        $addonModel = JModelLegacy::getInstance("addon_vendor", "JshoppingModel");
        $addonModel->checkUserSeller();
        $addonModel->checkTasksAccess("shippings");
        $ordering = $application->getUserStateFromRequest("com_jshopping.addon_vendors.shippings.ordering", "filter_order", "ordering", "cmd");
        $direction = strtoupper($application->getUserStateFromRequest("com_jshopping.addon_vendors.shippings.direction", "filter_order_Dir", "ASC", "cmd"));
        $shippings = $addonModel->getVendorShippings($this->vendorId, false, $ordering, $direction);
        $addonModel->checkShippingsPrices($shippings);
        $view = $this->getView("addon_vendor", "html", "", array("template_path" => JPATH_ROOT."/components/com_jshopping/templates/addons/addon_vendor"));
        $view->setLayout("shippings");
        $view->assign("rows", $shippings);
        $view->assign("filter_order", $filter_order);
        $view->assign("filter_order_Dir", $filter_order_Dir);
        $view->assign("pagination", new JPagination(count($shippings), 0, count($shippings)));
        $view->assign("ordering", $ordering);
        $view->assign("direction", $direction);
        appendPathWay(_JSHOP_SHIPPINGS);
        $view->display();
    }

    public function shippingEdit(){
        $addonModel = JModelLegacy::getInstance("addon_vendor", "JshoppingModel");
        $addonModel->checkUserSeller();
        $addonModel->checkTasksAccess("shippingEdit");
        JSFactory::loadAdminLanguageFile();
        $cid = JRequest::getVar("cid", array(), "POST", "array");
        $shippingId = (int)(count($cid) ? $cid[0] : JRequest::getInt("shipping_id"));
        $addonModel->checkShippingAccess($shippingId);
        $shipping = JTable::getInstance("shippingMethod", "jshop");
        $shipping->load($shippingId);
        $languagesModel = JModelLegacy::getInstance("languages", "JshoppingModel");
        $view = $this->getView("addon_vendor", "html", "", array("template_path" => JPATH_ROOT."/components/com_jshopping/templates/addons/addon_vendor"));
        $view->setLayout("shipping");
        $view->assign("shipping", $shipping);
        $view->assign("languages", $languagesModel->getAllLanguages(1));
        $view->assign("paymentsOptions", $addonModel->getShippingPaymentsOptions());
        $view->display();
    }

    public function shippingSave(){
        JSession::checkToken() or die(JText::_("JINVALID_TOKEN"));
        $addonModel = JModelLegacy::getInstance("addon_vendor", "JshoppingModel");
        $shippingId = JRequest::getInt("shipping_id");
        $addonModel->checkShippingAccess($shippingId);
        $shipping = JTable::getInstance("shippingMethod", "jshop");
        $shipping->setPayments(JRequest::getVar("listpayments", array(), "POST", "array"));
        $shipping->bind(JRequest::get("post"));
        $error = false;
        if($shipping->store()){
            $addonModel->addShippingToVendor($this->vendorId, $shipping->shipping_id);
        }else{
            $error = $shipping->getError();
        }
        if($error !== false){
            $this->setRedirect(SEFLink("index.php?option=com_jshopping&controller=addon_vendor&task=shippingEdit&shipping_id=".$shippingId, 1, 1), $error, "error");
        }else{
            $this->setRedirect(SEFLink("index.php?option=com_jshopping&controller=addon_vendor&task=shippings", 1, 1));
        }
    }

    public function shippingCancel(){
        $this->setRedirect(SEFLink("index.php?option=com_jshopping&controller=addon_vendor&task=shippings", 1, 1));
    }    

    public function shippingDelete(){
        JSession::checkToken() or die(JText::_("JINVALID_TOKEN"));
        $addonModel = JModelLegacy::getInstance("addon_vendor", "JshoppingModel");
        $cid = JRequest::getVar("cid", array(), "POST", "array");
        $shipping = JTable::getInstance("shippingMethod", "jshop");
        $v2s = JTable::getInstance("VendorsToShippings", "jshop");
        foreach($cid as $shippingId){
            $addonModel->checkShippingAccess($shippingId);
            if($shipping->delete($shippingId)){
                $addonModel->deleteShippingsPricesByShippingId($shippingId);
                if($v2s->load(array("shipping_id" => $shippingId))){
                    $v2s->delete();
                }
            }
        }
        $this->setRedirect(SEFLink("index.php?option=com_jshopping&controller=addon_vendor&task=shippings", 1, 1));
    }

    public function shippingPublish(){
        JSession::checkToken() or die(JText::_("JINVALID_TOKEN"));
        $addonModel = JModelLegacy::getInstance("addon_vendor", "JshoppingModel");
        $cid = JRequest::getVar("cid", array(), "POST", "array");
        $task = strtolower(JRequest::getCmd("task"));
        $shippingPublish = (int)($task == "shippingpublish");
        $shipping = JTable::getInstance("shippingMethod", "jshop");
        foreach($cid as $shippingId) {
            $addonModel->checkShippingAccess($shippingId);
            if($shipping->load($shippingId)){
                $shipping->published = $shippingPublish;
                $shipping->store();
            }
        }
        $this->setRedirect(SEFLink("index.php?option=com_jshopping&controller=addon_vendor&task=shippings", 1, 1));
    }

    public function shippingReorder(){
        JSession::checkToken() or die(JText::_("JINVALID_TOKEN"));
        $addonModel = JModelLegacy::getInstance("addon_vendor", "JshoppingModel");
        $cid = JRequest::getVar("cid", array(), "POST", "array");
        $task = strtolower(JRequest::getCmd("task"));
        $delta = $task == "shippingorderup" ? -1 : 1;
        $v2s = JTable::getInstance("VendorsToShippings", "jshop");
        foreach($cid as $shippingId){
            $addonModel->checkShippingAccess($shippingId);
            if($v2s->load(array("shipping_id" => $shippingId, "vendor_id" => $this->vendorId))){
                $v2s->move($delta, array("vendor_id" => $this->vendorId));
            }
        }
        $this->setRedirect(SEFLink("index.php?option=com_jshopping&controller=addon_vendor&task=shippings", 1, 1));
    }

    public function shippingSaveOrder(){
        JSession::checkToken() or jexit(JText::_("JINVALID_TOKEN"));
        $addonModel = JModelLegacy::getInstance("addon_vendor", "JshoppingModel");
        $cid = JRequest::getVar("cid", array(), "POST", "array");
        $order = JRequest::getVar("order", array(), "array");
        JArrayHelper::toInteger($cid);
        JArrayHelper::toInteger($order);
        $addonModel->shippingSaveOrder($cid, $order);
        $this->setRedirect(SEFLink("index.php?option=com_jshopping&controller=addon_vendor&task=shippings", 1, 1));
    }

    public function shippingsPrices(){
        JSFactory::loadAdminLanguageFile();
        $addonModel = JModelLegacy::getInstance("addon_vendor", "JshoppingModel");
        $addonModel->checkUserSeller();
        $addonModel->checkTasksAccess("shippingsPrices");
        $application = JFactory::getApplication();
        $ordering = $application->getUserStateFromRequest("com_jshopping.addon_vendors.shippingsprices.ordering", "filter_order", "sh_pr_method_id", "cmd");
        $direction = $application->getUserStateFromRequest("com_jshopping.addon_vendors.shippingsprices.direction", "filter_order_Dir", "desc", "cmd");
        $shippingId = JRequest::getInt("shipping_id");
        $shipping = JTable::getInstance("shippingMethod", "jshop");
        $shipping->load($shippingId);
        $currency = JTable::getInstance("currency", "jshop");
        $currency->load(JSFactory::getConfig()->mainCurrency);
        $view = $this->getView("addon_vendor", "html", "", array("template_path" => JPATH_ROOT."/components/com_jshopping/templates/addons/addon_vendor"));
        $view->setLayout("shippingsprices");
        $view->assign("rows", $addonModel->getShippingPrices($shippingId, $ordering, $direction));
        $view->assign("currency", $currency);
        $view->assign("filter_order", $ordering);
        $view->assign("filter_order_Dir", $direction);
        $view->assign("shipping", $shipping);
        appendPathWay(_JSHOP_SHIPPING_PRICES);
        $view->display(); 
    }

    public function shippingsPriceEdit(){
        JSFactory::loadAdminLanguageFile();
        $addonModel = JModelLegacy::getInstance("addon_vendor", "JshoppingModel");
        $addonModel->checkUserSeller();
        $addonModel->checkTasksAccess("shippingsPriceEdit");
        $cid = JRequest::getVar("cid", array(), "POST", "array");
        $shippingsPriceId = (int)(count($cid) ? $cid[0] : JRequest::getInt("shipping_price_id"));
        $shippingsPrice = JTable::getInstance("shippingMethodPrice", "jshop");
        $shippingsPrice->load($shippingsPriceId);
        $addonModel->checkShippingAccess($shippingsPrice->shipping_method_id);
        $shippingsPrice->prices = $shippingsPrice->getPrices();
        $shippingsModel = JModelLegacy::getInstance("shippings", "JshoppingModel");
        $countriesModel = JModelLegacy::getInstance("countries", "JshoppingModel");        
        $currency = JTable::getInstance("currency", "jshop");
        $currency->load(JSFactory::getConfig()->mainCurrency);        
        $view = $this->getView("addon_vendor", "html", "", array("template_path" => JPATH_ROOT."/components/com_jshopping/templates/addons/addon_vendor"));
        $view->setLayout("shippingsprice");
        $view->assign("sh_method_price", $shippingsPrice);
        $view->assign("taxOptions", $addonModel->getTaxOptions());
        $view->assign("countriesOptions", $countriesModel->getAllCountries(0));
        $view->assign("shippingsOptions", $shippingsModel->getAllShippings(0));
        $view->assign("deliveryTimeOptions", $addonModel->getDeliveryTimeOptions());
        $view->assign("currency", $currency);
        $view->display();
    }

    public function shippingsPriceSave(){        
        JSession::checkToken() or die(JText::_("JINVALID_TOKEN"));
        $addonModel = JModelLegacy::getInstance("addon_vendor", "JshoppingModel");
        $shippingId = JRequest::getInt("shipping_method_id");
        $addonModel->checkShippingAccess($shippingId);
        $post = JRequest::get("post");        
        $post['shipping_stand_price'] = saveAsPrice($post['shipping_stand_price']);
        $shippingPrice = JTable::getInstance('shippingMethodPrice', 'jshop');
        $shippingPrice->bind($post);
        $shippingPrice->setParams('');
        if(isset($post['sm_params'])){
            $shippingPrice->setParams($post['sm_params']);
        }
        if($shippingPrice->store()) {
            $shippingsModel = JModelLegacy::getInstance("shippings", "JshoppingModel");
            $shippingsModel->savePrices($shippingPrice->sh_pr_method_id, $post);
            $shippingsModel->saveCountries($shippingPrice->sh_pr_method_id, JRequest::getVar('shipping_countries_id'));
        }
        $this->setRedirect(SEFLink("index.php?option=com_jshopping&controller=addon_vendor&task=shippingsPrices&shipping_id=".$shippingId, 1, 1));
    }

    public function shippingsPriceDelete(){
        JSession::checkToken() or die(JText::_("JINVALID_TOKEN"));
        $addonModel = JModelLegacy::getInstance("addon_vendor", "JshoppingModel");
        $cid = JRequest::getVar("cid", array(), "POST", "array");
        $shippingId = JRequest::getInt("shipping_id");
        $shippingPrice = JTable::getInstance('shippingMethodPrice', 'jshop');
        $addonModel->deleteShippingsPricesByIds($cid);
        $this->setRedirect(SEFLink("index.php?option=com_jshopping&controller=addon_vendor&task=shippingsPrices&shipping_id=".$shippingId, 1, 1));
    }

    public function shippingsPriceCancel(){
        $this->setRedirect(SEFLink("index.php?option=com_jshopping&controller=addon_vendor&task=shippingsPrices&shipping_id=".JRequest::getVar("shipping_method_id"), 1, 1));
    }

    public function reviewsave(){
        JSession::checkToken() or die(JText::_("JINVALID_TOKEN"));
        $vendor_id = JRequest::getInt("vendor_id");
        $return_link = SEFLink("index.php?option=com_jshopping&controller=vendor&task=info&vendor_id=".$vendor_id, 1, 1);
        $addon_model = JModelLegacy::getInstance("addon_vendor", "JshoppingModel");
        $config = $addon_model->getVendorsConfig();
        $addon_model->checkTasksAccess("reviewSave");
        $review = JTable::getInstance("VendorsReview", "jshop");        
        $review->bind(JRequest::get("post"));
        $review->user_id = JSFactory::getUserShop()->user_id;
        $review->date = date("Y-m-d H:i:s");
        $review->published = $config->vendor_reviews_moderation_enabled ? 0 : 1;
        $message = null;
        $message_type = null;
        if ($config->vendor_reviews_moderation_enabled){
            $message = _JSHOP_VENDOR_REVIEW_MODERATION_MESSAGE;
            $message_type = "message";
        }
        if (!$review->store()){
            $message = $shipping->getError();
            $message_type = "error";
        }
        $this->setRedirect($return_link, $message, $message_type);
    }

    public function order_status_save(){
        $order_id = JRequest::getInt("order_id");
        $status = JRequest::getInt("order_status");
        $status_id = $status;
        $notify = 1;
        $comments = '';
        $addon_vendor = $this->getModel("addon_vendor");
        $vndConfig = $addon_vendor->getVendorsConfig();
        $order = $addon_vendor->getOrder($order_id);

        $order_status_modify = $vndConfig->order_status_modify && $order->vendor_id!=-1;
        if ($order_status_modify){
            $model = JSFactory::getModel('orderChangeStatus', 'jshop');
            $model->setData($order_id, $status, $sendmessage, $status_id, $notify, $comments, $include, 0);
            $model->setAppAdmin(1);
            $model->store();
            $message = _VF_ORDER_STATUS_CHANGED;
        }else{
            $message = "Error";
        }

        $return_link = SEFLink("index.php?option=com_jshopping&controller=addon_vendor&task=order&order_id=".$order_id, 1, 1);
        $this->setRedirect($return_link, $message);
    }


}