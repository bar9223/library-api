<?php

declare(strict_types=1);

namespace App\Application\Command\UpdateBookStatus;

use App\Dto\UpdateBookStatusRequest;

final readonly class UpdateBookStatusCommand
{
    public function __construct(
        public int $id,
        public UpdateBookStatusRequest $request,
    ) {
    }
}
