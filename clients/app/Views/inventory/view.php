<div class="modal-body clearfix">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <tr>
                            <td class="w30p"><strong><?php echo app_lang('item'); ?></strong></td>
                            <td><?php echo $model_info->item_title; ?></td>
                        </tr>
                        <tr>
                            <td><strong><?php echo app_lang('warehouse'); ?></strong></td>
                            <td><?php echo $model_info->warehouse_name; ?></td>
                        </tr>
                        <tr>
                            <td><strong><?php echo app_lang('current_stock'); ?></strong></td>
                            <td><?php echo $model_info->current_stock; ?></td>
                        </tr>
                        <tr>
                            <td><strong><?php echo app_lang('reserved_stock'); ?></strong></td>
                            <td><?php echo $model_info->reserved_stock; ?></td>
                        </tr>
                        <tr>
                            <td><strong><?php echo app_lang('available_stock'); ?></strong></td>
                            <td><?php echo ($model_info->current_stock - $model_info->reserved_stock); ?></td>
                        </tr>
                        <tr>
                            <td><strong><?php echo app_lang('reorder_level'); ?></strong></td>
                            <td><?php echo $model_info->reorder_level; ?></td>
                        </tr>
                        <tr>
                            <td><strong><?php echo app_lang('unit_of_measure'); ?></strong></td>
                            <td><?php echo $model_info->unit_of_measure ? $model_info->unit_of_measure : "-"; ?></td>
                        </tr>
                        <tr>
                            <td><strong><?php echo app_lang('status'); ?></strong></td>
                            <td>
                                <?php
                                $available_stock = $model_info->current_stock - $model_info->reserved_stock;
                                if ($available_stock <= 0) {
                                    echo "<span class='badge bg-danger'>" . app_lang('out_of_stock') . "</span>";
                                } else if ($available_stock <= $model_info->reorder_level) {
                                    echo "<span class='badge bg-warning'>" . app_lang('low_stock') . "</span>";
                                } else {
                                    echo "<span class='badge bg-success'>" . app_lang('in_stock') . "</span>";
                                }
                                ?>
                            </td>
                        </tr>
                        <?php if ($model_info->notes) { ?>
                            <tr>
                                <td><strong><?php echo app_lang('notes'); ?></strong></td>
                                <td><?php echo nl2br($model_info->notes); ?></td>
                            </tr>
                        <?php } ?>
                        <tr>
                            <td><strong><?php echo app_lang('last_updated'); ?></strong></td>
                            <td><?php echo format_to_datetime($model_info->updated_at); ?></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <?php if ($inventory_transactions) { ?>
            <div class="row mt-3">
                <div class="col-md-12">
                    <h4><?php echo app_lang('recent_transactions'); ?></h4>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th><?php echo app_lang('date'); ?></th>
                                    <th><?php echo app_lang('type'); ?></th>
                                    <th><?php echo app_lang('quantity'); ?></th>
                                    <th><?php echo app_lang('reference'); ?></th>
                                    <th><?php echo app_lang('notes'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($inventory_transactions as $transaction) { ?>
                                    <tr>
                                        <td><?php echo format_to_datetime($transaction->transaction_date); ?></td>
                                        <td>
                                            <?php
                                            $type_label = "";
                                            switch ($transaction->transaction_type) {
                                                case "purchase":
                                                    $type_label = "<span class='text-success'>" . app_lang('purchase') . "</span>";
                                                    break;
                                                case "sale":
                                                    $type_label = "<span class='text-danger'>" . app_lang('sale') . "</span>";
                                                    break;
                                                case "adjustment":
                                                    $type_label = "<span class='text-info'>" . app_lang('adjustment') . "</span>";
                                                    break;
                                                case "transfer":
                                                    $type_label = "<span class='text-warning'>" . app_lang('transfer') . "</span>";
                                                    break;
                                            }
                                            echo $type_label;
                                            ?>
                                        </td>
                                        <td>
                                            <?php
                                            if ($transaction->quantity > 0) {
                                                echo "<span class='text-success'>+" . $transaction->quantity . "</span>";
                                            } else {
                                                echo "<span class='text-danger'>" . $transaction->quantity . "</span>";
                                            }
                                            ?>
                                        </td>
                                        <td><?php echo $transaction->reference_number ? $transaction->reference_number : "-"; ?></td>
                                        <td><?php echo $transaction->notes ? $transaction->notes : "-"; ?></td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php } ?>
    </div>
</div>

<div class="modal-footer">
    <button type="button" class="btn btn-default" data-bs-dismiss="modal"><span data-feather="x" class="icon-16"></span> <?php echo app_lang('close'); ?></button>
</div>