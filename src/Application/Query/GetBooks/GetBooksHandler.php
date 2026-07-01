<?php

declare(strict_types=1);

namespace App\Application\Query\GetBooks;

use App\Repository\BookRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: 'query.bus')]
final readonly class GetBooksHandler
{
    public function __construct(
        private BookRepository $bookRepository,
    ) {
    }

    public function __invoke(GetBooksQuery $query): array
    {
        $result = $this->bookRepository->paginate($query->search, $query->page, $query->limit);
        $total = $result['total'];
        $pages = (int) max(1, (int) ceil($total / $query->limit));

        return [
            'items' => $result['items'],
            'total' => $total,
            'page' => $query->page,
            'limit' => $query->limit,
            'pages' => $pages,
        ];
    }
}
