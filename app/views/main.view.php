<?php
	require viewPath('partials\templates\header.php');
	require viewPath('partials\templates\banner.php');
?>
	<main class="flex-fill">
		<?php if(! isset($user) ): ?>
			<?php require viewPath('auth\login.view.php'); ?>
		<?php else: ?>
			<?php require viewPath('auth\user.view.php'); ?>
		<?php endif; ?>
	</main>

<?php require viewPath('partials\templates\footer.php'); ?>