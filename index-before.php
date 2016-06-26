<?php
if (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && $_SERVER['HTTP_X_FORWARDED_FOR'] != '') {
    $_SERVER['REMOTE_ADDR'] = $_SERVER['HTTP_X_FORWARDED_FOR'];
}

if(isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https') {
    $_SERVER['HTTPS'] = 'on';
    $_SERVER['SERVER_PORT'] = 80;
}

if(isset($_SERVER['HTTP_X_FORWARDED_PROTO_NEW']) && $_SERVER['HTTP_X_FORWARDED_PROTO_NEW'] == 'https') {
        $_SERVER['HTTPS'] = 'on';
        $_SERVER['SERVER_PORT'] = 80;
}

if(file_exists('/home/www/xhprof/external/header.php')) {
        require_once('/home/www/xhprof/external/header.php');
}
