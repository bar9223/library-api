<?php

declare(strict_types=1);

namespace App\Application\Command\AddBook;

use App\Entity\Book;
use App\Exception\DuplicateSerialNumberException;
use App\Repository\BookRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: 'command.bus')]
final readonly class AddBookHandler
{
    public function __construct(
        private BookRepository $bookRepository,
    ) {
    }

    public function __invoke(AddBookCommand $command): Book
    {
        $request = $command->request;

        if ($this->bookRepository->existsBySerialNumber((string) $request->serialNumber)) {
            throw new DuplicateSerialNumberException((string) $request->serialNumber);
        }

        $book = new Book(
            (string) $request->serialNumber,
            (string) $request->title,
            (string) $request->author,
        );

        $this->bookRepository->save($book);

        return $book;
    }
}
