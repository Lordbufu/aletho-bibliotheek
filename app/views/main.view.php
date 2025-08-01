<?php
	require viewPath('partials\templates\header.php');
	require viewPath('partials\templates\banner.php');

	if(!isset($user)) {
		require viewPath('auth\login.view.php');
	} else {
		require viewPath('auth\user.view.php');
	}

	require viewPath('partials\templates\footer.php');