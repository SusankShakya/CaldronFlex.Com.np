<?php

register_data_insert_hook(function ($hook_data) {
  
    $Hooks = new App\Libraries\Hooks();
    $table_without_prefix = get_array_value($hook_data, "table_without_prefix");
    if ($table_without_prefix === "invoice_payments") {   
        $Hooks->change_order_status_after_payment($hook_data);
    }

    $Hooks->check_automations($hook_data);

});
