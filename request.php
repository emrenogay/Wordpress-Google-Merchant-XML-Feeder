<?php

header("HTTP/1.1 200 OK");

if(isset($_POST['merchant_feeder'])){
		if($_POST['merchant'] == 1){
        update_option('merchant_product_asu',get_option('merchant_product_asu').$_POST['id'].'|');
        echo 'Yeni işlem: Merchant gönderim kapalı';
		exit;
    }else{
        $arr = explode('|',get_option('merchant_product_asu'));
        $key = array_search($_POST['id'], $arr);
        unset($arr[$key]);
        $arr = implode('|',$arr);
        update_option('merchant_product_asu', $arr);
        echo 'Yeni işlem: Merchant gönderim açık';
		exit;
    	}
	}
?>
<html>
	<head>
		<title>No index</title>
		<meta name="robots" content="noindex">
	</head>
	<body>
	</body>
</html>
