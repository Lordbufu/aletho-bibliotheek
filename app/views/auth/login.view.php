<div class="d-flex flex-column justify-content-center align-items-center text-center min-vh-100">
	<div class="login-cont container p-0">

		<!-- Header -->
		<div class="aletho-header aletho-border-top pt-1 pb-1" id="login-header-cont">
			<h3 class="">Inloggen</h3>
		</div>

		<!-- Login form -->
		<form class="aletho-modal-body needs-validation" id="login-form" novalidate>

			<!-- Name label -->
			<label class="aletho-labels extra-popin-style" for="login-name">Gebruikersnaam</label>
			<input class="aletho-inputs extra-popin-style" id="login-name" name="userName" placeholder="Gebruikersnaam" type="text" required>

			<!-- Password label -->
			<label class="aletho-labels extra-popin-style" for="login-passw">Wachtwoord</label>
			<input class="aletho-inputs extra-popin-style" id="login-passw" name="userPw" placeholder="Wachtwoord" type="text" required>
			
			<!-- Error label -->
			<?php if(isset($error) && !empty($error)) : ?>
			<div class="login-error mt-1">Melding:<p class="login-error-text"><?=$error?></p></div>
			<?php else: ?>
			<div class="login-error mt-2 mb-2"></div>
			<?php endif; ?>
			

			<!-- Submit -->
			<input class="aletho-buttons extra-popin-style" id="login-submit" type="submit" value="Inloggen">
		</form>
	</div>
</div>