<?php
	require_once("install-utils.php");

	$labels = [
		'db' => [
			'Host' => 'db_host',
			'Username' => 'db_user',
			'Password' => 'db_pass',
			'Nome' => 'db_name'],

		'user' => [
			'Username' => 'app_user',
			'Password' => 'app_pass']
	];

	$list = [
		'none' => [
			['path' => 'static/html/main.html', 'title' => 'Home']
		],

		'XHTML' => [
			['path' => 'static/html/elementi.html', 'title' => 'Elementi'],
			['path' => 'static/html/correttezza.html', 'title' => 'Correttezza']
		],

		'CSS' => [
			['path' => 'static/html/selettori.html', 'title' => 'Selettori'],
			['path' => 'static/html/box-model.html', 'title' => 'Box model'],
			['path' => 'static/html/layout.html', 'title' => 'Layout'],
			['path' => 'static/html/posizionamento.html', 'title' => 'Posizionamento']
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

	if (isset($_POST['action'])) {

		switch ($_POST['action']) {

			case 'Installa':
				$user = $_POST[$labels['user']['Username']];
				$pass = $_POST[$labels['user']['Password']];

				install($user, $pass);
				break;

			case 'Ripristina':
				restore();
				break;

			default: die ("Azione non valida.");
		}
	}

	if (isset($_GET['action']) && $_GET['action'] == 'db_issues') {

		try {
			if (!database_exists()) throw new Exception('Database non trovato');
			if (!tables_exist()) throw new Exception('Tabelle mancanti');

		} catch (Exception $e) {
			log_error($e);
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
			<div id="settings">
				<h1>Credenziali per il database</h1>
				<p>Estratte da connection.php</p>
				<?php generate_labels('db', false) ?>

				<h1>Utente predefinito</h1>
				<p>Per provare il login nell'applicazione</p>
				<?php generate_labels('user') ?>
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