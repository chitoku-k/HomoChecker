<?php
declare(strict_types=1);

namespace HomoChecker\Action;

use HomoChecker\Contracts\Service\ActivityPubService;
use Slim\Http\Response;
use Slim\Http\ServerRequest as Request;

final class WebFingerAction
{
    public function __construct(protected ActivityPubService $activityPub) {}

    public function __invoke(Request $request, Response $response)
    {
        $resource = $request->getQueryParams()['resource'] ?? null;
        if (!$resource) {
            return $response->withStatus(400);
        }

        $webFinger = $this->activityPub->webFinger($resource);
        if (!$webFinger) {
            return $response->withStatus(404);
        }

        return $response
            ->withJson($webFinger)
            ->withHeader('Content-Type', 'application/jrd+json; charset=utf-8');
    }
}
