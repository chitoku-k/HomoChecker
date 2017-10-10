<?php
declare(strict_types=1);

namespace HomoChecker\Action;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use HomoChecker\Model\Homo;
use HomoChecker\Model\Status;

class ListAction extends ActionBase
{
    public function route(Request $request, Response $response, array $args)
    {
        $name = $args['name'] ?? null;
        $homo = $this->container['homo'];
        $users = $name ? $homo->find(['screen_name' => $name]) : $homo->find();

        switch ($request->getQueryParams()['format'] ?? 'json') {
            case 'sql': {
                return $response->withHeader('Content-Type', 'application/sql')->withBody($this->createSql($response, $users));
            }
            default: {
                return $response->withJson($this->createArray($users), !empty($users) ? 200 : 404);
            }
        }
    }

    protected function createSql(Response $response, array $users)
    {
        $body = $response->getBody();
        $body->write("INSERT INTO `users` (`screen_name`, `url`) VALUES\n");
        $body->write(
            implode(
                ",\n",
                array_map(
                    function ($homo) {
                        foreach (['screen_name', 'url'] as $prop) {
                            $$prop = addcslashes($homo->$prop, '\\\'');
                        }
                        return "('{$screen_name}', '{$url}')";
                    },
                    $this->createArray($users)
                )
            )
        );
        return $body;
    }

    protected function createArray(array $homos): array
    {
        return array_map(function (Homo $item): \stdClass {
            $status = new Status($item);
            return $status->homo;
        }, $homos);
    }
}
