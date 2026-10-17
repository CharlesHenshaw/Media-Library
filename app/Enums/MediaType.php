<?php

namespace App\Enums;

enum MediaType: string
{
    case Film = 'film';
    case Tv = 'tv';
    case Game = 'game';
    case Book = 'book';

    public function label(): string
    {
        return match ($this) {
            self::Film => 'Film',
            self::Tv => 'TV show',
            self::Game => 'Game',
            self::Book => 'Book',
        };
    }
}
