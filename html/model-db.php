<?php
	require_once("connection.php");
	require_once("controller.php");

	$connection = null;

	function connect() {
		global $connection;
		global $settings;

		$db_host = $settings['db_host'];
		$db_user = $settings['db_user'];
		$db_pass = $settings['db_pass'];
		$db_name = $settings['db_name'];

		try {
			return $connection ?? new mysqli($db_host, $db_user, $db_pass, $db_name);

		} catch (mysqli_sql_exception $e) {
			log_error($e);
			throw $e;
		}
	}

	function insert_article($article) {
		$connection = connect();

		$article['title'] = $connection->real_escape_string($article['title']);
		$article['text'] = $connection->real_escape_string($article['text']);

		$sql = <<<END
		INSERT INTO Pages VALUES
		('{$article['name']}', 
			{$article['position']}, 
			'{$article['category']}', 
			'{$article['title']}', 
			'{$article['text']}');
		END;

		$connection->query($sql);
	}

	function update_article($article) {
		$connection = connect();

		$article['title'] = $connection->real_escape_string($article['title']);
		$article['text'] = $connection->real_escape_string($article['text']);

		$sql = <<<END
		UPDATE Pages
		SET title = '{$article['title']}', text = '{$article['text']}'
		WHERE name = '{$article['name']}';
		END;

		$connection->query($sql);
	}

	function insert_categories($list) {
		$connection = connect();
		$i = 1;

		foreach ($list as $category => $articles) {

			$sql = "INSERT INTO Categories VALUES ('{$category}', {$i});";
			$connection->query($sql);

			$i++;
		}
	}

	function select_categories() {
		$connection = connect();
		$sql = "SELECT name FROM Categories ORDER BY position;";
		$result = $connection->query($sql);

		$categories = [];

		while ($category = $result->fetch_column()) {
			$categories[] = $category;
		}

		return $categories;
	}

	function select_articles($category) {
		$connection = connect();
		$sql = "SELECT name,title FROM Pages WHERE category = '{$category}' ORDER BY position;";
		$result = $connection->query($sql);

		$articles = [];

		while ($article = $result->fetch_assoc()) {
			$articles[] = $article;
		}

		return $articles;
	}

	function select_article($article) {
		$connection = connect();
		$sql = "SELECT title,text FROM Pages WHERE name = '{$article}';";
		$result = $connection->query($sql);

		return $result->fetch_assoc();
	}

	function authenticate_user($username, $password) {
		$connection = connect();
		$sql = "SELECT user,pass FROM Users WHERE user = '{$username}';";
		$stored = $connection->query($sql)->fetch_assoc();

		if (isset($stored) && password_verify($password, $stored['pass'])) {

			$_SESSION['user'] = $username;

			msg_success("Login avvenuto con successo. Bentornato {$username}!");

		} else {
			header('Location:'.rewrite_URL('login.php', action:'login_failed', encode: false));

			exit();
		}
	}

	function log_error($e) {
		msg_failure(
			"Errore del database: {$e->getMessage()} ({$e->getFile()}:{$e->getLine()})");
	}

	function database_exists() {
		global $settings;
		$db_host = $settings['db_host'];
		$db_user = $settings['db_user'];
		$db_pass = $settings['db_pass'];
		$db_name = $settings['db_name'];

		$connection = new mysqli($db_host, $db_user, $db_pass);
		$sql = "SHOW DATABASES WHERE `Database` = '{$db_name}'";
		$result = $connection->query($sql);

		return $result->num_rows;
	}

	function tables_exist() {
		global $settings;
		$connection = connect();
		$tables = ['Pages', 'Categories', 'Users'];

		foreach ($tables as $table) {
			$sql = "SHOW TABLES WHERE Tables_in_{$settings['db_name']} = '{$table}'";
			$result = $connection->query($sql);

			if ($result->num_rows != 1) return false;
		}

		return true;
	}
?>