<!-- /* View content container, to style/position the main content. */ -->
<div class="view-container container-fluid" id="view-container">
<?php
    require viewPath('partials/containers/search-container.php');
    require viewPath('partials/containers/hamburger-container.php');
    foreach ($books as $book) {
        require viewPath('partials/containers/item-container.php');
    }
    require viewPath('partials/popins/add-book-popin.php');
    require viewPath('partials/popins/status-period-popin.php');
    require viewPath('partials/popins/password-reset-popin.php');
    require viewPath('partials/popins/change-book-status-popin.php');
?>
</div>