<?php
declare(strict_types=1);

namespace HomoChecker\Service\Validator;

use HomoChecker\Contracts\Service\ValidatorService as ValidatorServiceContract;
use HomoChecker\Domain\Validator\ValidationResult;
use Psr\Http\Message\ResponseInterface as Response;

class DOMValidatorService implements ValidatorServiceContract
{
    /**
     * @var string
     */
    protected $regex;

    public function __construct(string $regex)
    {
        $this->regex = $regex;
    }

    /**
     * {@inheritdoc}
     */
    public function validate(Response $response)
    {
        $doc = new \DOMDocument();
        @$doc->loadHTML((string)$response->getBody());
        $xpath = new \DOMXPath($doc);
        $xpath->registerNamespace('php', 'http://php.net/xpath');
        $xpath->registerPhpFunctions();
        $url = $xpath->evaluate('string(//meta[contains(php:functionString("strtolower", @http-equiv), "refresh")]/@content)');

        return preg_match($this->regex, $url) ? ValidationResult::OK : false;
    }
}
