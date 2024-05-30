<?php
	require_once("session.php");
	require_once("utils.php");
	require_once("model-db.php");
	require_once("model-xml.php");
	require_once("view.php");

	function check_database() {
		try {
			if (!database_exists() || !tables_exist() || !articles_exist()) throw new Exception();

		} catch (Exception $e) {
			header('Location:'.rewrite_URL('install.php', action:'db_issues', encode: false));
		}
	}

	function check_actions($current) { //controller

		try {
			get_article($current);

		} catch (Exception $e) {
			msg_failure("Articolo non trovato: \"{$current}\"");
			return;
		}

		if (!isset($_GET['action']))
			return;

		switch ($_GET['action']) {

			case 'login':
				if (isset($_POST['user']) && isset($_POST['pass'])) {

					authenticate_user($_POST['user'], $_POST['pass']); //model
				} break;

			case 'login_failed':
				msg_failure("Credenziali non corrette!"); break;

			case 'access_denied':
				msg_failure("Per questa azione devi essere loggato!"); break;

			case 'logout':
				msg_success("Logout avvenuto. A presto!"); break;

			case 'edit':
				if (isset($_POST['title']) && isset($_POST['text'])) {

					$article['name'] = $current;
					$article['title'] = $_POST['title'];
					$article['text'] = $_POST['text'];

					try {
						validate_content($article);

					} catch (Exception $e) {
						log_error_xml($e);
						return;
					}

					save_article($article);

					msg_success("Articolo aggiornato con successo.");
				} break;

			default: return;
		}
	}

	function msg_success($msg) { //view wrapper
		set_message($msg, true);
	}

	function msg_failure($msg) { //view wrapper
		set_message($msg, false);
	}

	function get_categories() { //dummy
		return ['none', 'XHTML', 'CSS'];
	}

	function get_articles($category) { //dummy
		$articles = array();

		switch ($category) {

			case 'none': $articles = [
					'home'
				]; break;

			case 'XHTML': $articles = [
					'elementi',
					'correttezza'
				]; break;

			case 'CSS': $articles = [
					'selettori',
					'box-model',
					'layout',
					'posizionamento'
				]; break;

			default: break;
		}

		return $articles;
	}

	function get_article($article) { //model wrapper
		$article = select_article($article);

		$article['title'] = htmlspecialchars($article['title'], ENT_QUOTES | ENT_XHTML);

		return $article;
	}

	function save_article($article) {
		update_article($article); //model
	}

?>