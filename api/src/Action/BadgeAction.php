<?php
declare(strict_types=1);

namespace HomoChecker\Action;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use HomoChecker\Model\Status;

class BadgeAction extends ActionBase
{
    public function route(Request $request, Response $response, array $args)
    {
        $status = $args['status'] ?? null;
        $count = $this->getCount($status);
        $label = "{$count} " . strtolower($status ?? 'registered');
        return $response->withRedirect($this->getURL('homo', $label, '7a6544', $request->getParams()));
    }

    protected function getURL(string $service, string $label, string $color, array $query = []): string
    {
        $uri = "https://img.shields.io/badge/{$service}-{$label}-{$color}.svg";
        return $query ? $uri . '?' . http_build_query($query) : $uri;
    }

    protected function getCount(string $status = null): int
    {
        if (!$status) {
            return count($this->container['homo']->find());
        }

        $result = $this->container['checker']->execute();
        return count(array_filter($result, function (Status $item) use ($status): bool {
            return strcasecmp($item->status, $status) === 0;
        }));
    }
}
