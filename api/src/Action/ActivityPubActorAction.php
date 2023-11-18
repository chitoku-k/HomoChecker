<?php
declare(strict_types=1);

namespace HomoChecker\Action;

use HomoChecker\Contracts\Service\ActivityPubService;
use Slim\Http\Response;
use Slim\Http\ServerRequest as Request;

class ActivityPubActorAction
{
    public function __construct(protected ActivityPubService $activityPub) {}

    public function __invoke(Request $request, Response $response)
    {
        $actor = $this->activityPub->actor();
        return $response
            ->withJson($actor)
            ->withHeader('Content-Type', 'application/activity+json; charset=utf-8');
    }
}
