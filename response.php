<?php
	require ('phpQuery/phpQuery.php');
	
	set_time_limit (999); // Время выполнения
	
	$current = $_POST['curr'] + 1;
	
	$url = str_replace("and", "&", $_POST['url']);
	$url = "https://catalog.api.onliner.by/search/".str_replace("http://catalog.onliner.by/", "", $url)."&page=".$current;
	
	$json_url = str_replace("https://catalog.api.onliner.by/search/", "", $url);
	$json_array = explode('?', $json_url);
	$json_name = $json_array[0];
		
	$file = fopen($json_name.".json", "a");
	fclose($file);
	
	$json = file_get_contents($json_name.".json", FILE_USE_INCLUDE_PATH);
	$json = json_decode($json, true);
	
	if ($_POST['url']){

		$ch = curl_init();
		
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_URL,$url);
		$result=curl_exec($ch);
		curl_close($ch);
		
		$array = json_decode($result, true);
		
		$last = $array['page']['last'];
		
		foreach($array['products'] as $prod){
			$prod_page = file_get_contents($prod['html_url']);
			
			$document = phpQuery::newDocument($prod_page);
			
			$cname = $document->find('.b-offers-desc__figure-wrap img');
			$pq = pq($cname);
			$prod_title = trim($pq->attr('title'));
			
			$cmanuf = $document->find('.b-offers-desc__info-specs p.complementary');
			$pq = pq($cmanuf)->eq(0)->text();
			$prod_manuf = trim(str_replace("Производитель: ", "", $pq));
			
			$param_table = $document->find('.product-specs__table');
			$param_table->find('tr.product-specs__table-title')->remove();
			$param = $param_table->find('tr');
			foreach ($param as $el) {
				$pq = pq($el);
				$pq->find('div.product-tip-wrapper')->remove();
				$pq->find('.product-specs__table-title')->remove();
				$plus = '<span class="i-tip"></span>';
				$minus = '<span class="i-x"></span>';
				$html_value = trim($pq->find('td')->eq(1)->html());
				if(strcmp($html_value, $plus) == 0){
					$value = "+";
				}elseif(strcmp($html_value, $minus) == 0){
					$value = "-";
				}else{
					$value = $pq->find('td')->eq(1)->text();
				}
				
				$params[trim($pq->find('td')->eq(0)->text())] = trim($value);
				
			}
			
			$item["title"] = $prod_title;
			$item["manuf"] = $prod_manuf;
			$item["params"] = $params;
			
			$products[] = $item;
		}
			
		$json[] = $products;
		
		$json = json_encode($json);
		file_put_contents($json_name.".json", $json);
	}else{
		echo "Введена не верная ссылка либо такого сайта не существует, рабочий пример: http://shop.by/avto/";
	}
	
	$arr = array('current' => $current, 'last' => $last, 'url' => $url);

	echo "(".json_encode($arr).")";

?>