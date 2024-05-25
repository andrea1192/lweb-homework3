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
		CREATE TABLE Categories (
		name 		VARCHAR(160)	PRIMARY KEY,
		position 	INT UNSIGNED 	UNIQUE);
		END;
		$connection->query($sql);

		$sql = <<<'END'
		CREATE TABLE Pages (
		name  		VARCHAR(160) 	PRIMARY KEY,
		position 	INT UNSIGNED 	NOT NULL,
		category 	VARCHAR(160) 	NOT NULL,
		title 		VARCHAR(160)	NOT NULL,
		text 		TEXT,
		UNIQUE(position, category),
		FOREIGN KEY(category) REFERENCES Categories(name));
		END;
		$connection->query($sql);

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

	function install($username, $password) { //setup
		global $list;
		global $settings;

		try {
			create_database();
			create_tables();
			create_user($username, $password);
			insert_categories($list);
			insert_list_xml($list);

		} catch (mysqli_sql_exception $e) {
			$errors = true;
			log_error($e);
		} 

		if (!isset($errors)) {
			$msg = <<<END
			<p>Database "{$settings['db_name']}" inizializzato.</p>
			<p><a href="display.php">Vai al sito &gt;&gt;&gt;</a></p>
			END;

			msg_success($msg);
		}
	}

	function restore() { //setup
		global $settings;

		try {
			$connection = connect(); //model

			$sql = "DROP TABLE IF EXISTS Pages, Categories, Users;";
			$connection->query($sql);

		} catch (mysqli_sql_exception $e) {
			$errors = true;
			log_error($e);
		} 

		if (!isset($errors)) {
			$msg = <<<END
			<p>Database "{$settings['db_name']}" ripristinato.</p>
			<p>Ora &egrave; possibile ripetere l'installazione.</p>
			END;

			msg_success($msg);
		}
	}

	function insert_list($list) { //model/setup

		foreach ($list as $category => $articles) {

			foreach ($articles as $number => $article) {
				$row = scan_article($article['path']); //utils

				$row['position'] = $number+1;
				$row['category'] = $category;
				$row['title'] = $article['title'];

				insert_article($row); //model
			}
		}
	}

	function insert_list_xml($list) { //model/setup

		foreach ($list as $category => $articles) {

			foreach ($articles as $number => $article) {
				$art = array();

				$art['category'] = $category;
				$art['title'] = $article['title'];
				$art['text'] = get_content_xml($article);

				insert_article_xml($art);
			}
		}
	}

?>