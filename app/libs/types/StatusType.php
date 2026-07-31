<?php
namespace App\Libs\Types;

/** */
final class StatusType {
    public const AANWEZIG       = 'Aanwezig';
    public const UITGELEEND     = 'Uitgeleend';
    public const TRANSPORT      = 'Transport';
    public const LIGT_KLAAR     = 'Ligt Klaar';
    public const GERESERVEERD   = 'Gereserveerd';
    public const OVERDATUM      = 'Overdatum';

    /** API: Map status id to status string */
    public static function fromId(int $id): string {
        return match ($id) {
            1 => self::AANWEZIG,
            2 => self::UITGELEEND,
            3 => self::TRANSPORT,
            4 => self::LIGT_KLAAR,
            5 => self::GERESERVEERD,
            6 => self::OVERDATUM,
            default => throw new \InvalidArgumentException("Unknown status id: $id"),
        };
    }

    /** API: Map status string to status id */
    public static function toId(string $type): int {
        return match ($type) {
            self::AANWEZIG     => 1,
            self::UITGELEEND   => 2,
            self::TRANSPORT    => 3,
            self::LIGT_KLAAR   => 4,
            self::GERESERVEERD => 5,
            self::OVERDATUM    => 6,
            default => throw new \InvalidArgumentException("Unknown status type: $type"),
        };
    }
}