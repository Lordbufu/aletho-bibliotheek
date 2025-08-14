<?php require viewPath('partials\templates\header.php'); ?>

<div class="page-wrapper">

	<?php require viewPath('partials\templates\banner.php'); ?>

		<main class="flex-grow-1 d-flex flex-column flex-md-row">

			<?php if(! isset($_SESSION['user']) ) {
				require viewPath('auth\login.view.php');
			} else {
				require viewPath('auth\user.view.php');
			} ?>

		</main>

	<?php require viewPath('partials\templates\footer.php'); ?>

</div>