<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Application\Command\AddBook\AddBookCommand;
use App\Application\Command\DeleteBook\DeleteBookCommand;
use App\Application\Command\UpdateBookStatus\UpdateBookStatusCommand;
use App\Application\Query\GetBooks\GetBooksQuery;
use App\Controller\AbstractApiController;
use App\Dto\AddBookRequest;
use App\Dto\UpdateBookStatusRequest;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/books')]
final class BookController extends AbstractApiController
{
    #[Route('', name: 'api_books_list', methods: ['GET'])]
    public function list(
        #[MapQueryParameter] ?string $search = null,
        #[MapQueryParameter] int $page = 1,
        #[MapQueryParameter] int $limit = 10,
    ): JsonResponse {
        $page = max(1, $page);
        $limit = min(100, max(1, $limit));

        return $this->serialized($this->dispatchQuery(new GetBooksQuery($search, $page, $limit)));
    }

    #[Route('', name: 'api_books_add', methods: ['POST'])]
    public function add(#[MapRequestPayload] AddBookRequest $request): JsonResponse
    {
        $book = $this->dispatchCommand(new AddBookCommand($request));

        return $this->serialized($book, Response::HTTP_CREATED);
    }

    #[Route('/{id}', name: 'api_books_delete', requirements: ['id' => '\d+'], methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $this->dispatchCommand(new DeleteBookCommand($id));

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    #[Route('/{id}/status', name: 'api_books_update_status', requirements: ['id' => '\d+'], methods: ['PATCH'])]
    public function updateStatus(int $id, #[MapRequestPayload] UpdateBookStatusRequest $request): JsonResponse
    {
        $book = $this->dispatchCommand(new UpdateBookStatusCommand($id, $request));

        return $this->serialized($book);
    }
}
