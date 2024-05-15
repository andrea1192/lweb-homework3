<?php
	require_once("view.php");

	function edit_content() {
		global $current;

		$article = get_article($current);

		$edit_UI = <<<END
		<h1><input name="title" value="{$article['title']}" /></h1>

		<textarea name="text" spellcheck="false">{$article['text']}</textarea>

		<input type="submit" value="Salva" />
		END;

		print($edit_UI);
	}

	check_actions($current);

	if (!get_authorization()) {
		header('Location:'.rewrite_URL('login.php', action:'access_denied'));

		exit();
	}
?>

<?= generate_prolog() ?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml">

	<head>
		<title>Linguaggi per il Web</title>

		<link rel="stylesheet" href="css/style.css" type="text/css" />
		<link rel="stylesheet" href="css/edit.css" type="text/css" />
	</head>

	<body>
		<a name="top"></a>
		<div id="header">
			<?php generate_header() ?>
		</div>
		<div id="wrapper" class="centered">
			<div id="menu">
				<div class="sticky">
					<?php generate_menu() ?>
				</div>
			</div>

			<div id="main">
				<form action="<?= rewrite_URL($referrer, view:'display.php', action:'edit') ?>" method="post">
					<?php
						if (!empty($message)) {

							generate_message();
						}

						edit_content();
					?>
				</form>
			</div>
		</div>
		<div id="footer">
			<div class="centered">
				<div>Andrea Ippoliti - matricola 1496769</div>
				<div><a href="#top">Torna su</a></div>
			</div>
		</div>
	</body>
	
</html>
