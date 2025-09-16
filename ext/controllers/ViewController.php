<?php

namespace ext\controllers;

use App\App;

class ViewController {
    public function landing() {
        if(isset($_SESSION['user']['role'])) {
            return App::view('auth/login', ['error' => null]);
        } else {
            header('Location: /home');
            exit;
        }
    }

    public function userLogin() {
        if(!isset($_SESSION['user'])) {
            return App::view('main', ['error' => null]);
        }

        // 1. Fetch all data
        $books = App::getService('database')->query()->fetchAll("SELECT * FROM books");
        $writers = App::getService('database')->query()->fetchAll("SELECT * FROM writers");
        $bookWriters = App::getService('database')->query()->fetchAll("SELECT * FROM book_writers");
        $genres = App::getService('database')->query()->fetchAll("SELECT * FROM genres");
        $bookGenres = App::getService('database')->query()->fetchAll("SELECT * FROM book_genre");
        $statuses = App::getService('database')->query()->fetchAll("SELECT * FROM status");
        $bookStatuses = App::getService('database')->query()->fetchAll("SELECT * FROM book_status");
        $bookStaMeta = App::getService('database')->query()->fetchAll("SELECT * FROM book_sta_meta");
        $offices = App::getService('database')->query()->fetchAll("SELECT * FROM offices");

        // 2. Build lookup maps
        $writerMap = [];
        foreach ($writers as $w) $writerMap[$w['id']] = $w['name'];
        $bookWriterMap = [];
        foreach ($bookWriters as $bw) $bookWriterMap[$bw['book_id']][] = $writerMap[$bw['writer_id']] ?? '';
        $genreMap = [];
        foreach ($genres as $g) $genreMap[$g['id']] = $g['name'];
        $bookGenreMap = [];
        foreach ($bookGenres as $bg) $bookGenreMap[$bg['book_id']] = $bg['genre_id'];
        $statusMap = [];
        foreach ($statuses as $s) $statusMap[$s['id']] = $s;
        $bookStatusMap = [];
        foreach ($bookStatuses as $bs) $bookStatusMap[$bs['book_id']] = $bs;
        $metaMap = [];
        foreach ($bookStaMeta as $meta) $metaMap[$meta['id']] = $meta;

        // 3. User info for permissions
        $user = $_SESSION['user'] ?? [];
        $userOfficeId = $user['office_id'] ?? null;
        $userCanEdit = $user['canEdit'] ?? false;

        // 4. Hydrate books
        foreach ($books as &$book) {
            // Writers (comma separated)
            $book['writers'] = isset($bookWriterMap[$book['id']])
                ? implode(', ', $bookWriterMap[$book['id']])
                : '';

            // Genre (single)
            $genreId = $bookGenreMap[$book['id']] ?? null;
            $book['genre'] = $genreId ? ($genreMap[$genreId] ?? '') : '';

            // Status and Expiration
            $statusInfo = $bookStatusMap[$book['id']] ?? null;
            if ($statusInfo) {
                $statusId = $statusInfo['stat_id'];
                $statusType = $statusMap[$statusId]['type'] ?? '';
                $periodeLength = $statusMap[$statusId]['periode_length'] ?? null;
                $startDate = $statusInfo['start_date'] ?? null;
                $book['status'] = $statusType;

                if ($statusType === 'Aanwezig' || !$periodeLength || !$startDate) {
                    unset($book['statusExp']);
                } else {
                    $expDate = date('Y-m-d', strtotime($startDate . " +$periodeLength days"));
                    if ($expDate && $expDate !== '1970-01-01') {
                        $book['statusExp'] = $expDate;
                    } else {
                        unset($book['statusExp']);
                    }
                }
            } else {
                $book['status'] = '';
                unset($book['statusExp']);
            }

            // Can edit office?
            $book['canEditOffice'] = $userCanEdit || ($userOfficeId && $book['office_id'] == $userOfficeId);
        }
        unset($book);

        // 5. Pass only what you need to the view
        return App::view('main', [
            'books' => $books,
            'offices' => $offices,
            'genres' => $genres,
            'statuses' => $statuses,
            // Add other arrays if needed for dropdowns, etc.
        ]);
    }
}