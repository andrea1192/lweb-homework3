<?php
	require_once("model-db.php");
	require_once("model-xml.php");

	function create_database() {
		global $settings;
		$db_host = $settings['db_host'];
		$db_user = $settings['db_user'];
		$db_pass = $settings['db_pass'];
		$db_name = $settings['db_name'];

		$connection = new mysqli($db_host, $db_user, $db_pass);
		$sql = "CREATE DATABASE IF NOT EXISTS {$db_name}";
		$result = $connection->query($sql);
}

	function create_tables() { //model/setup
		$connection = connect();

		$sql = <<<'END'
		CREATE TABLE Users (
		user 		VARCHAR(160)	PRIMARY KEY,
		pass 	 	VARCHAR(255)	NOT NULL);
		END;
		$connection->query($sql);
}

	function create_user($username, $password) { //model/setup
		$connection = connect();

		$user['user'] = $username;
		$user['pass'] = password_hash($password, PASSWORD_DEFAULT);

		$sql = <<<END
		INSERT INTO Users VALUES
		('{$user['user']}', '{$user['pass']}');
		END;

		$connection->query($sql);
	}

	function install($username, $password, $validation) { //setup
		global $list;
		global $settings;

		try {
			set_validation($validation);
			create_database();
			create_tables();
			create_user($username, $password);
			copy_DTDs();
			insert_list($list);

		} catch (mysqli_sql_exception $e) {
			$errors = true;
			log_error_db($e);
		} 

		if (!isset($errors)) {
			$msg = <<<END
			<p>Database "{$settings['db_name']}" inizializzato.</p>
			<p><a href="display.php">Vai al sito &gt;&gt;&gt;</a></p>
			END;

			msg_success($msg);
		}
	}

	function restore_db() { //setup
		global $settings;

		try {
			$connection = connect(); //model

			$sql = "DROP TABLE IF EXISTS Users;";
			$connection->query($sql);

		} catch (mysqli_sql_exception $e) {
			$errors = true;
			log_error_db($e);
		} 

		if (!isset($errors)) {
			$msg = <<<END
			<p>Database "{$settings['db_name']}" ripristinato.</p>
			<p>Ora &egrave; possibile ripetere l'installazione.</p>
			END;

			msg_success($msg);
		}
	}

	function copy_DTDs() {

		if (!is_dir(DTD_DIR))
			mkdir(DTD_DIR);

		if ($files = scandir('dtd/')) {
			$files = array_diff($files, ['.','..']);

			foreach ($files as $file) {
				$path = "dtd/{$file}";

				if (!is_dir($path))
					copy($path, DTD_DIR.$file);
			}
		}
	}

	function insert_list($list) { //model/setup

		foreach ($list as $category => $articles) {

			foreach ($articles as $number => $article) {
				$art = array();

				$art['category'] = $category;
				$art['title'] = $article['title'];
				$art['text'] = get_content($article);

				insert_article($art);
			}
		}
	}

?>