<?php
namespace HomoChecker\Action;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use HomoChecker\Model\Homo;
use HomoChecker\Model\HomoStatus;

class ListAction extends ActionBase
{
    public function route(Request $request, Response $response, array $args)
    {
        $name = $args['name'] ?? null;
        $homos = iterator_to_array(isset($name) ? Homo::getByScreenName($name) : Homo::getAll());

        return $response->withJson($this->create($homos), !empty($homos) ? 200 : 404);
    }

    protected function create(array $homos)
    {
        return array_map(function (Homo $item): array {
            $status = new HomoStatus($item);
            return $status->homo;
        }, $homos);
    }
}
