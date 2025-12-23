<?php require viewPath('partials/templates/header.php'); ?>

<main class="flex-grow-1 d-flex flex-column flex-md-row">
	<div class="page-wrapper">
		<?php
			require viewPath('partials/templates/banner.php');

			if ($_SESSION['user']['role'] !== 'Guest') {
				require viewPath('auth/user.php');
			} else {
				require viewPath('auth/login.php');
			}

			require viewPath('partials/templates/footer.php');
		?>
	</div>
</main>