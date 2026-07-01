<?php

declare(strict_types=1);

namespace App\Application\Query\GetBooks;

final readonly class GetBooksQuery
{
    public function __construct(
        public ?string $search = null,
        public int $page = 1,
        public int $limit = 10,
    ) {
    }
}
