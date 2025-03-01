<?php
declare(strict_types=1);

namespace HomoChecker\Service;

use HomoChecker\Contracts\Repository\HomoRepository as HomoRepositoryContract;
use HomoChecker\Contracts\Service\HomoService as HomoServiceContract;

final class HomoService implements HomoServiceContract
{
    public function __construct(protected HomoRepositoryContract $repository) {}

    #[\Override]
    public function count(?string $screenName = null): int
    {
        if (!$screenName) {
            return $this->repository->count();
        }

        return $this->repository->countByScreenName($screenName);
    }

    #[\Override]
    public function find(?string $screenName = null): array
    {
        if (!$screenName) {
            return $this->repository->findAll();
        }

        return $this->repository->findByScreenName($screenName);
    }

    #[\Override]
    public function export(): string
    {
        return $this->repository->export();
    }
}
