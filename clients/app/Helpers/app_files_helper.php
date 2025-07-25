<?php

use App\Libraries\Google;

/**
 * get a human readable file size format from bytes 
 * 
 * @param string $bytes
 * @return fise size
 */
if (!function_exists('convert_file_size')) {

    function convert_file_size($bytes) {
        $bytes = floatval($bytes);
        $result = 0 . " KB";
        $bytes_array = array(
            0 => array("unit" => "TB", "value" => pow(1024, 4)),
            1 => array("unit" => "GB", "value" => pow(1024, 3)),
            2 => array("unit" => "MB", "value" => pow(1024, 2)),
            3 => array("unit" => "KB", "value" => 1024),
            4 => array("unit" => "B", "value" => 1),
        );

        foreach ($bytes_array as $byte) {
            if ($bytes >= $byte["value"]) {
                $result = $bytes / $byte["value"];
                $result = strval(round($result, 2)) . " " . $byte["unit"];
                break;
            }
        }
        return $result;
    }
}


/**
 * get some predefined icons for some known file types 
 * 
 * @param string $file_ext
 * @return fontawesome icon class
 */
if (!function_exists('get_file_icon')) {

    function get_file_icon($file_ext = "") {
        switch ($file_ext) {
            case "jpeg":
            case "jpg":
            case "png":
            case "gif":
            case "bmp":
            case "svg":
                return "image";
                break;
            case "doc":
            case "dotx":
                return "file-text";
                break;
            case "xls":
            case "xlsx":
            case "csv":
                return "file-minus";
                break;
            case "ppt":
            case "pptx":
            case "pps":
            case "pot":
                return "file";
                break;
            case "zip":
            case "rar":
            case "7z":
            case "s7z":
            case "iso":
                return "file";
                break;
            case "pdf":
                return "file";
                break;
            case "html":
            case "css":
                return "file-text";
                break;
            case "txt":
                return "file-text";
                break;
            case "mp3":
            case "wav":
            case "wma":
                return "volume-2";
                break;
            case "mpg":
            case "mpeg":
            case "flv":
            case "mkv":
            case "webm":
            case "avi":
            case "mp4":
            case "3gp":
                return "film";
                break;
            default:
                return "file";
        };
    }
}

/**
 * check the file is a image
 * 
 * @param string $file_name
 * @return true/false
 */
if (!function_exists('is_image_file')) {

    function is_image_file($file_name = "") {
        $extension = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $image_files = array("jpg", "jpeg", "png", "gif", "bmp", "svg");
        return (in_array($extension, $image_files)) ? true : false;
    }
}


/**
 * check the file preview supported by google
 * 
 * @param string $file_name
 * @return true/false
 */
if (!function_exists('is_google_preview_available')) {

    function is_google_preview_available($file_name = "") {
        $extension = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $image_files = array("doc", "docx", "ppt", "pptx", "css", "xlsx");
        return (in_array($extension, $image_files)) ? true : false;
    }
}

/**
 * check the file format priview is available or not
 * 
 * @param string $file_name
 * @return true/false
 */
if (!function_exists('is_viewable_image_file')) {

    function is_viewable_image_file($file_name = "") {
        $viewable_extansions = array(
            "jpeg",
            "jpg",
            "png",
            "gif",
            "bmp",
            "svg",
            "webp"
        );
        $file_extension = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        if (in_array($file_extension, $viewable_extansions)) {
            return true;
        }
    }
}

/**
 * check the file format for video priview is available or not
 * 
 * @param string $file_name
 * @return true/false
 */
if (!function_exists('is_viewable_video_file')) {

    function is_viewable_video_file($file_name = "") {
        $viewable_extansions = array(
            "mp4",
            "webm",
            "ogv",
            "mp3"
        );
        $file_extension = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        if (in_array($file_extension, $viewable_extansions)) {
            return true;
        }
    }
}


/**
 * upload a file to temp folder when using dropzone autoque=true
 * 
 * @param file $_FILES
 * @return void
 */
if (!function_exists('upload_file_to_temp')) {

    function upload_file_to_temp($upload_to_local = false) {
        if (!empty($_FILES)) {
            $file = get_array_value($_FILES, "file");

            if (!$file) {
                die("Invalid file");
            }

            $temp_file = get_array_value($file, "tmp_name");
            $file_name = get_array_value($file, "name");
            $file_size = get_array_value($file, "size");

            if (!is_valid_file_to_upload($file_name)) {
                return false;
            }


            if (defined('PLUGIN_CUSTOM_STORAGE') && !$upload_to_local) {
                try {
                    app_hooks()->do_action('app_hook_upload_file_to_temp', array(
                        "temp_file" => $temp_file,
                        "file_name" => $file_name,
                        "file_size" => $file_size
                    ));
                } catch (\Exception $ex) {
                    log_message('error', '[ERROR] {exception}', ['exception' => $ex]);
                }
            } else if (get_setting("enable_google_drive_api_to_upload_file") && get_setting("google_drive_authorized") && !$upload_to_local) {
                $google = new Google();
                $google->upload_file($temp_file, $file_name, "temp", "", $file_size);
            } else {
                $temp_file_path = get_setting("temp_file_path");
                $target_path = getcwd() . '/' . $temp_file_path;
                if (!is_dir($target_path)) {
                    if (!mkdir($target_path, 0755, true)) {
                        die('Failed to create file folders.');
                    }
                }
                $target_file = $target_path . $file_name;
                copy($temp_file, $target_file);
            }
        }
    }
}

/**
 * this method process 3 types of files
 * 1. direct upload
 * 2. move a uploaded file which has been uploaded in temp folder
 * 3. copy a text based image
 * 
 * @param string $file_name
 * @param string $target_path
 * @param string $source_path 
 * @param string $static_file_name 
 * @return filename
 */
if (!function_exists('move_temp_file')) {

    function move_temp_file($file_name, $target_path, $related_to = "", $source_path = NULL, $static_file_name = "", $file_content = "", $direct_upload = false, $file_size = 0, $upload_to_local = false) {
        //to make the file name unique we'll add a prefix
        $filename_prefix = $related_to . "_" . uniqid("file") . "-";

        //if not provide any source path we'll find the default path
        if (!$source_path) {
            $source_path = getcwd() . "/" . get_setting("temp_file_path") . $file_name;
        }

        //remove unsupported values from the file name
        $new_filename = $filename_prefix . preg_replace('/\s+/', '-', $file_name);

        $new_filename = str_replace("’", "-", $new_filename);
        $new_filename = str_replace("'", "-", $new_filename);
        $new_filename = str_replace("(", "-", $new_filename);
        $new_filename = str_replace(")", "-", $new_filename);

        //overwrite extisting logic and use static file name
        if ($static_file_name) {
            $new_filename = $static_file_name;
        }

        //validate file
        if (!is_valid_file_to_upload($file_name)) {
            return false;
        }

        if (!is_valid_file_to_upload($new_filename)) {
            return false;
        }

        $files_data = array();

        if (!$upload_to_local && defined('PLUGIN_CUSTOM_STORAGE')) {
            try {
                $files_data = app_hooks()->apply_filters('app_filter_move_temp_file', array(
                    "related_to" => $related_to,
                    "file_name" => $file_name,
                    "new_filename" => $new_filename,
                    "file_content" => $file_content,
                    "source_path" => $source_path,
                    "target_path" => $target_path,
                    "direct_upload" => $direct_upload,
                ));
            } catch (\Exception $ex) {
                log_message('error', '[ERROR] {exception}', ['exception' => $ex]);
                exit();
            }
        } else if (!$upload_to_local && get_setting("enable_google_drive_api_to_upload_file") && get_setting("google_drive_authorized")) {
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

            $google = new Google();
            if ($file_name == "avatar.png" || $file_name == "site-logo.".$file_ext || $file_name == "invoice-logo.png" || $file_name == "estimate-logo.png" || $file_name == "order-logo.png" || $file_name == "favicon.png" || $related_to == "imap_ticket" || $related_to == "pasted_image" || $file_content || $direct_upload) {
                //directly upload to the main directory

                if (!$file_size && $source_path) {
                    $file_raw_data = get_array_value(explode(",", $source_path), 1);
                    if ($file_raw_data) {
                        $file_size = strlen(base64_decode($file_raw_data));
                    }
                }

                $files_data = $google->upload_file($source_path, $new_filename, get_drive_folder_name($target_path), $file_content, $file_size);
            } else {
                $files_data = $google->move_temp_file($file_name, $new_filename, get_drive_folder_name($target_path));
            }
        } else {
            //check destination directory. if not found try to create a new one
            if (!is_dir($target_path)) {
                if (!mkdir($target_path, 0755, true)) {
                    die('Failed to create file folders.');
                }
                //create a index.html file inside the folder
                copy(getcwd() . "/" . get_setting("system_file_path") . "index.html", $target_path . "index.html");
            }

            if ($file_content) {
                //check if it's the contents of file
                file_put_contents($target_path . $new_filename, $file_content);

                //                $fp = fopen($target_path . $new_filename, "w+");
                //                fwrite($fp, $file_content);
                //                fclose($fp);
            } else if (starts_with($source_path, "data")) {
                //check the file type is data or file. then copy to destination and remove temp file
                if (get_setting("file_copy_type") === "copy") {
                    copy($source_path, $target_path . $new_filename);
                } else {
                    copy_text_based_image($source_path, $target_path . $new_filename);
                }
            } else {
                if (file_exists($source_path)) {
                    copy($source_path, $target_path . $new_filename);
                    unlink($source_path);
                }
            }

            $files_data = array("file_name" => $new_filename);
        }

        if ($files_data && count($files_data)) {
            return $files_data;
        } else {
            return false;
        }
    }
}

/**
 * Get drive folder name
 * @param string $target_path
 * @return string folder name
 */
if (!function_exists("get_drive_folder_name")) {

    function get_drive_folder_name($target_path = "") {
        if ($target_path) {
            $folders_array = array("profile_images", "timeline_files", "project_files", "system", "general", "temp");
            $folders_array = app_hooks()->apply_filters('app_filter_add_folder_to_google_drive_valid_folders_array', $folders_array);

            $explode_target_path = explode("/", $target_path);

            foreach ($folders_array as $folder_name) {
                if (in_array($folder_name, $explode_target_path)) {
                    return $folder_name;
                }
            }

            return "others"; //if not matched anything
        }
    }
}

/**
 * Get source url of file
 * 
 * @param string $file_path
 * @param array $file_info
 * @return source url of file
 */
if (!function_exists('get_source_url_of_file')) {

    function get_source_url_of_file($file_info = array(), $file_path = "", $view_type = "", $only_file_path = false, $add_slash = false, $show_full_size_thumbnail = false) {
        $file_name = get_array_value($file_info, 'file_name');
        $file_id = get_array_value($file_info, "file_id");
        $service_type = get_array_value($file_info, "service_type");

        if ($service_type && $service_type !== "google") {
            try {
                $source_url = app_hooks()->apply_filters('app_filter_get_source_url_of_file', array(
                    "file_info" => $file_info,
                    "view_type" => $view_type,
                    "show_full_size_thumbnail" => $show_full_size_thumbnail
                ));

                return is_string($source_url) ? $source_url : "";
            } catch (\Exception $ex) {
                log_message('error', '[ERROR] {exception}', ['exception' => $ex]);
            }
        } else if ($service_type == "google") {
            //google drive file
            return get_source_url_of_google_drive_file($file_id, $view_type, $show_full_size_thumbnail, $file_name);
        } else {
            //local file
            $full_file_path = $file_path . $file_name;
            if ($only_file_path && $add_slash) {
                return "/" . $full_file_path;
            } else if ($only_file_path) {
                return $full_file_path;
            } else {
                return get_file_uri($full_file_path);
            }
        }
    }
}

/**
 * Get google drive files source url
 * @param string $file_id
 * @return source url
 */
if (!function_exists("get_source_url_of_google_drive_file")) {

    function get_source_url_of_google_drive_file($file_id = "", $view_type = "", $show_full_size_thumbnail = false, $file_name = "") {
        if ($view_type == "raw") {
            //get raw file url
            return "https://drive.google.com/uc?id=$file_id";
        } else if ($view_type == "thumbnail" || ($view_type != "thumbnail" && get_setting("disable_google_preview"))) {
            //show thumnail url as preview url, if the google viewer is disabled
            $size = $show_full_size_thumbnail ? "0" : "700";
            return "https://drive.google.com/thumbnail?id=$file_id&sz=s$size";
        } else {

            // Replace any character that is NOT in the allowed list with '-'
            $file_name = preg_replace('/[^' . get_setting('permittedURIChars') . ']+/i', '-', $file_name);

            //preview
            return get_uri("uploader/stream_google_drive_file/" . $file_id . "/" . $file_name . "/full");
        }
    }
}

/**
 * Get google drive files content
 * @param string $file_id
 * @return content
 */
if (!function_exists("get_google_drive_file_content")) {

    function get_google_drive_file_content($file_id = "") {
        $google = new Google();
        return $google->get_file_content($file_id);
    }
}



/**
 * Convert to a file from text based image
 * 
 * @param string $source_path 
 * @param string $target_path
 * @return file size
 */
if (!function_exists('copy_text_based_image')) {

    function copy_text_based_image($source_path, $target_path) {

        if (ini_get('allow_url_fopen')) {

            $buffer_size = 3145728;
            $byte_number = 0;
            $file_open = fopen($source_path, "rb");
            $file_wirte = fopen($target_path, "w");
            while (!feof($file_open)) {
                $byte_number += fwrite($file_wirte, fread($file_open, $buffer_size));
            }
            fclose($file_open);
            fclose($file_wirte);
            return $byte_number;
        } else {

            $file = explode(",", $source_path);
            $base64 = get_array_value($file, 1);
            if ($base64) {
                return file_put_contents($target_path, base64_decode($base64));
            }
        }
    }
}

/**
 * remove file name prefix which was added by move_temp_file() method
 * 
 * @param string $file_name
 * @return filename
 */
if (!function_exists('remove_file_prefix')) {

    function remove_file_prefix($file_name = "") {
        if(!$file_name){
            return "";
        }
        return substr($file_name, strpos($file_name, "-") + 1);
    }
}


/**
 * copy a directory to another directoryformat_to_datetime
 * 
 * @param string $src
 * @param string $dst
 * @return void
 */
if (!function_exists('copy_recursively')) {

    function copy_recursively($src, $dst) {
        $dir = opendir($src);
        @mkdir($dst);
        while (false !== ($file = readdir($dir))) {
            if (($file != '.') && ($file != '..')) {
                if (is_dir($src . '/' . $file)) {
                    copy_recursively($src . '/' . $file, $dst . '/' . $file);
                } else {
                    copy($src . '/' . $file, $dst . '/' . $file);
                }
            }
        }
        closedir($dir);
    }
}


/**
 * move file to a parmanent direnctory from the temp dirctory
 * 
 * dropzone file post data example
 * the input should be named as file_names and file_sizes
 * 
 * for old borwsers which doesn't supports dropzone the files will be handaled using manual process
 * the post data should be named as manualFiles
 * 
 * @param string $target_path
 * @param string $name
 * 
 * @return array of file ids
 */
if (!function_exists('move_files_from_temp_dir_to_permanent_dir')) {

    function move_files_from_temp_dir_to_permanent_dir($target_path = "", $related_to = "") {

        $request = \Config\Services::request();

        //process the fiiles which has been uploaded by dropzone
        $files_data = array();
        $file_names = $request->getPost("file_names");
        $file_sizes = $request->getPost("file_sizes");

        if ($file_names && get_array_value($file_names, 0)) {
            foreach ($file_names as $key => $file_name) {

                $file_size = get_array_value($file_sizes, $key);
                $file_data = move_temp_file($file_name, $target_path, $related_to, null, "", "", false, $file_size);
                $files_data[] = array(
                    "file_name" => get_array_value($file_data, "file_name"),
                    "file_size" => $file_size,
                    "file_id" => get_array_value($file_data, "file_id"),
                    "service_type" => get_array_value($file_data, "service_type")
                );
            }
        }

        //process the files which has been submitted manually
        if ($_FILES) {
            $files = isset($_FILES['manualFiles']) ? $_FILES['manualFiles'] : array();
            if ($files && count($files) > 0) {
                foreach ($files["tmp_name"] as $key => $file) {
                    $temp_file = $file;
                    $file_name = $files["name"][$key];
                    $file_size = $files["size"][$key];

                    $file_data = move_temp_file($file_name, $target_path, $related_to, $temp_file, "", "", false, $file_size);
                    $files_data[] = array(
                        "file_name" => get_array_value($file_data, "file_name"),
                        "file_size" => $file_size,
                        "file_id" => get_array_value($file_data, "file_id"),
                        "service_type" => get_array_value($file_data, "service_type")
                    );
                }
            }
        }
        return serialize($files_data);
    }
}


/**
 * check post file is valid or not
 * 
 * @param string $file_name
 * @return json data of success or error message
 */
if (!function_exists('validate_post_file')) {

    function validate_post_file($file_name = "") {
        if (!ini_get('file_uploads')) {
            echo json_encode(array("success" => false, 'message' => app_lang('please_enable_the_file_uploads_php_settings')));
            exit();
        }

        if (is_valid_file_to_upload($file_name)) {
            echo json_encode(array("success" => true));
        } else {
            echo json_encode(array("success" => false, 'message' => app_lang('invalid_file_type') . " ($file_name)"));
        }
    }
}


/**
 * check the file type is valid for upload
 * 
 * @param string $file_name
 * @return true/false
 */
if (!function_exists('is_valid_file_to_upload')) {

    function is_valid_file_to_upload($file_name = "") {

        if (!$file_name)
            return false;

        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

        //disable the php file uploading strictly
        $disallowed_extensions = array('php', 'phtml', 'php3', 'php4', 'php5', 'php7', 'inc');
        if (in_array($file_ext, $disallowed_extensions)) {
            log_message('error', ' PHP-related file types are not allowed for upload : ' . $file_name);
            return false;
        }

        $file_formates = explode(",", get_setting("accepted_file_formats"));
        if (in_array($file_ext, $file_formates)) {
            return true;
        }
    }
}

/**
 * delete file 
 * @param String file_path
 * @return void
 */
if (!function_exists('delete_file_from_directory')) {

    function delete_file_from_directory($file_path = "") {
        $source_path = getcwd() . "/" . $file_path;
        if (file_exists($source_path)) {
            unlink($source_path);
        }
    }
}

/**
 * delete files
 * @param String $directory_path
 * @param Array $files
 */
if (!function_exists("delete_app_files")) {

    function delete_app_files($directory_path = "", $files = array()) {
        if (is_array($files)) {
            foreach ($files as $file) {
                if (is_array($file)) {
                    $file_name = get_array_value($file, "file_name");
                    $file_id = get_array_value($file, "file_id");
                    $service_type = get_array_value($file, "service_type");

                    if (defined('PLUGIN_CUSTOM_STORAGE') && $service_type && $service_type !== "google") {
                        try {
                            app_hooks()->do_action('app_hook_delete_app_file', $file);
                        } catch (\Exception $ex) {
                            log_message('error', '[ERROR] {exception}', ['exception' => $ex]);
                        }
                    } else if ($service_type == "google") {
                        //google drive file
                        $google = new Google();
                        try {
                            $google->delete_file($file_id);
                        } catch (\Exception $ex) {
                        }
                    } else {
                        $source_path = $directory_path . $file_name;
                        delete_file_from_directory($source_path);
                    }
                } else if ($file) {
                    delete_file_from_directory($directory_path . $file); //single file
                }
            }
        } else if ($files) {
            delete_file_from_directory($directory_path . $files); //system files won't be array at first time
        }
    }
}

/**
 * Make array of file
 * @param $file_info stdClass object
 * @return array of file
 */
if (!function_exists("make_array_of_file")) {

    function make_array_of_file($file_info) {
        if ($file_info) {
            return array(
                "file_name" => $file_info->file_name,
                "file_id" => $file_info->file_id,
                "service_type" => $file_info->service_type,
            );
        }
    }
}

/**
 * Get system files setting value
 * @param string $setting_name
 * @return array/string setting value
 */
if (!function_exists("get_system_files_setting_value")) {

    function get_system_files_setting_value($setting_name = "") {
        $setting_value = get_setting($setting_name);
        if ($setting_value) {
            $setting_as_array = @unserialize($setting_value);
            if (is_array($setting_as_array)) {
                return array($setting_as_array);
            } else {
                return $setting_value;
            }
        }
    }
}

/**
 * get file path
 * 
 * @param string $file_path
 * @param string $serialized_file_data 
 * @return array
 */
if (!function_exists('prepare_attachment_of_files')) {

    function prepare_attachment_of_files($directory_path, $serialized_file_data) {
        $result = array();
        if ($serialized_file_data) {

            $files = unserialize($serialized_file_data);
            $file_path = getcwd() . '/' . $directory_path;

            foreach ($files as $file) {
                $file_name = get_array_value($file, 'file_name');
                $output_filename = remove_file_prefix($file_name);

                $path = get_source_url_of_file($file, $file_path, "raw", true);

                if ($path) {
                    $result[] = array("file_path" => $path, "file_name" => $output_filename);
                }
            }
        }
        return $result;
    }
}


/**
 * delete save files/ include new files
 * 
 * @param string $file_path
 * @param string $serialized_file_data 
 * @return remaining files array
 */
if (!function_exists('update_saved_files')) {

    function update_saved_files($file_path, $serialized_file_data, $new_files_array) {
        if ($serialized_file_data && $file_path) {
            $files_array = unserialize($serialized_file_data);
            $request = \Config\Services::request();

            //is deleted any file?
            $delete_files = $request->getPost("delete_file");
            if (!is_array($delete_files)) {
                $delete_files = array();
            }

            if (!is_array($new_files_array)) {
                $new_files_array = array();
            }

            //delete files from directory and update the database array
            foreach ($files_array as $file) {
                $file_name = get_array_value($file, "file_name");
                if (in_array($file_name, $delete_files)) {
                    delete_app_files($file_path, array($file));
                } else {
                    array_push($new_files_array, $file);
                }
            }
        }
        return $new_files_array;
    }
}


/**
 * return a file path of general files based on context
 * 
 * @param string $context   client/team_member/...
 * @param integer $context_id   client_id/team_member_id/...
 * @return string of file path
 */
if (!function_exists('get_general_file_path')) {

    function get_general_file_path($context, $context_id) {
        if ($context && $context_id) {
            $target_path = get_setting("general_file_path");
            if (!$target_path) {
                $target_path = 'files/general/';
            }

            return $target_path . $context . "/" . $context_id . "/";
        }
    }
}


/**
 * return a list of language by scanning the files from language directory.

 * @return array
 */
if (!function_exists('get_language_list')) {

    function get_language_list($type = "") {


        $supported_locales = get_setting("supportedLocales");

        if ($type == "list") {
            return $supported_locales;
        } else {
            $language_dropdown = array();
            foreach (get_setting("supportedLocales") as $language) {
                $language_dropdown[$language] = ucfirst($language);
            }
            return $language_dropdown;
        }
    }
}

if (!function_exists('update_file_indexes')) {

    function update_file_indexes($old_files = "", $new_files_array = array()) {
        if (isset($old_files) && $old_files && $new_files_array) {
            $old_files_array = unserialize($old_files);
            if (count($old_files_array)) {
                $final_files_array = array();

                foreach ($new_files_array as $file_name) {
                    //get old file array containing this sorted file name
                    array_push($final_files_array, get_array_value($old_files_array, array_search($file_name, array_column($old_files_array, "file_name"))));
                }

                return $final_files_array;
            }
        }
    }
}

if (!function_exists('get_store_item_image')) {

    function get_store_item_image($files) {
        $files = @unserialize($files);
        if ($files && is_array($files) && count($files)) {
            $first_file = get_array_value($files, 0);
            return get_source_url_of_file($first_file, get_setting("timeline_file_path"), "thumbnail");
        } else {
            return get_source_url_of_file(array("file_name" => "store-item-no-image.png"), get_setting("system_file_path"));
        }
    }
}


/**
 * check the file is pdf or text
 * 
 * @param string $file_name
 * @return true/false
 */
if (!function_exists('is_iframe_preview_available')) {

    function is_iframe_preview_available($file_name = "") {
        $extension = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $image_files = array("pdf", "txt");
        return (in_array($extension, $image_files)) ? true : false;
    }
}


/**
 * Replace large file name with some ... in the middle. 
 * 
 * @param string $file_name
 * @return short file name
 */
if (!function_exists('short_file_name')) {

    function short_file_name($file_name = "") {
        if (strlen($file_name) > 70) {

            $pattern = '/^(.{32}).*(.{5})(\..{3,4})$/';

            $new_file_name = preg_replace($pattern, '$1................$2$3', $file_name);

            return $new_file_name;
        } else {
            return $file_name;
        }
    }
}

/**
 * remove file extension from actual file name
 * 
 * @param string $file_name
 * @return filename
 */
if (!function_exists('remove_file_extension')) {

    function remove_file_extension($file_name = "") {
        $lastDotPosition = strrpos($file_name, ".");
        if ($lastDotPosition !== false) {
            return substr($file_name, 0, $lastDotPosition);
        } else {
            return $file_name; // No file extension found, return the original name
        }
    }
}

/**
 * prepare file preview command data
 * 
 * @param string $file_name
 * @return array of info
 */
if (!function_exists('get_file_preview_common_data')) {

    function get_file_preview_common_data($file_info, $file_path) {
        $data = array();
        $file_url = get_source_url_of_file(make_array_of_file($file_info), $file_path);

        $file_name = isset($file_info->file_name) ? $file_info->file_name : "";
        $data["file_url"] = $file_url;
        $data["is_image_file"] = is_image_file($file_name);
        $data["is_google_preview_available"] = is_google_preview_available($file_name);
        $data["is_viewable_video_file"] = is_viewable_video_file($file_name);
        $data["is_google_drive_file"] = (isset($file_info->file_id) && isset($file_info->service_type) && $file_info->file_id && $file_info->service_type == "google") ? true : false;
        $data["is_iframe_preview_available"] = is_iframe_preview_available($file_name);
        $data["file_info"] = $file_info;
        $data['file_id'] = $file_info->id;

        return $data;
    }
}
