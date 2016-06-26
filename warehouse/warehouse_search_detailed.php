<?php
$db_hostname = 'localhost';
$db_username = 'nwhhost_whouse';
$db_password = 'whouse123';
$db_database = 'nwhhost_xcart';

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
<center><img src="http://www.nutritionwarehouse.com.au/warehouse/barcode.png" style="width:50px;">&nbsp;&nbsp;&nbsp;&nbsp;<a href="http://www.nutritionwarehouse.com.au/warehouse/warehouse_search_location.php"><img src="http://www.nutritionwarehouse.com.au/warehouse/location.png" style="width:50px;"></a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="http://www.nutritionwarehouse.com.au/warehouse/warehouse_remap.php"><img src="http://www.nutritionwarehouse.com.au/warehouse/remap.png" style="width:50px;"></a><br>&nbsp;<br></center>
<form action="" method="post" name="boogie"> 
&nbsp;&nbsp;Barcode:<br> 
<center><input type="text" name="term" placeholder="barcode" style="width:200px; height:20px;"/> 

<input type="submit" value="Submit" style="width:60px; height:26px;"/>  
</form>  
<?php
if (!empty($_REQUEST['term'])) {

$term = mysql_real_escape_string($_REQUEST['term']);         

$sql = "SELECT xcart_products.product, xcart_variants.avail, xcart_variants.SKU, xcart_variants.rexlocation FROM xcart_products, xcart_variants WHERE xcart_products.productid = xcart_variants.productid and xcart_variants.SKU = '$term'"; 
$r_query = mysql_query($sql); 

while ($row = mysql_fetch_array($r_query)){
 
echo '<br /> Warehouse Location: <b>' .$row['rexlocation'];  
echo '</b><br>Product Barcode: ' .$row['SKU'];  
echo '<br /> Available Stock: ' .$row['avail'];  
echo '<br />'; 
echo '<br />' .$row['product']; 

}  

}
?>


<br>
<?php
if (!empty($_REQUEST['term'])) {

$term = mysql_real_escape_string($_REQUEST['term']);         

$sql = "SELECT xcart_product_options_lng.option_name FROM xcart_products, xcart_variants, xcart_variant_items, xcart_product_options_lng WHERE xcart_products.productid = xcart_variants.productid and xcart_variants.variantid = xcart_variant_items.variantid and xcart_variant_items.optionid = xcart_product_options_lng.optionid and xcart_variants.SKU = '$term'"; 
$r_query = mysql_query($sql); 

while ($row = mysql_fetch_array($r_query)){  
echo ''.$row['option_name']. '<br />';  
echo ' ';


}  

}
?>




<?php
if (!empty($_REQUEST['term'])) {

$term = mysql_real_escape_string($_REQUEST['term']);         

$sql = "SELECT SUM(Amount) As Website_Sales_30days, ROUND(AVG(price),2) As Average_Price FROM `xcart_order_details`, `xcart_orders`, `xcart_variants` Where `xcart_orders`.`orderid` = `xcart_order_details`.`orderid` and `xcart_order_details`.`productcode` = `xcart_variants`.`productcode` and `xcart_orders`.`status` <> 'F' and `xcart_orders`.`status` <> 'D' and `xcart_orders`.`date` > UNIX_TIMESTAMP(DATE_ADD(CURDATE(),INTERVAL -14 DAY)) and `xcart_variants`.`SKU` = '$term'";
$r_query = mysql_query($sql); 

while ($row = mysql_fetch_array($r_query)){  
echo '<br />';
echo '<br /> <b>NWH Web Sales History</b>';
echo '<br />14 Days:&nbsp;&nbsp; '.$row['Website_Sales_30days']; 
echo '&nbsp;&nbsp;  <small>('.round($row['Website_Sales_30days']/2, 2);
echo ' p/w)</small>';
echo '&nbsp;&nbsp;  <small>(Avg$ '.$row['Average_Price'];
echo ')</small>';

}  

}
?>
<?php
if (!empty($_REQUEST['term'])) {

$term = mysql_real_escape_string($_REQUEST['term']);         

$sql = "SELECT SUM(Amount) As Website_Sales_30days, ROUND(AVG(price),2) As Average_Price FROM `xcart_order_details`, `xcart_orders`, `xcart_variants` Where `xcart_orders`.`orderid` = `xcart_order_details`.`orderid` and `xcart_order_details`.`productcode` = `xcart_variants`.`productcode` and `xcart_orders`.`status` <> 'F' and `xcart_orders`.`status` <> 'D' and `xcart_orders`.`date` > UNIX_TIMESTAMP(DATE_ADD(CURDATE(),INTERVAL -30 DAY)) and `xcart_variants`.`SKU` = '$term'";
$r_query = mysql_query($sql); 

while ($row = mysql_fetch_array($r_query)){  
echo '<br />30 Days:&nbsp;&nbsp; '.$row['Website_Sales_30days'];
echo '&nbsp;&nbsp;  <small>('.round($row['Website_Sales_30days']/4.28, 2);
echo ' p/w)</small>'; 
echo '&nbsp;&nbsp;  <small>(Avg$ '.$row['Average_Price'];
echo ')</small>';

}  

}
?>
<?php
if (!empty($_REQUEST['term'])) {

$term = mysql_real_escape_string($_REQUEST['term']);         

$sql = "SELECT SUM(Amount) As Website_Sales_30days, ROUND(AVG(price),2) As Average_Price FROM `xcart_order_details`, `xcart_orders`, `xcart_variants` Where `xcart_orders`.`orderid` = `xcart_order_details`.`orderid` and `xcart_order_details`.`productcode` = `xcart_variants`.`productcode` and `xcart_orders`.`status` <> 'F' and `xcart_orders`.`status` <> 'D' and `xcart_orders`.`date` > UNIX_TIMESTAMP(DATE_ADD(CURDATE(),INTERVAL -90 DAY)) and `xcart_variants`.`SKU` = '$term'";
$r_query = mysql_query($sql); 

while ($row = mysql_fetch_array($r_query)){  
echo '<br />90 Days:&nbsp;&nbsp; '.$row['Website_Sales_30days']; 
echo '&nbsp;&nbsp;  <small>('.round($row['Website_Sales_30days']/12.85, 2);
echo ' p/w)</small>';
echo '&nbsp;&nbsp;  <small>(Avg$ '.$row['Average_Price'];
echo ')</small>';
}  

}
?>



<div style="position:absolute; bottom: 0; width:100%; height: 30px; text-align:center; vertical-align: middle;"><a href="http://www.nutritionwarehouse.com.au/warehouse/warehouse_search.php">Simple Search</a></div>
&nbsp;&nbsp;&nbsp;&nbsp;
<a href="http://media.nutritionwarehouse.com.au/product/135-2/">Expiry Check Form</a></div>
    </body>
</html>