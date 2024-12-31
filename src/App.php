<?php

namespace WordpressKint;

use WPTrait\Hook\AdminInit;
use WPTrait\Model;

class App extends Model
{
    use AdminInit;

    public array $actions = [
        'add_meta_boxes' => 'admin_init'
    ];

    // public $adminInit = [
    //     'wp_before_admin_bar_render' => 'admin_init',
    // ];

    public function admin_init(): void
    {
        // only run on edit pages
        global $pagenow;
        if ($pagenow !== 'post.php' && $this->request->query('action') !== 'edit') {
            return;
        }

        if (!is_admin()) {
            return;
        }

        add_meta_box(
            $this->plugin->slug,                 // Unique ID
            'Devel',      // Box title
            [$this, 'devel'],
            //['post', 'page', 'product'] // $screen                            // Post type
        );
    }

    public function devel(): void
    {
        if (!is_admin()) {
            return;
        }

        $post_id = $this->request->query('post');
        $post = get_post($post_id);
        if (empty($post)) {
            echo 'Could not load post';
        }

        \Kint\Kint::$enabled_mode = true;
        \Kint\Renderer\RichRenderer::$folder = false;

        \Kint\Kint::$plugins[] = 'Kint\\Parser\\SerializePlugin';
        \Kint\Parser\SerializePlugin::$safe_mode = false;

        // post or product or order
        if ($post->post_type === 'shop_order' && function_exists('wc_get_order')) {
            $order = wc_get_order($post_id);
            $order->get_meta_data();
            echo @d($order); //phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged,WordPress.Security.EscapeOutput.OutputNotEscaped
        } elseif ($post->post_type === 'product' && function_exists('wc_get_product')) {
            $product = wc_get_product($post_id);
            $product->get_meta_data();
            echo @d($product); //phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged,WordPress.Security.EscapeOutput.OutputNotEscaped
            if ($product->is_type('variable')) {
                /** @var WC_Product_Variable $product */
                $variations = get_posts(['post_parent' => $post_id, 'post_type' => 'product_variation', 'post_status' => ['publish', 'private']]);
                // echo @d($variations); //phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged,WordPress.Security.EscapeOutput.OutputNotEscaped
                foreach ($variations as $variation) {
                    $variation = wc_get_product($variation->ID);
                    $variation->get_meta_data();
                    echo @d($variation); //phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged,WordPress.Security.EscapeOutput.OutputNotEscaped
                }
            }
        }

        // post
        echo @d($post); //phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged,WordPress.Security.EscapeOutput.OutputNotEscaped

        // meta
        $post_meta = get_post_meta($post_id);
        if (!empty($post_meta)) {
            echo @d($post_meta); //phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged,WordPress.Security.EscapeOutput.OutputNotEscaped
        }

        // acf
        if (function_exists('get_field_objects')) {
            $acf_fields = get_field_objects($post_id);
            if (!empty($acf_fields)) {
                echo @d($acf_fields); //phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged,WordPress.Security.EscapeOutput.OutputNotEscaped
            }
        }
    }

    public function inline($variable): string
    {
        \Kint\Kint::$enabled_mode = true;
        \Kint\Renderer\RichRenderer::$folder = false;
        $inline = @d($variable); //phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
        return $inline;
    }
}
