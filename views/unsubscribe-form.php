<div class="sn-subscription-form-wrapper">
    <input class="subscribe-now-unsubscribe-email" type="text" name="email" placeholder="<?php esc_html_e('Email address', subscribe_now_config()['textDomain']) ?>">
    <button class="subscribe-now-unsubscribe-submit-button">
        <?php esc_html_e('Unsubscribe', subscribe_now_config()['textDomain']) ?> <span class="lds-dual-ring"></span>
    </button>
    <p></p>
</div>

<script type="text/javascript">
    var SUBSCRIBE_NOW_ADMIN_AJAX = '<?= home_url('/wp-admin/admin-ajax.php') ?>';
</script>