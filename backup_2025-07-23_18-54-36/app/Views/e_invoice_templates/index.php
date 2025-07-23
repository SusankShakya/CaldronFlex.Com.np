<?php
load_css(array(
    "assets/js/codemirror/codemirror.min.css",
    "assets/js/codemirror/material.min.css",

));

load_js(array(
    "assets/js/codemirror/codemirror.min.js",
    "assets/js/codemirror/xml.min.js",
    "assets/js/codemirror/matchbrackets.min.js",
    "assets/js/codemirror/closebrackets.min.js",
));
?>

<div class="table-responsive mb15">
    <table id="e-invoice-templates-table" class="display no-thead b-b-only" cellspacing="0" width="100%">
    </table>
</div>



<script type="text/javascript">
    $(document).ready(function() {
        $("#e-invoice-templates-table").appTable({
            hideTools: true,
            displayLength: 1000,
            source: '<?php echo_uri("e_invoice_templates/list_data") ?>',
            columns: [{
                    title: '<?php echo app_lang("title"); ?>'
                },
                {
                    title: '<i data-feather="menu" class="icon-16"></i>',
                    "class": "text-center option w100"
                }
            ]
           
        });

    });
</script>