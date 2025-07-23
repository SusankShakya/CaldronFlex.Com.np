<?php

namespace App\Libraries;

use App\Libraries\Automations;

class Ticket {

    private $Users_model;
    private $Tickets_model;
    private $Ticket_comments_model;

    public function __construct() {
        $this->Users_model = model('App\Models\Users_model');
        $this->Tickets_model = model('App\Models\Tickets_model');
        $this->Ticket_comments_model = model('App\Models\Ticket_comments_model');
    }

    function create_ticket_from_imap($data = array()) {
        if (!$data) {
            return false;
        }

        $Automations = new Automations();
        $Automations->trigger_automations("imap_email_received", $data);
        if (!$data) { // the data could be updated on trigger_automations function, so need to check it again
            return false;
        }

        $email_address = get_array_value($data, "email_address");
        $email_subject = get_array_value($data, "email_subject");
        $email_sender_name = get_array_value($data, "email_sender_name");

        // check if there has any client containing this email address
        // if so, go through with the client id
        $client_info = $this->Users_model->get_one_where(array("email" => $email_address, "user_type" => "client", "deleted" => 0));
        if (get_setting("create_tickets_only_by_registered_emails") && !$client_info->id) {
            return false;
        }

        $ticket_id = $this->_get_ticket_id_from_subject($email_subject);
        $replying_email = $ticket_id ? true : false; // if it's a replying email, we've to parse the message later

        if ($ticket_id) {
            // if the message have ticket id, we have to assume that, it's a reply of the specific ticket
            $ticket_comment_id = $this->_save_tickets_comment($ticket_id, $data, $client_info, $replying_email);

            if ($ticket_id && $ticket_comment_id) {
                log_notification("ticket_commented", array("ticket_id" => $ticket_id, "ticket_comment_id" => $ticket_comment_id, "exclude_ticket_creator" => true), $client_info->id ? $client_info->id : "0");
            }
        } else {

            $now = get_current_utc_time();
            $ticket_data = array(
                "title" => $email_subject ? $email_subject : $email_address, // show creator's email as ticket's title, if there is no subject
                "created_at" => $now,
                "creator_name" => $email_sender_name ? $email_sender_name : "",
                "creator_email" => $email_address ? $email_address : "",
                "client_id" => $client_info->id ? $client_info->client_id : 0,
                "created_by" => $client_info->id ? $client_info->id : 0,
                "last_activity_at" => $now
            );

            $ticket_data = clean_data($ticket_data);

            $ticket_id = $this->Tickets_model->ci_save($ticket_data);

            if ($ticket_id) {
                // save email message as the ticket's comment
                $ticket_comment_id = $this->_save_tickets_comment($ticket_id, $data, $client_info, $replying_email);

                if ($ticket_id && $ticket_comment_id) {
                    log_notification("ticket_created", array("ticket_id" => $ticket_id, "ticket_comment_id" => $ticket_comment_id, "exclude_ticket_creator" => true), $client_info->id ? $client_info->id : "0");
                }
            }
        }
    }

    // save tickets comment
    private function _save_tickets_comment($ticket_id, $data, $client_info, $is_reply = false) {
        $description = $is_reply ? get_array_value($data, "email_reply_content") : get_array_value($data, "email_content");

        $comment_data = array(
            "description" => $description,
            "ticket_id" => $ticket_id,
            "created_by" => $client_info->id ? $client_info->id : 0,
            "created_at" => get_current_utc_time()
        );

        $comment_data = clean_data($comment_data);

        $files_data = get_array_value($data, "email_attachments");
        $comment_data["files"] = $files_data ? serialize($files_data) : "";

        // add client_replied status when it's a reply
        if ($is_reply) {
            $ticket_data = array(
                "status" => "client_replied",
                "last_activity_at" => get_current_utc_time()
            );

            $this->Tickets_model->ci_save($ticket_data, $ticket_id);
        }

        $ticket_comment_id = $this->Ticket_comments_model->ci_save($comment_data);

        if (!$is_reply) {
            add_auto_reply_to_ticket($ticket_id);
        }

        return $ticket_comment_id;
    }

    private function _get_ticket_id_from_subject($subject = "") {
        if (!$subject) {
            return false;
        }

        $find_hash = strpos($subject, "#");
        if (!$find_hash) {
            return false;
        }

        $rest_from_hash = substr($subject, $find_hash + 1); // get the rest text from ticket's #
        $ticket_id = (int) substr($rest_from_hash, 0, strpos($rest_from_hash, " "));

        if (!($ticket_id && is_int($ticket_id))) {
            return false;
        }

        // found a ticket id, check if the ticket is exists on the app
        // if not, that will be considered as a new ticket
        $existing_ticket_info = $this->Tickets_model->get_one_where(array("id" => $ticket_id, "deleted" => 0));
        if (!$existing_ticket_info->id) {
            return false;
        }

        return $ticket_id;
    }
}
