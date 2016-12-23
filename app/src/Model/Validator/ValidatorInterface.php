<?php
namespace HomoChecker\Model\Validator;

use Psr\Http\Message\ResponseInterface as Response;

interface ValidatorInterface
{
    public function __invoke(Response $response);
}
