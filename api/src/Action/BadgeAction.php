<?php
declare(strict_types=1);

namespace HomoChecker\Action;

use HomoChecker\Contracts\Service\CheckService;
use HomoChecker\Contracts\Service\HomoService;
use HomoChecker\Domain\Result;
use HomoChecker\Domain\Status;
use HomoChecker\Domain\Validator\ValidationResult;
use Slim\Http\Response;
use Slim\Http\ServerRequest as Request;

final class BadgeAction
{
    public function __construct(protected CheckService $check, protected HomoService $homo) {}

    public function __invoke(Request $request, Response $response, array $args)
    {
        $status = $args['status'] ?? null;
        $count = $this->getCount($status);
        $label = "{$count} " . strtolower($status ?? 'registered');
        return $response->withRedirect($this->getURL('homo', $label, '7a6544', $request->getQueryParams()));
    }

    private function getURL(string $service, string $label, string $color, array $query = []): string
    {
        $uri = "https://img.shields.io/badge/{$service}-{$label}-{$color}.svg";
        return $query ? $uri . '?' . http_build_query($query) : $uri;
    }

    private function getCount(?string $status = null): int
    {
        if (!$status) {
            return $this->homo->count();
        }

        return collect($this->check->execute())
            ->map(fn (Status $item) => $item->getResult())
            ->filter(fn (?Result $item) => (bool) $item)
            ->map(fn (Result $item) => $item->getStatus())
            ->filter(fn (?ValidationResult $item) => ValidationResult::tryFrom(strtoupper($status)) === $item)
            ->count();
    }
}
