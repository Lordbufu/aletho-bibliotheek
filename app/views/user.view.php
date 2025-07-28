<!-- /* View content container, to style/position the main content. */ -->
<div class="view-container container-fluid" id="view-container">

  <?php require 'partials/containers/search-container.php'; ?>
  <?php require 'partials/containers/hamburger-container.php'; ?>

  <?php foreach ($books as $book): ?>
    <?php require 'partials/containers/item-container.php'; ?>
  <?php endforeach; ?>
</div>

<?php require 'partials/popins/add-book-popin.php'; ?>

<script>
// JavaScript to handle the hamburger menu add book popin functionality
$(function() {
  $('#boek-toev-button').on('click', function() {
    $('#add-book-popin').show();
  });
  $('#close-add-book-popin').on('click', function() {
    $('#add-book-popin').hide();
  });
  // Optional: Hide modal when clicking outside the modal-content
  $('#add-book-popin').on('click', function(e) {
    if (e.target === this) {
      $(this).hide();
    }
  });
});
</script>

