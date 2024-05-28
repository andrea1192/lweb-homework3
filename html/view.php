<?php
	require_once("controller.php");

	define('DEFAULT_VIEW', 'display.php');
	define('DEFAULT_CONTENT', 'home');

	header('Content-Type: application/xhtml+xml');

	$current = $_GET['page'] ?? DEFAULT_CONTENT;

	$message = '';
	$success = true;

	function generate_prolog() {
		return "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
	}

	function generate_header() {
		$home = generate_link();
		$login = '<a id="login" href="login.php">Accedi</a>';
		$logout = '<a id="login" href="logout.php">Esci</a>';
		$logged_in = get_authorization() ? $logout : $login;

		$header = <<<END
		<div class="centered">
			<a id="title" {$home}>Linguaggi per il Web</a>
			{$logged_in}
		</div>
		END;

		print($header);
	}

	function generate_footer() {
		$footer = <<<END
		<div class="centered">
			<div>Andrea Ippoliti - matricola 1496769</div>
			<div><a href="#top">Torna su</a></div>
		</div>
		END;

		print($footer);
	}

	function generate_menu() {

		foreach (get_categories() as $category) {
			if ($category != 'none') {
				print("<h1>{$category}</h1>\n");
			}

			print("<ul>");

			foreach (get_articles($category) as $article) {
				$href = generate_link(page:$article);
				$title = get_article($article)['title'];
				print("<li><a {$href}>{$title}</a></li>\n");
			}

			print("</ul>");
		}
	}

	function generate_link($view = DEFAULT_VIEW, $page = DEFAULT_CONTENT) {
		global $current;

		$href = rewrite_URL($view, page:$page);

		$link = "href=\"{$href}\"";

		if ($page == $current) {
			$link .= " class=\"active\"";
		}

		return $link;
	}

	function generate_message() {
		global $message;
		global $success;
		$class = $success ? 'success' : 'failure';

		$msg = <<<END
		<div class="mbox {$class}">
			{$message}
		</div>
		END;

		print($msg);
	}

	function set_message($msg, $sx) {
		global $message;
		global $success;
		$message = $msg;
		$success = $sx;
	}
?>