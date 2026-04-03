<?php
// TODO: review iff not redundant atm, this was added for ease of use, not for a long term solution.
namespace App;

use App\Database;
use App\Libs\{BookRepo, GenresRepo, OfficesRepo, StatusRepo, WritersRepo, LoanRepo, LoanerRepo};

class Libraries {
    protected array         $instances  = [];
    protected array         $factories  = [];
    protected Database      $db;

    public function __construct(Database $db) {
        $this->db = $db;

        // register factories
        $this->factories['books']           = fn() => new BookRepo($this->db, fn($n) => $this->resolveLibrary($n));
        $this->factories['genres']          = fn() => new GenresRepo($this->db, fn($n) => $this->resolveLibrary($n));
        $this->factories['writers']         = fn() => new WritersRepo($this->db, fn($n) => $this->resolveLibrary($n));
        $this->factories['offices']         = fn() => new OfficesRepo($this->db, fn($n) => $this->resolveLibrary($n));
        $this->factories['status']          = fn() => new StatusRepo($this->db, fn($n) => $this->resolveLibrary($n));
        $this->factories['loan']            = fn() => new LoanRepo($this->db, fn($n) => $this->resolveLibrary($n));
        $this->factories['loaner']          = fn() => new LoanerRepo($this->db, fn($n) => $this->resolveLibrary($n));
    }

    /** Helper: Generic Library resolver */
    protected function resolveLibrary(string $name) {
        if (isset($this->instances[$name])) {
            return $this->instances[$name];
        }

        if (!isset($this->factories[$name])) {
            throw new \InvalidArgumentException("Unknown library $name");
        }

        return $this->instances[$name] = ($this->factories[$name])();
    }

    /** API: Public accessors to offer various way to interact with these factories. */
    public function get(string $name) {
        return $this->resolveLibrary($name);
    }
}