<div id="order-top-bar" class="details-view-status-section mb20">
    <div class="page-title no-bg clearfix mb5 no-border">
        <h1 class="pl0">
            <span><i data-feather="shopping-cart" class='icon'></i></span>
            <?php echo get_order_id($order_info->id); ?>
        </h1>

        <div class="title-button-group mr0">
            <?php
            if ($show_invoice_option) {
                echo modal_anchor(get_uri("invoices/modal_form"), "<i data-feather='file-text' class='icon-16'></i> " . app_lang('create_invoice'), array("title" => app_lang("create_invoice"), "data-post-order_id" => $order_info->id, "class" => "btn btn-default"));
            }
            ?>
        </div>
    </div>

    <?php
    echo  js_anchor($order_info->order_status_title, array("style" => "background-color: $order_info->order_status_color", "class" => "badge rounded-pill large", "data-id" => $order_info->id, "data-value" => $order_info->status_id, "data-act" => "update-order-status"));

    $order_date = format_to_date($order_info->order_date, false);
    echo "<span class='badge rounded-pill large text-default b-a' title='" . app_lang("order_date") . "'> $order_date</span>";
    ?>
</div>