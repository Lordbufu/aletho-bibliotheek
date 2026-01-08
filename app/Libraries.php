<?php
namespace App;

use App\Database;
use App\Libs\{BookRepo, GenreRepo, LoanerRepo, OfficeRepo, StatusRepo, WriterRepo, UserRepo, NotificationRepo, BookStatusRuleRepo};

class Libraries {
    protected array         $instances  = [];
    protected array         $factories  = [];
    protected Database      $db;

    public function __construct(Database $db) {
        $this->db = $db;

        // register factories
        $this->factories['books']           = fn() => new BookRepo($this->db, fn($n) => $this->resolveLibrary($n));
        $this->factories['genre']           = fn() => new GenreRepo($this->db, fn($n) => $this->resolveLibrary($n));
        $this->factories['writer']          = fn() => new WriterRepo($this->db, fn($n) => $this->resolveLibrary($n));
        $this->factories['office']          = fn() => new OfficeRepo($this->db, fn($n) => $this->resolveLibrary($n));
        $this->factories['status']          = fn() => new StatusRepo($this->db, fn($n) => $this->resolveLibrary($n));
        $this->factories['loaner']          = fn() => new LoanerRepo($this->db, fn($n) => $this->resolveLibrary($n));
        $this->factories['user']            = fn() => new UserRepo($this->db, fn($n) => $this->resolveLibrary($n));
        $this->factories['status_rules']    = fn() => new BookStatusRuleRepo($this->db, fn($n) => $this->resolveLibrary($n));
        $this->factories['notification']    = fn() => new NotificationRepo($this->db, fn($n) => $this->resolveLibrary($n));
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

    /** Public accessors to offer various way to interact with these factories. */
    public function get(string $name) {
        return $this->resolveLibrary($name);
    }

    public function books(): BookRepo {
        return $this->resolveLibrary('books');
    }

    public function genres(): GenreRepo {
        return $this->resolveLibrary('genre');
    }

    public function writers(): WriterRepo {
        return $this->resolveLibrary('writer');
    }

    public function offices(): OfficeRepo {
        return $this->resolveLibrary('office');
    }

    public function statuses(): StatusRepo {
        return $this->resolveLibrary('status');
    }

    public function loaners(): LoanerRepo {
        return $this->resolveLibrary('loaner');
    }

    public function users(): UserRepo {
        return $this->resolveLibrary('user');
    }

    public function bookStatusRuleRepo(): BookStatusRuleRepo {
        return $this->resolveLibrary('status_rules');
    }

    public function notifications(): NotificationRepo {
        return $this->resolveLibrary('notification');
    }
}