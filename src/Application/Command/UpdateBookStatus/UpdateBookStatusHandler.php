<?php

declare(strict_types=1);

namespace App\Application\Command\UpdateBookStatus;

use App\Entity\Book;
use App\Exception\BookNotFoundException;
use App\Repository\BookRepository;
use DateTimeImmutable;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: 'command.bus')]
final readonly class UpdateBookStatusHandler
{
    public function __construct(
        private BookRepository $bookRepository,
    ) {
    }

    public function __invoke(UpdateBookStatusCommand $command): Book
    {
        $book = $this->bookRepository->find($command->id);

        if ($book === null) {
            throw new BookNotFoundException($command->id);
        }

        if ($command->request->borrowed === true) {
            $book->borrow((string) $command->request->borrowerCardNumber, new DateTimeImmutable());
        } else {
            $book->giveBack();
        }

        $this->bookRepository->save($book);

        return $book;
    }
}
