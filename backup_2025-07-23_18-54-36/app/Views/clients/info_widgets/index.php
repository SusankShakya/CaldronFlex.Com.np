<div class="row clearfix">
    <?php if ($show_invoice_info) { ?>
        <?php if ($show_project_info) { ?>
            <div class="col-md-3 col-sm-6 widget-container">
                <?php echo view("clients/info_widgets/tab", array("tab" => "projects")); ?>
            </div>
        <?php } ?>

        <?php if ($show_invoice_info) { ?>
            <div class="col-md-3 col-sm-6  widget-container">
                <?php echo view("clients/info_widgets/tab", array("tab" => "total_invoiced")); ?>
            </div>
        <?php } ?>

        <?php if (isset($show_payment_info) && $show_payment_info) { ?>
            <div class="col-md-3 col-sm-6  widget-container">
                <?php echo view("clients/info_widgets/tab", array("tab" => "payments")); ?>
            </div>
            <div class="col-md-3 col-sm-6  widget-container">
                <?php echo view("clients/info_widgets/tab", array("tab" => "due")); ?>
            </div>
        <?php } ?>

    <?php } ?>

    <?php if (!$show_project_info && !$show_invoice_info) { ?>
        <div class="col-sm-12 col-md-12" style="margin-top: 10%">
            <div class="text-center box">
                <div class="box-content" style="vertical-align: middle; height: 100%">
                    <span data-feather="meh" height="20rem" width="20rem" style="color:#CBCED0;"></span>
                </div>
            </div>
        </div>
    <?php } ?>
</div>