<?php
namespace App\Libs\Context;

/** Book context used for views */
final class BookContext {
    /* Static `books` table data */
    public int                  $id;
    public string               $title;
    public bool                 $active;
    public int                  $homeOfficeId;
    public int                  $curOfficeId;

    /* Extra `books` reservation meta data */
    public ?int                 $resvLoanerId   = null;
    public ?int                 $resvOfficeId   = null;
    public ?\DateTimeImmutable  $resvCreatedAt  = null;
    public ?\DateTimeImmutable  $resvExpiresAt  = null;

    /* Attached data from other related tables */
    public ?string              $curOfficeName  = null;
    public ?string              $homeOfficeName = null;
    public ?array               $writers        = null;
    public ?array               $genres         = null;
    public ?array               $status         = null;   
}