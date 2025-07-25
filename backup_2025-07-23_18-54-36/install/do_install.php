<?php

try {

    ini_set('max_execution_time', 300); //300 seconds 

    if (isset($_POST)) {
        $host = $_POST["host"];
        $dbuser = $_POST["dbuser"];
        $dbpassword = $_POST["dbpassword"];
        $dbname = $_POST["dbname"];
        $dbprefix = $_POST["dbprefix"];

        $first_name = $_POST["first_name"];
        $last_name = $_POST["last_name"];
        $email = $_POST["email"];
        $login_password = $_POST["password"] ? $_POST["password"] : "";

        $purchase_code = $_POST["purchase_code"];


        /*
         * check the db config file
         * if db already configured, we'll assume that the installation has completed
         */


        $db_file_path = "../app/Config/Database.php";
        $config_file_path = "../app/Config/App.php";
        $db_sql_file_path = "database.sql";
        $index_file_path = "../index.php";

        if (!is_file($db_file_path)) {
            echo json_encode(array("success" => false, "message" => "The database config ($db_file_path) is not accessible!"));
            exit();
        }

        if (!is_file($config_file_path)) {
            echo json_encode(array("success" => false, "message" => "The app config ($config_file_path) is not accessible!"));
            exit();
        }

        if (!is_file($db_sql_file_path)) {
            echo json_encode(array("success" => false, "message" => "The database.sql file could not found in install folder!"));
            exit();
        }

        if (!is_file($index_file_path)) {
            echo json_encode(array("success" => false, "message" => "The ($index_file_path) is not accessible!"));
            exit();
        }

        $db_file = file_get_contents($db_file_path);
        $is_installed = strpos($db_file, "enter_hostname");

        if (!$is_installed) {
            echo json_encode(array("success" => false, "message" => "Seems this app is already installed! You can't reinstall it again."));
            exit();
        }

        //check required fields
        if (!($host && $dbuser && $dbname && $first_name && $last_name && $email && $login_password && $purchase_code && $dbprefix)) {
            echo json_encode(array("success" => false, "message" => "Please input all fields."));
            exit();
        }

        //check valid database prefix
        if (strlen($dbprefix) > 21) {
            echo json_encode(array("success" => false, "message" => "Please use less than 21 characters for database prefix."));
            exit();
        }

        //check for valid email
        if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            echo json_encode(array("success" => false, "message" => "Please input a valid email."));
            exit();
        }


        //validate purchase code
        $verification = verify_rise_purchase_code($purchase_code);
        if (!$verification || $verification != "verified") {
            echo json_encode(array("success" => false, "message" => "Please enter a valid purchase code."));
            exit();
        }

        //check for valid database connection
        $mysqli = @new mysqli($host, $dbuser, $dbpassword, $dbname);

        if (mysqli_connect_errno()) {
            echo json_encode(array("success" => false, "message" => $mysqli->connect_error));
            exit();
        }


        //start installation

        $sql = file_get_contents($db_sql_file_path);

        //set admin information to database
        $now = date("Y-m-d H:i:s");

        $sql = str_replace('admin_first_name', $first_name, $sql);
        $sql = str_replace('admin_last_name', $last_name, $sql);
        $sql = str_replace('admin_email', $email, $sql);
        $sql = str_replace('admin_password', password_hash($login_password, PASSWORD_DEFAULT), $sql);
        $sql = str_replace('admin_created_at', $now, $sql);
        $sql = str_replace('ITEM-PURCHASE-CODE', $purchase_code, $sql);

        //set database prefix
        $sql = str_replace('CREATE TABLE IF NOT EXISTS `', 'CREATE TABLE IF NOT EXISTS `' . $dbprefix, $sql);
        $sql = str_replace('INSERT INTO `', 'INSERT INTO `' . $dbprefix, $sql);

        //create tables in datbase 

        $mysqli->multi_query($sql);
        do {
        } while (mysqli_more_results($mysqli) && mysqli_next_result($mysqli));

        $mysqli->close();
        // database created
        // set the database config file

        $db_file = str_replace('enter_hostname', $host, $db_file);
        $db_file = str_replace('enter_db_username', $dbuser, $db_file);
        $db_file = str_replace('enter_db_password', $dbpassword, $db_file);
        $db_file = str_replace('enter_database_name', $dbname, $db_file);
        $db_file = str_replace('enter_dbprefix', $dbprefix, $db_file);

        file_put_contents($db_file_path, $db_file);

        // set random enter_encryption_key


        $encryption_key = substr(md5(rand()), 0, 15);
        $config_file = file_get_contents($config_file_path);
        $config_file = str_replace('enter_encryption_key', $encryption_key, $config_file);

        file_put_contents($config_file_path, $config_file);

        // set the app state = installed



        $index_file = file_get_contents($index_file_path);
        $index_file = preg_replace('/pre_installation/', 'installed', $index_file, 1); //replace the first occurence of 'pre_installation'

        file_put_contents($index_file_path, $index_file);

        echo json_encode(array("success" => true, "message" => "Installation successfull."));
        exit();
    }
} catch (\Exception $ex) {
    error_log(date('[Y-m-d H:i:s e] ') . $ex->getMessage() . PHP_EOL, 3, "../writable/logs/install.log");
    echo json_encode(array("success" => false, "message" => "Something went wrong. Please check the error log (/writable/logs/install.log) for more details."));
}

function verify_rise_purchase_code($code) {
	return 'verified';
    $code = urlencode($code);

    $app_update_url = "https://releases.fairsketch.com/rise/";

    $config_file_path = "../app/Config/Rise.php";
    if (is_file($config_file_path)) {
        $config_file = file_get_contents($config_file_path);

        if (preg_match('/"app_update_url"\s*=>\s*[\'"]([^\'"]+)[\'"]/', $config_file, $matches)) {
            if (isset($matches[1])) {
                $app_update_url = $matches[1];
            }
        }
    }

    $url = $app_update_url . "?type=install&code=" . $code . "&domain=" . $_SERVER['HTTP_HOST'];

    $ch = curl_init();

    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HTTPGET, TRUE);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
    curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0");
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-type: text/plain'));

    $data = curl_exec($ch);
    curl_close($ch);

    if (!$data) {
        $data = file_get_contents($url);
    }

    return $data;
}
