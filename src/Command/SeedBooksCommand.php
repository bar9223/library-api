<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\Book;
use App\Repository\BookRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'app:seed-books', description: 'Insert a few sample books when the table is empty.')]
final class SeedBooksCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly BookRepository $bookRepository,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        if ($this->bookRepository->count([]) > 0) {
            $io->info('Books already present, skipping seed.');

            return Command::SUCCESS;
        }

        $firstBorrowed = new Book('100001', 'Lalka', 'Bolesław Prus');
        $firstBorrowed->borrow('654321', new DateTimeImmutable('-2 days'));

        $secondBorrowed = new Book('100007', 'Wesele', 'Stanisław Wyspiański');
        $secondBorrowed->borrow('111222', new DateTimeImmutable('-5 days'));

        $books = [
            $firstBorrowed,
            new Book('100002', 'Pan Tadeusz', 'Adam Mickiewicz'),
            new Book('100003', 'Ferdydurke', 'Witold Gombrowicz'),
            new Book('100004', 'Solaris', 'Stanisław Lem'),
            new Book('100005', 'Quo Vadis', 'Henryk Sienkiewicz'),
            new Book('100006', 'Chłopi', 'Władysław Reymont'),
            $secondBorrowed,
            new Book('100008', 'Dziady', 'Adam Mickiewicz'),
            new Book('100009', 'Zbrodnia i kara', 'Fiodor Dostojewski'),
            new Book('100010', 'Mistrz i Małgorzata', 'Michaił Bułhakow'),
            new Book('100011', 'Rok 1984', 'George Orwell'),
            new Book('100012', 'Folwark zwierzęcy', 'George Orwell'),
            new Book('100013', 'Wiedźmin: Ostatnie życzenie', 'Andrzej Sapkowski'),
            new Book('100014', 'Cyberiada', 'Stanisław Lem'),
            new Book('100015', 'Granica', 'Zofia Nałkowska'),
        ];

        foreach ($books as $book) {
            $this->entityManager->persist($book);
        }

        $this->entityManager->flush();

        $io->success(sprintf('Seeded %d books.', count($books)));

        return Command::SUCCESS;
    }
}
