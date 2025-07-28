<!DOCTYPE html>
<html lang="en">
	<?php require 'partials\header.php'; ?>

	<!-- /* Body to set the main page style and size. */ -->
	<body class="container-fluid secundary-color-2 p-1 text-center">
		<?php													// Build page with requires, based on session based user data.
			require 'partials\banner.php';						// Always load the banner its is also based on user based session data.

			if(!isset($userType)) {								// Check user type,
				require 'partials\login.container.php';			// load login container if not set;
			} else {
				require 'partials\view.container.php';			// load view container if set.
			}

			require 'partials\footer.php';						// Always load the footer its also based on user session data.
		?>
	</body>
</html>