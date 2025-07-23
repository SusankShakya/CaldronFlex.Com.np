<?php

namespace App\Libraries;

class E_invoice {

    private $ci;

    public function __construct($security_controller_instance) {
        $this->ci = $security_controller_instance;
    }

    function get_country_code($country_name) {
        if (!$country_name) return null;
        $country_name = strtolower(trim($country_name));
        $country_codes = [
            "afghanistan" => ["AF", "AFG"],
            "albania" => ["AL", "ALB"],
            "algeria" => ["DZ", "DZA"],
            "andorra" => ["AD", "AND"],
            "angola" => ["AO", "AGO"],
            "antigua and barbuda" => ["AG", "ATG"],
            "argentina" => ["AR", "ARG"],
            "armenia" => ["AM", "ARM"],
            "australia" => ["AU", "AUS"],
            "austria" => ["AT", "AUT"],
            "azerbaijan" => ["AZ", "AZE"],
            "bahamas" => ["BS", "BHS"],
            "bahrain" => ["BH", "BHR"],
            "bangladesh" => ["BD", "BGD"],
            "barbados" => ["BB", "BRB"],
            "belarus" => ["BY", "BLR"],
            "belgium" => ["BE", "BEL"],
            "belize" => ["BZ", "BLZ"],
            "benin" => ["BJ", "BEN"],
            "bhutan" => ["BT", "BTN"],
            "bolivia" => ["BO", "BOL"],
            "bosnia and herzegovina" => ["BA", "BIH"],
            "botswana" => ["BW", "BWA"],
            "brazil" => ["BR", "BRA"],
            "brunei" => ["BN", "BRN"],
            "bulgaria" => ["BG", "BGR"],
            "burkina faso" => ["BF", "BFA"],
            "burundi" => ["BI", "BDI"],
            "cabo verde" => ["CV", "CPV"],
            "cambodia" => ["KH", "KHM"],
            "cameroon" => ["CM", "CMR"],
            "canada" => ["CA", "CAN"],
            "central african republic" => ["CF", "CAF"],
            "chad" => ["TD", "TCD"],
            "chile" => ["CL", "CHL"],
            "china" => ["CN", "CHN"],
            "colombia" => ["CO", "COL"],
            "comoros" => ["KM", "COM"],
            "congo (democratic republic)" => ["CD", "COD"],
            "congo (republic)" => ["CG", "COG"],
            "costa rica" => ["CR", "CRI"],
            "croatia" => ["HR", "HRV"],
            "cuba" => ["CU", "CUB"],
            "cyprus" => ["CY", "CYP"],
            "czech republic" => ["CZ", "CZE"],
            "denmark" => ["DK", "DNK"],
            "djibouti" => ["DJ", "DJI"],
            "dominica" => ["DM", "DMA"],
            "dominican republic" => ["DO", "DOM"],
            "ecuador" => ["EC", "ECU"],
            "egypt" => ["EG", "EGY"],
            "el salvador" => ["SV", "SLV"],
            "equatorial guinea" => ["GQ", "GNQ"],
            "eritrea" => ["ER", "ERI"],
            "estonia" => ["EE", "EST"],
            "eswatini" => ["SZ", "SWZ"],
            "ethiopia" => ["ET", "ETH"],
            "fiji" => ["FJ", "FJI"],
            "finland" => ["FI", "FIN"],
            "france" => ["FR", "FRA"],
            "gabon" => ["GA", "GAB"],
            "gambia" => ["GM", "GMB"],
            "georgia" => ["GE", "GEO"],
            "germany" => ["DE", "DEU"],
            "ghana" => ["GH", "GHA"],
            "greece" => ["GR", "GRC"],
            "grenada" => ["GD", "GRD"],
            "guatemala" => ["GT", "GTM"],
            "guinea" => ["GN", "GIN"],
            "guinea-bissau" => ["GW", "GNB"],
            "guyana" => ["GY", "GUY"],
            "haiti" => ["HT", "HTI"],
            "honduras" => ["HN", "HND"],
            "hungary" => ["HU", "HUN"],
            "iceland" => ["IS", "ISL"],
            "india" => ["IN", "IND"],
            "indonesia" => ["ID", "IDN"],
            "iran" => ["IR", "IRN"],
            "iraq" => ["IQ", "IRQ"],
            "ireland" => ["IE", "IRL"],
            "israel" => ["IL", "ISR"],
            "italy" => ["IT", "ITA"],
            "jamaica" => ["JM", "JAM"],
            "japan" => ["JP", "JPN"],
            "jordan" => ["JO", "JOR"],
            "kazakhstan" => ["KZ", "KAZ"],
            "kenya" => ["KE", "KEN"],
            "korea (north)" => ["KP", "PRK"],
            "korea (south)" => ["KR", "KOR"],
            "kuwait" => ["KW", "KWT"],
            "kyrgyzstan" => ["KG", "KGZ"],
            "laos" => ["LA", "LAO"],
            "latvia" => ["LV", "LVA"],
            "lebanon" => ["LB", "LBN"],
            "libya" => ["LY", "LBY"],
            "liechtenstein" => ["LI", "LIE"],
            "lithuania" => ["LT", "LTU"],
            "luxembourg" => ["LU", "LUX"],
            "madagascar" => ["MG", "MDG"],
            "malaysia" => ["MY", "MYS"],
            "malta" => ["MT", "MLT"],
            "mexico" => ["MX", "MEX"],
            "mongolia" => ["MN", "MNG"],
            "morocco" => ["MA", "MAR"],
            "netherlands" => ["NL", "NLD"],
            "new zealand" => ["NZ", "NZL"],
            "nigeria" => ["NG", "NGA"],
            "norway" => ["NO", "NOR"],
            "pakistan" => ["PK", "PAK"],
            "panama" => ["PA", "PAN"],
            "paraguay" => ["PY", "PRY"],
            "peru" => ["PE", "PER"],
            "philippines" => ["PH", "PHL"],
            "poland" => ["PL", "POL"],
            "portugal" => ["PT", "PRT"],
            "qatar" => ["QA", "QAT"],
            "romania" => ["RO", "ROU"],
            "russia" => ["RU", "RUS"],
            "saudi arabia" => ["SA", "SAU"],
            "senegal" => ["SN", "SEN"],
            "serbia" => ["RS", "SRB"],
            "singapore" => ["SG", "SGP"],
            "slovakia" => ["SK", "SVK"],
            "slovenia" => ["SI", "SVN"],
            "south africa" => ["ZA", "ZAF"],
            "spain" => ["ES", "ESP"],
            "sri lanka" => ["LK", "LKA"],
            "sweden" => ["SE", "SWE"],
            "switzerland" => ["CH", "CHE"],
            "thailand" => ["TH", "THA"],
            "tunisia" => ["TN", "TUN"],
            "turkey" => ["TR", "TUR"],
            "ukraine" => ["UA", "UKR"],
            "united arab emirates" => ["AE", "ARE"],
            "united kingdom" => ["GB", "GBR"],
            "united states" => ["US", "USA"],
            "uruguay" => ["UY", "URY"],
            "venezuela" => ["VE", "VEN"],
            "vietnam" => ["VN", "VNM"],
            "zimbabwe" => ["ZW", "ZWE"]
        ];

        return get_array_value($country_codes, $country_name);
    }

    function generate_xml($invoice_data) {
        try {
            $parser = \Config\Services::parser();

            $invoice_info = get_array_value($invoice_data, "invoice_info");
            $invoice_total_summary = get_array_value($invoice_data, "invoice_total_summary");

            if (!$invoice_info) {
                show_404();
            }

            $Company_model = model('App\Models\Company_model');
            $company_info = $Company_model->get_one_where(array("id" => $invoice_info->company_id));

            $client_info = $this->ci->Clients_model->get_one($invoice_info->client_id);

            //since the variables will be used in e-invoice, we find all fields like as admin user
            $company_custom_fields = get_custom_variables_data("companies", $invoice_info->company_id, 1);
            $client_custom_fields = get_custom_variables_data("clients", $invoice_info->client_id, 1);
            $invoice_custom_fields = get_custom_variables_data("invoices", $invoice_info->id, 1);

            $display_id = $invoice_info->display_id;

            $parser_data = array();
            $parser_data["INVOICE_ID"] = $display_id;
            $parser_data["INVOICE_NUMBER"] = $invoice_info->id; // Use invoice unique id as invoice number
            $parser_data["INVOICE_BILL_DATE"] = $invoice_info->bill_date;
            $parser_data["INVOICE_DUE_DATE"] = $invoice_info->due_date;
            $parser_data["CURRENCY_CODE"] = $invoice_total_summary->currency;

            $parser_data["BATCH_IDENTIFIER"] = "INV" . $invoice_info->id;

            foreach ($invoice_custom_fields as $key => $value) {
                $parser_data[$key] = $value ? strip_tags($value) : $value;
            }

            //Company info
            $parser_data["COMPANY_NAME"] = strip_tags($company_info->name ? $company_info->name : "");
            $parser_data["COMPANY_ADDRESS"] =  str_replace(["\r", "\n"], ' ', strip_tags($company_info->address ? $company_info->address : ""));
            $parser_data["COMPANY_PHONE"] = strip_tags($company_info->phone ? $company_info->phone : "");
            $parser_data["COMPANY_EMAIL"] = strip_tags($company_info->email ? $company_info->email : "");
            $parser_data["COMPANY_VAT_NUMBER"] = $company_info->vat_number;
            $parser_data["COMPANY_GST_NUMBER"] = $company_info->gst_number;


            foreach ($company_custom_fields as $key => $value) {
                $parser_data[$key] = $value ? strip_tags($value) : $value;
            }

            //Customer info
            $parser_data["CLIENT_ID"] = $client_info->id;
            $parser_data["CLIENT_NAME"] = strip_tags($client_info->company_name ? $client_info->company_name : "");
            $parser_data["CLIENT_ADDRESS"] = str_replace(["\r", "\n"], ', ', strip_tags($client_info->address ? $client_info->address : ""));
            $parser_data["CLIENT_CITY"] = strip_tags($client_info->city ? $client_info->city : "");
            $parser_data["CLIENT_STATE"] = strip_tags($client_info->state ? $client_info->state : "");
            $parser_data["CLIENT_ZIP"] = $client_info->zip;
            $parser_data["CLIENT_VAT_NUMBER"] = $client_info->vat_number;
            $parser_data["CLIENT_GST_NUMBER"] = $client_info->gst_number;

            $client_country_code = $this->get_country_code($client_info->country);
            $parser_data["CLIENT_COUNTRY_CODE_ALPHA_2"] = $client_country_code ? get_array_value($client_country_code, 0) : "";
            $parser_data["CLIENT_COUNTRY_CODE_ALPHA_3"] = $client_country_code ? get_array_value($client_country_code, 1) : "";

            foreach ($client_custom_fields as $key => $value) {
                $parser_data[$key] = $value ? strip_tags($value) : $value;
            }

            //Tax Summary
            $invoice_subtotal = $invoice_total_summary->invoice_subtotal;
            $invoice_total = $invoice_total_summary->invoice_total;
            $tax1_percent = $invoice_total_summary->tax_percentage ? $invoice_total_summary->tax_percentage : 0;
            $tax2_percent = $invoice_total_summary->tax_percentage2 ? $invoice_total_summary->tax_percentage2 : 0;
            $tds_percentage = $invoice_total_summary->tax_percentage3 ? $invoice_total_summary->tax_percentage3 : 0;

            if ($invoice_info->type == "credit_note") {
                $invoice_subtotal = $invoice_subtotal * -1;
                $invoice_total = $invoice_total * -1;
            }

            $parser_data["TAX1_AMOUNT"] = $invoice_total_summary->tax;
            $parser_data["TAX2_AMOUNT"] = $invoice_total_summary->tax2;
            $parser_data["TDS_AMOUNT"] = $invoice_total_summary->tax3;


            $parser_data["TDS_PERCENT"] = $tds_percentage;

            $tax_total_amount = $invoice_total_summary->tax + $invoice_total_summary->tax2;
            $parser_data["TAX_TOTAL_AMOUNT"] = $tax_total_amount;

            $tax1_category_id = "Z"; //Z = Zero
            $tax2_category_id = "Z";

            if ($tax_total_amount) {

                if ($tax1_percent) {
                    $tax1_category_id = 'S'; //S = Standard rate
                }

                if ($tax2_percent) {
                    $tax2_category_id = 'O'; //O = Other
                }
            } else {
                $tax1_percent = 0;
                $tax2_percent = 0;
            }

            $parser_data["TAX1_PERCENT"] = $tax1_percent;
            $parser_data["TAX2_PERCENT"] = $tax2_percent;

            $parser_data["TAX1_CATEGORY_ID"] = $tax1_category_id;
            $parser_data["TAX2_CATEGORY_ID"] =  $tax2_category_id;

            $parser_data["INVOICE_SUBTOTAL"] = $invoice_subtotal;
            $parser_data["INVOICE_TOTAL"] = $invoice_total;
            $parser_data["INVOICE_TOTAL_BEFORE_TAX"] = $invoice_subtotal - $invoice_total_summary->discount_total;
            $parser_data["INVOICE_BALANCE_DUE"] = $invoice_total_summary->balance_due;
            $parser_data["INVOICE_DISCOUNT_TOTAL"] = $invoice_total_summary->discount_total;

            $invoice_items = get_array_value($invoice_data, "invoice_items");

            // Overwrite unit_type for each item
            foreach ($invoice_items as $item) {
                if ($invoice_info->type == "credit_note") {
                    $item->total = $item->total * -1;
                }
            }

            // Prepare the invoice items data for the required variables

            $taxable_subtotal_amount = 0;
            $non_taxable_subtotal_amount = 0;

            $invoice_lines_data = [];

            $item_line_serial = 0;
            foreach ($invoice_items as $item) {

                if ($item->taxable) {
                    $taxable_subtotal_amount += $item->total;
                } else {
                    $non_taxable_subtotal_amount += $item->total;
                }

                $item_line_serial++;
                $invoice_lines_data[] = [
                    "INVOICE_LINE_SERIAL" => $item_line_serial,
                    "INVOICE_LINE_ITEM_ID" => $item->id,
                    "INVOICE_LINE_TITLE" => strip_tags($item->title ? $item->title : ""),
                    "INVOICE_LINE_DESCRIPTION" => strip_tags($item->description ? $item->description : ""),
                    "INVOICE_LINE_QUANTITY" => $item->quantity,
                    "INVOICE_LINE_UNIT_TYPE" => strip_tags($item->unit_type ? $item->unit_type : ""),
                    "INVOICE_LINE_RATE" => $item->rate,
                    "INVOICE_LINE_TOTAL" => $item->total,
                    "INVOICE_LINE_TAX1_CATEGORY_ID" => $item->taxable && $tax1_percent ? $tax1_category_id : "Z",
                    "INVOICE_LINE_TAX1_PERCENT" => $item->taxable && $tax1_percent ? $tax1_percent : 0,
                    "INVOICE_LINE_TAX2_CATEGORY_ID" => $item->taxable && $tax2_percent ? $tax2_category_id : "Z",
                    "INVOICE_LINE_TAX2_PERCENT" => $item->taxable && $tax2_percent ? $tax2_percent : 0,
                    "INVOICE_LINE_TAX_TOTAL" =>  $item->taxable && $tax1_percent ? $item->total * $tax1_percent / 100 : 0, //support only one tax for now.
                ];

                $item_custom_fields = get_custom_variables_data("items", $item->item_id, 1); //since the variables will be used in e-invoice, we find all fields like as admin user

                if (is_array($item_custom_fields)) {
                    foreach ($item_custom_fields as $key => $value) {
                        $last_index = array_key_last($invoice_lines_data);
                        $invoice_lines_data[$last_index][$key] = $value ? strip_tags($value) : $value;
                    }
                }
            }

            $taxable_item_discount = 0;
            $non_taxable_item_discount = 0;

            if ($invoice_total_summary->discount_total && $taxable_subtotal_amount && $non_taxable_subtotal_amount) {
                //invoice has both taxable and non-taxable items
                //discount will be deducted from both taxable and non-taxable amount based on discount percentage
                $discount_percentage =  $invoice_total_summary->discount_total * 100 / $invoice_subtotal;
                $taxable_item_discount = ($discount_percentage / 100 * $taxable_subtotal_amount);
                $non_taxable_item_discount = ($discount_percentage / 100 * $non_taxable_subtotal_amount);

                $taxable_subtotal_amount = $taxable_subtotal_amount - $taxable_item_discount;
                $non_taxable_subtotal_amount = $non_taxable_subtotal_amount - $non_taxable_item_discount;
            } else if ($invoice_total_summary->discount_total && $taxable_subtotal_amount) {
                //invoice has only taxable items
                //discount will be deducted from taxable amount based on discount percentage
                $taxable_subtotal_amount = $taxable_subtotal_amount - $invoice_total_summary->discount_total;
                $taxable_item_discount = $invoice_total_summary->discount_total;
            } else if ($invoice_total_summary->discount_total && $non_taxable_subtotal_amount) {
                //invoice has only non-taxable items
                //discount will be deducted from non-taxable amount based on discount percentage
                $non_taxable_subtotal_amount = $non_taxable_subtotal_amount - $invoice_total_summary->discount_total;
                $non_taxable_item_discount = $invoice_total_summary->discount_total;
            }

            $parser_data["INVOICE_TAXABLE_SUBTOTAL"] =  $taxable_subtotal_amount;
            $parser_data["INVOICE_NON_TAXABLE_SUBTOTAL"] = $non_taxable_subtotal_amount;


            $parser_data["INVOICE_TAXABLE_ITEM_DISCOUNT"] = $taxable_item_discount;
            $parser_data["INVOICE_NON_TAXABLE_ITEM_DISCOUNT"] = $non_taxable_item_discount;

            $parser_data["INVOICE_LINES"] = $invoice_lines_data;

            $E_invoice_templates_model = model("App\Models\E_invoice_templates_model");

            if ($invoice_info->type == "invoice") {
                $e_invoice_template = $E_invoice_templates_model->get_one(get_setting("default_e_invoice_template"))->template;
            } else if ($invoice_info->type == "credit_note") {
                $e_invoice_template = $E_invoice_templates_model->get_one(get_setting("default_e_invoice_template_for_credit_note"))->template;
            }

            $renderedXml = $parser->setData($parser_data)->renderString($e_invoice_template);

            return $renderedXml;
        } catch (\Exception $ex) {
            log_message('error', '[ERROR] {exception}', ['exception' => $ex]);
            return "";
        }
    }
}
