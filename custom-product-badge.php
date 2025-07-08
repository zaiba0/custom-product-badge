<?php
/**
 * Plugin Name: Custom Product Badge
 * Plugin URI: https://Custom Product Badge.com
 * Description: Add custom badges like "New", "Sale", etc., to WooCommerce products with custom text and color.
 * Version: 1.1
 * Author: zaiba
 * License: GPL2
 */

// Add badge select and custom text/color meta box
add_action('add_meta_boxes', 'cpb_add_badge_meta_box');
function cpb_add_badge_meta_box() {
    add_meta_box('cpb_badge', 'Product Badge', 'cpb_badge_meta_box_callback', 'product', 'side');
}

function cpb_badge_meta_box_callback($post) {
    $badge = get_post_meta($post->ID, '_cpb_badge', true);
    $badge_text = get_post_meta($post->ID, '_cpb_badge_text', true);
    $badge_color = get_post_meta($post->ID, '_cpb_badge_color', true);
    ?>
    <label for="cpb_badge">Select Badge:</label><br>
    <select name="cpb_badge" id="cpb_badge">
        <option value="">--None--</option>
        <option value="new" <?php selected($badge, 'new'); ?>>New</option>
        <option value="sale" <?php selected($badge, 'sale'); ?>>Sale</option>
        <option value="featured" <?php selected($badge, 'featured'); ?>>Featured</option>
    </select><br><br>

    <label for="cpb_badge_text">Custom Badge Text:</label><br>
    <input type="text" name="cpb_badge_text" id="cpb_badge_text" value="<?php echo esc_attr($badge_text); ?>"><br><br>

    <label for="cpb_badge_color">Badge Color:</label><br>
    <input type="color" name="cpb_badge_color" id="cpb_badge_color" value="<?php echo esc_attr($badge_color ?: '#ff4b2b'); ?>"><br>
    <?php
}

add_action('save_post', 'cpb_save_badge_meta_box');
function cpb_save_badge_meta_box($post_id) {
    if (isset($_POST['cpb_badge'])) {
        update_post_meta($post_id, '_cpb_badge', sanitize_text_field($_POST['cpb_badge']));
    }
    if (isset($_POST['cpb_badge_text'])) {
        update_post_meta($post_id, '_cpb_badge_text', sanitize_text_field($_POST['cpb_badge_text']));
    }
    if (isset($_POST['cpb_badge_color'])) {
        update_post_meta($post_id, '_cpb_badge_color', sanitize_hex_color($_POST['cpb_badge_color']));
    }
}

// Display badge on product loop and single product page
add_action('woocommerce_before_shop_loop_item_title', 'cpb_display_product_badge', 10);
add_action('woocommerce_single_product_summary', 'cpb_display_product_badge', 5);

function cpb_display_product_badge() {
    global $product;
    $product_id = $product->get_id();
    $badge = get_post_meta($product_id, '_cpb_badge', true);
    $badge_text = get_post_meta($product_id, '_cpb_badge_text', true);
    $badge_color = get_post_meta($product_id, '_cpb_badge_color', true);

    // Auto-apply "New" badge if product is less than 30 days old and no badge set
    if (!$badge) {
        $post_date = get_the_date('U', $product_id);
        if ((time() - $post_date) <= 30 * DAY_IN_SECONDS) {
            $badge = 'new';
        }
    }

    if ($badge) {
        $text = $badge_text ?: ucfirst($badge);
        $style = $badge_color ? ' style="background:' . esc_attr($badge_color) . '"' : '';
        echo '<span class="cpb-badge cpb-' . esc_attr($badge) . '"' . $style . '>' . esc_html($text) . '</span>';
    }
}

// Add inline styles for badges
add_action('wp_head', 'cpb_badge_styles');
function cpb_badge_styles() {
    echo '<style>
        .cpb-badge {
            position: absolute;
            color: white;
            padding: 5px 10px;
            font-size: 12px;
            top: 10px;
            left: 10px;
            z-index: 9;
            border-radius: 4px;
        }
    </style>';
}
