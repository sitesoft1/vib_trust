function addon_vendor_releted_product_search(start, no_id){
    var text = jQuery("#related_search").val();
    var url = baseurl+'index.php?option=com_jshopping&controller=addon_vendor&task=search_related&start='+start+'&no_id='+no_id+'&text='+encodeURIComponent(text);

	function showResponse(data){
        jQuery("#list_for_select_related").html(data);
    }
    jQuery.get(url, showResponse);
}

function addon_vendor_deleteFotoProduct(id){
    var url = baseurl+'index.php?option=com_jshopping&controller=addon_vendor&task=delete_foto&id='+id;
    function showResponse(data){
        jQuery("#foto_product_"+id).hide();
    }
    jQuery.get(url, showResponse);
}

function addon_vendor_deleteFileProduct(id, type){
    var url = baseurl+'index.php?option=com_jshopping&controller=addon_vendor&task=delete_file&id='+id+"&type="+type;
    function showResponse(data){
        if (type=="demo"){
            jQuery("#product_demo_"+id).html("");
        }
        if (type=="file"){
            jQuery("#product_file_"+id).html("");
        }
        if (data=="1") jQuery(".rows_file_prod_"+id).hide();
    }
    jQuery.get(url, showResponse);
}

function addon_vendor_deleteVideoProduct(id){
    var url = baseurl+'index.php?option=com_jshopping&controller=addon_vendor&task=delete_video&id='+id;
    function showResponse(data){
        jQuery("#video_product_"+id).hide();
    }
    jQuery.get(url, showResponse);
}

function addon_vendor_reloadProductExtraField(product_id){
    var catsurl = "";
    jQuery("#addon_vendor_edit_form #category_id :selected").each(function(j, selected){ 
      value = jQuery(selected).val(); 
      text = jQuery(selected).text();
      if (value!=0){
          catsurl += "&cat_id[]="+value;
      }
    });
    
    var url = baseurl+'index.php?option=com_jshopping&controller=addon_vendor&task=product_extra_fields&product_id='+product_id+catsurl+"&ajax=1";
    function showResponse(data){
        jQuery("#extra_fields_space").html(data);
    }
    jQuery.get(url, showResponse);
}

function addVendorAttributValue(){
    attr_tmp_row_num++;
    var id=0;
    var ide=0;
    var value = "";
    var text = "";
    var html="";
    var hidden="";
    var field="";
    var count_attr_sel = 0;
    var tmpmass = {};
    var tmpimg = "";
    var selectedval = {};
    var num = 0;
    var current_index_list = [];
    var max_index_list = [];
    var combination = 1;
    var count_attributs = attrib_ids.length;
    var index = 0;
    var option = {};
            
    for (var i=0; i<count_attributs; i++){
        current_index_list[i] = 0;
        id = attrib_ids[i];
        ide = "value_id"+id;
        selectedval[id] = [];
        num = 0;
        jQuery("#"+ide+" :selected").each(function(j, selected){ 
          value = jQuery(selected).val(); 
          text = jQuery(selected).text();
          if (value!=0){
              selectedval[id][num] = {"text":text, "value":value};
              num++;
          }
        });

        if (selectedval[id].length==0){
            selectedval[id][0] = {"text":"-", "value":"0"};
        }else{
            count_attr_sel++;    
        }
        max_index_list[i] = selectedval[id].length;
        combination = combination * max_index_list[i];
    }
    
    var first_attr = jQuery("input:hidden","#list_attr_value tr:eq(1)");
    if (first_attr.length > 0) {
        for (var k=0; k<count_attributs; k++)
        {
            id = attrib_ids[k];
            if (first_attr[k].value==0) 
            {
                if (selectedval[id][0].value != 0) 
                {
                    alert(lang_error_attribute);
                    return 0;
                }
            }
            if (first_attr[k].value!=0) 
            {
                if (selectedval[id][0].value == 0) 
                {
                    alert(lang_error_attribute);
                    return 0;
                }
            }
        }
    }
    
    if (count_attr_sel==0){
        alert(lang_error_attribute);
        return 0;
    }
    
    var list_key = [];
    for(var j=0; j<combination; j++){
        list_key[j] = [];
        for (var i=0; i<count_attributs; i++){
            id = attrib_ids[i];
            num = current_index_list[i];
            list_key[j][i] = num;
        }
        
        index = 0;
        for (var i=0; i<count_attributs; i++){
            if (i==index){
                current_index_list[index]++;
                if (current_index_list[index] >= max_index_list[index]){
                    current_index_list[index] = 0;
                    index++;
                }
            }
        }
    }

    var entered_price = jQuery("#attr_price").val();
    var entered_count = jQuery("#attr_count").val();
    var entered_ean = jQuery("#attr_ean").val();    
    var entered_weight = jQuery("#attr_weight").val();
    var entered_weight_volume_units = jQuery("#attr_weight_volume_units").val();
    var entered_old_price = jQuery("#attr_old_price").val();
    var entered_buy_price = jQuery("#attr_buy_price").val();
    var count_added_rows = 0;
    for(var j=0; j<combination; j++){
        tmpmass = {};
        html = "<tr id='attr_row_"+attr_tmp_row_num+"'>";
        for (var i=0; i<count_attributs; i++){
            id = attrib_ids[i];
            num = list_key[j][i];
            option = selectedval[id][num];
            hidden = "<input type='hidden' name='attrib_id["+id+"][]' value='"+option.value+"'>";
            tmpimg="";
            if (option.value!=0 && attrib_images[option.value]!=""){
                tmpimg ='<img src="'+folder_image_attrib+'/'+attrib_images[option.value]+'" style="margin-right:5px;" width="16" height="16" class="img_attrib">';
            }
            html+="<td>" + hidden + tmpimg + option.text + "</td>";
            tmpmass[id] = option.value;
        }

        field="<input type='text' name='attrib_price[]' value='"+entered_price+"'>";
        html+="<td>"+field+"</td>";
        
        field="<input type='text' name='attr_count[]' value='"+entered_count+"'>";
        html+="<td>"+field+"</td>";
        
        field="<input type='text' name='attr_ean[]' value='"+entered_ean+"'>";
        html+="<td>"+field+"</td>";
        
        field="<input type='text' name='attr_weight[]' value='"+entered_weight+"'>";
        html+="<td>"+field+"</td>";
        
        if (use_basic_price=="1"){
            field="<input type='text' name='attr_weight_volume_units[]' value='"+entered_weight_volume_units+"'>";
            html+="<td>"+field+"</td>";
        }
        
        field="<input type='text' name='attrib_old_price[]' value='"+entered_old_price+"'>";
        html+="<td>"+field+"</td>";
        
        if (use_bay_price=="1"){
            field="<input type='text' name='attrib_buy_price[]' value='"+entered_buy_price+"'>";
            html+="<td>"+field+"</td>";
        }
            
        html+="<td><a href='#' onclick=\"deleteTmpRowAttrib('"+attr_tmp_row_num+"');return false;\"><img src='" + uriRoot + "administrator/components/com_jshopping/images/publish_r.png' border='0'></a></td>";
        
        html+="</tr>";
        html+="";
        
        var existcheck = 0;
        for ( var k in attrib_exist ){
            var exist = 1; 
            for(var i=0; i<count_attributs; i++){
                id = attrib_ids[i];
                if (attrib_exist[k][id]!=tmpmass[id]) exist=0;
            }
            if (exist==1) {
                existcheck = 1;
                break;
            }
        }
        
        if (!existcheck){
            jQuery("#list_attr_value").append(html);
            attrib_exist[attr_tmp_row_num] = tmpmass;
            attr_tmp_row_num++;
            count_added_rows++;
        }
    }
    
    if (count_added_rows==0){
        alert(lang_attribute_exist);
        return 0;
    }   
    return 1; 
}

function addVendorAttributValue2(id){
    var value_id = jQuery("#attr_ind_id_tmp_"+id+"  :selected").val();
    var attr_value_text = jQuery("#attr_ind_id_tmp_"+id+"  :selected").text();
    var mod_price = jQuery("#attr_price_mod_tmp_"+id).val();
    var price = jQuery("#attr_ind_price_tmp_"+id).val();
    var existcheck = jQuery('#attr_ind_'+id+'_'+value_id).val();
    if (existcheck){
        alert(lang_attribute_exist);
        return 0;
    }    
    if (value_id=="0"){
        alert(lang_error_attribute);
        return 0;
    }
    html = "<tr id='attr_ind_row_"+id+"_"+value_id+"'>"; 
    hidden = "<input type='hidden' id='attr_ind_"+id+"_"+value_id+"' name='attrib_ind_id[]' value='"+id+"'>";
    hidden2 = "<input type='hidden' name='attrib_ind_value_id[]' value='"+value_id+"'>";
    tmpimg="";
    if (value_id!=0 && attrib_images[value_id]!=""){
        tmpimg ='<img src="'+folder_image_attrib+'/'+attrib_images[value_id]+'" style="margin-right:5px;" width="16" height="16" class="img_attrib">';
    }
    html+="<td>" + hidden + hidden2 + tmpimg + attr_value_text + "</td>";
    html+="<td><input type='text' style = 'width: 110px; margin-right: 5px;' name='attrib_ind_price_mod[]' value='"+mod_price+"'></td>";
    html+="<td><input type='text' style = 'width: 110px; margin-right: 5px;' name='attrib_ind_price[]' value='"+price+"'></td>";
    html+="<td><a href='#' onclick=\"jQuery('#attr_ind_row_"+id+"_"+value_id+"').remove();return false;\"><img src='" + uriRoot + "administrator/components/com_jshopping/images/publish_r.png' border='0'></a></td>";
    html += "</tr>";    
    jQuery("#list_attr_value_ind_"+id).append(html);
}

function addVendorNewPrice(){
    add_price_num++;
    var html;    
    html = '<tr id="add_price_'+add_price_num+'">';
    html += '<td><input type = "text" style = "width: 100px" name = "quantity_start[]" id="quantity_start_'+add_price_num+'" value = "" /></td>';
    html += '<td><input type = "text" style = "width: 100px" name = "quantity_finish[]" id="quantity_finish_'+add_price_num+'" value = "" /></td>';
    html += '<td><input type = "text" style = "width: 100px" name = "product_add_discount[]" id="product_add_discount_'+add_price_num+'" value = "" onkeyup="productAddPriceupdateValue('+add_price_num+')" /></td>';
    html += '<td><input type = "text" style = "width: 100px" id="product_add_price_'+add_price_num+'" value = "" onkeyup="productAddPriceupdateDiscount('+add_price_num+')" /></td>';    
    html += '<td align="center"><a href="#" onclick="delete_add_price('+add_price_num+');return false;"><img src="' + uriRoot + 'administrator/components/com_jshopping/images/publish_r.png" border="0"/></a></td>';
    html += '</tr>';
    jQuery("#table_add_price").append(html);
}

function changeVendorCategory(){
    var catid = jQuery("#category_parent_id").val();
    var url = uriRoot + 'administrator/index.php?option=com_jshopping&controller=categories&task=sorting_cats_html&catid=' + catid + '&ajax=1';
    function showResponse(data){
        jQuery('#ordering').html(data);
    }
    jQuery.get(url, showResponse);
}

function submitConfirmButton(task, list, msg){
    if(list){
        if(document.adminForm.boxchecked.value == 0){
            var message = Joomla.JText._("JLIB_HTML_PLEASE_MAKE_A_SELECTION_FROM_THE_LIST");
            if(typeof(message) !== "undefined"){
                alert(message);
            }
        }else{
            submitConfirmButton(task, false, msg);
        }
    }else{
        if(typeof(msg) === "undefined" || confirm(msg)){
            Joomla.submitbutton(task);
        }
    }
}

jQuery(document).ready(function(){
    jQuery('#payments_list').find("input:radio[name=cashing_payment]").change(function(){
        jQuery('tr#payment_list_fields').remove();
        var id = jQuery('input:radio[name=cashing_payment]:checked').val(); 
       
        var fields = paymentsFields[this.value];
        if(fields){
            jQuery.each(fields, function(key, field){
                var field_star_element = '';
                if(field.required*1){
                    field_star_element = '<span class="required">*</span>';
                }

                jQuery('tr#payment_row_' + id).after('<tr id="payment_list_fields"><td colspan="3">' +
                    '<label style="font-size: 10px">' + field.name + '</label>' + field_star_element + '<br />' + 
                    '<input type="text" style="width: 200px; height: 14px;" name="paymentFields[' + field.id + '][value]" value=""/>' + '<br />' +  
                    '<input type="hidden" name="paymentFields[' + field.id + '][required]" value="' + field.required + '"/>' + '</td></tr>');             
            });
        } 
        jQuery('#avCashingSumm').trigger('keyup');
    });
    
    jQuery('#vp_commission').change(function(){
       if (jQuery('#vp_commission').val() == '0'){
           jQuery('fieldset#product_commission_config').hide();
           jQuery('fieldset#vendor_commission_config').show();
       } else {
           jQuery('fieldset#vendor_commission_config').hide();
           jQuery('fieldset#product_commission_config').show();
       }
    });
    
    var vndRequest = null;
    var timeoutVndId = null;

    jQuery('#avCashingSumm').keyup(function(){
        var data = 'cashingSum=' + jQuery(this).val();
        data += '&payment_id=' + jQuery('input:radio[name=cashing_payment]:checked').val();
   
        if (timeoutVndId){
            clearTimeout(timeoutVndId);
        }
    
        timeoutVndId = setTimeout(function(){
            if (vndRequest){
                vndRequest.abort();
            }

            vndRequest = jQuery.ajax({
                url: url_calc_comm, 
                dataType: "json", 
                data : data, 
                cache: false,
                beforeSend: function() {
                    jQuery('.cashing_block').after("<div class = 'cashing_loading' style = 'margin-left: 1px; width: " + (jQuery('.cashing_block').width() + 20) + "px; height: " + (jQuery('.cashing_block').height() + 18) + "px; margin-top: " + (-(jQuery('.cashing_block').height() + 25)) + "px;'><div class = 'cashing_request_back' style = 'height: " + (jQuery('.cashing_block').height() + 20) + "px;'></div></div>");
                },
                success: function(data) {
                    jQuery('.cashing_loading').remove();
                    jQuery('.vendor_cashing_sum').html(data.viewCashingSum).show();
                    jQuery('.payment_commission').html(data.commission).show();
                    jQuery('.balance_after_commision').html(data.balance).show();
                    if (data.cashingSum <= 0 || data.vndBalance < 0 || data.cashingSum < data.min_cashing_sum || (data.max_cashing_sum > 0 && (data.cashingSum + data.vndCommission > data.max_cashing_sum))) jQuery('#avCashingSumm').css({"background-color" : "#F95E41"});
                        else jQuery('#avCashingSumm').css({"background-color" : "#fff"});
                }
            });
        }, 800);
    });
});

function addonVendorEditAttributeExtendParams(id){
    window.open(product_attr_link+id,'windowae','width=1000, height=760, scrollbars=yes,status=no,toolbar=no,menubar=no,resizable=yes,location=yes');
}