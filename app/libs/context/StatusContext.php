<?php
namespace App\Libs\Context;

/** Basically just `status` table properties */
final class StatusContext {
    public int      $id;            // status.id
    public string   $type;          // status.type
    public int      $periodLength;  // status.period_length
    public int      $reminderDay;   // status.reminder_day
    public int      $overdueDay;    // status.overdue_day
}