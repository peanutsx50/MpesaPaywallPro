<?php

/**
 * Access Control Settings Template
 *
 * @package    MpesaPaywallPro
 * @subpackage MpesaPaywallPro/admin/partials
 * @link       https://festuswp.gumroad.com/l/MpesaPaywallPro
 */

if (!defined('ABSPATH')) {
    exit;
}

// Fetch options once
$options = get_option('mpesapaywallpro_options', []);
?>

<div class="mpesapaywallpro-settings-section">
    <h2 class="title">
        <span class="dashicons dashicons-shield"></span>
        <?php esc_html_e('Access Control & Security', 'mpesapaywallpro'); ?>
    </h2>

    <div class="mpesapaywallpro-notice notice-warning">
        <p>
            <?php esc_html_e('Configure security settings and access controls for your paywall system.', 'mpesapaywallpro'); ?>
        </p>
    </div>

    <table class="form-table">
    <!-- Exempt User Roles Setting -->
        <tr>
            <th scope="row">
                <label for="allowed_user_roles"><?php esc_html_e('Exempt User Roles', 'mpesapaywallpro'); ?></label>
            </th>
            <td>
                <?php
                $roles = wp_roles()->get_names();
                $selected_roles = $options['allowed_user_roles'] ?? ['administrator'];
                ?>
                <fieldset>
                    <?php foreach ($roles as $role_value => $role_name): ?>
                        <label style="display: block; margin-bottom: 5px;">
                            <input type="checkbox"
                                name="mpesapaywallpro_options[allowed_user_roles][]"
                                value="<?php echo esc_attr($role_value); ?>"
                                <?php checked(in_array($role_value, (array)$selected_roles)); ?>>
                            <?php echo esc_html(translate_user_role($role_name)); ?>
                        </label>
                    <?php endforeach; ?>
                </fieldset>
                <p class="description">
                    <?php esc_html_e('Selected user roles will bypass the paywall without payment', 'mpesapaywallpro'); ?>
                </p>
            </td>
        </tr>
        
    </table>

    <div class="mpesapaywallpro-security-tips">
        <h3>
            <span class="dashicons dashicons-lightbulb"></span>
            <?php esc_html_e('Security Tips', 'mpesapaywallpro'); ?>
        </h3>
        <ul>
            <li><?php esc_html_e('Always use SSL/HTTPS for your website when processing payments', 'mpesapaywallpro'); ?></li>
            <li><?php esc_html_e('Keep your M-Pesa API credentials secure and never share them', 'mpesapaywallpro'); ?></li>
            <li><?php esc_html_e('Regularly update WordPress and the MpesaPaywallPro plugin', 'mpesapaywallpro'); ?></li>
            <li><?php esc_html_e('Monitor transaction logs for suspicious activity', 'mpesapaywallpro'); ?></li>
            <li><?php esc_html_e('Use IP whitelisting for internal testing only', 'mpesapaywallpro'); ?></li>
        </ul>
    </div>
</div>