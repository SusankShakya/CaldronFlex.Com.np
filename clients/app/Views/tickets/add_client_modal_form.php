<?php echo form_open(get_uri("tickets/link_client_to_ticket"), array("id" => "ticket-form", "class" => "general-form", "role" => "form")); ?>

<div class="modal-body clearfix">
    <div class="container-fluid">
        <input type="hidden" name="ticket_id" value="<?php echo $ticket_id; ?>" />

        <div class="form-group">
            <div class="row">
                <label for="client_id" class=" col-md-3"><?php echo app_lang('client'); ?></label>
                <div class="col-md-9">
                    <?php
                    echo form_input(array(
                        "id" => "client_id",
                        "name" => "client_id",
                        "value" => "",
                        "class" => "form-control",
                        "placeholder" => app_lang('client'),
                        "data-rule-required" => true,
                        "data-msg-required" => app_lang("field_required"),
                    ));
                    ?>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal-footer">
    <button type="button" class="btn btn-default" data-bs-dismiss="modal"><span data-feather="x" class="icon-16"></span> <?php echo app_lang('close'); ?></button>
    <button type="submit" class="btn btn-primary"><span data-feather="check-circle" class="icon-16"></span> <?php echo app_lang('save'); ?></button>
</div>

<?php echo form_close(); ?>


<script type="text/javascript">
    $(document).ready(function() {
        $("#ticket-form").appForm({
            onSuccess: function(result) {
                location.reload();
            }
        });
        $("#client_id").appDropdown({
            list_data: <?php echo $clients_dropdown; ?>
        });
    });
</script>