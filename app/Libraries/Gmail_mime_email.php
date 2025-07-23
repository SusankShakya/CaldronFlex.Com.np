<?php

namespace App\Libraries;

use CodeIgniter\Email\Email as BaseEmail;

class Gmail_mime_email extends BaseEmail {

    public function prepareForGmail(): string {
        // Build the headers and body using parent class methods
        parent::buildHeaders();
        parent::buildMessage();

        // finalBody is already a full MIME message at this point
        $raw = $this->headerStr . $this->finalBody;

        // Base64url encode for Gmail API
        return base64_encode($raw);
    }
}
