<?php
set_time_limit(0);

ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

require_once __DIR__ . '/functions.php';

require_once __DIR__ . '/classes/db.php';
//require_once __DIR__ . '/lib/phpQuery-onefile.php';

/*
$db = new Db();

$vib_adsmanager_field_values_result = $db->query("SELECT * FROM `vib_adsmanager_field_values` WHERE fieldid='9' ORDER BY `vib_adsmanager_field_values`.`fieldvalue` ASC");
//dump($vib_adsmanager_field_values_result);
$city_cnt = 1;
while($vib_adsmanager_field_values_row = $vib_adsmanager_field_values_result->fetch_assoc()){
    //dump($vib_adsmanager_field_values_row);
    
    $country_name = $vib_adsmanager_field_values_row['fieldtitle'];
    $fieldvalue = $vib_adsmanager_field_values_row['fieldvalue'];
    dump($country_name);
    dump($fieldvalue);
    
    $country_id = $db->query_insert_id("INSERT INTO `vib_jshopping_countries` (`country_publish`, `name_en-GB`, `name_de-DE`, `name_ru-RU`) VALUES ('1', '$country_name', '$country_name', '$country_name')");
    
    if($country_id){
        $vib_adsmanager_field_city_result = $db->query("SELECT * FROM `vib_adsmanager_field_city` WHERE citycod='$fieldvalue'");
        while($vib_adsmanager_field_city_row = $vib_adsmanager_field_city_result->fetch_assoc()){
            dump($vib_adsmanager_field_city_row);
            $citytitle = $vib_adsmanager_field_city_row['citytitle'];
    
            $city_id = $db->query_insert_id("INSERT INTO `vib_jshopping_states` (`country_id`, `state_publish`, `ordering`, `name_en-GB`, `name_de-DE`, `name_ru-RU`) VALUES ('$country_id', '1', '$city_cnt', '$citytitle', '$citytitle', '$citytitle')");
            if($city_id){
                show("Добавлен город: $citytitle id -> $city_id");
                $city_cnt++;
            }
        }
    }
    
}
*/

