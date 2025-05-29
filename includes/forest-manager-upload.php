<?php

// Название <input type="file"> 
$input_name = 'file';
 
// Разрешенные расширения файлов.
$allow = array('pdf'); 
 
// Запрещенные расширения файлов.
$deny = array(
	'phtml', 'php', 'php3', 'php4', 'php5', 'php6', 'php7', 'phps', 'cgi', 'pl', 'asp', 
	'aspx', 'shtml', 'shtm', 'htaccess', 'htpasswd', 'ini', 'log', 'sh', 'js', 'html', 
	'htm', 'css', 'sql', 'spl', 'scgi', 'fcgi', 'exe'
);
 
// Директория куда будут загружаться файлы.
$path = $_SERVER['DOCUMENT_ROOT'] . '/wp-content/uploads/';

file_put_contents('api-log.txt',var_export($path,true).PHP_EOL,FILE_APPEND); 
file_put_contents('api-log.txt',var_export($path1,true).PHP_EOL,FILE_APPEND); 
 
$success = false;
if (!isset($_FILES[$input_name])) {
	$message = 'Файл не загружен.';
} else {
	$file = $_FILES[$input_name];
 
	// Проверим на ошибки загрузки.
	if (!empty($file['error']) || empty($file['tmp_name'])) {
		$message = 'Не удалось загрузить файл.';
	} elseif ($file['tmp_name'] == 'none' || !is_uploaded_file($file['tmp_name'])) {
		$message = 'Не удалось загрузить файл.';
	} else {
		// Оставляем в имени файла только буквы, цифры и некоторые символы.
		$pattern = "[^a-zа-яё0-9,~!@#%^-_\$\?\(\)\{\}\[\]\.]";
		$name = mb_eregi_replace($pattern, '-', $file['name']);
		$name = mb_ereg_replace('[-]+', '-', $name);
		$parts = pathinfo($name);
 
		if (empty($name) || empty($parts['extension'])) {
			$message = 'Недопустимый тип файла';
		} elseif (!empty($allow) && !in_array(strtolower($parts['extension']), $allow)) {
			$message = 'Недопустимый тип файла';
		} elseif (!empty($deny) && in_array(strtolower($parts['extension']), $deny)) {
			$message = 'Недопустимый тип файла';
		} else {
			// Перемещаем файл в директорию.
			if (move_uploaded_file($file['tmp_name'], $path . $name)) {
				// Далее можно сохранить название файла в БД и т.п.
				$message = 'Файл «' . $name . '» успешно загружен.';
				$success = true;
			} else {
				$message = 'Не удалось загрузить файл.';
			}
		}
	}
}

 
$data = array(
	'success' => $success,
	'message' => $message,
	'name'    => $name,
);
 
header('Content-Type: application/json');
echo json_encode($data, JSON_UNESCAPED_UNICODE);
exit();