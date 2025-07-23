<form action="<?php echo get_uri("collect_leads/save"); ?>" role="form" method="post" accept-charset="utf-8">

    <div><input type="text" name="company_name" id="company_name" placeholder="<?php echo app_lang('company_name'); ?>" required="required" /></div>
    <div><input type="text" name="first_name" id="first_name" placeholder="<?php echo app_lang('first_name'); ?>"></div>
    <div><input type="text" name="last_name" id="last_name" placeholder="<?php echo app_lang('last_name'); ?>" required="required"></div>
    <div><input type="email" name="email" id="email" placeholder="<?php echo app_lang('email'); ?>" autocomplete="off"></div>
    <div><textarea name="address" cols="40" rows="10" id="address" placeholder="<?php echo app_lang('address'); ?>"></textarea></div>
    <div><input type="text" name="city" id="city" placeholder="<?php echo app_lang('city'); ?>"></div>
    <div><input type="text" name="state" id="state" placeholder="<?php echo app_lang('state'); ?>"></div>
    <div><input type="text" name="zip" id="zip" placeholder="<?php echo app_lang('zip'); ?>"></div>
    <div><input type="text" name="country" id="country" placeholder="<?php echo app_lang('country'); ?>"></div>
    <div><input type="text" name="phone" id="phone" placeholder="<?php echo app_lang('phone'); ?>"></div>
    <?php echo view("custom_fields/form/prepare_context_fields", array("return_only_field" => 1)); ?>

    <!-- You can use following fields to set the lead source and owner
    <input type="hidden" name="lead_source_id" value="1" />
    <input type="hidden" name="lead_owner_id" value="1" />
    -->


    <div><button type="submit"><?php echo app_lang('submit'); ?></button></div>

</form>