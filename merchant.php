<?php

if ( !is_plugin_active( 'woocommerce/woocommerce.php' ) ) exit;

header("Content-Type:text/xml");

require_once($_SERVER['DOCUMENT_ROOT'] . '/wp-load.php');

$args = array(
    'post_type' => 'product',
    'posts_per_page' => -1,
);
$loop = new WP_Query($args);
?>

<rss version="2.0"
     xmlns:g="http://base.google.com/ns/1.0">
    <channel>
        <title><?php echo get_bloginfo(); ?></title>
        <link><?php echo get_site_url(); ?></link>
        <description>Tüm Ürünler</description>

        <?php
        while ($loop->have_posts()) : $loop->the_post();
        global $product;
        $arr = explode('|', get_option('merchant_product_asu'));
        if (!in_array(get_the_ID(), $arr)) {
            ?>
            <item>
                <g:kimlik><?php echo get_the_ID(); ?></g:kimlik>
                <g:başlık><?php echo get_the_title(); ?></g:başlık>
                <g:açıklama><?php echo htmlspecialchars(get_the_excerpt()) ?></g:açıklama>
                <g:resim_bağlantısı><?php echo get_the_post_thumbnail_url() ?></g:resim_bağlantısı>
                <g:bağlantı><?php echo get_the_permalink(); ?></g:bağlantı>
                <g:stok_durumu>stokta</g:stok_durumu>
                <?php
                if (empty($product->get_sale_price())) {
                    echo strstr($product->get_price(), '.') || strstr($product->get_price(), ',') ? '<g:fiyat>' . $product->get_price() . ' TRY' . '</g:fiyat>' : '<g:fiyat>' . $product->get_price() . '.00 TRY' . '</g:fiyat>';
                } else {
                    echo strstr($product->get_regular_price(), '.') || strstr($product->get_regular_price(), ',') ? '<g:fiyat>' . $product->get_regular_price() . ' TRY' . '</g:fiyat>' : '<g:fiyat>' . $product->get_regular_price() . '.00 TRY' . '</g:fiyat>';
                    echo strstr($product->get_sale_price(), '.') || strstr($product->get_sale_price(), ',') ? '<g:indirimli_fiyat>' . $product->get_sale_price() . ' TRY</g:indirimli_fiyat>' : '<g:indirimli_fiyat>' . $product->get_sale_price() . '.00 TRY</g:indirimli_fiyat>';
                }
                ?>
                <g:durum>yeni</g:durum>
                <g:marka><?php echo get_bloginfo(); ?></g:marka>
            </item>
            <?php
        }
        endwhile;
        wp_reset_query();
        ?>
    </channel>
</rss>

