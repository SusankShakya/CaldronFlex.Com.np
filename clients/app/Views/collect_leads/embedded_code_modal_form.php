<div class="general-form">
    <div class="modal-body clearfix">
        <div class="container-fluid">
            <div class="form-group">
                <div class="row">
                    <div class="col-md-12">
                        <?php
                        echo form_textarea(array(
                            "id" => "embedded-code",
                            "name" => "embedded-code",
                            "value" => $embedded,
                            "class" => "form-control"
                        ));
                        ?>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <div class="row">
                    <label for="source" class="col-md-3"><?php echo app_lang('source'); ?></label>
                    <div class="col-md-9">
                        <?php
                        $lead_source = array('' => "- " . app_lang("source") . " -");

                        foreach ($sources as $source) {
                            $lead_source[$source->id] = $source->title;
                        }

                        echo form_dropdown("lead_source_id", $lead_source, '', "class='select2' id='lead_source_id'");
                        ?>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <div class="row">
                    <label for="owner" class="col-md-3"><?php echo app_lang('owner'); ?></label>
                    <div class="col-md-9">
                        <?php
                        $lead_owner = array('' => "- " . app_lang("owner") . " -");

                        foreach ($owners as $owner) {
                            $lead_owner[$owner->id] = $owner->first_name . " " . $owner->last_name;
                        }

                        echo form_dropdown("lead_owner_id", $lead_owner, '', "class='select2' id='lead_owner_id'");
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-default" data-bs-dismiss="modal"><span data-feather="x" class="icon-16"></span> <?php echo app_lang('close'); ?></button>
        <button type="button" id="copy-button" class="btn btn-primary"><span data-feather="copy" class="icon-16"></span> <?php echo app_lang('copy'); ?></button>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function() {
        $("#copy-button").click(function() {
            var copyTextarea = document.querySelector('#embedded-code');
            copyTextarea.focus();
            copyTextarea.select();
            document.execCommand('copy');
        });

        $(".select2").select2();

        var sourceId = "";
        var ownerId = "";

        $("#lead_source_id").on("change", function() {
            sourceId = $(this).val();
            updateEmbeddedCode();
        });

        $("#lead_owner_id").on("change", function() {
            ownerId = $(this).val();
            updateEmbeddedCode();
        });

        function updateEmbeddedCode() {
            var src = "<?php echo get_uri('collect_leads') . '/index/'; ?>";
            var embeddedCode = "<?php echo $embedded; ?>";

            if (sourceId || ownerId) {
                var iframeSrc = src + (sourceId ? sourceId : "0") + "/" + (ownerId ? ownerId : "0");
                var iframeHtml = "<iframe width='768' height='360' src='" + iframeSrc + "' frameborder='0'></iframe>";
                $("#embedded-code").val(iframeHtml);
            } else {
                $("#embedded-code").val(embeddedCode);
            }
        }

    });
</script>