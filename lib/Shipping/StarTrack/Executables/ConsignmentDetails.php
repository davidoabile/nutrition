<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<!-- 
     <document_root>/MyWebSite/ConsignmentDetails.php
     Sample application invoking getConsignmentDetails, a StarTrack eService operation
     StarTrack
     19 March 2012
     Version 4.4 
-->

<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Consignment Details Sample</title>
</head>

<body>
<p>To obtain details on one or more consignments, complete the following:</p>

<form action="EnquireConsignmentDetails.php" method="post">
<table width="1000" border="0">
  <tr>
    <th scope="row" align="left">Account No&nbsp;</th>
    <td align="left"><input type="text" name="accountNo" />&nbsp;</td>
   </tr>
  <tr>
    <th scope="row" align="left">Consignment IDs (separated by spaces)&nbsp;</th>
    <td align="left"><input type="text" name="consignmentId" />&nbsp;</td>
  </tr>
</table>
<br />
<input type="submit" value="Get Consignment Details" />
</form>

</body>
</html>