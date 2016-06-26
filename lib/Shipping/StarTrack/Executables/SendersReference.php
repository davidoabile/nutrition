<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<!-- 
     <document_root>/MyWebSite/SendersReference.php
     Sample application invoking searchConsignments and getConsignmentDetails, StarTrack eService operations
     StarTrack
     19 March 2012
     Version 4.4
-->

<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Sender's Reference Sample</title>
</head>

<body>
<p>To obtain details on one or more consignments, complete the following:</p>

<form action="EnquireSendersReference.php" method="post">
<table width="1000" border="0">
  <tr>
    <th scope="row" align="left">Account No&nbsp;</th>
    <td align="left"><input type="text" name="accountNo" />&nbsp;</td>
   </tr>
  <tr>
    <th scope="row" align="left">Sender's References (separated by spaces)&nbsp;</th>
    <td align="left"><input type="text" name="senderReferenceNumber" />&nbsp;</td>
  </tr>
   <tr>
    <th scope="row" align="left">Despatch Location Codes (separated by spaces)&nbsp;</th>
    <td align="left"><input type="text" name="despatchLocationCode" />&nbsp;</td>
  </tr>
 
</table>
<br />
<input type="submit" value="Get Consignment Details" />
</form>

</body>
</html>