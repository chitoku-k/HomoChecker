<?php
declare(strict_types=1);

namespace HomoChecker\Service;

use HomoChecker\Contracts\Repository\HomoRepository as HomoRepositoryContract;
use HomoChecker\Contracts\Service\HomoService as HomoServiceContract;

class HomoService implements HomoServiceContract
{
    protected HomoRepositoryContract $repository;

    public function __construct(HomoRepositoryContract $repository)
    {
        $this->repository = $repository;
    }

    public function count(string $screenName = null): int
    {
        if (!$screenName) {
            return $this->repository->count();
        }

        return $this->repository->countByScreenName($screenName);
    }

    public function find(string $screenName = null): array
    {
        if (!$screenName) {
            return $this->repository->findAll();
        }

        return $this->repository->findByScreenName($screenName);
    }

    public function export(): string
    {
        return $this->repository->export();
    }
}
