<?php
namespace HomoChecker\Action;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use HomoChecker\Model\HomoInterface;
use HomoChecker\Model\Status;

class ListAction extends ActionBase
{
    public function route(Request $request, Response $response, array $args)
    {
        $name = $args['name'] ?? null;
        $homo = $this->container['homo'];
        $users = $name ? $homo->find(['screen_name' => $name]) : $homo->find();

        return $response->withJson($this->create($users), !empty($users) ? 200 : 404);
    }

    protected function create(array $homos): array
    {
        return array_map(function (HomoInterface $item): \stdClass {
            $status = new Status($item);
            return $status->homo;
        }, $homos);
    }
}
