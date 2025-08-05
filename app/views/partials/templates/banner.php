<!-- /* Header container, for the header menus and text. */ -->
<nav class="aletho-header container-xxl p-1 align-items-center banner-nav">
	<?php if(!isset($user)) : ?>
	<image class="banner-image" alt="Alétho Logo" src="/images/huisstijl/Logo-bibliotheek-Wit-RGB.png">
	<?php else : ?>
    <button id="hamburger-button" class="aletho-menu-buttons fas fa-bars" type="button"
    	data-bs-toggle="collapse" data-bs-target="#customHamburgerDropdown"
    	aria-expanded="false" aria-controls="customHamburgerDropdown"
	></button>
		
	<img class="banner-image" alt="Alétho Logo" src="/images/huisstijl/Logo-bibliotheek-Wit-RGB.png">

    <button id="search-button" class="aletho-menu-buttons" type="button"
    	data-bs-toggle="collapse" data-bs-target="#customSearchDropdown"
    	aria-expanded="false" aria-controls="customSearchDropdown"
	>&#128269;</button>
	<?php endif; ?>
</nav>