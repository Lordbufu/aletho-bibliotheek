		<!-- /* Header container, for the header menus and text. */ -->
		<nav class="container-xxl p-1 primary-color-1 border border-dark rounded d-flex flex-row justify-content-center align-items-center">

		<?php if(!isset($userType)) : ?>
			<image class="banner-image" alt="Alétho Logo" src="/images/huisstijl/Logo-bibliotheek-Wit-RGB.png">
			<!-- <h1 class="banner-header text-white titel-tekst">Alétho Bibliotheek App</h1> -->
		<?php else : ?>
			<div class="nav-row-cont row align-items-center">
				<div class="col-1 p-0 d-flex justify-content-start">
					<button class="hamburger-icon primary-color-1 fas fa-bars bg-sec-col border border-0" id="hamburger-button" type="button" data-bs-toggle="collapse" data-bs-target="#customHamburgerDropdown" aria-expanded="false" aria-controls="customHamburgerDropdown"></button>
				</div>

				<div class="col p-0 d-flex justify-content-center">
					<image class="banner-image" alt="Alétho Logo" src="/images/huisstijl/Logo-bibliotheek-Wit-RGB.png">
				</div>

				<div class="p-0 col-1 d-flex justify-content-end">
					<button class="search-icon primary-color-1 bg-sec-col border border-0" id="search-button" type="button" data-bs-toggle="collapse" data-bs-target="#customSearchDropdown" aria-expanded="false" aria-controls="customSearchDropdown">&#128269;</button>
				</div>
			</div>
		<?php endif; ?>
		
		</nav>