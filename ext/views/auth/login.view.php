<div class="page-wrapper">
	<?php require viewPath('partials\templates\banner.php'); ?>
		<main class="flex-grow-1 d-flex flex-column flex-md-row">
			<div class="centered-view">
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

						<?php if (!empty($_SESSION['_flashInline'])): ?>
							<div class="aletho-border aletho-inline-<?= $_SESSION['_flashInline']['type'] ?>" role="alert">
								<?= htmlspecialchars($_SESSION['_flashInline']['message']) ?>
							</div>
							<?php unset($_SESSION['_flashInline']); ?>
						<?php endif; ?>

						<input class="aletho-buttons extra-popin-style mt-2 mb-2" id="login-submit" type="submit" value="Inloggen">
					</form>

				</div>
			</div>
		</main>
	<?php require viewPath('partials\templates\footer.php'); ?>
</div>