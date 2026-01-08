<?php

/**
 * Fired during plugin activation
 *
 * @link       https://posemedia.sa
 * @since      3.1.0
 *
 * @package    Pose_Mobile_App_Bar
 * @subpackage Pose_Mobile_App_Bar/includes
 */

class Pmab_Activator {

	/**
	 * Set default options upon activation.
	 *
	 * @since    3.1.0
	 */
	public static function activate() {
        // Default Design
        if (!get_option('pmab_bg_color'))
            update_option('pmab_bg_color', '#ffffff');
        if (!get_option('pmab_text_color'))
            update_option('pmab_text_color', '#9ca3af');
        if (!get_option('pmab_active_color'))
            update_option('pmab_active_color', '#2563eb');
        if (!get_option('pmab_blur_amount'))
            update_option('pmab_blur_amount', '10');

        // Default Menu Items
        $defaults = [
            1 => ['label' => 'Home', 'icon' => 'dashicons-admin-home', 'url' => home_url('/')],
            2 => ['label' => 'Gallery', 'icon' => 'dashicons-format-gallery', 'url' => home_url('/gallery')],
            3 => ['label' => 'Cart', 'icon' => 'dashicons-cart', 'url' => home_url('/cart')],
            4 => ['label' => 'Contact', 'icon' => 'dashicons-email', 'url' => home_url('/contact')],
            5 => ['label' => 'Account', 'icon' => 'dashicons-admin-users', 'url' => home_url('/my-account')],
        ];

        foreach ($defaults as $i => $item) {
            if (!get_option("pmab_item_{$i}_label"))
                update_option("pmab_item_{$i}_label", $item['label']);
            if (!get_option("pmab_item_{$i}_icon"))
                update_option("pmab_item_{$i}_icon", $item['icon']);
            if (!get_option("pmab_item_{$i}_url"))
                update_option("pmab_item_{$i}_url", $item['url']);
        }

        // Enable by default
        if (!get_option('pmab_enable'))
            update_option('pmab_enable', 1);
	}

}
