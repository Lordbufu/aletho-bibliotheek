<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1">
		<meta name="author" content="Marco Visscher">
		<meta name="description" content="Een bibliotheek App voor Alétho.">
		<title>Alétho Bibliotheek App</title>
		<!-- Font Awesome min css: -->
		<link href="css/fontawesome-free-6.7.2-web.all.min.css" rel="stylesheet" />
		<!-- Manual Bootstrap min css: -->
		<link href="css/bootstrap/bootstrap.min.css" rel="stylesheet">
		<!-- Costom CSS & JS stuff: -->
		<link rel="stylesheet" type="text/css" href="css/style.css">
		<script src="js/main.js"></script>
	</head>

	<!-- /* Body to set the main page style and size. */ -->
	<body class="container-fluid bg-prim-col p-1 text-center">

		<!-- /* Header container, for the header menus and text. */ -->
		<nav class="nav-bar container-fluid bg-sec-col border border-dark rounded">
			<div class="nav-row-cont row align-items-center">
				<div class="col-1 p-0">
					<button class="hamburger-icon fas fa-bars bg-sec-col border border-0" id="hamburger-button" type="button"></button>
				</div>

				<h1 class="p-0 col-10 fs-1 text-white">Alétho Bibliotheek App</h1>

				<div class="p-0 col-1">
					<button class="search-icon bg-sec-col border border-0" id="search-button" type="button">&#128269;</button>
				</div>
			</div>
		</nav>

    	<!-- /* View content container, to style/position the main content. */ -->
		<div class="view-container container-fluid">

			<!-- /* Hamburger dropdown menu, and the associated content. */ -->
			<div class="collapse text-center p-1 pt-2 pb-2 bg-sec-col border border-top-0 border-dark rounded-bottom" id="customSearchDd">
				<div class="search-container row m-0 p-0" id="search-cont">
					<label class="search-option-label text-white mb-1" for="search-options">Search for:</label>
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

            <!-- /* Item container example, with a dropdown for pontetially editable details*/ -->
            <div class="item-container container-sm p-1" id="item-container-1">
                <label class="item-name-label d-flex flex-row border border-dark mn-main-col" for="itemButton">
                    <button class="item-button d-flex justify-content-start mn-main-col border border-0" id="itemButton" type="button">▼</button>
                    <input class="item-name d-flex flex-fill mn-main-col text-center" value="Boek Naam 1">
                </label>


                <div class="item-details flex-column mn-main-col" id="customItemDb">
                    <input class="dropdown-item border border-top-0 border-dark" value="Boek Schrijver">
                    <input class="dropdown-item border border-top-0 border-dark" value="Boek Categorie">
                    <input class="dropdown-item border border-top-0 border-dark" value="Boek Vestiging">
                </div>
            </div>

            <!-- /* Item container example, with a dropdown for pontetially editable details*/ -->
            <div class="item-container container-sm p-1" id="item-container-2">
                <label class="item-name-label d-flex flex-row border border-dark mn-main-col" for="itemButton">
                    <button class="item-button d-flex justify-content-start mn-main-col border border-0" id="itemButton" type="button">▼</button>
                    <input class="item-name d-flex flex-fill center mn-main-col text-center" value="Boek Naam 2">
                </label>


                <div class="item-details flex-column mn-main-col collapse" id="customItemDb">
                    <input class="dropdown-item border border-top-0 border-dark" value="Boek Schrijver">
                    <input class="dropdown-item border border-top-0 border-dark" value="Boek Categorie">
                    <input class="dropdown-item border border-top-0 border-dark" value="Boek Vestiging">
                </div>
            </div>

        </div>

		<!-- Manual Bootstrap min js: -->
		<script src="js/bootstrap/bootstrap.bundle.min.js"></script>
    </body>

</html>