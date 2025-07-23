<?php

$statuses = array(array("id" => "", "text" => "- " . app_lang("status") . " -"));
foreach ($lead_statuses as $status) {
    $selected_status = false;
    if (isset($selected_status_id) && $selected_status_id) {
        if ($status->id == $selected_status_id) {
            $selected_status = true;
        } else {
            $selected_status = false;
        }
    }

    $statuses[] = array("id" => $status->id, "text" => $status->title, "isSelected" => $selected_status);
}

echo json_encode($statuses);
