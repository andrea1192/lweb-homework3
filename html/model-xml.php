<?php
	require_once("controller.php");

	define('DTD_DIR', 'static/xml/dtd/');
	define('XML_DIR', 'static/xml/');
	define('XHTML_DIR', 'static/html/');

	function insert_article($article) {
		$article['name'] = generate_UID($article['title']);

		$imp = new DOMImplementation();
		$doctype = $imp->createDocumentType('article', '', 'dtd/article.dtd');
		$document = $imp->createDocument('', '', $doctype);
		$document->encoding = 'UTF-8';
		$document->resolveExternals = true;
		$document->formatOutput = true;

		$art = $document->createElement("article");
		$document->appendChild($art);

		$name = $document->createAttribute("name");
		$category = $document->createAttribute("category");
		$title = $document->createElement("title");
		$text = $document->createElement("text");

		$name->value = $article['name'];
		$category->value = $article['category'];
		$title->nodeValue = $article['title'];

		$cdata = $document->createCDATASection($article['text']);

		$art->appendChild($name);
		$art->appendChild($category);
		$art->appendChild($title);
		$art->appendChild($text);
		$text->appendChild($cdata);

		if (!$document->validate())
			throw new Exception("{$article['name']}.xml non valido secondo {$document->doctype->systemId}");

		$xml = $document->save(XML_DIR."{$article['name']}.xml");
	}

	function select_article($article) {
		$document = new DOMDocument("1.0", "UTF-8");
		$document->resolveExternals = true;

		if (!file_exists(XML_DIR."{$article}.xml"))
			throw new Exception("{$article}.xml non trovato");

		$document->load(XML_DIR."{$article}.xml");

		if (!$document->validate())
			throw new Exception("{$article}.xml non valido secondo {$document->doctype->systemId}");

		$art = array();

		$art['name'] = $document->getElementsByTagName("article")->item(0)->getAttribute("name");
		$art['category'] = $document->getElementsByTagName("article")->item(0)->getAttribute("category");
		$art['title'] = $document->getElementsByTagName("title")->item(0)->nodeValue;
		$art['text'] = $document->getElementsByTagName("text")->item(0)->nodeValue;

		return $art;
	}

	function update_article($article) {
		$document = new DOMDocument("1.0", "UTF-8");
		$document->resolveExternals = true;

		if (!file_exists(XML_DIR."{$article['name']}.xml"))
			throw new Exception("{$article['name']}.xml non trovato");

		$document->load(XML_DIR."{$article['name']}.xml");

		if (!$document->validate())
			throw new Exception("{$article}.xml non valido secondo {$document->doctype->systemId}");

		$document->getElementsByTagName("title")->item(0)->firstChild->nodeValue = $article['title'];

		$cdata = $document->createCDATASection($article['text']);
		$text = $document->getElementsByTagName("text")->item(0);
		$text->replaceChild($cdata, $text->firstChild);

		$xml = $document->save(XML_DIR."{$article['name']}.xml");
	}

	function articles_exist() {
		try {

			foreach (get_categories() as $category) {
				foreach (get_articles($category) as $article) {

					get_article($article);
				}
			}
		
		} catch (Exception $e) {
			return false;
		}

		return true;
	}
?>