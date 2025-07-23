<?php

namespace App\Libraries;

require_once APPPATH . "ThirdParty/tcpdf/tcpdf.php";

class Pdf extends \TCPDF {

    private $pdf_type;

    public function __construct($pdf_type = '') {
        parent::__construct(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        $this->pdf_type = $pdf_type;
        $this->SetFontSize(10);
        $this->setListIndentWidth(7);
        $this->setImageScale(1.42);
    }

    public function Header() {

        if ($this->pdf_type == 'invoice') {
            $break_margin = $this->getBreakMargin();
            $auto_page_break = $this->AutoPageBreak;
            $this->SetAutoPageBreak(false, 0);

            //if setting is desable then don't show header
            if (!get_setting("enable_background_image_for_invoice_pdf")) {
                $this->setPrintHeader(false);
            }

            $img_file = get_file_from_setting("invoice_pdf_background_image", false, get_setting("timeline_file_path"));
            $img_file = $this->_convert_image_links($img_file);
            $this->Image($img_file, 0, 0, 210, 297, '', '', '', false, 500, '', false, false, 0);

            // restore auto-page-break status
            $this->SetAutoPageBreak($auto_page_break, $break_margin);
        } else {
            parent::Header();
        }
    }

    public function PreparePDF($content, $pdf_file_name,  $mode = "download", $is_mobiel_preview = false) {

        if (!$content) {
            return;
        }

        if ($this->pdf_type != 'invoice') {
            $this->setPrintHeader(false);
            $this->setPrintFooter(false);
        }

        //show background image on first page. Disable on other pages
        if ($this->pdf_type == 'invoice' && !get_setting("enable_background_image_for_invoice_pdf")) {
            $this->setPrintHeader(false);
        }

        $this->AddPage();

        if ($this->pdf_type == 'invoice' && get_setting("set_invoice_pdf_background_only_on_first_page")) {
            $this->setPrintHeader(false);
        }

        $this->writeHTML($content, true, false, true, false, '');


        $pdf_file_name = get_hyphenated_string($pdf_file_name) . ".pdf";

        if ($mode === "download") {
            $this->Output($pdf_file_name, "D");
        } else if ($mode === "send_email") {
            $temp_download_path = getcwd() . "/" . get_setting("temp_file_path") . $pdf_file_name;
            $this->Output($temp_download_path, "F");
            return $temp_download_path;
        } else if ($mode === "view") {

            if ($is_mobiel_preview) {
                $this->SetTitle($pdf_file_name);
                $pdf_content = $this->Output($pdf_file_name, "S");  // Get PDF content as a variable

                echo '<div class="app-modal">';
                echo '<div class="app-modal-content">';
                echo '<iframe id="iframe-file-viewer" src="data:application/pdf;base64,' . base64_encode($pdf_content) . '" width="100%" height="100%" style="border: none;"></iframe>';
                echo '</div>';
                echo '</div>';
            } else {
                $this->SetTitle($pdf_file_name);
                $this->Output($pdf_file_name, "I");
                exit;
            }
        }
    }

    public function writeHTML($html, $ln = true, $fill = false, $reseth = false, $cell = false, $align = '') {
        $html = $this->_convert_image_links($html);
        $html = $this->_rebuild_html($html);
        parent::writeHTML($html, $ln, $fill, $reseth, $cell, $align);
    }

    private function _convert_image_links($content) {
        //if (get_setting('only_file_path')) {
        $base_url = base_url();
        $assets_path = $base_url . "assets";
        $files_path = $base_url . "files";
        $content = str_replace([$assets_path, $files_path], ["assets/", "files/"], $content);
        //}
        return $content;
    }

    private function _rebuild_html($content) {

        // Add cellpadding to <table> tags 
        $cellpadding = 10;
        $content = preg_replace_callback('/<table(.*?)>/i', function ($matches) use ($cellpadding) {
            $attributes = $matches[1];

            // Check if cellpadding is already set
            if (!preg_match('/\bcellpadding\s*=\s*["\']?\d+["\']?/i', $attributes)) {
                $attributes .= ' cellpadding="' . $cellpadding . '"';
            }

            return '<table' . $attributes . '>';
        }, $content);


        // Add line-height to <li>
        $li_line_height = 1.5;

        $content = preg_replace_callback('/<li(.*?)>/i', function ($matches) use ($li_line_height) {
            $attributes = $matches[1];

            if (preg_match('/\bstyle\s*=\s*["\'](.*?)["\']/i', $attributes, $style_match)) {
                // Extract existing styles
                $existing_styles = $style_match[1];

                // Add line-height if not already present
                if (!preg_match('/\bline-height\s*:\s*[\d.]+(px|em|rem|%)?\b/i', $existing_styles)) {
                    $newStyles = rtrim($existing_styles, ';') . '; line-height:' . $li_line_height . ';';
                    $attributes = str_replace($style_match[0], 'style="' . $newStyles . '"', $attributes);
                }
            } else {
                // No style attribute, add it
                $attributes .= ' style="line-height:' . $li_line_height . ';"';
            }

            return '<li' . $attributes . '>';
        }, $content);



        $ul_line_height = 0.2;

        $content = preg_replace_callback('/<ul(.*?)>/i', function ($matches) use ($ul_line_height) {
            $attributes = $matches[1];

            if (preg_match('/\bstyle\s*=\s*["\'](.*?)["\']/i', $attributes, $style_match)) {
                // Extract existing styles
                $existing_styles = $style_match[1];

                // Add line-height if not already present
                if (!preg_match('/\bline-height\s*:\s*[\d.]+(px|em|rem|%)?\b/i', $existing_styles)) {
                    $newStyles = rtrim($existing_styles, ';') . '; line-height:' . $ul_line_height . ';';
                    $attributes = str_replace($style_match[0], 'style="' . $newStyles . '"', $attributes);
                }
            } else {
                // No style attribute, add it
                $attributes .= ' style="line-height:' . $ul_line_height . '; padding-bottom: 0px;"';
            }

            return '<ul' . $attributes . '>';
        }, $content);


        //change paragraph line height
        $content = preg_replace_callback('/<p\b([^>]*)>/i', function ($matches) {
            $tag = $matches[0];
            if (strpos($tag, 'style=') !== false) {
                // Append line-height to existing style
                return preg_replace('/style="([^"]*)"/i', 'style="$1; line-height: 20px;"', $tag);
            } else {
                // Add a new style attribute
                return str_replace('<p', '<p style="line-height: 20px;"', $tag);
            }
        }, $content);

        //change p line height inside table
        $content = preg_replace_callback('/<table\b[^>]*>.*?<\/table>/is', function ($matches) {
            $tableContent = $matches[0];
            $tableContent = preg_replace('/<\s*p\b([^>]*)>/i', '<span$1>', $tableContent);
            $tableContent = preg_replace('/<\s*\/\s*p\s*>/i', '</span><br style="line-height: 23px;" />', $tableContent);
            return $tableContent;
        }, $content);

        // Wrap <hr> inside <p> while preserving attributes
        $content =  preg_replace_callback('/<hr(.*?)>/i', function () {
            return '<p><hr style="color:#f2f4f6;"></p>';
        }, $content);


        $page_width = $this->getPageWidth();

        $page_width_in_px = ($page_width / 25.4) * 92;

        // Replace percentage-based styles with pixel-based styles
        $content = preg_replace_callback('/style=["\'](.*?)["\']/i', function ($matches) use ($page_width_in_px) {
            $style = $matches[1];

            // Replace percentage-based width with pixel-based width
            $style = preg_replace_callback('/width\s*:\s*(\d+%)/i', function ($width_matches) use ($page_width_in_px) {
                $percentage_width = $width_matches[1];
                $pixelWidth = $page_width_in_px * (floatval($percentage_width) / 100);
                return 'width: ' . $pixelWidth . 'px';
            }, $style);

            return 'style="' . $style . '"';
        }, $content);


        $rem_base = 14; // 1rem = 14px for pdf

        $content = preg_replace_callback('/font-size\s*:\s*([\d.]+)\s*rem/i', function ($matches) use ($rem_base) {
            $rem_value = (float) $matches[1];
            $px_value = $rem_value * $rem_base;
            return 'font-size: ' . $px_value . 'px';
        }, $content);

        $default_style = "<style>
                h1 { font-size: 32px; font-weight: normal; }
                h2 { font-size: 28px; font-weight: normal; }
                h3 { font-size: 24px; font-weight: normal; }
                h4 { font-size: 18px; font-weight: normal; }
                h5 { font-size: 17px; }
                h6 { font-size: 14px; font-weight: normal; }
                </style>";

        $content = $default_style . $content;

        return $content;
    }
}
