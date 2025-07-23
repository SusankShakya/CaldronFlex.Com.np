<li class="js-cookie-tab" data-tab="list"><a class="<?php echo ($active_tab == 'list') ? 'active' : ''; ?>" href="<?php echo_uri('notes'); ?>"><?php echo app_lang("list"); ?></a></li>
<li class="js-cookie-tab" data-tab="grid"><a class="<?php echo ($active_tab == 'grid') ? 'active' : ''; ?>" href="<?php echo_uri('notes/grid/'); ?>"><?php echo app_lang("grid"); ?></a></li>
<li><a class="<?php echo ($active_tab == 'categories') ? 'active' : ''; ?>" href="<?php echo_uri('notes/categories/'); ?>"><?php echo app_lang("categories"); ?></a></li>

<script>
    var activeTab = "<?php echo $active_tab; ?>";
    var cookieTab = getCookie("selected_note_tab_" + "<?php echo $login_user->id; ?>");

    if (activeTab === "list" && cookieTab === "grid") {
        window.location.href = "<?php echo_uri('notes/grid/'); ?>";
    }

    //save the selected tab in browser cookie
    $(document).ready(function() {
        $(".js-cookie-tab").click(function() {
            var tab = $(this).attr("data-tab");
            if (tab) {
                setCookie("selected_note_tab_" + "<?php echo $login_user->id; ?>", tab);
            }
        });
    });
</script>