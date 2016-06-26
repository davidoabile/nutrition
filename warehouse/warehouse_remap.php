<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <title>NWH HQ Location Search</title>
    </head>
    <body style="background-color:#f5aab0;"  onLoad="self.focus();document.boogie.sku.focus()">
<p>
<center><a href="https://www.nutritionwarehouse.com.au/warehouse/warehouse_search.php"><img src="https://www.nutritionwarehouse.com.au/warehouse/barcode.png" style="width:50px;"></a>&nbsp;&nbsp;&nbsp;&nbsp;<a href="https://www.nutritionwarehouse.com.au/warehouse/warehouse_search_location.php"><img src="https://www.nutritionwarehouse.com.au/warehouse/location.png" style="width:50px;"></a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="https://www.nutritionwarehouse.com.au/warehouse/warehouse_remap.php"><img src="https://www.nutritionwarehouse.com.au/warehouse/remap.png" style="width:50px;"></a><br>&nbsp;<br>




<?php
if(isset($_POST['update']))
{
$conn1 = mysql_connect('production.c4ffouecxrat.ap-southeast-2.rds.amazonaws.com:3306', 'root', 'Y65UXUKwEyjLS6Jm');
$conn2 = mysql_connect('122.201.121.182:3306', 'suppleme_locsync', 'sync123');
if(! $conn1 )
{
  die('Could not connect: ' . mysql_error());
}

$sku = $_POST['sku'];
$rexlocation = $_POST['rexlocation'];

// SQL Query to update location in Database
$sql2 = "UPDATE `catalog_product_entity_varchar`
SET `catalog_product_entity_varchar`.`value` = '$rexlocation'
WHERE `catalog_product_entity_varchar`.`attribute_id` = '820' and `catalog_product_entity_varchar`.`entity_id` = (SELECT `entity_id` FROM (SELECT `catalog_product_entity_varchar`.`entity_id`,  `catalog_product_entity_varchar`.`attribute_id`, `catalog_product_entity_varchar`.`value` FROM `catalog_product_entity_varchar` WHERE `catalog_product_entity_varchar`.`attribute_id` = '823') AS temp101 WHERE `attribute_id` = '823' and `value` = '$sku')" ;

mysql_select_db('nutrition',$conn1);

//Check if product (Sku) is in website database
$result = mysql_query("SELECT `catalog_product_entity_varchar`.`value` FROM `catalog_product_entity_varchar` WHERE `catalog_product_entity_varchar`.`attribute_id` = 823 and `catalog_product_entity_varchar`.`value` = '$sku'",$conn1);

if (!$result) {
    die('Invalid query: ' . mysql_error());
}

if(mysql_num_rows($result) == 0) {
 echo "Nutrition Warehouse<br>\n";
 echo"Product Sku not in Database<p>"; // Sku Not Found
} 
else { //Sku Found
$retval = mysql_query( $sql2, $conn1);
if(! $retval )
{
  die('Could not update data: ' . mysql_error());
}
echo "Nutrition Warehouse<br>\n";
echo "Sku: $sku <br>\n";
echo "Location: $rexlocation <br>\n";
echo "Updated data successfully<p>\n";
mysql_close($conn1);
  }


//Run update for Supplement Depot Database
if(! $conn2 )
{
  die('Could not connect: ' . mysql_error());
}

// SQL Query to update location in Supplement DepotDatabase
$sql3 = "UPDATE `suppleme_catalog_product_entity_varchar`
SET `suppleme_catalog_product_entity_varchar`.`value` = '$rexlocation'
WHERE `suppleme_catalog_product_entity_varchar`.`attribute_id` = '210' and `suppleme_catalog_product_entity_varchar`.`entity_id` = (SELECT `entity_id` FROM (SELECT `suppleme_catalog_product_entity_varchar`.`entity_id`,  `suppleme_catalog_product_entity_varchar`.`attribute_id`, `suppleme_catalog_product_entity_varchar`.`value` FROM `suppleme_catalog_product_entity_varchar` WHERE `suppleme_catalog_product_entity_varchar`.`attribute_id` = '211') AS temp101 WHERE `attribute_id` = '211' and `value` = '$sku')" ;

mysql_select_db('suppleme_metona',$conn2);

//Check if product (Sku) is in website database
$result = mysql_query("SELECT `suppleme_catalog_product_entity_varchar`.`value` FROM `suppleme_catalog_product_entity_varchar` WHERE `suppleme_catalog_product_entity_varchar`.`attribute_id` = 211 and `suppleme_catalog_product_entity_varchar`.`value` = '$sku'",$conn2);

if (!$result) {
    die('Invalid query: ' . mysql_error());
}

if(mysql_num_rows($result) == 0) {
 echo "Supplement Depot<br>\n";
 echo"Product Sku not in Database<p>"; // Sku Not Found
} 
else { //Sku Found
$retval = mysql_query( $sql3, $conn2);
if(! $retval )
{
  die('Could not update data: ' . mysql_error());
}
echo "Supplement Depot<br>\n";
echo "Sku: $sku <br>\n";
echo "Location: $rexlocation <br>\n";
echo "Updated data successfully\n";
mysql_close($conn2);
  }




}
else
{


?>








<form method="post" action="<?php $_PHP_SELF ?>"  name="boogie">
<table border="0" cellspacing="1" cellpadding="2" width="400">
<tr>
<td>Barcode / SKU<br></td>
</tr>
<tr>
<td><input name="sku" type="text" id="sku" style="height:20px; width:400px"></td>
</tr>
<tr>
<td>New Location</td>
</tr>
<tr>
<td><input name="rexlocation" type="text" id="rexlocation" style="height:20px; width:400px"></td>
</tr>
<tr>
<td> </td>
</tr>
<tr height="30px"><td>&nbsp;</td></tr>
<tr>
<td>
<input name="update" type="submit" id="update" value="Update the Location NOW!!!" style="height:30px; width:400px; font-weight:bold; font-size:100%; border: 1px solid black;">
</td>
</tr>
</table>
</form>
</center>
<?php
}
?>
<p><center><a href="https://www.nutritionwarehouse.com.au/warehouse/warehouse_remap.php" style="text-decoration: none">Reset</a>


<div style="position:absolute; bottom: 0; width:100%; height: 30px; text-align:center; vertical-align: middle;"><a href="http://media.nutritionwarehouse.com.au/product/135-2/">Expiry Check Form</a></div>

    </body>
</html>