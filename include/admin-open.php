<div class="wrap">
    <?php
        echo '<h1 class="wp-heading">' . get_admin_page_title() . '</h1>';
        echo '<div class="wrap"><form method="post" action="options.php">';
            settings_fields( 'openone_settings' );
            do_settings_sections( 'openone_slug' );
            submit_button();
        echo '</form></div>';
        $msgstore = $GLOBALS["msg_store"];
        _e ( '<h2 class="msg-store">'.$msgstore.'</h2' );
    ?>
</div>