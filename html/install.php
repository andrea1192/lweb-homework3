<?php
	require_once("install-utils.php");

	$labels = [
		'db' => [
			'Host' => 'db_host',
			'Nome' => 'db_name',
			'Username' => 'db_user',
			'Password' => 'db_pass'],

		'user' => [
			'Username' => 'app_user',
			'Password' => 'app_pass']
	];

	$list = [
		'none' => [
			['path' => XHTML_DIR.'/main.html', 'title' => 'Home']
		],

		'XHTML' => [
			['path' => XHTML_DIR.'/elementi.html', 'title' => 'Elementi'],
			['path' => XHTML_DIR.'/correttezza.html', 'title' => 'Correttezza']
		],

		'CSS' => [
			['path' => XHTML_DIR.'/selettori.html', 'title' => 'Selettori'],
			['path' => XHTML_DIR.'/box-model.html', 'title' => 'Box model'],
			['path' => XHTML_DIR.'/layout.html', 'title' => 'Layout'],
			['path' => XHTML_DIR.'/posizionamento.html', 'title' => 'Posizionamento']
		]
	];

	function generate_labels($group, $enabled = true) {
		global $labels;
		global $settings;

		foreach ($labels[$group] as $label => $name) {
			$disable = $enabled ? '' : 'disabled="disabled"';
			$value = $_POST[$name] ?? $settings[$name];
			
			$html = <<<END
			<label>{$label}: 
				<input name="{$name}" value="{$value}" {$disable} />
			</label>
			END;

			print($html);
		}
	}

	function checked_XML($label) {
		$choice = $_POST['xml'] ?? 'DTD';
		$choice = strtolower($choice);
		$label = strtolower($label);

		return ($label == $choice) ? 'checked="checked"' : '';
	}

	if (isset($_POST['action'])) {

		switch ($_POST['action']) {

			case 'Installa':
				$user = $_POST[$labels['user']['Username']];
				$pass = $_POST[$labels['user']['Password']];
				$validation = $_POST['xml'];

				install($user, $pass, $validation);
				break;

			case 'Ripristina':
				restore_db();
				break;

			default: die ("Azione non valida.");
		}
	}

	if (isset($_GET['action']) && $_GET['action'] == 'db_issues') {

		try {
			if (!database_exists()) throw new Exception('Database non trovato');
			if (!tables_exist()) throw new Exception('Tabelle mancanti');
			if (!articles_exist()) throw new Exception('Articoli mancanti o non validi');

		} catch (Exception $e) {
			log_error_db($e);
		}
	}

?>
<?= generate_prolog() ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml">

	<head>
		<title>Install Script</title>

		<link rel="stylesheet" href="css/install.css" type="text/css" />
	</head>

	<body>
		<form action="<?= $_SERVER['PHP_SELF'] ?>" method="post">
			<div id="credentials">
				<h1>Credenziali per il database</h1>
				<p>Estratte da connection.php</p>
				<div><?php generate_labels('db', false) ?></div>

				<h1>Utente predefinito</h1>
				<p>Per provare il login nell'applicazione</p>
				<div><?php generate_labels('user') ?></div>
			</div>
			<div id="xml">
				<h1>Controllo XML:</h1>
				<label><input type="radio" name="xml" value="DTD" <?= checked_XML('DTD'); ?> />DTD</label>
				<label><input type="radio" name="xml" value="Schema" <?= checked_XML('Schema'); ?> />Schema</label>
			</div>
			<div id="controls">
				<input type="submit" name="action" value="Installa" />
				<input type="submit" name="action" value="Ripristina" />
			</div>
		</form>

		<?php
			if(isset($_REQUEST['action'])) {

				generate_message();
			}
		?>

	</body>

</html>