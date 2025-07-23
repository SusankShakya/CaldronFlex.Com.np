<?php

$php_version_success = false;
$mysql_success = false;
$curl_success = false;
$mbstring_success = false;
$intl_success = false;
$json_success = false;
$mysqlnd_success = false;
$xml_success = false;
$gd_success = false;
$zlib_success = false;

$php_version_required = "8.1";
$current_php_version = PHP_VERSION;

//check required php version
if ($current_php_version >= $php_version_required) {
    $php_version_success = true;
}

//check mySql 
if (function_exists("mysqli_connect")) {
    $mysql_success = true;
}

//check curl 
if (function_exists("curl_version")) {
    $curl_success = true;
}

//check mbstring 
if (extension_loaded('mbstring')) {
    $mbstring_success = true;
}

//check intl 
if (extension_loaded('intl')) {
    $intl_success = true;
}

//check json 
if (extension_loaded('json')) {
    $json_success = true;
}

//check mysqlnd 
if (extension_loaded('mysqlnd')) {
    $mysqlnd_success = true;
}

//check xml 
if (extension_loaded('xml')) {
    $xml_success = true;
}

//check gd
if (extension_loaded('gd') && function_exists('gd_info')) {
    $gd_success = true;
}

if (!ini_get("zlib.output_compression")) {
    $zlib_success = true;
}

//check if all requirement is success
if ($php_version_success && $mysql_success && $curl_success && $mbstring_success && $gd_success && $intl_success && $json_success && $mysqlnd_success && $xml_success && $zlib_success) {
    $all_requirement_success = true;
} else {
    $all_requirement_success = false;
}


$writeable_directories = array(
    'files' => '/files',
    'routes' => '/index.php',
    'config' => '/app/Config/App.php',
    'database' => '/app/Config/Database.php'
);

foreach ($writeable_directories as $value) {
    if (!is_writeable(".." . $value)) {
        $all_requirement_success = false;
    }
}


$domain = $_SERVER['HTTP_HOST'] . $_SERVER['SCRIPT_NAME'];

$domain = preg_replace('/install.*/', '', $domain);
$domain = strtolower($domain);
if (!empty($_SERVER['HTTPS'])) {
    $domain = 'https://' . $domain;
} elseif (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
    $domain = 'https://' . $domain;
} else {
    $domain = 'http://' . $domain;
}


$index_file_path = "../index.php";
$index_file = file_get_contents($index_file_path);
$already_installed = strpos($index_file, '$app_state = "installed"');
if ($already_installed) {
    echo ("<div style='text-align:center; padding-top:10%; font-family:Arial'><h1>Already installed.</h1>" . "<p>Please visit: <a href='$domain'>$domain</a></p></div>");
} else {
    include "view/index.php";
}
