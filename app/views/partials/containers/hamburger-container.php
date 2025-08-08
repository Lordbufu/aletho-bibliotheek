<!-- /* Hamburger dropdown menu, and the associated user based content. */ -->
<div class="collapse aletho-dropdown-body aletho-search-container" id="customHamburgerDropdown">
    <div class="hamburger-container mb-1" id="hamb-cont">
        <?php if(isset($_SESSION['user']) && $_SESSION['user']['canEdit']): ?>
            <!-- /* Hamburger menu items for admin users */ -->
            <input class="aletho-buttons" id="boek-add-button" type="submit" value="Boek Toevoegen">
            <input class="aletho-buttons" id="status-periode-button" type="submit" value="Periode Wijzigen">
            <input class="aletho-buttons" id="password-change-button" type="submit" value="Wachtwoord Wijzigen">
        <?php endif; ?>
        <!-- /* Hamburger menu items for all users */ -->
        <input class="aletho-buttons" id="logoff-button" type="submit" value="Uitloggen">
    </div>
</div>