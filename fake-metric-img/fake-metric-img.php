<?php
/**
 * @package Fake_Metric_Image_For_Yandex
 * @version 1.0
 */
/*
Plugin Name: Фейковый Счетчик
Plugin URI: http://clever-as.ru/
Description: Плагин генерирует фейковую картинку счетчика посещений от Яндекса.
Armstrong: Вводим несколько настроек, и в подвале появляется фековая картинка =)
Author: saxa:p
Version: 1.0
Author URI: http://dontforget.pro/
*/

//Настройка плагина в админке
function fakeimg_add_admin_page() 
{
    // Функция для генерации страницы настроек плагина в подменю "Внешний вид"
    add_theme_page('Фейковый счетчик', 'Фейковый счетчик', 8, 'fakeimgcounter', 'fakeimg_options_page');

}
		//Стучим в API ЯндексМетрики
function fakeimg_get_real_options() {

	$opts = array('http' =>
		array(
			'method'  => 'GET',
			'header'  => 'Content-Type: application/x-yametrika+json',
			'content' => ''
		)
	);
	$context = stream_context_create($opts);
	$result = file_get_contents('http://api-metrika.yandex.ru/stat/traffic/summary.json?id='.get_option('counterid_option').'&pretty=1&oauth_token='.get_option('token_option'), false, $context);
	$result = (array)json_decode($result);
	$result = (array)$result['data'][0];
	return $result;

}
// Сама страница
function fakeimg_options_page() {
	if ($_GET['settings-updated'] == 'true') { 
	fakeimg_create();
	}
	print_r($result);
	$result = fakeimg_get_real_options();

	if (!isset($result['page_views'])) {
	$views = 'Нет данных';
	} else {	
	$views = $result['page_views']; }
	if (!isset($result['visits'])) {
	$visits = 'Нет данных';
	} else {
	$visits = $result['visits'];
	}
	if (!isset($result['visitors'])) {
	$visitors = 'Нет данных';
	} else {
	$visitors = $result['visitors'];
	}

	echo '<div class="wrap">';
	echo '<h2>Настройки ПсевдоСчетчика Яндекса</h2>';
	echo '<form method="post" action="options.php">';
	wp_nonce_field('update-options');
	echo '<table class="form-table">';
	echo '<tr valign="top">';
	echo '<th scope="row">Авторизационный токен яндекса</th>';
	echo '<td><input type="text" name="token_option" value="'.get_option('token_option').'" size="40" />';
	echo '  <a href="http://api.yandex.ru/oauth/doc/dg/tasks/get-oauth-token.xml" target="_blank">Как получить?</a></td>';
	echo '</tr>';
	echo '<tr valign="top">';
	echo '<th scope="row">Идентификатор счетчика</th>';
	echo '<td><input type="text" name="counterid_option" value="'.get_option('counterid_option').'" size="40">';
	echo '  № счётчика в списке счетчиков на странице <a href="http://metrika.yandex.ru/list/" target="_blank">http://metrika.yandex.ru/list/</a></td>';
	echo '</tr>';
	echo '</table>';

	echo '<table class="form-table" style="width: auto;">';
	echo '<tr valign="top"><td colspan="2"><h3>Реальная статистика</h3></td><td colspan="2"><h3>Действие</h3></td><td><h3>Результат</h3></td></tr>';
	echo '<tr valign="top">';
	echo '<th scope="row">Просмотры</th>';
	echo '<td><input type="text" id="views" value="'.$views.'" size="20" disabled />';
	if (get_option('views_action_option') == '+') {
	$checkviewsplus = 'selected'; $checkviewsumn = '';
	} else { $checkviewsplus = ''; $checkviewsumn = 'selected'; }
	echo '</td><td><select name="views_action_option" class="action" rel="views"><option value="+" '.$checkviewsplus.'>+</option><option value="*" '.$checkviewsumn.'>*</option></select></td>';
	echo '<td><input type="text" name="views_num_option" class="action" rel="views" value="'.get_option('views_num_option').'" size="10"></td>';
	echo '<td><input type="text" name="views_res_option" rel="views" class="res" value="'.get_option('views_res_option').'" size="20"></td>';
	echo '</tr>';
	echo '<tr valign="top">';
	echo '<th scope="row">Визиты</th>';
	echo '<td><input type="text" id="visits" value="'.$visits.'" size="20" disabled />';
	if (get_option('visits_action_option') == '+') {
	$checkvisitssplus = 'selected'; $checkvisitsumn = '';
	} else { $checkvisitssplus = ''; $checkvisitsumn = 'selected'; }
	echo '</td><td><select name="visits_action_option" class="action" rel="visits"><option value="+" '.$checkvisitssplus.'>+</option><option value="*" '.$checkvisitsumn.'>*</option></select></td>';
	echo '<td><input type="text" name="visits_num_option" class="action" rel="visits" value="'.get_option('visits_num_option').'" size="10"></td>';
	echo '<td><input type="text" name="visits_res_option" rel="visits" class="res" value="'.get_option('visits_res_option').'" size="20"></td>';
	echo '</tr>';
	echo '<tr valign="top">';
	echo '<th scope="row">Посетители</th>';
	echo '<td><input type="text" id="visitors" value="'.$visitors.'" size="20" disabled />';
	if (get_option('visitors_action_option') == '+') {
	$checkvisitorssplus = 'selected'; $checkvisitorsumn = '';
	} else { $checkvisitorssplus = ''; $checkvisitorsumn = 'selected'; }
	echo '</td><td><select name="visitors_action_option" class="action" rel="visitors"><option value="+" '.$checkvisitorssplus.'>+</option><option value="*" '.$checkvisitorsumn.'>*</option></select></td>';
	echo '<td><input type="text" name="visitors_num_option" class="action" rel="visitors" value="'.get_option('visitors_num_option').'" size="10"></td>';
	echo '<td><input type="text" name="visitors_res_option" rel="visitors" class="res" value="'.get_option('visitors_res_option').'" size="20"></td>';
	echo '</tr>';
	echo '</table>';
	echo '<br /><input name="realtime_option" type="checkbox" value="1" ';
	checked( '1', get_option( 'realtime_option' ) );
	echo ' />  Обновлять счетчик при каждом заходе. (Влияет на производительность)<br />';
	echo '<input type="hidden" name="action" value="update" />';
	echo '<input type="hidden" name="page_options" value="token_option,counterid_option,views_action_option,visits_action_option,visitors_action_option,views_num_option,visits_num_option,visitors_num_option,views_res_option,visits_res_option,visitors_res_option, realtime_option" />';
	echo '<h3>Картинка счетчика</h3><img src="" class="fakeimg"/><br />';
	echo '<p class="submit">';
	echo '<input type="submit" name="generate" class="button-primary" value="Сохранить и Сгенерировать" />';
	echo '</p>';
	echo '</form>';
	echo '</div>';
	//Считалка результатов в реальном времени
	echo '<script>
			jQuery(".action").change(function(){
				var rel = jQuery(this).attr("rel"),
				action = jQuery("select.action[rel=\'"+rel+"\'] option:selected").val(),
				num = parseInt(jQuery("input.action[rel=\'"+rel+"\']").val()),
				real = parseInt(jQuery("input#"+rel+"").val()), res;
				if (action == "+") {
				res = real+num; } else {
				res = real*num; }
				jQuery("input.res[rel=\'"+rel+"\']").val(res);
			});
			jQuery(document).ready(function(){
				jQuery("img.fakeimg").attr("src","/wp-content/plugins/fake-metric-img/img-new.png?"+Math.random());
			});
		  </script>';

}
function fakeimg_update_options() {

	$result = fakeimg_get_real_options();

	if (!isset($result['page_views'])) {
	$views = 0;
	} else {	
	$views = $result['page_views']; }
	if (!isset($result['visits'])) {
	$visits = 0;
	} else {
	$visits = $result['visits'];
	}
	if (!isset($result['visitors'])) {
	$visitors = 0;
	} else {
	$visitors = $result['visitors'];
	}

	$numviews = get_option('views_num_option');
	$numvisits = get_option('visits_num_option');
	$numvisitors = get_option('visitors_num_option');

	if (get_option('views_action_option') == '+') {
		$res = $views + $numviews;
		update_option('views_res_option', $res);
	} else {
		$res = $views * $numviews;
		update_option('views_res_option', $res);
	}
	if (get_option('visits_action_option') == '+') {
		$res = $visits + $numvisits;
		update_option('visits_res_option', $res);
	} else {
		$res = $visits * $numvisits;
		update_option('visits_res_option', $res);
	}
	if (get_option('visitors_action_option') == '+') {
		$res = $visitors + $numvisitors;
		update_option('visitors_res_option', $res);
	} else {
		$res = $visitors * $numvisitors;
		update_option('visitors_res_option', $res);
	}

}
function fakeimg_create() {

	$dir = dirname(__FILE__);


	$string1 = get_option('views_res_option');	
	$string2 = get_option('visits_res_option');
	$string3 = get_option('visitors_res_option');

	$im     = imagecreatefrompng($dir."/img-clear.png");
	$textcolor = imagecolorallocate($im, 0, 0, 0);
	
	if (strlen($string1) == 1) {
		$x1 = 65;
	} elseif (strlen($string1) == 2) {
		$x1 = 60;
	} elseif (strlen($string1) == 3) {
		$x1 = 55;
	} elseif (strlen($string1) == 4) {
		$x1 = 45;
	} elseif (strlen($string1) == 5) {
		$x1 = 40;
	} elseif (strlen($string1) == 6) {
		$x1 = 35;
	}
	if (strlen($string1) > 3) {
		$string1 = substr_replace($string1, ' ', -3, 0);
	}

	if (strlen($string2) == 1) {
		$x2 = 65;
	} elseif (strlen($string2) == 2) {
		$x2 = 60;
	} elseif (strlen($string2) == 3) {
		$x2 = 55;
	} elseif (strlen($string2) == 4) {
		$x2 = 45;
	} elseif (strlen($string2) == 5) {
		$x2 = 40;
	} elseif (strlen($string2) == 6) {
		$x2 = 35;
	}
	if (strlen($string2) > 3) {
		$string2 = substr_replace($string2, ' ', -3, 0);
	}

	if (strlen($string3) == 1) {
		$x3 = 65;
	} elseif (strlen($string3) == 2) {
		$x3 = 60;
	} elseif (strlen($string3) == 3) {
		$x3 = 55;
	} elseif (strlen($string3) == 4) {
		$x3 = 45;
	} elseif (strlen($string3) == 5) {
		$x3 = 40;
	} elseif (strlen($string3) == 6) {
		$x3 = 35;
	}
	if (strlen($string3) > 3) {
		$string3 = substr_replace($string3, ' ', -3, 0);
	}

	imagestring($im, 1, $x1, 2, $string1, $textcolor);
	imagestring($im, 1, $x2, 11, $string2, $textcolor);
	imagestring($im, 1, $x3, 21, $string3, $textcolor);
	imagepng($im, $dir."/img-new.png");
}

function fakeimg_real_time_generate() {
	fakeimg_update_options();
	fakeimg_create();
}

//Проверка на постоянную генерацию
if (get_option('realtime_option') == 1) {

	add_action('wp_head', 'fakeimg_real_time_generate');

}

// Хукаем страницу настройки плагина
add_action('admin_menu', 'fakeimg_add_admin_page');
?>