<?php
$sqlFileToExecute = '/home/suppleme/public_html/updatelocations.sql';
$hostname = 'localhost';
$db_user = 'suppleme_locsync';
$db_password = 'sync123';
$link = mysql_connect($hostname, $db_user, $db_password);
if (!$link) {
  die ("MySQL Connection error");
}
 
$database_name = 'suppleme_metona';
mysql_select_db($database_name, $link) or die ("Wrong MySQL Database");
 
// read the sql file
$f = fopen($sqlFileToExecute,"r+");
$sqlFile = fread($f, filesize($sqlFileToExecute));
$sqlArray = explode(';',$sqlFile);
foreach ($sqlArray as $stmt) {
  if (strlen($stmt)>3 && substr(ltrim($stmt),0,2)!='/*') {
    $result = mysql_query($stmt);
    if (!$result) {
      $sqlErrorCode = mysql_errno();
      $sqlErrorText = mysql_error();
      $sqlStmt = $stmt;
      break;
    }
  }
}
if ($sqlErrorCode == 0) {
  echo "Script is executed succesfully!";
} else {
  echo "An error occured during installation!<br/>";
  echo "Error code: $sqlErrorCode<br/>";
  echo "Error text: $sqlErrorText<br/>";
  echo "Statement:<br/> $sqlStmt<br/>";
}
 
?>