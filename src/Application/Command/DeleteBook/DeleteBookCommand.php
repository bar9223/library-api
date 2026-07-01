<?php

declare(strict_types=1);

namespace App\Application\Command\DeleteBook;

final readonly class DeleteBookCommand
{
    public function __construct(
        public int $id,
    ) {
    }
}
