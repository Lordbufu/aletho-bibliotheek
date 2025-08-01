<?php
    // Required authentication logic
    $canEdit = $perm->canEdit();                                        // is admin and can edit
?>

<!-- /* View content container, to style/position the main content. */ -->
<div class="view-container container-fluid" id="view-container">

<?php
    require viewPath('partials/containers/search-container.php');
    require viewPath('partials/containers/hamburger-container.php');
?>
    <!-- /* Sort container */ -->
    <div class="sort-container d-flex justify-content-center my-3">
        <div class="sort-group d-inline-flex align-items-center">
            <label for="sort-options" class="m-0 me-2">Sorteren op:</label>
            <select id="sort-options" class="form-select form-select-sm w-auto">
                <option value="title-asc">Titel oplopend</option>
                <option value="title-desc">Titel aflopend</option>
                <option value="writer-asc">Schrijver oplopend</option>
                <option value="writer-desc">Schrijver aflopend</option>
                <option value="genre-asc">Genre oplopend</option>
                <option value="genre-desc">Genre aflopend</option>
            </select>
        </div>
    </div>

<?php
    foreach ($books as $book) { require viewPath('partials/containers/item-container.php'); }
    require viewPath('partials/popins/add-book-popin.php');
    require viewPath('partials/popins/status-period-popin.php');
    require viewPath('partials/popins/password-reset-popin.php');
    require viewPath('partials/popins/change-book-status-popin.php');
?>
</div>