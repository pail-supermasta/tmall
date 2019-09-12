<?








$stores = array(
	// 'f80cdf08-29a0-11e6-7a69-971100124ae8', // Склад-РЦ3
	// 'f257b41d-c2d9-11e7-6b01-4b1d00131678', // СКЛАД ХД
	'48de3b8e-8b84-11e9-9ff4-34e8001a4ea1',  // MP_NFF
);



foreach ($logins as $login) {


	// Получаем из БД все товары, у которых поле TMall ID не пустое
	$products = $sql->query("SELECT * FROM `ms_product` WHERE `attributes` LIKE '%{$login['field_id']}%'");

	foreach ($products as $key => $product) {

		// if ($key > 2) continue;

		$product['attributes'] = json_decode($product['attributes'], true);
		// Получаем ID товара в Aliexpress
		$product['ali_product_id'] = $product['attributes'][$login['field_id']];
		if ($product['ali_product_id'] == '') continue;

		// Получаем сток из БД
		$product['stock'] = getMsStock($product['id'], $stores);
		$product['ali_stock'] = $product['stock'];
		// Ограничиваем максимальный сток 5 штуками
//		if ($product['ali_stock'] > 5) $product['ali_stock'] = 5;
		if ($product['ali_stock'] < 0) $product['ali_stock'] = 0;

		$arr = ali_setProductStock($product['ali_product_id'], $product['ali_stock'], $login);
		
		if (!$arr){
			dump($product['name'].' неверный ID aliexpress для '.$login['name']);
			continue;
		}

		$product = array_merge($product, $arr);

		if ($product['new_stock'] === false){
			dump($product['ali_product_id'].' '.$product['code'].' '.round(100 * ($key / count($products))).'% '.$product['name'].' '.$product['old_stock'].' без изменений');
		} else {
			dump($product['ali_product_id'].' '.$product['code'].' '.round(100 * ($key / count($products))).'% '.$product['name'].' '.$product['old_stock'].' '.$product['new_stock']);
		}

		$products[$key] = $product;
	}

	// Получить товар
	// ali_getProduct(33018374032);

	// Установить сток
	// ali_setProductStock(33018374032, 1);

	// Получить список товаров
	// ali_getProductList()


	?>





	<?
	$test = false;
	$message = "Обновление стока «{$login['name']}» на Aliexpress:";
	foreach($products as $key => $product){ 
		// if ($key > 2) continue;
		if (!isset($product['ali_product_id'])) continue;
		if ($product['new_stock'] === false){
			// $message.= $product['name']."\n";
		} else {
			$test = true;
			$message.= $product['name'].' '.$product['old_stock'].' => <b>'.$product['new_stock']."</b>\n";
		}
	}

	if (!$test){
		$message.= " без изменений<br>";
	}

	dump(telegram($message));

	echo $message;
}
