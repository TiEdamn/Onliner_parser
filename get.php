<?php
	$file = file_get_contents('rotaryhammers.json', FILE_USE_INCLUDE_PATH);

	$array = json_decode($file, true);
	
	//var_dump(json_decode($file, true));
	
	print_r($array);