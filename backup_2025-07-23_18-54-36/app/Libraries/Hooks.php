<?php

namespace App\Libraries;

class Hooks {

    public function change_order_status_after_payment($hook_data) {
        $order_status_after_payment = get_setting("order_status_after_payment");
        if (!$order_status_after_payment) {
            return true;
        }

        $Invoices_model = model("App\Models\Invoices_model");
        $data = get_array_value($hook_data, "data");
        $invoice_info = $Invoices_model->get_one(get_array_value($data, "invoice_id"));

        //if there is any order_id, we assume that it's the associated invoice of that order
        if (!$invoice_info->order_id) {
            return true;
        }

        $Orders_model = model("App\Models\Orders_model");
        $Order_status_model = model("App\Models\Order_status_model");

        $first_status = $Order_status_model->get_first_status();
        $order_info = $Orders_model->get_one($invoice_info->order_id);
        if ($order_info->status_id !== $first_status) {
            return true;
        }

        $order_data["status_id"] = $order_status_after_payment;
        $Orders_model->ci_save($order_data, $invoice_info->order_id);
    }

    public function check_automations($hook_data) {
        $Automations = new Automations();

        $id = get_array_value($hook_data, "id");
        $table_name = get_array_value($hook_data, "table_without_prefix");
        $data = get_array_value($hook_data, "data");

        $event_name = "";

        if ($table_name == "ticket_comments") {
            $this->_modify_data_for_the_event_new_ticket_created_by_imap_email($event_name, $data, $id);
        }

        if ($event_name) {
            log_message('notice', 'Automation: -- Inital trigger. Event - ' . $event_name . ', id - ' . $id);
            $Automations->trigger_automations($event_name, $data, $id);
        }
    }

    private function _modify_data_for_the_event_new_ticket_created_by_imap_email(&$event_name, &$data, &$id) {
        $ticket_id = get_array_value($data, "ticket_id");

        if ($ticket_id) {
            $Tickets_model = model("App\Models\Tickets_model");
            $ticket_info = $Tickets_model->get_one($ticket_id);

            if ($ticket_info && $ticket_info->creator_email) {

                $event_name = "new_ticket_created_by_imap_email";
                $id = $ticket_info->id;

                $new_data = array();
                $new_data["title"] = $ticket_info->title;
                $new_data["description"] = get_array_value($data, "description");
                $data = $new_data;
            }
        }
    }
}
