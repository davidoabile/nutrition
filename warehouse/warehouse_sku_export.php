<?php

    $db_record = 'xcart_variants';
    // optional where query
    $where = 'WHERE SKU <> "" AND productcode <> "" AND productcode REGEXP "^-?[0-9]+$"';
    // filename for export
    $csv_fileName = 'updatesku.sql';

    // database variables
    $hostname = "localhost";
    $user = "nwhhost_whouse";
    $password = "whouse123";
    $database = "nwhhost_xcart";

    // Database connecten voor alle services
    mysql_connect($hostname, $user, $password)
    or die('Could not connect: ' . mysql_error());

    mysql_select_db($database)
    or die ('Could not select database ' . mysql_error());

    $csv_export = '';

    $query = mysql_query("SELECT concat(' Update `suppleme_catalog_product_entity`, `suppleme_catalog_product_entity_varchar` SET `suppleme_catalog_product_entity_varchar`.`value` = \"', SKU, '\" WHERE `suppleme_catalog_product_entity_varchar`.`entity_id` = `suppleme_catalog_product_entity`.`entity_id` and `suppleme_catalog_product_entity_varchar`.`attribute_id` = 211 and `suppleme_catalog_product_entity`.`sku` = ', productcode, ';') FROM ".$db_record." ".$where);
    $field = mysql_num_fields($query);

    // create line with field names
    //for($i = 0; $i < $field; $i++) {
    //$csv_export.= mysql_field_name($query,$i).',';
    //}
    $csv_export.= "\r\n";
    while($row = mysql_fetch_array($query)) {
      // create line with field values
      for($i = 0; $i < $field; $i++) {
        $csv_export.= ''.$row[mysql_field_name($query,$i)].'';
      } 
      $csv_export.= "\r\n"; 
    }

    // Export the data and prompt a csv file for download
    header("Content-type: text/x-csv");
    header("Content-Disposition: attachment; filename=".$csv_fileName."");
    echo($csv_export);
?>