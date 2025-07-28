		<!-- /* Login container, to position everything in the center of the screen/view. */ -->
		<div class="login-cont container p-0 center">
			<!-- /* Login header container, so i can give the whole thing a nice looking border. */ -->
			<div class="primary-color-1 border border-dark rounded-top p-1" id="login-header-cont">
				<h3 class="text-white titel-tekst">Inloggen</h3>
			</div>

			<!-- /* Login form, the main body of the login window/pop-in. */ -->
			<form class="login-form secundary-color-1 border border-dark rounded-bottom border-top-0 p-1 needs-validation" id="login-form" novalidate>
				<!-- /* Name label, to give a label to the input if its already filled in. */ -->
				<label class="login-name-label form-label ondertitel-tekst" for="login-name">Gebruikersnaam
					<input class="login-name-inp text-center form-control tussenkoppen-tekst" id="login-name" name="userName" placeholder="Gebruikersnaam" type="text" required>
				</label>

				<!-- /* Password label, to give a label to the input if its already filled in. */ -->
				<label class="login-pw-label form-label ondertitel-tekst" for="login-passw">Wachtwoord
					<input class="login-pw-inp text-center form-control tussenkoppen-tekst" id="login-passw" name="userPw" placeholder="Wachtwoord" type="text" required>
				</label>
				
				<!-- /* Error label, to create a area for error during the login process (normaly empty unless triggered). */ -->
				<label class="login-error ondertitel-tekst">
					<?php if(isset($error)) : ?>
					Melding:
					<p class="error-text tussenkoppen-tekst"><?=$error?></p>
					<?php endif; ?>
				</label>

				<!-- /* The actual submit button, to send the input data to the server. */ -->
				<input class="login-butt buttons md-1 border border-dark rounded overige-tekst secundary-color-4" id="login-submit" type="submit" value="Inloggen">
			</form>
		</div>