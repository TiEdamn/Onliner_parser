<?php
	require ('phpQuery/phpQuery.php');
	
	set_time_limit (999); // Время выполнения
	
	$exclude = array("support@deal.by", "21@21vek.by"); // Исключения
	
	//error_reporting( E_ERROR );  // Выпиливает варнинги и ноутисы
	
	$json_url = str_replace("https://catalog.api.onliner.by/search/", "", "https://catalog.api.onliner.by/search/rotaryhammers?mfr[0]=bosch&order=reviews_rating:desc");
	$json_array = explode('?', $json_url);
	$json_name = $json_array[0];
	
	echo $json_name."<br/>";
	
	$file = fopen($json_name.".json", "a");
	fclose($file);
	
	$json = file_get_contents($json_name.".json", FILE_USE_INCLUDE_PATH);
	$json = json_decode($json, true);
	
	if (get_headers("https://catalog.api.onliner.by/search/rotaryhammers?mfr[0]=bosch&order=reviews_rating:desc", 1)){

		$ch = curl_init();
		
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_URL,"https://catalog.api.onliner.by/search/oven_cooker?mfr%5B0%5D=bosch&order=price%3Aasc");
		$result=curl_exec($ch);
		curl_close($ch);
		
		//echo $result;
		//var_dump(json_decode($result, true));
		
		$array = json_decode($result, true);
		
		echo "Количество товаров: ".$array['total']."<br/>";
		echo "Количество страниц: ".$array['page']['last']."<br/><br/>";
		
		
		
		foreach($array['products'] as $prod){
			$prod_page = file_get_contents($prod['html_url']);
			
			$document = phpQuery::newDocument($prod_page);
			
			$cname = $document->find('.b-offers-desc__figure-wrap img');
			$pq = pq($cname);
			$prod_title = trim($pq->attr('title'));
			echo $prod_title."<br/>";
			
			$cmanuf = $document->find('.b-offers-desc__info-specs p.complementary');
			$pq = pq($cmanuf)->eq(0)->text();
			$prod_manuf = trim(str_replace("Производитель: ", "", $pq));
			echo $prod_manuf."<br/>";
			
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
				echo "<p>".trim($pq->find('td')->eq(0)->text())." : ".$value."</p>";
				
				$params[trim($pq->find('td')->eq(0)->text())] = trim($value);
				
			}
			
			$item["title"] = $prod_title;
			$item["manuf"] = $prod_manuf;
			$item["params"] = $params;
			
			$products[] = $item;
			
			echo "<br/><hr/><br/>";
		}
			
		$json[] = $products;
		
		$json = json_encode($json);
		file_put_contents($json_name.".json", $json);
		
	}else{
		echo "Введена не верная ссылка либо такого сайта не существует, рабочий пример: http://shop.by/avto/";
	}

?>