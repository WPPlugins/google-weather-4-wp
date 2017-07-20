<?php

/*
Plugin Name: Google Weather 4 WP
Plugin URI: http://www.virtual2.net/
Description: Plugin to show the weather on your blog.
Author: César Araújo & Ricardo Jorge
Version: 1.5
Author URI: http://www.virtual2.net/
*/



function getCitysNames($cidade){
	// Cria Google API url
	$meteorologia_url = 'http://www.ipma.pt/extdatasource/prev_locais.jsp?dayPrev=0';
	$arrayCity = array();
	// Obtem o ficheiro XML
        $wxml = simplexml_load_string(file_get_contents($meteorologia_url));
	if ($wxml) 
	{
            $resultado = array();
            $weather = $wxml->forecast;
            foreach ($weather->land as $values ) {
				array_push($arrayCity, $values[name]);
            }
	} 
         
	return  $arrayCity;
}


function getWeatherInfo($cidade){
	// Cria Google API url
	$meteorologia_url = 'http://www.google.com/ig/api?weather='. $cidade . '&hl=' . $lingua;
	$meteorologia_url = 'http://www.ipma.pt/extdatasource/prev_locais.jsp?dayPrev=0';
	$resultado = array();	
	// Obtem o ficheiro XML
        $wxml = simplexml_load_string(file_get_contents($meteorologia_url));
	if ($wxml) 
	{
            $resultado = array();
            $weather = $wxml->forecast;
            foreach ($weather->land as $values ) {
                if($values[name] == $cidade)
                {
                    $resultado['tmin'] = $values->landWeather->temp->tempMin;
                    $resultado['tmax'] = $values->landWeather->temp->tempMax;
                    $resultado['curWeatherPic'] = $values->landWeather->currentWeather[symbolID];
                    $resultado['curWeatherDes'] = $values->landWeather->currentWeather->symbolDesc;
                    $resultado['windPic'] = $values->landWeather->wind['windSymbolID'];
                    $resultado['windDesc'] = $values->landWeather->wind->windDirectionDescription;
                    $resultado['windDescResume'] = $values->landWeather->wind->windDirectionDescription;
                    $resultado['windSpeed'] = $values->landWeather->wind->windSpeed;
                }
            }
	} 
                    $resultado['cidade'] = $cidade;
	return  $resultado;
}

// Fim de funções auxiliares de calculo

function calc_googleWeather4WP($lang, $city, $dias) // Onde desenhamos o conteudo :)
{

	// Vou verificar se o user defeniu qTranslate ou não e se o mesmo nao existir defenimos uma linguagem default.
	

	

	// Vamos obter o estado metereologico para um array
	$resultado =  getWeatherInfo($city);
	
	drawWidget($resultado);

}

function drawWidget($resultado){
?>
<div id="gw4wpimg" style="position:relative; text-align:center;">
	<img src="<?php echo get_bloginfo('wpurl') . '/wp-content/plugins/google-weather-4-wp/imgs/' . $resultado['curWeatherPic']; ?>.png" alt="<?php echo $resultado['curWeatherDes']; ?>" title="<?php echo $resultado['curWeatherDes']; ?>" />
	<div id="gw4wpdesc" style="font-size:40px; position:absolute; bottom:10px; width:100%; margin-left:auto; margin-right:auto; text-align:center;">
	<?php echo $resultado['tmax']; ?> <sup>o</sup>C</div>
</div>
<?php echo $resultado['cidade']; ?>
<?php
}

 // ************************************************************************************ //
 
 
 
function widget_googleWeather4WP($args) {
  extract($args);
  $options = get_option("widget_googleWeather4WP");
  if (!is_array( $options )){
		$options = array(
      	'title' => 'GoogleWeather 4 WP',
      	'days' => 0,
      	'translate' => true,
      	'defaultlang' => 'pt-pt'
      	
      );
  }
  
  // Validação da linguagem usando o qTranslate
	if($options['translate']){
		if (function_exists(qtrans_getLanguage)){ //Temos qTranslate?
			$lang= qtrans_getLanguage();	// Parece que sim portanto vamos usar a linguagem lá defenida
	
		 }else{
		 	$lang = $options['defaultlang']; // Não temos qTranslate portanto linguagem default
		 }
	}else{
		$lang = $options['defaultlang']; // Ora bem o user não selecionou a  opção qTranslate por isso linguagem default
	}

	if (!$lang){ $lang = 'pt-pt'; } // Não temos nada? está a usar um plugin fresquinho sem configurar por isso é tuga =D
	if ($options['city']) { $city= $options['city']; }else{ $city = 'lisboa,Portugal'; }  // No caso de cidade não defenida, é cá dos nossos, Porto carago
  
  
  echo $before_widget;
  echo $before_title;?><?php echo $options['title'] ?><?php echo $after_title; calc_googleWeather4WP($lang, $city, $dias);
  echo $after_widget;
}
 
 
 // ************************************************************************************ //
 
 
 function googleWeather4WP_control()
{
	$options = get_option("widget_googleWeather4WP");
	if (!is_array( $options )){
		$options = array(
      	'title' => 'GoogleWeather 4 WP',
      	'days' => 0,
      	'translate' => true,
      	'defaultlang' => 'pt-pt'
		);
	}
 
  if ($_POST['googleWeather4WP-Submit'])
  {
    $options['title'] = htmlspecialchars($_POST['googleWeather4WP-WidgetTitle']);
    $options['city'] = htmlspecialchars($_POST['googleWeather4WP-Widgetcity']);
    $options['days'] = htmlspecialchars($_POST['googleWeather4WP-Widgetdays']);
    $options['translate'] = htmlspecialchars($_POST['googleWeather4WP-Widgettranslate']);
    $options['defaultlang'] = htmlspecialchars($_POST['googleWeather4WP-Widgetdefaultlang']);
    update_option("widget_googleWeather4WP", $options);
  }

	// Verificação da existencia do plugin qTranslate
	if (function_exists(qtrans_getLanguage)){
		$autolang= qtrans_getLanguage();
	 }else{
	 	$autolang="";
	 }

?>

<p>
	<label for="googleWeather4WP-WidgetTitle">Título: </label><br />
   <input type="text" id="googleWeather4WP-WidgetTitle" name="googleWeather4WP-WidgetTitle" value="<?php echo $options['title'];?>" />
</p>
<p>
	<label for="googleWeather4WP-Widgetcity">Cidade:</label><br />
      <select id="googleWeather4WP-Widgetcity" name="googleWeather4WP-Widgetcity">
		<?php $arrayCity = getCitysNames(); ?>
		<?php foreach ($arrayCity as $city) { ?>
			<option value="<?php echo $city; ?>" <?php if ($options['city']==$city){ echo 'selected="true"'; } ?>><?php echo $city; ?></option>
		<?php } ?>
	</select>
</p>
<p>
   <label for="googleWeather4WP-Widgetdays" style="visibility:hidden;">Ver previsão para: </label><br />
   <select id="googleWeather4WP-Widgetdays" style="visibility:hidden;" name="googleWeather4WP-Widgetdays">
		<option value="0" <?php if ($options['days']==0){ echo 'selected="true"'; } ?>>Não mostrar previsão</option>
		<option value="1" <?php if ($options['days']==1){ echo 'selected="true"'; } ?>>1 dia</option>
		<option value="2" <?php if ($options['days']==2){ echo 'selected="true"'; } ?>>2 dias</option>
		<option value="3" <?php if ($options['days']==3){ echo 'selected="true"'; } ?>>3 dias</option>
		<option value="4" <?php if ($options['days']==4){ echo 'selected="true"'; } ?>>4 dias</option>
		<option value="5" <?php if ($options['days']==5){ echo 'selected="true"'; } ?>>5 dias</option>
	</select>
</p>
<p>


	<input type="checkbox" id="googleWeather4WP-Widgettranslate" name="googleWeather4WP-Widgettranslate" value="translate" <?php if ($options['translate']){ echo 'checked="true"'; } ?> <?php  if (!$autolang){ echo 'disabled="true"'; } ?>> Use Qtranslate<br />

<br />
	<label  style="visibility:hidden;" for="googleWeather4WP-Widgetdefaultlang">Language by default: (ex: es) </label><br />
   <input  style="visibility:hidden;" type="text" id="googleWeather4WP-Widgetdefaultlang" name="googleWeather4WP-Widgetdefaultlang" value="<?php echo $options['defaultlang'];?>" />

</p>
<p>
	<input type="hidden" id="googleWeather4WP-Submit" name="googleWeather4WP-Submit" value="1" />
</p>
<?php
}


 // ************************************************************************************ //


function googleWeather4WP_init()
{
  register_sidebar_widget(__('Google Weather 4 WP'), 'widget_googleWeather4WP');
  register_widget_control(   'Google Weather 4 WP', 'googleWeather4WP_control', 300, 200 );
}


 // ************************************************************************************ //


add_action("plugins_loaded", "googleWeather4WP_init");
