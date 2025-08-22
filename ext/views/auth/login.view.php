
<?php use App\App;
	require viewPath('partials\templates\header.php'); ?>

<div class="page-wrapper">
	<?php require viewPath('partials\templates\banner.php'); ?>
		<main class="flex-grow-1 d-flex flex-column flex-md-row">
			<?php if( App::getService('auth')->guest() ): ?>

			<div class="centered-view">
				<div class="login-cont">

					<div class="aletho-header aletho-border-top pt-1 pb-1" id="login-header-cont">
						<h3 class="">Inloggen</h3>
					</div>

					<form class="aletho-modal-body needs-validation" id="login-form" method="post" action="/login" novalidate>

						<label class="aletho-labels extra-popin-style" for="login-name">Gebruikersnaam</label>
						<input class="aletho-inputs extra-popin-style" id="login-name" name="userName" placeholder="Gebruikersnaam" type="text" autocomplete="username" required>

						<label class="aletho-labels extra-popin-style" for="login-passw">Wachtwoord</label>
						<input
							class="aletho-inputs extra-popin-style" id="login-passw" name="userPw" placeholder="Wachtwoord" type="password"
							required
							autocomplete="current-password"
							minlength="8"
							pattern="(?=.*[A-Z])(?=.*[a-z])(?=.*\d).{8,}"
							title="Minimaal 8 tekens, met minstens één hoofdletter, één kleine letter en één cijfer">

						
						<?php if(isset($error) && !empty($error)) : ?>
						<div class="login-error mt-1">Melding:<p class="login-error-text"><?=$error?></p></div>
						<?php else: ?>
						<div class="login-error mt-2 mb-2"></div>
						<?php endif; ?>
						
						<input class="aletho-buttons extra-popin-style mt-1 mb-2" id="login-submit" type="submit" value="Inloggen">
					</form>
					
				</div>
			</div>
			<?php endif;?>

		</main>

	<?php require viewPath('partials\templates\footer.php'); ?>

</div>