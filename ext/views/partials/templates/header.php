<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<meta name="author" content="Marco Visscher">
		<meta name="description" content="Een bibliotheek App voor Alétho.">
		<link rel="icon" type="image/x-icon" href="/images/favicon.ico">
		<title>Alétho Bibliotheek App</title>
		<link href="css/fontawesome.all.min.css" rel="stylesheet">
		<link href="css/bootstrap/bootstrap.min.css" rel="stylesheet">
		<link rel="stylesheet" href="https://use.typekit.net/rsa4jkk.css">
		<link href='https://fonts.googleapis.com/css?family=Quicksand' rel='stylesheet'>
		<link rel="stylesheet" type="text/css" href="css/style.css">
		<script src="js/jquery-3.7.1.min.js"></script>
		<script type="module" src="js/main.js"></script>
		<!-- PHP code for device specific styles etc goes here -->
	</head>

	<!-- Body to set the main page style and size -->
	<body class="aletho-background">
		<!-- user feedback container for the entire app -->
		<?php if (!empty($_SESSION['_flash'])): ?>
			<div class="aletho-border aletho-alert alert-global-<?= $_SESSION['_flash']['type'] ?>" role="alert">
				<?= htmlspecialchars($_SESSION['_flash']['message']) ?>
			</div>
			<?php unset($_SESSION['_flash']); ?>
		<?php endif; ?>