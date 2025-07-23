<div id="proposal-top-bar" class="details-view-status-section mb20">
    <div class="page-title no-bg clearfix mb5 no-border">
        <h1 class="pl0">
            <span><i data-feather="anchor" class='icon'></i></span>
            <?php echo get_proposal_id($proposal_info->id); ?>
            <input type="" value="" class="hidden-input-field " />
        </h1>

        <div class="title-button-group mr0">
            <?php
            echo anchor(get_uri("offer/preview/" . $proposal_info->id . "/" . $proposal_info->public_key), "<i data-feather='external-link' class='icon-16'></i> " . app_lang('proposal') . " " . app_lang("url"), array("class" => "btn btn-default", "target" => "_blank"));

            if ($proposal_status == "draft" || $proposal_status == "sent") {
                if ($client_info->is_lead) {
                    echo modal_anchor(get_uri("proposals/send_proposal_modal_form/" . $proposal_info->id), "<i data-feather='mail' class='icon-16'></i> " . app_lang('send_to_lead'), array("class" => "btn btn-primary mr0", "title" => app_lang('send_to_lead'), "data-post-id" => $proposal_info->id, "data-post-is_lead" => true, "role" => "menuitem", "tabindex" => "-1"));
                } else {
                    echo modal_anchor(get_uri("proposals/send_proposal_modal_form/" . $proposal_info->id), "<i data-feather='mail' class='icon-16'></i> " . app_lang('send_to_client'), array("class" => "btn btn-primary mr0", "title" => app_lang('send_to_client'), "data-post-id" => $proposal_info->id, "role" => "menuitem", "tabindex" => "-1"));
                }
            }
            ?>
        </div>
    </div>

    <?php
    echo "<span>$proposal_status_label</span>";

    $last_email_sent_date = (is_date_exists($proposal_info->last_email_sent_date)) ? format_to_date($proposal_info->last_email_sent_date, FALSE) : app_lang("never");
    echo "<span class='badge rounded-pill large text-default b-a' title='" . app_lang("last_email_sent") . "'><i data-feather='mail' class='icon-14 text-off '></i> $last_email_sent_date</span>";
    ?>
</div>