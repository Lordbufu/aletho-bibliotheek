<?php
namespace App;

use App\Database;
use App\Libs\{BookRepo, GenreRepo, LoanersRepo, OfficeRepo, StatusRepo, WriterRepo};

class Libraries {
    protected Database      $db;
    protected ?BookRepo     $books      = null;
    protected ?GenreRepo    $genres     = null;
    protected ?WriterRepo   $writers    = null;
    protected ?OfficeRepo   $offices    = null;
    protected ?StatusRepo   $statuses   = null;
    protected ?LoanersRepo  $loaners    = null;

    public function __construct(Database $db) {
        $this->db = $db;
    }

    public function get(): self {
        return $this;
    }

    public function books(): BookRepo {
        return $this->books ??= new BookRepo($this->db);
    }

    public function genres(): GenreRepo {
        return $this->genres ??= new GenreRepo($this->db);
    }

    public function writers(): WriterRepo {
        return $this->writers ??= new WriterRepo($this->db);
    }

    public function offices(): OfficeRepo {
        return $this->offices ??= new OfficeRepo($this->db);
    }

    public function statuses(): StatusRepo {
        return $this->statuses ??= new StatusRepo($this->db);
    }

    public function loaners(): LoanersRepo {
        return $this->loaners ??= new LoanersRepo($this->db);
    }
}