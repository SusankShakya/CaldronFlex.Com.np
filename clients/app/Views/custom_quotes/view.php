<div class="modal-body clearfix">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h4><?php echo app_lang('custom_quote') . " #" . $quote_info->quote_number; ?></h4>
                        <span class="badge bg-<?php echo $status_class; ?>"><?php echo app_lang($quote_info->status); ?></span>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <strong><?php echo app_lang('client'); ?>:</strong><br>
                                <?php echo $quote_info->client_name; ?>
                            </div>
                            <div class="col-md-6">
                                <strong><?php echo app_lang('item'); ?>:</strong><br>
                                <?php echo $quote_info->item_title; ?>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-3">
                                <strong><?php echo app_lang('quantity'); ?>:</strong><br>
                                <?php echo $quote_info->quantity; ?>
                            </div>
                            <div class="col-md-3">
                                <strong><?php echo app_lang('area_sqft'); ?>:</strong><br>
                                <?php echo $quote_info->area ?: '-'; ?>
                            </div>
                            <div class="col-md-3">
                                <strong><?php echo app_lang('base_price'); ?>:</strong><br>
                                <?php echo to_currency($quote_info->base_price); ?>
                            </div>
                            <div class="col-md-3">
                                <strong><?php echo app_lang('custom_price'); ?>:</strong><br>
                                <?php echo to_currency($quote_info->custom_price); ?>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <strong><?php echo app_lang('discount'); ?>:</strong><br>
                                <?php echo $quote_info->discount_percentage; ?>%
                            </div>
                            <div class="col-md-4">
                                <strong><?php echo app_lang('valid_until'); ?>:</strong><br>
                                <?php echo format_to_date($quote_info->valid_until); ?>
                            </div>
                            <div class="col-md-4">
                                <strong><?php echo app_lang('created_date'); ?>:</strong><br>
                                <?php echo format_to_datetime($quote_info->created_at); ?>
                            </div>
                        </div>

                        <?php if ($quote_info->variants_data) { ?>
                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <strong><?php echo app_lang('variants'); ?>:</strong><br>
                                    <?php
                                    $variants = json_decode($quote_info->variants_data, true);
                                    if ($variants && is_array($variants)) {
                                        foreach ($variants as $variant_id => $value) {
                                            echo "<span class='badge bg-secondary me-1'>" . $variant_names[$variant_id] . ": " . $value . "</span>";
                                        }
                                    }
                                    ?>
                                </div>
                            </div>
                        <?php } ?>

                        <?php if ($quote_info->calculation_data) { ?>
                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <strong><?php echo app_lang('price_breakdown'); ?>:</strong>
                                    <div class="table-responsive mt-2">
                                        <table class="table table-sm table-bordered">
                                            <?php
                                            $calc_data = json_decode($quote_info->calculation_data, true);
                                            if (isset($calc_data['breakdown'])) {
                                                foreach ($calc_data['breakdown'] as $item) {
                                                    echo "<tr>";
                                                    echo "<td>" . $item['description'] . "</td>";
                                                    echo "<td class='text-end'>" . to_currency($item['amount']) . "</td>";
                                                    echo "</tr>";
                                                }
                                            }
                                            ?>
                                            <tr class="table-active">
                                                <td><strong><?php echo app_lang('total'); ?></strong></td>
                                                <td class="text-end"><strong><?php echo to_currency($quote_info->custom_price); ?></strong></td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        <?php } ?>

                        <?php if ($quote_info->notes) { ?>
                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <strong><?php echo app_lang('notes'); ?>:</strong><br>
                                    <div class="text-muted"><?php echo nl2br($quote_info->notes); ?></div>
                                </div>
                            </div>
                        <?php } ?>

                        <div class="row">
                            <div class="col-md-6">
                                <strong><?php echo app_lang('created_by'); ?>:</strong><br>
                                <?php echo $quote_info->created_by_name; ?>
                            </div>
                            <?php if ($quote_info->approved_by) { ?>
                                <div class="col-md-6">
                                    <strong><?php echo app_lang('approved_by'); ?>:</strong><br>
                                    <?php echo $quote_info->approved_by_name; ?><br>
                                    <small class="text-muted"><?php echo format_to_datetime($quote_info->approved_at); ?></small>
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal-footer">
    <?php if ($can_edit && $quote_info->status == 'pending') { ?>
        <?php echo modal_anchor(get_uri("custom_quotes/modal_form/" . $quote_info->id), "<i data-feather='edit' class='icon-16'></i> " . app_lang('edit'), array("class" => "btn btn-default", "title" => app_lang('edit_custom_quote'))); ?>
    <?php } ?>
    
    <?php if ($can_approve && $quote_info->status == 'pending') { ?>
        <?php echo ajax_anchor(get_uri("custom_quotes/update_status/" . $quote_info->id . "/approved"), "<i data-feather='check-circle' class='icon-16'></i> " . app_lang('approve'), array("class" => "btn btn-success", "title" => app_lang('approve'), "data-reload-on-success" => "1")); ?>
        <?php echo ajax_anchor(get_uri("custom_quotes/update_status/" . $quote_info->id . "/rejected"), "<i data-feather='x-circle' class='icon-16'></i> " . app_lang('reject'), array("class" => "btn btn-danger", "title" => app_lang('reject'), "data-reload-on-success" => "1")); ?>
    <?php } ?>
    
    <button type="button" class="btn btn-default" data-bs-dismiss="modal"><span data-feather="x" class="icon-16"></span> <?php echo app_lang('close'); ?></button>
</div>