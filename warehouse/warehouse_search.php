<?php
$db_hostname = 'production.c4ffouecxrat.ap-southeast-2.rds.amazonaws.com:3306';
$db_username = 'root';
$db_password = 'Y65UXUKwEyjLS6Jm';
$db_database = 'nutrition';

// Database Connection String
$con = mysql_connect($db_hostname,$db_username,$db_password);
if (!$con)
  {
  die('Could not connect: ' . mysql_error());
  }

mysql_select_db($db_database, $con);
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <title>NWH HQ Barcode</title>
    </head>
    <body style="background-color:#f5aab0;" onLoad="self.focus();document.boogie.term.focus()">
<p>
<center><img src="https://nutritionwarehouse.com.au/warehouse/barcode.png" style="width:50px;">&nbsp;&nbsp;&nbsp;&nbsp;<a href="https://nutritionwarehouse.com.au/warehouse/warehouse_search_location.php"><img src="https://nutritionwarehouse.com.au/warehouse/location.png" style="width:50px;"></a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="https://nutritionwarehouse.com.au/warehouse/warehouse_remap.php"><img src="https://nutritionwarehouse.com.au/warehouse/remap.png" style="width:50px;"></a><br>&nbsp;<br></center>
<form action="" method="post" name="boogie"> 
&nbsp;&nbsp;Barcode:<br> 
<center><input type="text" name="term" placeholder="barcode" style="width:200px; height:20px;"/> 

<input type="submit" value="Submit" style="width:60px; height:26px;"/>  
</form>  
<?php
if (!empty($_REQUEST['term'])) {

$term = mysql_real_escape_string($_REQUEST['term']);         

$sql = "SELECT `catalog_product_entity_varchar`.`attribute_id`, `catalog_product_entity_varchar`.`value`, `cataloginventory_stock_item`.`qty` 
FROM `catalog_product_entity_varchar`, `cataloginventory_stock_item` 
WHERE `catalog_product_entity_varchar`.`entity_id` = (
Select `catalog_product_entity_varchar`.`entity_id` FROM `catalog_product_entity_varchar` WHERE `catalog_product_entity_varchar`.`attribute_id` = 823 and `catalog_product_entity_varchar`.`value` = '$term'
 LIMIT 0 , 1) and `cataloginventory_stock_item`.`product_id` = `catalog_product_entity_varchar`.`entity_id` 
and (`catalog_product_entity_varchar`.`attribute_id` = 71 or `catalog_product_entity_varchar`.`attribute_id` = 820 or `catalog_product_entity_varchar`.`attribute_id` = 823)";

$r_query = mysql_query($sql); 

echo "<P>";
while ($row = mysql_fetch_array($r_query)){

if ($row['attribute_id'] == 71) {
    echo '<br /> Product Name: ' .$row['value'];
} elseif ($row['attribute_id'] == 820) {
    echo '<br /><b> Warehouse Location: ' .$row['value'];
    echo '</b>';
} elseif ($row['attribute_id'] == 823) {
    echo '<br /> Barcode: ' .$row['value'];
    echo '<br /> Qty Avail: ' .$row['qty'];
}


  
}
}
?>


<br>
<?php
//Used to use a second query for Xcart but commented out now we moved to Magento
//if (!empty($_REQUEST['term'])) {
//$term = mysql_real_escape_string($_REQUEST['term']);         
//$sql = "SELECT xcart_product_options_lng.option_name FROM xcart_products, xcart_variants, xcart_variant_items, xcart_product_options_lng WHERE xcart_products.productid = xcart_variants.productid and xcart_variants.variantid = xcart_variant_items.variantid and xcart_variant_items.optionid = xcart_product_options_lng.optionid and xcart_variants.SKU = '$term'"; 
//$r_query = mysql_query($sql); 
//while ($row = mysql_fetch_array($r_query)){  
//echo ''.$row['option_name']. '<br />';  
//echo ' ';
//}  
//}
?>



<div style="position:absolute; bottom: 0; width:100%; height: 30px; text-align:center; vertical-align: middle;">
<!--
<a href="https://nutritionwarehouse.com.au/warehouse/warehouse_search_detailed.php">Detailed Search</a>
&nbsp;&nbsp;&nbsp;&nbsp;
-->
<a href="http://media.nutritionwarehouse.com.au/product/135-2/">Expiry Check Form</a></div>

    </body>
</html>