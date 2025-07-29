<!-- /* View content container, to style/position the main content. */ -->
<div class="view-container container-fluid" id="view-container">

  <?php require 'partials/containers/search-container.php'; ?>
  <?php require 'partials/containers/hamburger-container.php'; ?>

  <?php foreach ($books as $book): ?>
	<?php require 'partials/containers/item-container.php'; ?>
  <?php endforeach; ?>
</div>


<?php require 'partials/popins/add-book-popin.php'; ?>
<?php require 'partials/popins/status-period-popin.php'; ?>
<?php require 'partials/popins/password-reset-popin.php'; ?>
<?php require 'partials/popins/change-book-status-popin.php'; ?>