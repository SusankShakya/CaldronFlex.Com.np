<div class="card">
    <div class="card-header fw-bold lead-info">
        <span class="d-inline-block mt-1">
            <i data-feather="layers" class="icon-16"></i> &nbsp;<?php echo app_lang("lead_info"); ?>
        </span>

        <div class="float-end">
            <div class="action-option" data-bs-toggle="dropdown" aria-expanded="true">
                <i data-feather="more-horizontal" class="icon-16"></i>
            </div>
            <ul class="dropdown-menu" role="menu">
                <li role="presentation"><?php echo modal_anchor(get_uri("leads/modal_form"), "<i data-feather='edit' class='icon-16'></i> " . app_lang('edit'), array("class" => "dropdown-item", "title" => app_lang('edit_lead'), "data-post-id" => $lead_info->id)); ?></li>
            </ul>
        </div>

    </div>

    <div class="card-body">
        <ul class="list-group info-list pt0 border-top-0">
            <li class="list-group-item pt0 border-top-0">
                <span class="mr10" title="<?php echo app_lang("type"); ?>"><i data-feather="hexagon" class="icon-16"></i></span><span><?php echo app_lang($lead_info->type); ?></span>
            </li>
            <li class="list-group-item">
                <span class="mr10" title="<?php echo app_lang("status"); ?>"><i data-feather="check-circle" class="icon-16"></i></span>
                <?php
                $status = "<span class='text-off'>" . app_lang("add") . " " . app_lang("status") . "<span>";

                $lead_status = "<span class='mt0 badge rounded-pill' style='background-color: $lead_info->lead_status_color'>" . $lead_info->lead_status_title . "</span>";
                if (isset($lead_status) && $lead_status) {
                    $status = $lead_status;
                }

                echo js_anchor($status, array(
                    'title' => "",
                    "class" => "",
                    "data-id" => $lead_info->id,
                    "data-value" => $lead_info->lead_status_id,
                    "data-act" => "lead-modifier",
                    "data-modifier-group" => "lead_info",
                    "data-field" => "status",
                    "data-action-url" => get_uri("leads/update_lead_info/$lead_info->id/lead_status_id")
                ));
                ?>
            </li>
            <li class="list-group-item">
                <span class="mr10" title="<?php echo app_lang("source"); ?>"><i data-feather="search" class="icon-16"></i></span>
                <?php
                $source = "<span class='text-off'>" . app_lang("add") . " " . app_lang("source") . "<span>";

                $lead_source = "<span class='mt0 badge rounded-pill text-default b-a'>" . $lead_info->lead_source_title . "</span>";
                if (isset($lead_source) && $lead_source) {
                    $source = $lead_source;
                }

                echo js_anchor($source, array(
                    'title' => "",
                    "class" => "",
                    "data-id" => $lead_info->id,
                    "data-value" => $lead_info->lead_source_id,
                    "data-act" => "lead-modifier",
                    "data-modifier-group" => "lead_info",
                    "data-field" => "source",
                    "data-action-url" => get_uri("leads/update_lead_info/$lead_info->id/lead_source_id")
                ));
                ?>
            </li>
            <li class="list-group-item">
                <span class="mr10" title="<?php echo app_lang("labels"); ?>"><i data-feather="tag" class="icon-16"></i></span>
                <?php
                $labels = "<span class='text-off'>" . app_lang("add") . " " . app_lang("label") . "<span>";

                if (isset($lead_labels) && $lead_labels) {
                    $labels = $lead_labels;
                }

                echo js_anchor($labels, array(
                    'title' => "",
                    "class" => "",
                    "data-id" => $lead_info->id,
                    "data-value" => $lead_info->labels,
                    "data-act" => "lead-modifier",
                    "data-modifier-group" => "lead_info",
                    "data-field" => "labels",
                    "data-multiple-tags" => "1",
                    "data-action-url" => get_uri("leads/update_lead_info/$lead_info->id/labels")
                ));
                ?>
            </li>
            <li class="list-group-item">
                <span class="mr10" title="<?php echo app_lang("owner"); ?>"><i data-feather="user" class="icon-16"></i></span>
                <?php

                $image_url = get_avatar($lead_info->owner_avatar);
                echo "<span class='avatar avatar-xxs mr5'><img id='lead-owner-avatar' src='$image_url' alt='...'></span>";

                echo js_anchor(
                    $lead_info->owner_name ? $lead_info->owner_name : "<span class='text-off'>" . app_lang("add") . " " . app_lang("owner") . "<span>",
                    array(
                        'title' => "",
                        "class" => "",
                        "data-id" => $lead_info->id,
                        "data-value" => $lead_info->owner_id,
                        "data-act" => "lead-modifier",
                        "data-modifier-group" => "lead_info",
                        "data-field" => "owner_id",
                        "data-action-url" => get_uri("leads/update_lead_info/$lead_info->id/owner_id")
                    )
                );
                ?>
            </li>
            <li class="list-group-item">
                <span class="mr10" title="<?php echo app_lang("managers"); ?>"><i data-feather="users" class="icon-16"></i></span>
                <?php

                echo js_anchor(
                    $lead_info->managers ? $managers : "<span class='text-off'>" . app_lang("add") . " " . app_lang("managers") . "<span>",
                    array(
                        'title' => "",
                        "class" => "",
                        "data-id" => $lead_info->id,
                        "data-value" => $lead_info->managers,
                        "data-act" => "lead-modifier",
                        "data-modifier-group" => "lead_info",
                        "data-field" => "managers",
                        "data-multiple-tags" => "1",
                        "data-action-url" => get_uri("leads/update_lead_info/$lead_info->id/managers")
                    )
                );
                ?>
            </li>
            <?php if ($lead_info->address || $lead_info->city || $lead_info->state || $lead_info->zip || $lead_info->country) { ?>
                <li class="list-group-item">
                    <div class="d-flex">
                        <span class="mr10" title="<?php echo app_lang("address"); ?>"><i data-feather="map-pin" class="icon-16"></i></span>
                        <span class="flex-fill">
                            <div><?php echo $lead_info->address; ?></div>
                            <div><?php echo $lead_info->city . ", " . $lead_info->state . ", " . $lead_info->zip; ?></div>
                            <div><?php echo $lead_info->country; ?></div>
                        </span>
                    </div>
                </li>
            <?php } ?>
            <?php if ($lead_info->phone) { ?>
                <li class="list-group-item">
                    <span class="mr10" title="<?php echo app_lang("phone"); ?>"><i data-feather="phone" class="icon-16"></i></span>
                    <label><?php echo $lead_info->phone ? $lead_info->phone : "<span class='text-off'>" . app_lang("not_found") . "</span>"; ?></label>
                </li>
            <?php } ?>
            <?php
            if ($lead_info->website) {
                $website = $lead_info->website;

                if (!preg_match("/^https?:\/\//", $website)) {
                    $website = "https://" . $website;
                }
            ?>
                <li class="list-group-item">
                    <span class="mr10" title="<?php echo app_lang("website"); ?>">
                        <i data-feather="globe" class="icon-16"></i>
                    </span>
                    <label>
                        <a href="<?php echo $website; ?>" target="_blank"><?php echo $lead_info->website; ?></a>
                    </label>
                </li>
            <?php } ?>
            <?php if ($lead_info->currency) { ?>
                <li class="list-group-item">
                    <strong class="mr10"><?php echo app_lang("currency") . ": "; ?></strong>
                    <span class="<?php echo $lead_info->currency_symbol ? "b-r pr10" : ""; ?>"><?php echo $lead_info->currency; ?></span> <?php if ($lead_info->currency_symbol) { ?> <span class="pl10" title="<?php echo app_lang("currency_symbol"); ?>"><?php echo $lead_info->currency_symbol; ?></span> <?php } ?>
                </li>
            <?php } ?>
            <?php if ($lead_info->vat_number) { ?>
                <li class="list-group-item"><strong><?php echo app_lang("vat_number") . ": "; ?></strong><label><?php echo $lead_info->vat_number; ?></label></li>
            <?php } ?>
            <?php if ($lead_info->gst_number) { ?>
                <li class="list-group-item"><strong><?php echo app_lang("gst_number") . ": "; ?></strong><label><?php echo $lead_info->gst_number; ?></label></li>
            <?php } ?>
        </ul>
    </div>
</div>