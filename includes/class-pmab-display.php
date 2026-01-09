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
        $label_color = get_option('pmab_label_color', $text_color); // Fallback to text_color
        $active_color = get_option('pmab_active_color', '#2563eb');
        $separator_color = get_option('pmab_separator_color', '#e5e7eb');
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
            --pmab-label-color: {$label_color};
            --pmab-active-color: {$active_color};
            --pmab-separator-color: {$separator_color};
        }";

        // Convert separator color to RGB for opacity
        $sep_hex = str_replace('#', '', $separator_color);
        if (strlen($sep_hex) == 3) {
            $sep_r = hexdec(substr($sep_hex, 0, 1) . substr($sep_hex, 0, 1));
            $sep_g = hexdec(substr($sep_hex, 1, 1) . substr($sep_hex, 1, 1));
            $sep_b = hexdec(substr($sep_hex, 2, 1) . substr($sep_hex, 2, 1));
        } else {
            $sep_r = hexdec(substr($sep_hex, 0, 2));
            $sep_g = hexdec(substr($sep_hex, 2, 2));
            $sep_b = hexdec(substr($sep_hex, 4, 2));
        }
        $custom_css .= "
        :root {
            --pmab-separator-rgb: {$sep_r}, {$sep_g}, {$sep_b};
        }";

        // Label Color Logic
        $custom_css .= "
        .pmab-item .label {
            color: var(--pmab-label-color);
        }
        .pmab-item.active .label {
            color: var(--pmab-active-color);
        }";

        // Separators Logic
        if (get_option('pmab_enable_separators')) {
            $sep_width = get_option('pmab_separator_width', '1');
            $sep_opacity = get_option('pmab_separator_opacity', '1');

            $custom_css .= "
            .pmab-item {
                border-right: {$sep_width}px solid var(--pmab-separator-color);
                border-right-color: rgba(var(--pmab-separator-rgb), {$sep_opacity});
            }
            .pmab-item:last-child {
                border-right: none;
            }";
        }

        // Visibility Logic (Native Mode + Checkboxes)
        $hide_selectors = [];

        // 1. Native Mode (Legacy/Master Switch)
        if (get_option('pmab_native_mode')) {
            $hide_selectors[] = 'header';
            $hide_selectors[] = 'footer';
            $hide_selectors[] = '#copyright';
        }

        // 2. Specific Visibility Settings
        if (get_option('pmab_hide_header')) {
            $hide_selectors[] = 'header';
            $hide_selectors[] = '.site-header';
            $hide_selectors[] = '#masthead';
            $hide_selectors[] = '[data-row="top"]';
            $hide_selectors[] = '[data-row="middle"]';
        }

        if (get_option('pmab_hide_footer')) {
            $hide_selectors[] = 'footer';
            $hide_selectors[] = '.site-footer';
            $hide_selectors[] = '#colophon';
        }

        if (get_option('pmab_hide_header_bottom')) {
            $hide_selectors[] = '[class*="ct-header"] [data-row="bottom"]';
            $hide_selectors[] = '.h-bottom'; // Common class
        }

        // 3. Custom Selectors
        $custom_selectors_input = get_option('pmab_hide_selectors');
        if (!empty($custom_selectors_input)) {
            $hide_selectors[] = $custom_selectors_input;
        }

        // Generate CSS if we have selectors
        if (!empty($hide_selectors)) {
            // Flatten and unique
            $final_selectors = implode(', ', array_unique($hide_selectors));

            $custom_css .= "
            @media screen and (max-width: 768px) {
                {$final_selectors} { display: none !important; }
            }";
        }

        // Custom CSS
        $user_custom_css = get_option('pmab_custom_css');
        if (!empty($user_custom_css)) {
            $custom_css .= "\n" . $user_custom_css;
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
                    $item_index_class = 'pmab-item-' . $i;

                    echo '<a href="' . esc_url($url_raw) . '" class="pmab-item ' . $active_class . ' ' . $item_index_class . '">';
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
