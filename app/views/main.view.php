<div class="page-wrapper">
	<?php
		require viewPath('partials\templates\header.php');
		require viewPath('partials\templates\banner.php');
	?>
		<main class="flex-grow-1 d-flex flex-column flex-md-row">
			<?php if(! isset($_SESSION['user']) ): ?>
				<?php require viewPath('auth\login.view.php'); ?>
			<?php else: ?>
				<?php require viewPath('auth\user.view.php'); ?>
			<?php endif; ?>
		</main>

	<?php require viewPath('partials\templates\footer.php'); ?>
</div>