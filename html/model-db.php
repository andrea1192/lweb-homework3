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
		$tables = ['Users'];

		foreach ($tables as $table) {
			$sql = "SHOW TABLES WHERE Tables_in_{$settings['db_name']} = '{$table}'";
			$result = $connection->query($sql);

			if ($result->num_rows != 1) return false;
		}

		return true;
	}
?>