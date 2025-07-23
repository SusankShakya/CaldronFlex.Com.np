<div class="card">
    <div class="card-header fw-bold client-info">
        <span class="d-inline-block mt-1">
            <i data-feather="briefcase" class="icon-16"></i> &nbsp;<?php echo app_lang("client_info"); ?>
        </span>

        <?php if ($can_edit_clients) { ?>
            <div class="float-end">
                <div class="action-option" data-bs-toggle="dropdown" aria-expanded="true">
                    <i data-feather="more-horizontal" class="icon-16"></i>
                </div>
                <ul class="dropdown-menu" role="menu">
                    <li role="presentation"><?php echo modal_anchor(get_uri("clients/modal_form"), "<i data-feather='edit' class='icon-16'></i> " . app_lang('edit'), array("class" => "dropdown-item", "title" => app_lang('edit_client'), "data-post-id" => $client_info->id)); ?></li>

                </ul>
            </div>
        <?php } ?>

    </div>

    <div class="card-body">
        <ul class="list-group info-list pt0 border-top-0">
            <li class="list-group-item pt0 border-top-0">
                <span class="mr10" title="<?php echo app_lang("type"); ?>"><i data-feather="hexagon" class="icon-16"></i></span><span><?php echo app_lang($client_info->type); ?></span>
            </li>
            <li class="list-group-item">
                <span class="mr10" title="<?php echo app_lang("labels"); ?>"><i data-feather="tag" class="icon-16"></i></span>
                <?php
                $labels = "<span class='text-off'>" . app_lang("add") . " " . app_lang("label") . "<span>";

                if (isset($client_labels) && $client_labels) {
                    $labels = $client_labels;
                }

                echo js_anchor($labels, array(
                    'title' => "",
                    "class" => "",
                    "data-id" => $client_info->id,
                    "data-value" => $client_info->labels,
                    "data-act" => "client-modifier",
                    "data-modifier-group" => "client_info",
                    "data-field" => "labels",
                    "data-multiple-tags" => "1",
                    "data-action-url" => get_uri("clients/update_client_info/$client_info->id/labels")
                ));
                ?>
            </li>
            <li class="list-group-item">
                <span class="mr10" title="<?php echo app_lang("client_groups"); ?>"><i data-feather="align-left" class="icon-16"></i></span>
                <?php
                $groups = "<span class='text-off'>" . app_lang("add") . " " . app_lang("client_groups") . "</span>";

                if (isset($client_info->client_groups) && $client_info->client_groups) {
                    $client_groups = explode(',', $client_info->client_groups);
                    $groups = "";

                    foreach ($client_groups as $group) {
                        $groups .= '<span class="badge rounded-pill text-default b-a mr-1 mt0">' . trim($group) . '</span> ';
                    }
                }

                echo js_anchor($groups, array(
                    'title' => "",
                    "class" => "",
                    "data-id" => $client_info->id,
                    "data-value" => $client_info->group_ids,
                    "data-act" => "client-modifier",
                    "data-modifier-group" => "client_info",
                    "data-field" => "group_ids",
                    "data-multiple-tags" => "1",
                    "data-action-url" => get_uri("clients/update_client_info/$client_info->id/group_ids")
                ));
                ?>
            </li>
            <li class="list-group-item">
                <span class="mr10" title="<?php echo app_lang("owner"); ?>"><i data-feather="user" class="icon-16"></i></span>
                <?php

                $image_url = get_avatar($client_info->owner_avatar);
                echo $owner_avatar = ($client_info->owner_avatar || $can_edit_clients) ? "<span class='avatar avatar-xxs mr5'><img id='client-owner-avatar' src='$image_url' alt='...'></span>" : "";

                echo js_anchor(
                    $client_info->owner_name ? $client_info->owner_name : "<span class='text-off'>" . app_lang("add") . " " . app_lang("owner") . "<span>",
                    array(
                        'title' => "",
                        "class" => "",
                        "data-id" => $client_info->id,
                        "data-value" => $client_info->owner_id,
                        "data-act" => "client-modifier",
                        "data-modifier-group" => "client_info",
                        "data-field" => "owner_id",
                        "data-action-url" => get_uri("clients/update_client_info/$client_info->id/owner_id")
                    )
                );
                ?>
            </li>
            <li class="list-group-item">
                <span class="mr10" title="<?php echo app_lang("managers"); ?>"><i data-feather="users" class="icon-16"></i></span>
                <?php

                echo js_anchor(
                    $client_info->managers ? $managers : "<span class='text-off'>" . app_lang("add") . " " . app_lang("managers") . "<span>",
                    array(
                        'title' => "",
                        "class" => "",
                        "data-id" => $client_info->id,
                        "data-value" => $client_info->managers,
                        "data-act" => "client-modifier",
                        "data-modifier-group" => "client_info",
                        "data-field" => "managers",
                        "data-multiple-tags" => "1",
                        "data-action-url" => get_uri("clients/update_client_info/$client_info->id/managers")
                    )
                );
                ?>
            </li>
            <?php if ($client_info->address || $client_info->city || $client_info->state || $client_info->zip || $client_info->country) { ?>
                <li class="list-group-item">
                    <div class="d-flex">
                        <span class="mr10" title="<?php echo app_lang("address"); ?>"><i data-feather="map-pin" class="icon-16"></i></span>
                        <span class="flex-fill">
                            <div><?php echo $client_info->address; ?></div>
                            <div><?php echo $client_info->city . ", " . $client_info->state . ", " . $client_info->zip; ?></div>
                            <div><?php echo $client_info->country; ?></div>
                        </span>
                    </div>
                </li>
            <?php } ?>
            <?php if ($client_info->phone) { ?>
                <li class="list-group-item">
                    <span class="mr10" title="<?php echo app_lang("phone"); ?>"><i data-feather="phone" class="icon-16"></i></span>
                    <label><?php echo $client_info->phone ? $client_info->phone : "<span class='text-off'>" . app_lang("not_found") . "</span>"; ?></label>
                </li>
            <?php } ?>
            <?php
            if ($client_info->website) {
                $website = $client_info->website;

                if (!preg_match("/^https?:\/\//", $website)) {
                    $website = "https://" . $website;
                }
            ?>
                <li class="list-group-item">
                    <span class="mr10" title="<?php echo app_lang("website"); ?>">
                        <i data-feather="globe" class="icon-16"></i>
                    </span>
                    <label>
                        <a href="<?php echo $website; ?>" target="_blank"><?php echo $client_info->website; ?></a>
                    </label>
                </li>
            <?php } ?>
            <?php if ($client_info->lead_source_id) { ?>
                <li class="list-group-item">
                    <span class="mr10" title="<?php echo app_lang("source"); ?>"><i data-feather="anchor" class="icon-16"></i></span>
                    <label><?php echo $client_info->lead_source_title; ?></label>
                </li>
            <?php } ?>
            <?php if ($client_info->currency) { ?>
                <li class="list-group-item">
                    <strong class="mr10"><?php echo app_lang("currency") . ": "; ?></strong>
                    <span class="<?php echo $client_info->currency_symbol ? "b-r pr10" : ""; ?>"><?php echo $client_info->currency; ?></span> <?php if ($client_info->currency_symbol) { ?> <span class="pl10" title="<?php echo app_lang("currency_symbol"); ?>"><?php echo $client_info->currency_symbol; ?></span> <?php } ?>
                </li>
            <?php } ?>
            <?php if ($client_info->vat_number) { ?>
                <li class="list-group-item"><strong><?php echo app_lang("vat_number") . ": "; ?></strong><label><?php echo $client_info->vat_number; ?></label></li>
            <?php } ?>
            <?php if ($client_info->gst_number) { ?>
                <li class="list-group-item"><strong><?php echo app_lang("gst_number") . ": "; ?></strong><label><?php echo $client_info->gst_number; ?></label></li>
            <?php } ?>
            <?php if ($client_info->disable_online_payment) { ?>
                <li class="list-group-item">
                    <i data-feather="alert-triangle" class="icon-16 mr5"></i> <?php echo app_lang("online_payment_disabled"); ?>
                </li>
            <?php } ?>
        </ul>
    </div>
</div>