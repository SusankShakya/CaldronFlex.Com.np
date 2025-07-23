<?php

//This helpers provided only for developers
//Don't include this in production/live project
//
//read file
function read_file_by_curl($path) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_URL, $path);
    curl_setopt($ch, CURLOPT_POST, 1);

    $content = curl_exec($ch);
    curl_close($ch);
    return $content;
}

//preapre app.all.css
function write_css($files) {
    merge_file($files, "assets/css/app.all.css");
}

//preapre app.all.js
function write_js($files) {
    merge_file($files, "assets/js/app.all.js");
}

//merge all files into one
function merge_file($files, $file_name) {
    $txt = "";
    $base_path = getcwd() . "/";
    foreach ($files as $file) {
        //echo  getcwd() . "/" .$file."<br>";
        $txt .= file_get_contents($base_path . $file);
    }

    file_put_contents($file_name, $txt);
}

//prepare css from scss
function write_scss($files) {

    $libraryFile = APPPATH . 'ThirdParty/scssphp/vendor/autoload.php';
    if (!file_exists($libraryFile)) {
        echo "<p style='font-family:arial;font-size:16px'>
        <b>Note</b>: The <i>scssphp</i> library is intended for development use only and should not be included in the production version.<br>
         As such, it is not bundled with the main file. <br>
         For development purposes, please download and place the <i>scssphp</i> library in the <strong>/app/ThirdParty/scssphp/</strong> directory.<br>
         <br>
         <a href='https://github.com/scssphp/scssphp'>https://github.com/scssphp/scssphp</a>
        </p>
        ";
        echo '<pre style="font-family:monospace;font-size:16px">composer require scssphp/scssphp "^2.0.0"</pre>';
        exit();
    }

    require_once $libraryFile;

    $base_path = getcwd() . "/";
    $scss = new ScssPhp\ScssPhp\Compiler();
    $scss->setImportPaths($base_path);

    $css = file_get_contents($base_path . "assets/css/app.all.css"); //put contents with the existing content of app.all.css
    foreach ($files as $file) {
        $css .= $scss->compileString(file_get_contents($base_path . $file))->getCss();
    }
    file_put_contents("assets/css/app.all.css", $css);

    //prepare css from color scss
    //scan the scss files for theme color
    try {
        $dir = getcwd() . '/assets/scss/color/';
        $files = scandir($dir);
        if ($files && is_array($files)) {
            foreach ($files as $file) {
                if ($file != "." && $file != ".." && $file != "index.html") {
                    $css = $scss->compileString(file_get_contents($base_path . "assets/scss/color/$file"))->getCss();
                    $color_code = str_replace(".scss", "", $file);
                    file_put_contents("assets/css/color/$color_code.css", $css);
                }
            }
        }
    } catch (\Exception $exc) {
    }

    //prepare css from other special scss files
    $scss_files = array("invoice", "rtl");
    foreach ($scss_files as $scss_file) {
        $css = $scss->compileString(file_get_contents($base_path . "assets/scss/$scss_file.scss"))->getCss();
        file_put_contents("assets/css/$scss_file.css", $css);
    }
}
