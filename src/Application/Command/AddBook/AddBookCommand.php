<?php

declare(strict_types=1);

namespace App\Application\Command\AddBook;

use App\Dto\AddBookRequest;

final readonly class AddBookCommand
{
    public function __construct(
        public AddBookRequest $request,
    ) {
    }
}
