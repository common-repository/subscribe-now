<h1><?php esc_html_e('Settings', subscribe_now_config()['textDomain']) ?></h1>

<form class="subscribe-now-settings-form" action="" method="post">
    <?php wp_nonce_field('subscribe-now-save-settings') ?>
    <?php $remove_table = get_option('subscribe_now_remove_table'); ?>

    <input id="remove-table" name="remove-table" value="1" type="radio" <?php if ($remove_table == 1): ?> checked="checked" <?php endif; ?>>

    <label for="remove-table"><?php esc_html_e('Check this if you want to delete table and data on plugin uninstallation', subscribe_now_config()['textDomain']) ?></label>

    <br><div style="margin-top: 5px;"></div>

    <input id="do-not-remove-table" name="remove-table" value="0" type="radio" <?php if ($remove_table == 0): ?> checked="checked" <?php endif; ?>>

    <label for="do-not-remove-table"><?php esc_html_e('Do not delete data on plugin uninstallation', subscribe_now_config()['textDomain']) ?></label>

    <?php submit_button('Save'); ?>
</form>