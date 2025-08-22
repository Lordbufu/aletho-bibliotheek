<div class="aletho-dropdown-body collapse" id="customHamburgerDropdown">
    <div class="hamburger-container" id="hamb-cont">

        <?php if(isset($_SESSION['user']) && $_SESSION['user']['canEdit']): ?>
            <input class="aletho-buttons" id="boek-add-button" type="submit" value="Boek Toevoegen">
            <input class="aletho-buttons" id="status-periode-button" type="submit" value="Periode Wijzigen">
            <input class="aletho-buttons" id="password-change-button" type="submit" value="Wachtwoord Wijzigen">
        <?php endif; ?>

        <form action="/logout" method="post">
            <button type="submit" class="aletho-buttons" id="logoff-button">
                Uitloggen
            </button>
        </form>
            
    </div>
</div>