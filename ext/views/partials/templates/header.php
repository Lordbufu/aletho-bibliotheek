<?php
    $old = $_SESSION['_flashForm']['message'] ?? [];
    unset($_SESSION['_flashForm']);

	// Store the correct inline error formats, for book details.
    if (!empty($_SESSION['_flashInline']) && !is_array($_SESSION['_flashInline'])) {
        $errors[$_SESSION['_flashInline']['type']] = $_SESSION['_flashInline']['message'];
    } elseif (!empty($_SESSION['_flashInline']) && is_array($_SESSION['_flashInline'])) {
		foreach($_SESSION['_flashInline']['type'] as $key => $value) {
			$errors[$value] = $_SESSION['_flashInline']['message'][$key];
		}
	}
	unset($_SESSION['_flashInline']);

	// Store the correct inline error formats, for popins.
    if (!empty($_SESSION['_flashInlinePop']) && !is_array($_SESSION['_flashInlinePop'])) {
        $popErrors[$_SESSION['_flashInlinePop']['type']] = $_SESSION['_flashInlinePop']['message'];
    } elseif (!empty($_SESSION['_flashInlinePop']) && is_array($_SESSION['_flashInlinePop'])) {
		foreach($_SESSION['_flashInlinePop']['type'] as $key => $value) {
			$popErrors[$value] = $_SESSION['_flashInlinePop']['message'][$key];
		}
	}
	unset($_SESSION['_flashInlinePop']);
?>
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
		<?php if (!empty($_SESSION['_flashGlobal'])): ?>
			<div class="aletho-border aletho-alert-global aletho-global-<?= $_SESSION['_flashGlobal']['type'] ?>" role="alert">
				<?= htmlspecialchars($_SESSION['_flashGlobal']['message']) ?>
			</div>
			<?php unset($_SESSION['_flashGlobal']); ?>
		<?php endif; ?>