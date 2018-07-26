<?php

/* Тестовое задание */
/* Реализация API для приложения 'Каталог рецептов' */

/* Возможности: 
- создание пользователя
- авторизация
- создание, редактирование и удаление рецептов от лица пользователя
- загрузка фотографии рецепта
*/

/* Разработчик: Web Implementator 26.07.2018 */

include('./mysqli.php');
include('./class.php');
$Obj = new mainClass();

if ($_FILES)
{
	$token = $mysqli->real_escape_string($_POST['token']);
	$id = $mysqli->real_escape_string($_POST['id']);
		
	$check = $mysqli->query("SELECT * FROM users WHERE user_token='".$token."'")->fetch_object();
		
	$diff = $Obj->date_diff(date('Y-m-d H:i:s'), $check->user_token_created);
	
	if ($check && $diff < 15) {
			
		$path = $_SERVER['DOCUMENT_ROOT'] . '/images/';
			
		if(is_uploaded_file($_FILES["filename"]["tmp_name"])) move_uploaded_file($_FILES["filename"]["tmp_name"], $path  .  $_FILES["filename"]["name"]);
		$scheme = isset($_SERVER['HTTP_SCHEME']) ? $_SERVER['HTTP_SCHEME'] : (((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') || 443 == $_SERVER['SERVER_PORT']) ? 'https' : 'http');
		$img_url = $scheme . '://' . $_SERVER["SERVER_NAME"] . '/images' . $_FILES['filename']['tmp_name'];
			
		$mysqli->query("UPDATE recipes SET recipes_photo='".$img_url."' WHERE recipes_id=".$id);
		$data = array("status" => "success", "desc" => 'Фотография рецепта успешно загружена!');
	}
	else $data = array("status" => "error", "desc" => 'Token не найден или устарел, авторизуйтесь заного!');
	
	$result = json_encode($data);  
			
	return $result;
}
if (!empty($_GET)) {

	/* Пользователь */
	if ($_GET['command'] == 'user_create')
	{
		$login = $mysqli->real_escape_string($_GET['login']);
		$password = $mysqli->real_escape_string($_GET['password']);
		
		$check = $mysqli->query("SELECT * FROM users WHERE user_login='".$login."'")->fetch_object();
		
		if ($check) $data = array("status" => "error", "desc" => "Пользователь с указанным логином уже создан!");
		else {
			
			$mysqli->query("INSERT INTO users (`user_login`, `user_password`, `user_token`, `user_token_created`) VALUES ('".$login."', '".md5($password)."', NULL, NULL)");
			printf ("ID новой записи: %d.\n", $mysqli->insert_id);
			
			$data = array("status" => "success", "desc" => "Пользователь успешно сознад!");
		}
		
		$result = json_encode($data);  
		
		return $result;
	}
	if ($_GET['command'] == 'user_auth')
	{
		$login = $mysqli->real_escape_string($_GET['login']);
		$password = $mysqli->real_escape_string($_GET['password']);
		
		$check = $mysqli->query("SELECT * FROM users WHERE user_login='".$login."'")->fetch_object();
		
		if ($check)
		{
			if (md5($password) == $check->user_password)
			{
				$diff = $Obj->date_diff(date('Y-m-d H:i:s'), $check->user_token_created);
				
				if ($diff > 15) {
					$token = $mysqli->real_escape_string(md5($check->user_id.'-'.date('Y-m-d H:i:s')));
					$query = "UPDATE users SET user_token='".$token."', user_token_created='".date('Y-m-d H:i:s')."' WHERE user_id=".$check->user_id;
					$mysqli->query($query);
				}
				else $token = $check->user_token;
		
				$data = array("status" => "success", "token" => $token);
			}
			else {
				$data = array("status" => "error", "desc" => "Ошибка авторизации! Не верный пароль.");
			}
		}
		else {
			$data = array("status" => "error", "desc" => "Ошибка авторизации! Пользователь не найден.");
		}
		
		$result = json_encode($data);  
			
		return $result;
		
	}
	
	/* Рецепты */
	if ($_GET['command'] == 'recipes_create')
	{
		$token = $mysqli->real_escape_string($_GET['token']);
		$title = $mysqli->real_escape_string($_GET['title']);
		$desc = $mysqli->real_escape_string($_GET['desc']);
		
		$check = $mysqli->query("SELECT * FROM users WHERE user_token='".$token."'")->fetch_object();
		
		$diff = $Obj->date_diff(date('Y-m-d H:i:s'), $check->user_token_created);
		
		if ($check && $diff < 15) {
			$mysqli->query("INSERT INTO recipes (`recipes_author_id` ,`recipes_title`, `recipes_desc`) VALUES (".$check->user_id.", '".$title."', '".$desc."')");
			
			$data = array("status" => "success", "desc" => 'Рецепт успешно создан!');
		}
		else $data = array("status" => "error", "desc" => 'Token не найден или устарел, авторизуйтесь заного!');
	
		$result = json_encode($data);  
			
		return $result;
	}
	
	if ($_GET['command'] == 'recipes_edit')
	{
		$token = $mysqli->real_escape_string($_GET['token']);
		$title = $mysqli->real_escape_string($_GET['title']);
		$desc = $mysqli->real_escape_string($_GET['desc']);
		$id = $mysqli->real_escape_string($_GET['id']);

		$check = $mysqli->query("SELECT * FROM users WHERE user_token='".$token."'")->fetch_object();
		
		$diff = $Obj->date_diff(date('Y-m-d H:i:s'), $check->user_token_created);
		
		if ($check && $diff < 15) {
			
			$mysqli->query("UPDATE recipes SET recipes_title='".$title."', recipes_desc='".$desc."' WHERE recipes_id=".$id);
			$data = array("status" => "success", "desc" => 'Рецепт успешно изменён!');
		}
		else $data = array("status" => "error", "desc" => 'Token не найден или устарел, авторизуйтесь заного!');
		
		$result = json_encode($data);  
			
		return $result;
	}
	if ($_GET['command'] == 'recipes_delete')
	{
		$token = $mysqli->real_escape_string($_GET['token']);
		$id = $mysqli->real_escape_string($_GET['id']);

		$check = $mysqli->query("SELECT * FROM users WHERE user_token='".$token."'")->fetch_object();
		
		$diff = $Obj->date_diff(date('Y-m-d H:i:s'), $check->user_token_created);
		
		if ($check && $diff < 15) {
			$mysqli->query("DELETE FROM recipes WHERE recipes_id=".$id);
			
			$data = array("status" => "success", "desc" => 'Рецепт успешно удалён!');
		}
		else $data = array("status" => "error", "desc" => 'Token не найден или устарел, авторизуйтесь заного!');
	
		$result = json_encode($data);  
			
		return $result;
	}
}
else {
	header("HTTP/1.1 403 Forbidden" ); 
	exit();
}

$mysqli->close();