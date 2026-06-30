<?php
    $statusClassMap = [                     // old code for indication lights, still needs concepting and refactoring.
        'aanwezig' => 'statusOne',
        'uitgeleend' => 'statusTwo',
        'overdatum' => 'statusTwo',
        'transport' => 'statusThree',
        'ligt klaar' => 'statusThree',
        'gereserveerd' => 'statusFour'
    ];

    $status = strtolower($book->status);
    $statusClass = $statusClassMap[$status] ?? '';
?>

<div class="aletho-item-container" id="item-container-<?= $book->id ?>">
    <div class="aletho-item">
        <button class="aletho-dropdown-buttons"
                id="itemButton-<?= $book->id ?>"
                type="button"
                data-bs-toggle="collapse"
                data-bs-target="#customItemDropdown-<?= $book->id ?>"
                aria-expanded="<?= (isset($_SESSION['_flashSingle']) && (int)$_SESSION['_flashSingle']['message'] === (int)$book->id) ? 'true' : 'false' ?>"
                aria-controls="customItemDropdown-<?= $book->id ?>">
            ▼
        </button>
        <span class="dropdown-item flex-fill text-center mn-main-col"><?= htmlspecialchars($book->title) ?></span>
        <span class="status-dot d-flex justify-content-end <?= $statusClass ?>" id="status-dot-<?= $book->id ?>"></span>
    </div>

    <div id="customItemDropdown-<?= $book->id ?>" class="collapse aletho-item-dropdown <?= (isset($_SESSION['_flashSingle']) && (int)$_SESSION['_flashSingle']['message'] === (int)$book->id) ? ' show' : '' ?>">
        <form class="book-edit-form p-1" data-book-id="<?= $book->id ?>" method="post" action="/editBook">
            <?php component('item-dropdown-title', [ 'book'   => $book, 'errors' => $errors ?? [], ]); ?>
            <?php component('item-dropdown-writers', [ 'book'   => $book, 'errors' => $errors ?? [], ]); ?>
            <?php component('item-dropdown-genres', [ 'book'   => $book, 'errors' => $errors ?? [], ]); ?>
            <?php component('item-dropdown-location', [ 'book'   => $book, 'errors' => $errors ?? [], ]); ?>
            <?php component('item-dropdown-status', [ 'book'   => $book, 'errors' => $errors ?? [], ]); ?>
            <?php component('item-dropdown-loaners', [ 'book'   => $book, 'errors' => $errors ?? [], ]); ?>
            <?php component('item-dropdown-buttons', [ 'book'   => $book, 'errors' => $errors ?? [], ]); ?>
        </form>
    </div>
</div>