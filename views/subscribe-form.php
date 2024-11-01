<div class="sn-subscription-form-wrapper">
    <input class="subscribe-now-subscribe-email" type="text" name="email" placeholder="<?php esc_attr_e('Email address', subscribe_now_config()['textDomain']) ?>">
    <button class="subscribe-now-subscribe-submit-button">
        <?php esc_html_e('Sign up', subscribe_now_config()['textDomain']) ?> <span class="lds-dual-ring"></span>
    </button>
    <p></p>
</div>

<script type="text/javascript">
    var SUBSCRIBE_NOW_ADMIN_AJAX = '<?= home_url('/wp-admin/admin-ajax.php') ?>';
</script>