<?php
	require viewPath('partials\templates\header.php');
	require viewPath('partials\templates\banner.php');
?>
	<main class="flex-fill">
		<?php if(! isset($user) ): ?>
		<div class="d-flex justify-content-center align-items-center text-center min-vh-100">
			<?php require viewPath('auth\login.view.php'); ?>
		</div>
		<?php else: ?>
			<?php require viewPath('auth\user.view.php'); ?>
		<?php endif; ?>
	</main>

<?php require viewPath('partials\templates\footer.php'); ?>