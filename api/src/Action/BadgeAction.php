<?php
declare(strict_types=1);

namespace HomoChecker\Action;

use HomoChecker\Contracts\Service\CheckService;
use HomoChecker\Contracts\Service\HomoService;
use HomoChecker\Domain\Status;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class BadgeAction
{
    /**
     * @var CheckService
     */
    protected $check;

    /**
     * @var HomoService
     */
    protected $homo;

    public function __construct(CheckService $check, HomoService $homo)
    {
        $this->check = $check;
        $this->homo = $homo;
    }

    public function __invoke(Request $request, Response $response, array $args)
    {
        $status = $args['status'] ?? null;
        $count = $this->getCount($status);
        $label = "{$count} " . strtolower($status ?? 'registered');
        return $response->withRedirect($this->getURL('homo', $label, '7a6544', $request->getQueryParams()));
    }

    protected function getURL(string $service, string $label, string $color, array $query = []): string
    {
        $uri = "https://img.shields.io/badge/{$service}-{$label}-{$color}.svg";
        return $query ? $uri . '?' . http_build_query($query) : $uri;
    }

    protected function getCount(string $status = null): int
    {
        if (!$status) {
            return $this->homo->count();
        }

        $result = $this->check->execute();
        return count(array_filter($result, function (Status $item) use ($status): bool {
            return strcasecmp($item->getStatus(), $status) === 0;
        }));
    }
}
