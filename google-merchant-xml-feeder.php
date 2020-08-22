<?php 

/**
 * Plugin Name: Google Merchant XML Feeder
 * Description: WooCommerce altyapısını kullanan sitelerin, Google Merchant'a ürün gönderebilmesi için XML dosyası üreten basit bir eklenti.
 * Author: Emre Nogay
 * Author URI: https://emrenogay.com/
 * Version: 1.3
 */

if( !defined('ABSPATH') ) exit;



if ( !is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
		add_action('admin_notices', function(){
        global $lang;
        echo '<div class="notice notice-warning is-dismissible"><p>Görünen o ki sitenizde WooCommerce kurulu değil. Etkin olmadığı sürece Google Merchant için XML dosyası üretmemiz mümkün değil...</p></div>';
    });
	
} 

function xml_feeder_css()
{
    $style = '
            <style type="text/css">
                .main-cdn{width:100%;}
                .container-cdn{
                    margin:0 auto;width:450px;
                    background:#fff;padding:25px;
                    border-radius:10px;margin-top:6%;
					box-shadow: 1px 0px 25px rgba(0,0,0,0.2);
                }
            </style>';
    echo $style;
}
add_action('admin_head', 'xml_feeder_css');
add_action('admin_menu', 'xml_feeder_menu');

function xml_feeder_menu()
{
    global $lang;
    add_menu_page('Google Merchant XML Feeder', 'Google Merchant XML Feeder', 'manage_options', 'merchant_xml_emre_nogay', 'callback_xml_feeder');
}

function callback_xml_feeder(){
?>
<div class="main-cdn">
        <div class="container-cdn">
            <h1>Google Merchant XML Feeder</h1>
			<span>
				<?php 
					$arr = explode('|', get_option('merchant_product_asu'));
					$count = count($arr) - 1;
					echo $count.' adet gönderilmeyen ürün var<br>';
					echo 'Merchant linkiniz: '.get_site_url().'/google-merchant.xml/';
				?>
			</span>
            <hr>
            <div>
                 <div>
                   <?php
						foreach($arr as $id):
							if(!empty($id)){
								$product = wc_get_product($id);
								echo '<a target="_blank" href="'.$product->get_permalink().'">'.$product->get_name().'</a><br>';
							}
						endforeach;
					 ?>
                 </div>
            </div>
        </div>
    </div>
<?php
}

$check_page_exist = get_page_by_title('Google Merchant XML Feeder'); 
if(empty($check_page_exist->post_title)){ 
    global $wpdb; 
    $table = $wpdb->prefix.'posts';
    $query = array(
        'post_author' => get_current_user_id(),
        'post_title' =>  'Google Merchant XML Feeder',
        'post_name' => 'google-merchant.xml', 
        'post_type' => 'page',
        'comment_status' => 'closed',
        'ping_status' => 'closed',
    );
    $wpdb->insert($table, $query);
}

   

if (strstr($_SERVER['REQUEST_URI'], '/google-merchant.xml')) {
    add_filter('template_include', 'include_temp');
    function include_temp()
    {
        return plugin_dir_path( __FILE__ ) . '/merchant.php';
    }
} 

if (strstr($_SERVER['REQUEST_URI'], '/google-merchant-request')) {
    add_filter('template_include', 'include_request');
    function include_request()
    {
        return plugin_dir_path( __FILE__ ) . '/request.php';
    }
} 

add_filter( 'manage_edit-product_columns', function ( $columns ){
   if(strstr($_SERVER['REQUEST_URI'],'post_type=product')){
       ?>
       <script
               src="https://code.jquery.com/jquery-3.5.1.js"
               integrity="sha256-QWo7LDvxbWT2tbbQ97B53yJnYU3WhH/C8ycbRAkjPDc="
               crossorigin="anonymous">
       </script>
       <?php
       $columns['merchant'] = 'Merchant Gönder/Gönderme';
       return $columns;
   }
   return null;
} );


add_action( 'manage_product_posts_custom_column', function ( $column, $product_id ){
    $or = $product_id;
    if ( $column == 'merchant' ) {
        ?>
        <div id="modalajax<?php echo $product_id ?>" >
            <input
                    type="hidden"
                    name="merchant_feeder<?php echo $product_id ?>"
                    id="merchant_feeder<?php echo $product_id ?>"
            />
            <input
                    type="hidden"
                    value="<?php echo $or ?>"
                    name="product_id<?php echo $product_id ?>"
                    id="product_id<?php echo $product_id ?>"
            />
            <?php
            $arr = explode('|', get_option('merchant_product_asu'));
            if(in_array($or,$arr)){
                ?>
                <label>
                   <span id="modalbody<?php echo $or ?>">
                        Gönderim: Aktif Değil
                   </span>
                    <input
                            type="checkbox"
                            name="merchant<?php echo $product_id ?>"
                            id="merchant<?php echo $product_id ?>"
                            value="1"
                            onchange="alikum<?php echo $or ?>()"
                            checked="checked" />
                </label>
                <?php
            }else{
                ?>
                <label>
                    <span id="modalbody<?php echo $or ?>">
                    Gönderim: Aktif
                    </span>

                    <input
                            type="checkbox"
                            name="merchant<?php echo $product_id ?>"
                            id="merchant<?php echo $product_id ?>"
                            value="0"
                            onchange="alikum<?php echo $or ?>()"
                    />
                </label>
                <?php
            }
            ?>

        </div>

        <script type="text/javascript">

            function alikum<?php echo $or ?>() {

                if($('#merchant<?php echo $product_id ?>').attr('value') === '1'){
                    $('#merchant<?php echo $product_id ?>').attr('value',0);
                }else{
                    $('#merchant<?php echo $product_id ?>').attr('value',1);
                }

                var id = $('input#product_id<?php echo $product_id ?>').val();
                var merchant_feeder = $('input#merchant_feeder<?php echo $product_id ?>').val();
                var merchant = $('input#merchant<?php echo $product_id ?>').val();

                $('#modalbody<?php echo $or ?>').html("Yükleniyor...");

                $.ajax({
                    type: 'post',
                    url: '<?php echo get_site_url() . "/google-merchant-request" ?>',
                    data: {
                        id:id,
                        merchant_feeder:merchant_feeder,
                        merchant:merchant
                    },
                    success: function (msg) {
                        $('#modalbody<?php echo $or ?>').html(msg);
                    },
                    error: function(msg){
                        $('#modalbody<?php echo $or ?>').html(msg);
                    }
                });
            }
        </script>
        <?php
    }
}, 99, 2 );

