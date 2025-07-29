<!-- /* Hamburger dropdown menu, and the associated user based content. */ -->
<div    class= "secundary-color-1
                collapse
                text-center
                p-1
                pt-2
                pb-2
                bg-sec-col
                border
                border-top-0
                border-dark
                rounded-bottom"
        id="customHamburgerDropdown">
    <div class="hamburger-container row m-0 p-0" id="hamb-cont">
        <!-- /* Hamburger menu items for admin users */ -->
        <?php if(isset($userType) && ($userType === 'office_admins' || $userType === 'global_admins')): ?>
            <input class="secundary-color-4 buttons text-black rounded-2 p-1 text-center" id="boek-toev-button" type="submit" value="Boek Toevoegen">
            <input class="secundary-color-4 buttons text-black rounded-2 p-1 text-center" id="periode-wijz-button" type="submit" value="Periode Wijzigen">
            <input class="secundary-color-4 buttons text-black rounded-2 p-1 text-center" id="wachtwoord-wijz-button" type="submit" value="Wachtwoord Wijzigen">
        <?php endif; ?>
        <!-- /* Hamburger menu items for all users */ -->
        <input class="secundary-color-4 buttons text-black rounded-2 p-1 text-center" id="logoff-button" type="submit" value="Uitloggen">
    </div>
</div>