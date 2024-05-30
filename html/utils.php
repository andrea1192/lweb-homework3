<?php
	require_once("controller.php");

	function rewrite_URL($URL, $view = null, $page = null, $action = null, $encode = true) { //utils
		$URL_path = parse_url($URL, PHP_URL_PATH);
		$URL_query = parse_url($URL, PHP_URL_QUERY);

		if (isset($view)) {
			$old_basename = basename($URL_path);
			$new_basename = $view;
			$URL_path = str_replace($old_basename, $new_basename, $URL_path);
		}

		if (isset($page) || isset($action)) {

			if (isset($URL_query)) {
				parse_str($URL_query, $args);
			} else {
				$args = [];
			}

			if (isset($page)) $args['page'] = $page;
			if (isset($action)) $args['action'] = $action;

			$URL_query = http_build_query($args);
		}

		if (!$encode)
			return "{$URL_path}?{$URL_query}";

		return htmlspecialchars("{$URL_path}?{$URL_query}", ENT_XHTML);
	}

	function generate_UID($title) { //utils
		$pattern = '/[[:^alnum:]]+/';

		$UID = preg_replace($pattern, '-', $title);
		$UID = strtolower($UID);

		return $UID;
	}

	function generate_XHTML($content) {
		$html = <<<END
		<?xml version="1.0" encoding="UTF-8"?>
		<!DOCTYPE html SYSTEM "dtd/xhtml1-strict.dtd">

		<html xmlns="http://www.w3.org/1999/xhtml">

			<head>
				<title>Article</title>
			</head>

			<body>
				{$content}
			</body>
			
		</html>
		END;

		$html = preg_replace("/^[[:blank:]]*/m", '', $html);
		$html = preg_replace("/^\R/m", '', $html);
		$html = preg_replace("/>\R/", '>', $html);

		return $html;
	}

	function get_content($article) {
		$source = new DOMDocument("1.0", "UTF-8");
		$source->resolveExternals = true;
		$source->formatOutput = true;

		$content = file_get_contents($article['path']);

		if (!@$source->loadXML(generate_XHTML($content)))
			throw new Exception("{$article['path']} non è well-formed.");

		if (!@$source->validate()) {
			throw new Exception("{$article['path']} non è valido.");
		}

		$title = $source->getElementsByTagName("h1")->item(0);
		$body = $source->getElementsByTagName("body")->item(0);
		$body->removeChild($title);

		$content = "\n";

		foreach ($body->childNodes as $node) {

			if ($node->nodeType == XML_ELEMENT_NODE) {

				$content .= $source->saveXML($node)."\n\n";
			}
		}

		return $content;
	}

	function validate_content($article) {
		$source = new DOMDocument("1.0", "UTF-8");
		$source->resolveExternals = true;
		$source->formatOutput = true;

		$content = $article['text'];

		if (!@$source->loadXML(generate_XHTML($content)))
			throw new Exception("Il codice inserito non è well-formed.");

		if (!@$source->validate()) {
			throw new Exception("Il codice inserito non è valido.");
		}

		return true;
	}

?>