<?php
$checkout_button = "hide";
$has_banner_image = (!isset($login_user->id) && get_setting("banner_image_on_public_store")) ? true : false;
if ($cart_items_count) {
    $checkout_button = "";
}
?>

<?php if ($has_banner_image) { ?>
    <div class="clearfix mb30">
        <img class="w100p" src="<?php echo get_file_from_setting("banner_image_on_public_store", false, get_setting("timeline_file_path")); ?>" alt="..." />
    </div>
<?php } ?>
<div id="page-content" class="page-wrapper clearfix <?php echo $has_banner_image ? "pt0" : ""; ?>">
    <div class="<?php echo isset($login_user->id) ? "" : "container store-page"; ?>">
        <div class="clearfix mb15">
            <h4 class="float-start"><?php echo app_lang('store'); ?></h4>

            <div class="float-end">
                <?php echo anchor(get_uri("store/process_order"), "<i data-feather='check-circle' class='icon-16'></i> " . app_lang("checkout"), array("id" => "item-checkout-button", "class" => "btn btn-success $checkout_button ml15")); ?>
            </div>

            <div class="float-end item-search-box-section">
                <?php
                echo form_input(array(
                    "id" => "item-search-box",
                    "class" => "form-control custom-filter-search item-search-box",
                    "placeholder" => app_lang('search'),
                ));
                ?>
            </div>
            <div class="float-end custom-toolbar item-categories-filter-section">
                <?php
                echo form_input(array(
                    "id" => "item-categories-filter",
                    "name" => "item-categories-filter",
                    "class" => "select2 w200 mr15"
                ));
                ?>
            </div>
        </div>

        <div class="row" id="items-container">
            <?php echo view("items/items_grid_data"); ?>
        </div>
    </div>
</div>

<script>
    $(document).ready(function () {
        var $searchBox = $("#item-search-box");

        $searchBox.on("keyup", function (e) {
            if (!(e.which >= 37 && e.which <= 40)) {
                //witi 200 ms to request ajax cll
                clearTimeout($.data(this, 'timer'));
                var wait = setTimeout(getItemSuggestions, 200);
                $(this).data('timer', wait);
            }
        });

        var $itemCategoriesFilter = $("#item-categories-filter");
        $itemCategoriesFilter.select2({
            data: <?php echo $categories_dropdown; ?>
        }).on("change", function () {
            getItemSuggestions();
        });

        function getItemSuggestions() {
            appLoader.show();

            appAjaxRequest({
                url: "<?php echo get_uri('store/index/'); ?>",
                data: {search: $searchBox.val(), item_search: true, category_id: $itemCategoriesFilter.val()},
                cache: false,
                type: 'POST',
                dataType: 'json',
                success: function (response) {
                    appLoader.hide();

                    if (response.success) {
                        $("#items-container").html(response.data);
                    }
                }
            });
        }

        $("body").on("click", ".item-add-to-cart-btn", function () {
            var itemId = $(this).attr("data-item_id"),
                    $instance = $(this);
            appLoader.show();

            //add item to the order items table and show count on cart box
            appAjaxRequest({
                url: "<?php echo get_uri('store/add_item_to_cart'); ?>" + "/" + itemId,
                data: {id: itemId},
                cache: false,
                type: 'POST',
                dataType: 'json',
                success: function (response) {
                    appLoader.hide();

                    if (response.success) {
                        //reload cart value
                        window.countCartItems();

                        //change the selector when it's added from view modal
                        if ($("#ajaxModal").hasClass('in')) {
                            $instance = $("#items-container").find("[data-item_id='" + itemId + "']");
                            $("#ajaxModal").modal('hide');
                        }

                        //change button text
                        $instance.text("<?php echo app_lang("added_to_cart"); ?>");
                        $instance.removeClass("item-add-to-cart-btn");
                        $instance.attr("disabled", "disabled");
                    }
                }
            });
        });

        window.refreshAfterUpdate = true;
    });
</script>