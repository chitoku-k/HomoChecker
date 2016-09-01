<?php
namespace HomoChecker\Action;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use HomoChecker\Model\Check;
use HomoChecker\Model\Homo;
use HomoChecker\Model\HomoStatus;

class BadgeAction extends ActionBase
{
    const API_URI = 'https://img.shields.io/badge/';

    public function route(Request $request, Response $response, array $args)
    {
        $status = $args['status'] ?? null;
        $count = $this->getCount($status);

        $query = http_build_query($request->getParams());
        $label = "{$count} " . strtolower($status ?? 'registered');
        $uri = self::API_URI . "homo-{$label}-7a6544.svg" . ($query ? "?{$query}" : '');

        return $response->withRedirect($uri);
    }

    protected function getCount(string $status = null): int
    {
        if (!$status) {
            return iterator_count(Homo::getAll());
        }

        $result = (new Check)->execute();
        return count(array_filter($result, function (HomoStatus $item) use ($status): bool {
            return strcasecmp($item->status, $status) === 0;
        }));
    }
}
