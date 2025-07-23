<div class="card">
    <div class="card-body">
        <div class="box">
            <div class="box-content b-r pb15">
                <?php echo anchor(get_uri("invoices/preview/" . $invoice_info->id . "/1"), "<i data-feather='search' class='icon-16'></i> " . app_lang('preview'), array("title" => app_lang('preview'), "class" => "")); ?>
            </div>
            <div class="box-content pl15 pb15">
                <?php echo js_anchor("<i data-feather='printer' class='icon-16'></i> " . app_lang('print'), array('title' => app_lang('print'), 'id' => 'print-invoice-btn', "class" => "")); ?>
            </div>
        </div>
        <div class="box b-t">
            <div class="box-content pt15 b-r">
                <?php echo anchor(get_uri("invoices/download_pdf/" . $invoice_info->id . "/view"), "<i data-feather='file-text' class='icon-16'></i> " . app_lang('view_pdf'), array("title" => app_lang('view_pdf'), "target" => "_blank", "class" => "pdf-view-btn")); ?>
                <span class="d-block d-md-none"><?php echo js_anchor("<i data-feather='file-text' class='icon-16'></i> " . app_lang('view_pdf'), array('title' => app_lang('view_pdf'), "data-group" => "invoice-pdf", "data-toggle" => "app-modal", "data-sidebar" => "0", "data-url" => get_uri("invoices/download_pdf/" . $invoice_info->id . "/view/0/1"), "class" => "mobile-pdf-view-btn")) ?></span>

            </div>
            <div class="box-content pl15 pt15">
                <?php echo anchor(get_uri("invoices/download_pdf/" . $invoice_info->id), "<i data-feather='download' class='icon-16'></i> " . app_lang('download_pdf'), array("title" => app_lang('download_pdf'), "class" => "")); ?>
            </div>
        </div>
    </div>
</div>

<?php if (get_setting("enable_e_invoice")) { ?>
    <div class="card">
        <div class="card-body text-center">
            <?php echo anchor(get_uri("invoices/download_xml/" . $invoice_info->id), "<i data-feather='file' class='icon-16'></i> " . "Download XML", array("title" => "Download XML", "class" => "")); ?>
        </div>
    </div>
<?php } ?>