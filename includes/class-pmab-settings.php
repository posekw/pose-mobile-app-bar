<?php

/**
 * Handle admin settings and UI
 *
 * @package    Pose_Mobile_App_Bar
 * @subpackage Pose_Mobile_App_Bar/includes
 */
class Pmab_Settings
{

    public function init()
    {
        // Handle Reset Action
        if (isset($_POST['pmab_reset']) && isset($_POST['pmab_reset_nonce'])) {
            if (wp_verify_nonce($_POST['pmab_reset_nonce'], 'pmab_reset_action')) {
                $this->reset_settings();
                add_settings_error('pmab_messages', 'pmab_message', 'Settings reset to defaults.', 'updated');
            }
        }

        add_action('admin_init', [$this, 'register_settings']);
        add_action('admin_menu', [$this, 'add_admin_menu']);
    }

    private function reset_settings()
    {
        global $wpdb;
        $wpdb->query("DELETE FROM $wpdb->options WHERE option_name LIKE 'pmab_%'");
    }

    public function register_settings()
    {
        register_setting('pmab_settings_group', 'pmab_enable');
        register_setting('pmab_settings_group', 'pmab_native_mode');
        register_setting('pmab_settings_group', 'pmab_hide_selectors');

        // Accessibility / Visibility
        register_setting('pmab_settings_group', 'pmab_hide_header');
        register_setting('pmab_settings_group', 'pmab_hide_footer');
        register_setting('pmab_settings_group', 'pmab_hide_header_bottom');

        // Design
        register_setting('pmab_settings_group', 'pmab_height');
        register_setting('pmab_settings_group', 'pmab_bg_color');
        register_setting('pmab_settings_group', 'pmab_bg_opacity');
        register_setting('pmab_settings_group', 'pmab_text_color');
        register_setting('pmab_settings_group', 'pmab_label_color');
        register_setting('pmab_settings_group', 'pmab_icon_label_spacing');
        register_setting('pmab_settings_group', 'pmab_active_color');

        // Separators
        register_setting('pmab_settings_group', 'pmab_enable_separators');
        register_setting('pmab_settings_group', 'pmab_separator_color');
        register_setting('pmab_settings_group', 'pmab_separator_width');
        register_setting('pmab_settings_group', 'pmab_separator_opacity');

        // Custom CSS
        register_setting('pmab_settings_group', 'pmab_custom_css');

        // Menu Items (1-5)
        for ($i = 1; $i <= 5; $i++) {
            register_setting('pmab_settings_group', "pmab_item_{$i}_icon");
            register_setting('pmab_settings_group', "pmab_item_{$i}_label");
            register_setting('pmab_settings_group', "pmab_item_{$i}_url");
        }
    }

    public function add_admin_menu()
    {
        add_options_page(
            'App Bar Settings',
            'Mobile App Bar',
            'manage_options',
            'pose-mobile-app-bar',
            [$this, 'render_settings_page']
        );
    }

    public function render_settings_page()
    {
        // Security check: only render on our settings page
        $screen = get_current_screen();
        if (!$screen || $screen->id !== 'settings_page_pose-mobile-app-bar') {
            return;
        }

        wp_enqueue_style('pmab-admin-css', plugins_url('../assets/css/admin.css', __FILE__));

        ?>
        <div class="pmab-wrap">
            <form method="post" action="options.php">
                <?php settings_fields('pmab_settings_group'); ?>

                <div class="pmab-header">
                    <h1>📱 Mobile App Bar <span class="pmab-badge">v<?php echo esc_html(PMAB_VERSION); ?></span></h1>
                </div>

                <!-- GENERAL SETTINGS -->
                <div class="pmab-card">
                    <div class="pmab-section-title">General Settings</div>

                    <div class="pmab-form-row">
                        <div class="pmab-label">Enable App Bar</div>
                        <div class="pmab-input-group">
                            <label class="switch">
                                <input type="checkbox" name="pmab_enable" value="1" <?php checked(1, get_option('pmab_enable'), true); ?> />
                                <span style="margin-left: 10px;">Activate on mobile devices</span>
                            </label>
                        </div>
                    </div>

                    <div class="pmab-form-row">
                        <div class="pmab-label">Native App Mode 🚀</div>
                        <div class="pmab-input-group">
                            <input type="checkbox" name="pmab_native_mode" value="1" <?php checked(1, get_option('pmab_native_mode'), true); ?> />
                            <span style="margin-left: 10px;">Hide default Header & Footer on mobile</span>
                            <p class="pmab-help">Makes your site feel like a real app by removing web elements.</p>
                        </div>
                    </div>

                </div>

                <!-- VISIBILITY SETTINGS -->
                <div class="pmab-card">
                    <div class="pmab-section-title">Visibility Controls</div>
                    <p class="pmab-help">Check the elements you want to <b>HIDE</b> on mobile devices.</p>

                    <div class="pmab-form-row">
                        <div class="pmab-label">Hide Standard Elements</div>
                        <div class="pmab-input-group">
                            <label class="switch" style="display:block; margin-bottom:10px;">
                                <input type="checkbox" name="pmab_hide_header" value="1" <?php checked(1, get_option('pmab_hide_header'), true); ?> />
                                <span style="margin-left: 10px;">Hide Main Header (Logo/Menu)</span>
                            </label>

                            <label class="switch" style="display:block; margin-bottom:10px;">
                                <input type="checkbox" name="pmab_hide_header_bottom" value="1" <?php checked(1, get_option('pmab_hide_header_bottom'), true); ?> />
                                <span style="margin-left: 10px;">Hide Header Bottom Row (Menu Bar)</span>
                            </label>

                            <label class="switch" style="display:block;">
                                <input type="checkbox" name="pmab_hide_footer" value="1" <?php checked(1, get_option('pmab_hide_footer'), true); ?> />
                                <span style="margin-left: 10px;">Hide Site Footer</span>
                            </label>
                        </div>
                    </div>

                    <div class="pmab-form-row">
                        <div class="pmab-label">Advanced Selectors</div>
                        <div class="pmab-input-group">
                            <textarea name="pmab_hide_selectors" rows="2"
                                placeholder=".custom-class, #element-id"><?php echo esc_attr(get_option('pmab_hide_selectors')); ?></textarea>
                            <p class="pmab-help">Comma-separated CSS selectors to hide extra elements.</p>
                        </div>
                    </div>
                </div>

                <!-- DESIGN SETTINGS -->
                <div class="pmab-card">
                    <div class="pmab-section-title">Glassmorphism Design</div>

                    <div class="pmab-form-row">
                        <div class="pmab-label">Background Color</div>
                        <div class="pmab-input-group">
                            <input type="color" name="pmab_bg_color"
                                value="<?php echo esc_attr(get_option('pmab_bg_color', '#ffffff')); ?>" />
                            <span style="vertical-align: super; margin-left:10px;">(With transparency works best)</span>
                        </div>
                    </div>

                    <div class="pmab-form-row">
                        <div class="pmab-label">Blur Amount</div>
                        <div class="pmab-input-group">
                            <input type="range" name="pmab_blur_amount" min="0" max="20"
                                value="<?php echo esc_attr(get_option('pmab_blur_amount', '10')); ?>"
                                oninput="this.nextElementSibling.value = this.value" />
                            <output><?php echo esc_attr(get_option('pmab_blur_amount', '10')); ?></output>px
                            <p class="pmab-help">Controls the "Frosted Glass" effect intensity.</p>
                        </div>
                    </div>

                    <div class="pmab-form-row">
                        <div class="pmab-label">Glass Opacity</div>
                        <div class="pmab-input-group">
                            <input type="range" name="pmab_opacity" min="0.1" max="1.0" step="0.05"
                                value="<?php echo esc_attr(get_option('pmab_opacity', '0.85')); ?>"
                                oninput="this.nextElementSibling.value = Math.round(this.value * 100) + '%'" />
                            <output><?php echo round(floatval(get_option('pmab_opacity', '0.85')) * 100); ?>%</output>
                            <p class="pmab-help">Controls how transparent the background color is.</p>
                        </div>
                    </div>

                    <div class="pmab-form-row">
                        <div class="pmab-label">Bar Height</div>
                        <div class="pmab-input-group">
                            <input type="number" name="pmab_height" min="50" max="100"
                                value="<?php echo esc_attr(get_option('pmab_height', '65')); ?>" style="width: 80px;" /> px
                            <p class="pmab-help">Height of the bottom bar in pixels (Default: 65).</p>
                        </div>
                    </div>

                    <div class="pmab-form-row">
                        <div class="pmab-label">Text Color</div>
                        <div class="pmab-input-group">
                            <input type="color" name="pmab_text_color"
                                value="<?php echo esc_attr(get_option('pmab_text_color', '#9ca3af')); ?>" />
                        </div>
                    </div>

                    <div class="pmab-form-row">
                        <div class="pmab-label">Label Color</div>
                        <div class="pmab-input-group">
                            <input type="color" name="pmab_label_color"
                                value="<?php echo esc_attr(get_option('pmab_label_color', '#9ca3af')); ?>" />
                            <p class="pmab-help">Specific color for text labels.</p>
                        </div>
                    </div>

                    <div class="pmab-form-row">
                        <div class="pmab-label">Opacity (%)</div>
                        <div class="pmab-input-group">
                            <input type="number" name="pmab_bg_opacity" min="0" max="100" step="5"
                                value="<?php echo esc_attr(get_option('pmab_bg_opacity', '85')); ?>" />
                        </div>
                    </div>

                    <div class="pmab-form-row">
                        <div class="pmab-label">Icon-Label Spacing (px)</div>
                        <div class="pmab-input-group">
                            <input type="number" name="pmab_icon_label_spacing" min="0" max="10" step="1"
                                value="<?php echo esc_attr(get_option('pmab_icon_label_spacing', '0')); ?>"
                                style="width: 80px;" />
                            <small style="display: block; margin-top: 5px; color: #666;">Distance between icon and text
                                (0-10px)</small>
                        </div>
                    </div>

                    <div class="pmab-form-row">
                        <div class="pmab-label">Active Color</div>
                        <div class="pmab-input-group">
                            <input type="color" name="pmab_active_color"
                                value="<?php echo esc_attr(get_option('pmab_active_color', '#2563eb')); ?>" />
                        </div>
                    </div>

                    <div class="pmab-form-row">
                        <div class="pmab-label">Separators</div>
                        <div class="pmab-input-group">
                            <label class="switch" style="display:block; margin-bottom:10px;">
                                <input type="checkbox" name="pmab_enable_separators" value="1" <?php checked(1, get_option('pmab_enable_separators'), true); ?> />
                                <span style="margin-left: 10px;">Show dividers between items</span>
                            </label>

                            <div style="display: flex; gap: 10px; margin-top: 10px;">
                                <div style="flex: 1;">
                                    <label style="display: block; margin-bottom: 5px; font-size: 12px;">Color</label>
                                    <input type="color" name="pmab_separator_color"
                                        value="<?php echo esc_attr(get_option('pmab_separator_color', '#e5e7eb')); ?>"
                                        style="width: 100%;" />
                                </div>
                                <div style="flex: 1;">
                                    <label style="display: block; margin-bottom: 5px; font-size: 12px;">Width (px)</label>
                                    <input type="number" name="pmab_separator_width" min="1" max="5" step="1"
                                        value="<?php echo esc_attr(get_option('pmab_separator_width', '1')); ?>"
                                        style="width: 100%;" />
                                </div>
                                <div style="flex: 1;">
                                    <label style="display: block; margin-bottom: 5px; font-size: 12px;">Opacity</label>
                                    <input type="number" name="pmab_separator_opacity" min="0" max="1" step="0.1"
                                        value="<?php echo esc_attr(get_option('pmab_separator_opacity', '1')); ?>"
                                        style="width: 100%;" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- CUSTOM CSS -->
                <div class="pmab-card">
                    <div class="pmab-section-title">Custom CSS</div>
                    <p class="pmab-help">Add your own CSS rules to customize specific elements (e.g., static Gallery button
                        color).</p>

                    <div class="pmab-form-row">
                        <div class="pmab-input-group">
                            <textarea name="pmab_custom_css" rows="8" style="font-family: monospace; width: 100%;"
                                placeholder=".pmab-item-2 { color: #ff0055 !important; }"><?php echo esc_textarea(get_option('pmab_custom_css')); ?></textarea>
                        </div>
                    </div>
                </div>

                <!-- MENU ITEMS -->
                <div class="pmab-card">
                    <div class="pmab-section-title">Menu Items (Max 5)</div>
                    <p class="pmab-help" style="margin-bottom: 20px;">Use <a
                            href="https://developer.wordpress.org/resource/dashicons/" target="_blank">Dashicons</a> classes
                        (e.g. <code>dashicons-admin-home</code>) or FontAwesome.</p>

                    <?php
                    $defaults = [
                        1 => ['label' => 'Home', 'icon' => 'dashicons-admin-home'],
                        2 => ['label' => 'Gallery', 'icon' => 'dashicons-format-gallery'],
                        3 => ['label' => 'Cart', 'icon' => 'dashicons-cart'],
                        4 => ['label' => 'Contact', 'icon' => 'dashicons-email'],
                        5 => ['label' => 'Account', 'icon' => 'dashicons-admin-users'],
                    ];
                    for ($i = 1; $i <= 5; $i++):
                        $val_label = get_option("pmab_item_{$i}_label");
                        $val_icon = get_option("pmab_item_{$i}_icon");

                        // placeholder logic
                        $ph_label = $defaults[$i]['label'];
                        $ph_icon = $defaults[$i]['icon'];
                        ?>
                        <div class="pmab-menu-item">
                            <div class="pmab-menu-item-header">Item #<?php echo $i; ?></div>
                            <div class="pmab-grid-item">
                                <div>
                                    <label style="font-size:12px; font-weight:600;">Label</label>
                                    <input type="text" name="pmab_item_<?php echo $i; ?>_label"
                                        placeholder="<?php echo $ph_label; ?>" value="<?php echo esc_attr($val_label); ?>" />
                                </div>
                                <div>
                                    <label style="font-size:12px; font-weight:600;">Icon Class</label>
                                    <input type="text" name="pmab_item_<?php echo $i; ?>_icon" placeholder="<?php echo $ph_icon; ?>"
                                        value="<?php echo esc_attr($val_icon); ?>" />
                                </div>
                                <div>
                                    <label style="font-size:12px; font-weight:600;">Link URL</label>
                                    <input type="text" name="pmab_item_<?php echo $i; ?>_url" placeholder="/"
                                        value="<?php echo esc_attr(get_option("pmab_item_{$i}_url")); ?>" />
                                </div>
                            </div>
                        </div>
                    <?php endfor; ?>
                </div>

                <div class="pmab-save-bar">
                    <input type="submit" name="submit" id="submit" class="button" value="Save Settings">
                </div>
            </form>

            <!-- Reset Form -->
            <form method="post" action=""
                style="margin-top: 30px; text-align: right; border-top: 1px solid #ddd; padding-top: 20px;">
                <?php wp_nonce_field('pmab_reset_action', 'pmab_reset_nonce'); ?>
                <span style="color: #666; margin-right: 10px;">Mess up? </span>
                <input type="submit" name="pmab_reset" class="button button-link-delete" value="Reset to Factory Defaults"
                    onclick="return confirm('Are you sure? This will clear all your settings.');">
            </form>
        </div>
        <?php
    }
}
