<?php

include_once 'func.crypt.php';
include_once 'blowfish.php';

$result = '';
//$result = text_decrypt('B-0bfcce9262c00d0838cb88ce255bd10f1b86754cb071620c', 'fc6b162e60219e3344b1e55e6106bb2f');
//$result = text_decrypt('B-06eb55bebf35e7b197e8c44421e68c31', 'fc6b162e60219e3344b1e55e6106bb2f');
$result = text_decrypt('B-829fe8543760ef7f0b6cfff4b3d87fdc066cc61b6878f91d', 'fc6b162e60219e3344b1e55e6106bb2f');
echo $result;
?>
