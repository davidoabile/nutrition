<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<!-- 
     <document_root>/MyWebSite/ReferenceDataDownloadWeb.php
     Sample application using eServices to retrieve reference data from StarTrack.

     ******* See also ReferenceDataDownloadFromCommandLine.php (batch version) *****

     StarTrack
     19 March 2012
     Version 4.4 
-->

<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Retrieve Reference Data Sample</title>
</head>

<body>

<p>Retrieves Reference Data via eServices, storing it in JSON format. Recommended execution frequency: daily.</p>

<form action="EnquireReferenceData.php" method="post">
<table width="1000" border="1">
  <tr>
    <th scope="row" align="left">Destination Directory:&nbsp;</th>
    <td align="left"> <input type="text" size="50" maxlength="30" value="/Temp/" name="destinationDirectory" /></td>
   </tr>
</table>
<br /><br />

<input type=checkbox checked value="depots" name="depots" /> Depots
<br />
<input type=checkbox checked value="serviceCodes" name="serviceCodes" /> Service Codes
<br />
<input type=checkbox checked value="qcCodes" name="qcCodes" /> QC Codes
<br />
<input type=checkbox checked value="locations" name="locations" /> Locations (Suburb, Postcode, State) and Nearest Depots
<br />
<input type=checkbox value="fastServiceCodes" name="fastServiceCodes" /> Fast Service Codes
<br /><br />

<input type="submit" value="Get Reference Data" />
</form>

</body>
</html>