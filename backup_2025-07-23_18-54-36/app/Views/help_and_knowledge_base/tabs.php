<li class="title-tab">
    <h4 class="pl15 pt10 pr15"><?php echo app_lang($type); ?></h4>
</li>

<li data-tab="articles_list"><a class="<?php echo ($active_tab == 'articles_list') ? 'active' : ''; ?>" href="<?php echo_uri('help/' . $type . '_articles'); ?>"><?php echo app_lang('articles'); ?></a></li>
<li data-tab="categories_list"><a class="<?php echo ($active_tab == 'categories_list') ? 'active' : ''; ?>" href="<?php echo_uri('help/' . $type . '_categories'); ?>"><?php echo app_lang('categories'); ?></a></li>
<li data-tab="panel"><a href="<?php echo_uri($type); ?>"><?php echo app_lang('panel'); ?></a></li>