<div class="card">
    <div class="card-body">
        <div class="box">
            <div class="box-content b-r pb15">
                <?php echo anchor(get_uri("store/order_preview/" . $order_info->id . "/1"), "<i data-feather='search' class='icon-16'></i> " . app_lang('order_preview'), array("title" => app_lang('order_preview'))); ?>
            </div>
            <div class="box-content pl15 pb15">
                <?php echo anchor(get_uri("orders/download_pdf/" . $order_info->id . "/view"), "<i data-feather='file-text' class='icon-16'></i> " . app_lang('view_pdf'), array("title" => app_lang('view_pdf'), "target" => "_blank", "class" => "")); ?>
            </div>
        </div>
        <div class="box b-t">
            <div class="box-content pt15 text-center">
                <?php echo anchor(get_uri("orders/download_pdf/" . $order_info->id), "<i data-feather='download' class='icon-16'></i> " . app_lang('download_pdf'), array("title" => app_lang('download_pdf'), "class" => "")); ?>
            </div>
        </div>
    </div>
</div>