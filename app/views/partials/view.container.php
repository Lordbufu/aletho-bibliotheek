		<!-- /* View content container, to style/position the main content. */ -->
		<div class="view-container container-fluid" id="view-container">

			<!-- /* Search dropdown menu, and the associated content. */ -->
			<div class="secundary-color-1 collapse text-center p-1 pt-2 pb-2 bg-sec-col border border-top-0 border-dark rounded-bottom" id="customSearchDropdown">

				<div class="search-container row m-0 p-0" id="search-cont">

					<label class="search-option-label mb-1" for="search-options">Boek zoeken:</label>

					<form class="needs-validation row p-0 m-0" id="search-form" novalidate>

						<select class="search-options text-center col" name="search-method" id="search-options">
							<option name="title">Titel</option>
							<option name="schrijver">Schrijver</option>
							<option name="categorie">Categorie</option>
						</select>

						<input class="search-inp rounded ms-1" id="search-inp" type="text" name="searchInp" placeholder="Zoek op titel ..." required>

					</form>

				</div>

			</div>

			<!-- /* Hamburger dropdown menu, and the associated user based content. */ -->
			<div class="secundary-color-1 collapse text-center p-1 pt-2 pb-2 bg-sec-col border border-top-0 border-dark rounded-bottom" id="customHamburgerDropdown">

				<div class="hamburger-container row m-0 p-0" id="hamb-cont">
					<input class="secundary-color-4 buttons text-black rounded-2 p-1 text-center" id="boek-toev-button" type="submit" value="Boek Toevoegen">
					<input class="secundary-color-4 buttons text-black rounded-2 p-1 text-center" id="periode-wijz-button" type="submit" value="Periode Wijzigen">
					<input class="secundary-color-4 buttons text-black rounded-2 p-1 text-center" id="wachtwoord-wijz-button" type="submit" value="Wachtwoord Wijzigen">
					<input class="secundary-color-4 buttons text-black rounded-2 p-1 text-center" id="logoff-button" type="submit" value="Uitloggen">
				</div>

			</div>

            <!-- /* Item container example, with a dropdown for potetially editable details and user based content. */ -->
            <div class="item-container container-sm p-1" id="item-container-1">

                <!-- Default item info -->
                <div class="secundary-color-1 item-name-label d-flex flex-row border border-dark mn-main-col">
                    <button class="secundary-color-4 item-button buttons d-flex justify-content-start mn-main-col border-top-0 border-bottom-0 border-start-0" id="itemButton-1" type="button" data-bs-toggle="collapse" data-bs-target="#customItemDropdown-1" aria-expanded="false" aria-controls="customItemDropdown-1">▼</button>
                    <input class="dropdown-item d-flex flex-fill mn-main-col text-center" value="Boek Naam 1" disabled>
					<span class="status-dot d-flex justify-content-end statusOne" id="status-dot-1"></span>
				</div>

                <!-- Detailed item info, based on user type -->
                <div class="item-details flex-column mn-main-col collapse" id="customItemDropdown-1">
					<!-- /* For Admin users, i need to create edit events that make the inputs editable.
						And ill likely need a form around the enitre item, with JS code that add the potentially changed boek name when submitted.
						For the user these can all be labels or something else that simply displays the text from the database. */ -->
					<div class="secundary-color-1 dropdown-container border border-top-0 border-dark">
                    	<input class="dropdown-item" value="Schrijver" disabled>
					</div>

					<div class="secundary-color-1 dropdown-container border border-top-0 border-dark">
						<select class="dropdown-item dropdown-select text-center">
							<option>&nbsp;&nbsp;&nbsp;&nbsp;Categorie</option>
						</select>
					</div>

					<div class="secundary-color-1 dropdown-container border border-top-0 border-dark">
						<select class="dropdown-item dropdown-select" disabled>
							<option>&nbsp;&nbsp;&nbsp;&nbsp;Vestiging</option>
						</select>
					</div>
					
					<div class="secundary-color-1 dropdown-container border border-top-0 border-dark">
						<input class="dropdown-item" value="Status" disabled>
					</div>
					
					<div class="secundary-color-1 dropdown-container border border-top-0 border-dark">
						<input class="dropdown-item" value="Status verlopen" disabled>
					</div>

					<!-- Admin additions: -->
					<div class="secundary-color-1 dropdown-container border border-top-0 border-dark">
						<input class="dropdown-item" value="Laatste lener" disabled>
					</div>

					<div class="secundary-color-1 border border-top-0 border-dark">
						<button class="secundary-color-4 buttons text-black rounded-2 m-1 text-center" id="boek-save-button" type="submit">Wijzigingen Opslaan</button>
					</div>

					<div class="secundary-color-1 border border-top-0 border-dark">
						<button class="secundary-color-4 buttons text-black rounded-2 m-1 text-center" id="boek-status-button" type="submit">Status Aanpassen</button>
					</div>

					<div class="secundary-color-1 border border-top-0 border-dark">
						<button class="secundary-color-4 buttons text-black rounded-2 m-1 text-center" id="boek-delete-button" type="submit">Boek Verwijderen</button>
					</div>
                </div>
            </div>

            <!-- /* Item container example, with a dropdown for potetially editable details and user based content. */ -->
            <div class="item-container container-sm p-1" id="item-container-2">

                <!-- Default item info -->
                <div class="secundary-color-1 item-name-label d-flex flex-row border border-dark mn-main-col">
                    <button class="secundary-color-4 item-button buttons d-flex justify-content-start mn-main-col border-top-0 border-bottom-0 border-start-0" id="itemButton-2" type="button" data-bs-toggle="collapse" data-bs-target="#customItemDropdown-2" aria-expanded="false" aria-controls="customItemDropdown-2">▼</button>
                    <input class="dropdown-item d-flex flex-fill mn-main-col text-center" value="Boek Naam 2" disabled>
					<span class="status-dot d-flex justify-content-end statusOne" id="status-dot-2"></span>
				</div>

                <!-- Detailed item info, based on user type -->
                <div class="item-details flex-column mn-main-col collapse" id="customItemDropdown-2">
					<!-- /* For Admin users, i need to create edit events that make the inputs editable.
						And ill likely need a form around the enitre item, with JS code that add the potentially changed boek name when submitted.
						For the user these can all be labels or something else that simply displays the text from the database. */ -->
					<div class="secundary-color-1 dropdown-container border border-top-0 border-dark">
                    	<input class="dropdown-item" value="Schrijver" disabled>
					</div>

					<div class="secundary-color-1 dropdown-container border border-top-0 border-dark">
						<select class="dropdown-item dropdown-select text-center">
							<option>&nbsp;&nbsp;&nbsp;&nbsp;Categorie</option>
						</select>
					</div>

					<div class="secundary-color-1 dropdown-container border border-top-0 border-dark">
						<select class="dropdown-item dropdown-select" disabled>
							<option>&nbsp;&nbsp;&nbsp;&nbsp;Vestiging</option>
						</select>
					</div>
					
					<div class="secundary-color-1 dropdown-container border border-top-0 border-dark">
						<input class="dropdown-item" value="Status" disabled>
					</div>
					
					<div class="secundary-color-1 dropdown-container border border-top-0 border-dark">
						<input class="dropdown-item" value="Status verlopen" disabled>
					</div>

					<!-- Admin additions: -->
					<div class="secundary-color-1 dropdown-container border border-top-0 border-dark">
						<input class="dropdown-item" value="Laatste lener" disabled>
					</div>

					<div class="secundary-color-1 border border-top-0 border-dark">
						<button class="secundary-color-4 buttons text-black rounded-2 m-1 text-center" id="boek-save-button" type="submit">Wijzigingen Opslaan</button>
					</div>

					<div class="secundary-color-1 border border-top-0 border-dark">
						<button class="secundary-color-4 buttons text-black rounded-2 m-1 text-center" id="boek-status-button" type="submit">Status Aanpassen</button>
					</div>

					<div class="secundary-color-1 border border-top-0 border-dark">
						<button class="secundary-color-4 buttons text-black rounded-2 m-1 text-center" id="boek-status-button" type="submit">Boek Verwijderen</button>
					</div>
                </div>
            </div>

			<!-- /* Item container example, with a dropdown for potetially editable details and user based content. */ -->
            <div class="item-container container-sm p-1" id="item-container-3">

                <!-- Default item info -->
                <div class="secundary-color-1 item-name-label d-flex flex-row border border-dark mn-main-col">
                    <button class="secundary-color-4 item-button buttons d-flex justify-content-start mn-main-col border-top-0 border-bottom-0 border-start-0" id="itemButton-3" type="button" data-bs-toggle="collapse" data-bs-target="#customItemDropdown-3" aria-expanded="false" aria-controls="customItemDropdown-3">▼</button>
                    <input class="dropdown-item d-flex flex-fill mn-main-col text-center" value="Boek Naam 3" disabled>
					<span class="status-dot d-flex justify-content-end statusOne" id="status-dot-3"></span>
				</div>

                <!-- Detailed item info, based on user type -->
                <div class="item-details flex-column mn-main-col collapse" id="customItemDropdown-3">
					<!-- /* For Admin users, i need to create edit events that make the inputs editable.
						And ill likely need a form around the enitre item, with JS code that add the potentially changed boek name when submitted.
						For the user these can all be labels or something else that simply displays the text from the database. */ -->
					<div class="secundary-color-1 dropdown-container border border-top-0 border-dark">
                    	<input class="dropdown-item" value="Schrijver" disabled>
					</div>

					<div class="secundary-color-1 dropdown-container border border-top-0 border-dark">
						<select class="dropdown-item dropdown-select text-center">
							<option>&nbsp;&nbsp;&nbsp;&nbsp;Categorie</option>
						</select>
					</div>

					<div class="secundary-color-1 dropdown-container border border-top-0 border-dark">
						<select class="dropdown-item dropdown-select" disabled>
							<option>&nbsp;&nbsp;&nbsp;&nbsp;Vestiging</option>
						</select>
					</div>
					
					<div class="secundary-color-1 dropdown-container border border-top-0 border-dark">
						<input class="dropdown-item" value="Status" disabled>
					</div>
					
					<div class="secundary-color-1 dropdown-container border border-top-0 border-dark">
						<input class="dropdown-item" value="Status verlopen" disabled>
					</div>

					<!-- Admin additions: -->
					<div class="secundary-color-1 dropdown-container border border-top-0 border-dark">
						<input class="dropdown-item" value="Laatste lener" disabled>
					</div>

					<div class="secundary-color-1 border border-top-0 border-dark">
						<button class="secundary-color-4 buttons text-black rounded-2 m-1 text-center" id="boek-save-button" type="submit">Wijzigingen Opslaan</button>
					</div>

					<div class="secundary-color-1 border border-top-0 border-dark">
						<button class="secundary-color-4 buttons text-black rounded-2 m-1 text-center" id="boek-status-button" type="submit">Status Aanpassen</button>
					</div>

					<div class="secundary-color-1 border border-top-0 border-dark">
						<button class="secundary-color-4 buttons text-black rounded-2 m-1 text-center" id="boek-status-button" type="submit">Boek Verwijderen</button>
					</div>
                </div>
            </div>

			<!-- /* Item container example, with a dropdown for potetially editable details and user based content. */ -->
            <div class="item-container container-sm p-1" id="item-container-4">

                <!-- Default item info -->
                <div class="secundary-color-1 item-name-label d-flex flex-row border border-dark mn-main-col">
                    <button class="secundary-color-4 item-button buttons d-flex justify-content-start mn-main-col border-top-0 border-bottom-0 border-start-0" id="itemButton-4" type="button" data-bs-toggle="collapse" data-bs-target="#customItemDropdown-4" aria-expanded="false" aria-controls="customItemDropdown-4">▼</button>
                    <input class="dropdown-item d-flex flex-fill mn-main-col text-center" value="Boek Naam 4" disabled>
					<span class="status-dot d-flex justify-content-end statusOne" id="status-dot-4"></span>
				</div>

                <!-- Detailed item info, based on user type -->
                <div class="item-details flex-column mn-main-col collapse" id="customItemDropdown-4">
					<!-- /* For Admin users, i need to create edit events that make the inputs editable.
						And ill likely need a form around the enitre item, with JS code that add the potentially changed boek name when submitted.
						For the user these can all be labels or something else that simply displays the text from the database. */ -->
					<div class="secundary-color-1 dropdown-container border border-top-0 border-dark">
                    	<input class="dropdown-item" value="Schrijver" disabled>
					</div>

					<div class="secundary-color-1 dropdown-container border border-top-0 border-dark">
						<select class="dropdown-item dropdown-select text-center">
							<option>&nbsp;&nbsp;&nbsp;&nbsp;Categorie</option>
						</select>
					</div>

					<div class="secundary-color-1 dropdown-container border border-top-0 border-dark">
						<select class="dropdown-item dropdown-select" disabled>
							<option>&nbsp;&nbsp;&nbsp;&nbsp;Vestiging</option>
						</select>
					</div>
					
					<div class="secundary-color-1 dropdown-container border border-top-0 border-dark">
						<input class="dropdown-item" value="Status" disabled>
					</div>
					
					<div class="secundary-color-1 dropdown-container border border-top-0 border-dark">
						<input class="dropdown-item" value="Status verlopen" disabled>
					</div>

					<!-- Admin additions: -->
					<div class="secundary-color-1 dropdown-container border border-top-0 border-dark">
						<input class="dropdown-item" value="Laatste lener" disabled>
					</div>

					<div class="secundary-color-1 border border-top-0 border-dark">
						<button class="secundary-color-4 buttons text-black rounded-2 m-1 text-center" id="boek-save-button" type="submit">Wijzigingen Opslaan</button>
					</div>

					<div class="secundary-color-1 border border-top-0 border-dark">
						<button class="secundary-color-4 buttons text-black rounded-2 m-1 text-center" id="boek-status-button" type="submit">Status Aanpassen</button>
					</div>

					<div class="secundary-color-1 border border-top-0 border-dark">
						<button class="secundary-color-4 buttons text-black rounded-2 m-1 text-center" id="boek-status-button" type="submit">Boek Verwijderen</button>
					</div>
                </div>
            </div>

			<!-- /* Item container example, with a dropdown for potetially editable details and user based content. */ -->
            <div class="item-container container-sm p-1" id="item-container-5">

                <!-- Default item info -->
                <div class="secundary-color-1 item-name-label d-flex flex-row border border-dark mn-main-col">
                    <button class="secundary-color-4 item-button buttons d-flex justify-content-start mn-main-col border-top-0 border-bottom-0 border-start-0" id="itemButton-5" type="button" data-bs-toggle="collapse" data-bs-target="#customItemDropdown-5" aria-expanded="false" aria-controls="customItemDropdown-5">▼</button>
                    <input class="dropdown-item d-flex flex-fill mn-main-col text-center" value="Boek Naam 5" disabled>
					<span class="status-dot d-flex justify-content-end statusOne" id="status-dot-5"></span>
				</div>

                <!-- Detailed item info, based on user type -->
                <div class="item-details flex-column mn-main-col collapse" id="customItemDropdown-5">
					<!-- /* For Admin users, i need to create edit events that make the inputs editable.
						And ill likely need a form around the enitre item, with JS code that add the potentially changed boek name when submitted.
						For the user these can all be labels or something else that simply displays the text from the database. */ -->
					<div class="secundary-color-1 dropdown-container border border-top-0 border-dark">
                    	<input class="dropdown-item" value="Schrijver" disabled>
					</div>

					<div class="secundary-color-1 dropdown-container border border-top-0 border-dark">
						<select class="dropdown-item dropdown-select text-center">
							<option>&nbsp;&nbsp;&nbsp;&nbsp;Categorie</option>
						</select>
					</div>

					<div class="secundary-color-1 dropdown-container border border-top-0 border-dark">
						<select class="dropdown-item dropdown-select" disabled>
							<option>&nbsp;&nbsp;&nbsp;&nbsp;Vestiging</option>
						</select>
					</div>
					
					<div class="secundary-color-1 dropdown-container border border-top-0 border-dark">
						<input class="dropdown-item" value="Status" disabled>
					</div>
					
					<div class="secundary-color-1 dropdown-container border border-top-0 border-dark">
						<input class="dropdown-item" value="Status verlopen" disabled>
					</div>

					<!-- Admin additions: -->
					<div class="secundary-color-1 dropdown-container border border-top-0 border-dark">
						<input class="dropdown-item" value="Laatste lener" disabled>
					</div>

					<div class="secundary-color-1 border border-top-0 border-dark">
						<button class="secundary-color-4 buttons text-black rounded-2 m-1 text-center" id="boek-save-button" type="submit">Wijzigingen Opslaan</button>
					</div>

					<div class="secundary-color-1 border border-top-0 border-dark">
						<button class="secundary-color-4 buttons text-black rounded-2 m-1 text-center" id="boek-status-button" type="submit">Status Aanpassen</button>
					</div>

					<div class="secundary-color-1 border border-top-0 border-dark">
						<button class="secundary-color-4 buttons text-black rounded-2 m-1 text-center" id="boek-status-button" type="submit">Boek Verwijderen</button>
					</div>
                </div>
            </div>

			<!-- /* Item container example, with a dropdown for potetially editable details and user based content. */ -->
            <div class="item-container container-sm p-1" id="item-container-6">

                <!-- Default item info -->
                <div class="secundary-color-1 item-name-label d-flex flex-row border border-dark mn-main-col">
                    <button class="secundary-color-4 item-button buttons d-flex justify-content-start mn-main-col border-top-0 border-bottom-0 border-start-0" id="itemButton-6" type="button" data-bs-toggle="collapse" data-bs-target="#customItemDropdown-6" aria-expanded="false" aria-controls="customItemDropdown-6">▼</button>
                    <input class="dropdown-item d-flex flex-fill mn-main-col text-center" value="Boek Naam 6" disabled>
					<span class="status-dot d-flex justify-content-end statusOne" id="status-dot-6"></span>
				</div>

                <!-- Detailed item info, based on user type -->
                <div class="item-details flex-column mn-main-col collapse" id="customItemDropdown-6">
					<!-- /* For Admin users, i need to create edit events that make the inputs editable.
						And ill likely need a form around the enitre item, with JS code that add the potentially changed boek name when submitted.
						For the user these can all be labels or something else that simply displays the text from the database. */ -->
					<div class="secundary-color-1 dropdown-container border border-top-0 border-dark">
                    	<input class="dropdown-item" value="Schrijver" disabled>
					</div>

					<div class="secundary-color-1 dropdown-container border border-top-0 border-dark">
						<select class="dropdown-item dropdown-select text-center">
							<option>&nbsp;&nbsp;&nbsp;&nbsp;Categorie</option>
						</select>
					</div>

					<div class="secundary-color-1 dropdown-container border border-top-0 border-dark">
						<select class="dropdown-item dropdown-select" disabled>
							<option>&nbsp;&nbsp;&nbsp;&nbsp;Vestiging</option>
						</select>
					</div>
					
					<div class="secundary-color-1 dropdown-container border border-top-0 border-dark">
						<input class="dropdown-item" value="Status" disabled>
					</div>
					
					<div class="secundary-color-1 dropdown-container border border-top-0 border-dark">
						<input class="dropdown-item" value="Status verlopen" disabled>
					</div>

					<!-- Admin additions: -->
					<div class="secundary-color-1 dropdown-container border border-top-0 border-dark">
						<input class="dropdown-item" value="Laatste lener" disabled>
					</div>

					<div class="secundary-color-1 border border-top-0 border-dark">
						<button class="secundary-color-4 buttons text-black rounded-2 m-1 text-center" id="boek-save-button" type="submit">Wijzigingen Opslaan</button>
					</div>

					<div class="secundary-color-1 border border-top-0 border-dark">
						<button class="secundary-color-4 buttons text-black rounded-2 m-1 text-center" id="boek-status-button" type="submit">Status Aanpassen</button>
					</div>

					<div class="secundary-color-1 border border-top-0 border-dark">
						<button class="secundary-color-4 buttons text-black rounded-2 m-1 text-center" id="boek-status-button" type="submit">Boek Verwijderen</button>
					</div>
                </div>
            </div>