<?php

namespace WordpressKint;

use WPTrait\Hook\AdminInit;
use WPTrait\Model;

class Main extends Model
{
    use AdminInit;

    public array $actions = [
        'add_meta_boxes' => 'admin_init'
    ];

    public function admin_init(): void
    {
        // only run on edit pages
        global $pagenow;

        if (
            !is_admin()
            || empty($pagenow)
            || !in_array($pagenow, ['post.php', 'admin.php'])
            || $this->request->query('action') !== 'edit'
        ) {
            return;
        }

        add_meta_box(
            $this->plugin->slug,
            'Devel',
            [$this, 'devel'],
        );
    }

    public function devel(): void
    {
        $post_id = $this->request->query('id');
        if (empty($post_id)) {
            $post_id = $this->request->query('post');
        }

        if (empty($post_id)) {
            echo 'Cloud not determine post id';
            return;
        }

        \Kint\Kint::$enabled_mode = true;
        \Kint\Renderer\RichRenderer::$folder = false;

        \Kint\Kint::$plugins[] = 'Kint\\Parser\\SerializePlugin';
        \Kint\Parser\SerializePlugin::$safe_mode = false;

        echo @d($post_id); //phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged,WordPress.Security.EscapeOutput.OutputNotEscaped

        $post = get_post($post_id);
        if (empty($post)) {
            echo 'Could not load post';
            return;
        }

        // post or product or order
        if (($post->post_type === 'shop_order' || $post->post_type === 'shop_order_placehold') && function_exists('wc_get_order')) {
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
}
