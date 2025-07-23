<?php

$statuses = array();
foreach ($project_statuses as $status) {

    $is_selected = false;

    if (isset($selected_status_id) && $selected_status_id) {
        //if there is any specific status selected, select only the status.
        if ($selected_status_id == $status->id) {
            $is_selected = true;
        }
    }

    $statuses[] = array("text" => ($status->title_language_key ? app_lang($status->title_language_key) : $status->title), "value" => $status->id, "isChecked" => $is_selected);
}

echo json_encode($statuses);
