<nav class="aletho-header banner-nav">
	<?php if( $_SESSION['user']['role'] === 'guest' ) : ?>
		<img class="banner-image" alt="Alétho Logo" src="/images/huisstijl/Logo-bibliotheek-Wit-RGB.png">
		<?php else : ?>
		<button	id="hamburger-button"
				class="aletho-menu-buttons fas fa-bars"
				type="button"
				data-bs-toggle="collapse"
				data-bs-target="#customHamburgerDropdown"
				aria-expanded="false"
				aria-controls="customHamburgerDropdown">
		</button>
			
		<img class="banner-image" alt="Alétho Logo" src="/images/huisstijl/Logo-bibliotheek-Wit-RGB.png">
	<?php endif; ?>
</nav>