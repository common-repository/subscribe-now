<h1 style="display: inline-block;"><?php esc_html_e('Subscribers', subscribe_now_config()['textDomain']) ?></h1>
<a href="<?= home_url('/subscribe-now/export') ?>" class="sn-page-title-action"><?php esc_html_e('Export CSV', subscribe_now_config()['textDomain']) ?></a>

<?php if($flashMessage): ?>
    <div class="notice notice-info is-dismissible subscribe-now-flash-message">
        <p><?= $flashMessage ?></p>
    </div>
<?php endif; ?>

<?php if ($subscribers): ?>

    <table class="widefat" cellspacing="0">
        <thead>
            <tr>
                <td>#</td>
                <td><?php esc_html_e('Email', subscribe_now_config()['textDomain']) ?></td>
                <td><?php esc_html_e('Added at', subscribe_now_config()['textDomain']) ?></td>
                <td><?php esc_html_e('Actions', subscribe_now_config()['textDomain']) ?></td>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($subscribers as $subscriber): ?>

                <tr>
                    <td><?= $subscriber['id'] ?></td>
                    <td><?= htmlspecialchars($subscriber['email']) ?></td>
                    <td><?= $subscriber['created_at'] ?></td>
                    <td>
                        <form action="" method="post">
                            <?php wp_nonce_field('subscribe-now-remove-subscriber') ?>
                            <input type="hidden" name="subscriber-id" value="<?= $subscriber['id'] ?>">
                            <input class="button button-secondary" type="submit" onclick="if (!confirm('<?php esc_html_e('Are you sure you want to remove this subscriber?', subscribe_now_config()['textDomain']) ?>')) {event.preventDefault();}" value="<?php esc_attr_e('Remove', subscribe_now_config()['textDomain']) ?>">
                        </form>
                    </td>
                </tr>

            <?php endforeach; ?>
        </tbody>
    </table>

<?php else: ?>

    <p><?php esc_html_e('There are no subscribers', subscribe_now_config()['textDomain']) ?></p>

<?php endif;
