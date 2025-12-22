<?php
/** Example stucture:
 *      event_key => [
 *          'status'  => [toStatusId],
 *          'from'    => optional [fromStatusId],
 *          'trigger' => 'user_action' | 'auto_action' | 'cron_action',
 *          'strict'  => true | false
 *      ]
 * 
 *      status  = the required final status id
 *      from    = the required previous status (only if strict)
 *      trigger = who/what initiated the transition
 *      strict  = whether the transition must match exactly
 */

return [
    // Manual status change events:
    'loan_confirm'          => ['status' => [2], 'from' => [1], 'trigger' => 'user_action', 'strict' => true],      // Working as intended
    'pickup_ready_confirm'  => ['status' => [4], 'from' => [3], 'trigger' => 'user_action', 'strict' => true],      // Working as intended
    'pickup_confirm'        => ['status' => [2], 'from' => [4], 'trigger' => 'user_action', 'strict' => true],      // Working as intended
    'reserv_confirm'        => ['status' => [5], 'trigger' => 'user_action', 'strict' => false],                    // Working as intended
    'transport_request'     => ['status' => [3], 'trigger' => 'user_action', 'strict' => false],                    // Might be redundant ??

    // Automated (logic driven) status change events:
    'reserv_confirm_auto'   => ['status' => [5], 'from' => [2], 'trigger' => 'auto_action', 'strict' => true],      // Working as intended
    'transp_req_auto'       => ['status' => [3], 'from' => [1, 2], 'trigger' => 'auto_action', 'strict' => true],   // Working as intended

    // CRON status change events:
    'return_reminder'       => ['status' => [2], 'from' => [2], 'trigger' => 'cron_action', 'strict' => true],      // Needs live testing
    'overdue_reminder_user' => ['status' => [6], 'from' => [2], 'trigger' => 'cron_action', 'strict' => true],      // Needs live testing
    'overdue_notice_admin'  => ['status' => [6], 'from' => [2], 'trigger' => 'cron_action', 'strict' => true],      // Needs live testing
];