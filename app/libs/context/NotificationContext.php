<?php
namespace App\Libs\Context;

final class NotificationContext {
    // Required properties
    public string   $notiType;
    public int      $notificationId;
    public int      $bookStatusId;
    public string   $bookName;
    public ?string  $loanerName     = null;
    public ?string  $loanerEmail    = null;
    // Optional properties
    public ?string  $dueDate        = null;
    public ?string  $officeName     = null;
    public ?string  $adminName      = null;
    public ?string  $adminEmail     = null;
    public ?string  $adminOffice    = null;
    public ?string  $actionType     = null;
    public ?string  $actionToken    = null;
    // public ?string  $actionLink     = null;     // Has to be constructed
}