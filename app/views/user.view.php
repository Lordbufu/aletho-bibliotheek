<!-- /* View content container, to style/position the main content. */ -->
<div class="view-container container-fluid" id="view-container">
<?php
    require 'partials/containers/search-container.php';
    require 'partials/containers/hamburger-container.php';
    foreach ($books as $book) {
        require 'partials/containers/item-container.php';
    }
    require 'partials/popins/add-book-popin.php';
    require 'partials/popins/status-period-popin.php';
    require 'partials/popins/password-reset-popin.php';
    require 'partials/popins/change-book-status-popin.php';
?>
</div>