<?php
namespace App\ViewModel;

use App\Libs\Context\StatusContext;

// Re-factor status: tested and working
final class StatusViewModel {
    public int      $id;                // PK for `status`.`status_id`.
    public string   $naam;              // Derived from `status`.`status_name`.
    public ?int     $lengte     = null; // Derived from `status`.`status_length`
    public ?int     $reminder   = null; // Derived from `status`.`status_reminder`
    public ?int     $overdatum  = null; // Derived from `status`.`status_overdue`

    public function __construct(StatusContext $status) {
        $this->id           = $status->status_id;
        $this->naam         = $status->status_name;
        $this->lengte       = $status->status_length ?? null;
        $this->reminder     = $status->status_reminder ?? null;
        $this->overdatum    = $status->status_overdue ?? null;
    }

    /** Helper: Build array from model based on the $mode */
    private function toArray(?string $mode = null): array {
        return match ($mode) {
            'edit' => [
                'id'         => $this->id,
                'naam'       => $this->naam,
                'lengte'     => $this->lengte,
                'reminder'   => $this->reminder,
                'overdatum'  => $this->overdatum,
            ],
            default => [
                'id'         => $this->id,
                'naam'       => $this->naam,
            ]
        };
    }

    /** API: Set single dataset to model */
    public static function formatOne(array $status): self {
        return new self($status);
    }

    /** API: Convert inner array to viewmodel */
    public static function formatMany(array $statusen, string $mode): array {
        $formatted = [];

        foreach ($statusen as $status) {
            $vm = new self($status);
            $formatted[] = $vm->toArray($mode);
        }

        return $formatted;
    }
}

    // /** Helper: Set model from context */
    // private static function fromContext(StatusContext $ctx): self {
    //     return new self([
    //         'status_id'        => $ctx->status_id,
    //         'status_name'      => $ctx->status_name,
    //         'status_length'    => $ctx->status_length,
    //         'status_reminder'  => $ctx->status_reminder,
    //         'status_overdue'   => $ctx->status_overdue
    //     ]);
    // }