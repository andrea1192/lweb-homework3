<?php
	require_once("controller.php");

	define('DTD_DIR', 'static/xml/dtd/');
	define('XML_DIR', 'static/xml/');
	define('XHTML_DIR', 'static/html/');

	function insert_article_xml($article) {
		$article['name'] = generate_UID($article['title']);

		$imp = new DOMImplementation();
		$doctype = $imp->createDocumentType('article', '', DTD_DIR.'article.dtd');
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

		if ($document->validate())
			print("\n<p>{$article['name']}.xml valido secondo {$document->doctype->systemId}.</p>");

		$xml = $document->save(XML_DIR."{$article['name']}.xml");
	}

	function select_article_xml($article) {
		$imp = new DOMImplementation();
		$doctype = $imp->createDocumentType('article', '', DTD_DIR.'article.dtd');
		$document = $imp->createDocument('', '', $doctype);
		$document->encoding = 'UTF-8';
		$document->resolveExternals = true;

		$document->load("test/{$article}.xml");

		if ($document->validate())
			print("\n<p>{$article}.xml valido secondo {$document->doctype->systemId}.</p>");

		$art = array();

		$art['name'] = $document->getElementsByTagName("article")->item(0)->getAttribute("name");
		$art['category'] = $document->getElementsByTagName("article")->item(0)->getAttribute("category");
		$art['title'] = $document->getElementsByTagName("title")->item(0)->nodeValue;
		$art['text'] = $document->getElementsByTagName("text")->item(0)->nodeValue;

		return $art;
	}
?>