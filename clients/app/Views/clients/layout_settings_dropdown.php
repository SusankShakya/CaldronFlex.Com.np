<?php if ($login_user->is_admin) { ?>
    <div class="action-option <?php echo $view_type != 'overview' ? 'btn square-btn float-start me-3' : '' ?>" data-bs-toggle="dropdown" aria-expanded="true">
        <i data-feather="more-horizontal" class="icon-16"></i>
    </div>
    <ul class="dropdown-menu" role="menu">
        <?php if ($view_type != "overview") { ?>
            <li role="presentation"><?php echo ajax_anchor(get_uri("settings/save_details_page_layout_settings"), "<i data-feather='layout' class='icon-16'></i> " . app_lang('move_to_overview'), array("class" => "dropdown-item", "data-post-context" => $context, "data-post-value" => "right_panel", "data-reload-on-success" => true)); ?></li>
        <?php } else { ?>
            <li role="presentation"><?php echo ajax_anchor(get_uri("settings/save_details_page_layout_settings"), "<i data-feather='layout' class='icon-16'></i> " . app_lang('move_to_tab'), array("class" => "dropdown-item", "data-post-context" => $context, "data-post-value" => "tab", "data-reload-on-success" => true)); ?></li>
        <?php } ?>
    </ul>
<?php } ?>