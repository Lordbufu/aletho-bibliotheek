<div class="centered-view view-container">
	<div class="login-cont">
		<div class="aletho-header aletho-border-top pt-1 pb-1" id="login-header-cont">
			<h3 class="">Inloggen</h3>
		</div>
		<form class="aletho-modal-body needs-validation" id="login-form" method="post" action="/login" novalidate>
			<label class="aletho-labels extra-popin-style" for="login-name">Gebruikersnaam</label>
			<input	class="aletho-inputs extra-popin-style"
					id="login-name"
					name="userName"
					placeholder="Gebruikersnaam"
					type="text"
					value="<?= htmlspecialchars($old['userName'] ?? '') ?>"
					autocomplete="username"
					required>
			<label class="aletho-labels extra-popin-style" for="login-passw">Wachtwoord</label>
			<input	class="aletho-inputs extra-popin-style mb-2"
					id="login-passw"
					name="userPw"
					placeholder="Wachtwoord"
					type="password"
					autocomplete="current-password"
					required>
			<?php if (!empty($errors)): ?>
				<div class="aletho-border aletho-alert-inline" role="alert">
					<?= htmlspecialchars($errors['credentials']) ?>
				</div>
			<?php endif; ?>
			<input class="aletho-buttons extra-popin-style mt-2 mb-2" id="login-submit" type="submit" value="Inloggen">
		</form>
	</div>
</div>