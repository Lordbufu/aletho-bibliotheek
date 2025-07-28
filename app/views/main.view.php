<?php
	require 'partials\templates\header.php';
	require 'partials\templates\banner.php';

	if(!isset($userType)) {
		require 'auth\login.view.php';
	} else {
		require 'user.view.php';
	}

	require 'partials\templates\footer.php';