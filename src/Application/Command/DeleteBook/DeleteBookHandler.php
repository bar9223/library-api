<?php

declare(strict_types=1);

namespace App\Application\Command\DeleteBook;

use App\Exception\BookNotFoundException;
use App\Repository\BookRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: 'command.bus')]
final readonly class DeleteBookHandler
{
    public function __construct(
        private BookRepository $bookRepository,
    ) {
    }

    public function __invoke(DeleteBookCommand $command): void
    {
        $book = $this->bookRepository->find($command->id);

        if ($book === null) {
            throw new BookNotFoundException($command->id);
        }

        $this->bookRepository->remove($book);
    }
}
