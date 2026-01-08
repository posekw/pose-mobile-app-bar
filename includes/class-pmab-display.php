<?php

/**
 * Handle frontend display
 *
 * @package    Pose_Mobile_App_Bar
 * @subpackage Pose_Mobile_App_Bar/includes
 */
class Pmab_Display
{

    public function init()
    {
        add_action('wp_enqueue_scripts', [$this, 'enqueue_styles']);
        add_action('wp_footer', [$this, 'render_bar']);
    }

    public function enqueue_styles()
    {
        if (!get_option('pmab_enable'))
            return;
        if (!wp_is_mobile())
            return;

        wp_enqueue_style('dashicons');
        wp_enqueue_style('pmab-frontend-css', plugins_url('../assets/css/frontend.css', __FILE__));

        // Inject Dynamic CSS Variables
        $this->inject_dynamic_css();
    }

    private function inject_dynamic_css()
    {
        $text_color = get_option('pmab_text_color', '#9ca3af');
        $active_color = get_option('pmab_active_color', '#2563eb');
        $blur = get_option('pmab_blur_amount', '10');
        $opacity = get_option('pmab_opacity', '0.85');
        $height = get_option('pmab_height', '65');

        // Calc RGBA
        $bg_color = get_option('pmab_bg_color', '#ffffff');
        $hex = str_replace('#', '', $bg_color);
        if (strlen($hex) == 3) {
            $r = hexdec(substr($hex, 0, 1) . substr($hex, 0, 1));
            $g = hexdec(substr($hex, 1, 1) . substr($hex, 1, 1));
            $b = hexdec(substr($hex, 2, 1) . substr($hex, 2, 1));
        } else {
            $r = hexdec(substr($hex, 0, 2));
            $g = hexdec(substr($hex, 2, 2));
            $b = hexdec(substr($hex, 4, 2));
        }
        $rgba_bg = "rgba($r, $g, $b, $opacity)";

        $custom_css = "
        :root {
            --pmab-height: {$height}px;
            --pmab-bg-rgba: {$rgba_bg};
            --pmab-blur: {$blur}px;
            --pmab-text-color: {$text_color};
            --pmab-active-color: {$active_color};
        }";

        // Handle selector hiding for Native Mode
        $native_mode = get_option('pmab_native_mode');
        if ($native_mode) {
            $selectors = get_option('pmab_hide_selectors', 'header, footer');
            // Clean up selecotrs
            $selectors = trim($selectors);
            if (!empty($selectors)) {
                $custom_css .= "
                 @media screen and (max-width: 768px) {
                    {$selectors} { display: none !important; }
                 }";
            }
        }

        wp_add_inline_style('pmab-frontend-css', $custom_css);
    }

    public function render_bar()
    {
        if (!get_option('pmab_enable'))
            return;
        if (!wp_is_mobile())
            return;

        ?>
        <div id="pmab-bar">
            <?php
            global $wp;
            $current_url = home_url(add_query_arg([], $wp->request));
            $current_path = parse_url($current_url, PHP_URL_PATH);
            if ($current_path == '')
                $current_path = '/';

            // Handling home path specifically
            $is_home = (is_front_page() || is_home());

            for ($i = 1; $i <= 5; $i++) {
                $label = get_option("pmab_item_{$i}_label");
                $url_raw = get_option("pmab_item_{$i}_url");
                $icon = get_option("pmab_item_{$i}_icon");

                if (!empty($label) && !empty($url_raw)) {
                    // Normalize URL for comparison
                    $item_path = parse_url($url_raw, PHP_URL_PATH);
                    if (!$item_path)
                        $item_path = $url_raw; // Fallback if full URL not provided
    
                    $is_active = false;

                    // 1. Exact Match
                    if ($url_raw == $current_url || $item_path == $current_path) {
                        $is_active = true;
                    }
                    // 2. Front Page Exception
                    if ($is_home && ($url_raw == '/' || $url_raw == home_url('/'))) {
                        $is_active = true;
                    }
                    // 3. Make sure non-home links don't light up on home
                    if ($is_home && $url_raw != '/' && $url_raw != home_url('/')) {
                        $is_active = false;
                    }

                    $active_class = $is_active ? 'active' : '';

                    echo '<a href="' . esc_url($url_raw) . '" class="pmab-item ' . $active_class . '">';
                    echo '<span class="dashicons ' . esc_attr($icon) . '"></span>';
                    echo '<span class="label">' . esc_html($label) . '</span>';
                    echo '</a>';
                }
            }
            ?>
        </div>
        <?php
    }
}
