<html>
<head>
	<meta charset="UTF-8"/>
	<script src="//code.jquery.com/jquery-1.10.2.js"></script>
	<style>
		.results p img{
			height: 20px;
		}
	</style>
</head>
<body>
<p>Введите ссылку на категорию онлайнера ( Пример: http://catalog.onliner.by/oven_cooker?mfr%5B0%5D=bosch&order=price:asc ), можно с уточняющими параметрами из фильтра.</p>
<input type="text" class="url" placeholder="Введите адрес категории onliner.by" size="40" /><button name="btn" class="btn">Поехали</button><br/><br/>
<div class="results"></div>
<div class="loader"></div>

<script language="javascript" type="text/javascript">

    $('.btn').click(ajaxRequest);
    function ajaxRequest(data){
    	var info = eval(data);
    	if(info.current){
    		var current = info.current;
    	}else{
    		var current = 0;
    	}
    	$.ajax({
          type: 'POST',
          url: 'response.php',
          data: 'url=' + $('.url').val().replace(new RegExp("&",'g'), "and") + '&curr=' + current,
		  beforeSend: function(){
            $('.loader').html('<img src="loader.gif">');
        },
          success: function(data){
          	var info = eval(data);
            $('.results').append("Обработанно страниц: "+info.current+" из "+info.last+" Ссылка: "+info.url+"<br/>");
            $('.loader').html('');
            if(info.current<info.last){
            	ajaxRequest(data);
            }
          }
        });
   	}
    </script>
</body>
</html>