<?php
declare(strict_types=1);

namespace HomoChecker\Service\Validator;

use HomoChecker\Contracts\Service\ValidatorService as ValidatorServiceContract;
use HomoChecker\Domain\Validator\ValidationResult;
use Psr\Http\Message\ResponseInterface as Response;

final class DOMValidatorService implements ValidatorServiceContract
{
    /**
     * @param non-empty-string $regex
     */
    public function __construct(private string $regex) {}

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function validate(Response $response): false|ValidationResult
    {
        set_error_handler(fn ($severity, $message, $filename, $line) => throw new \ErrorException($message, 0, $severity, $filename, $line));

        try {
            if (!$body = (string) $response->getBody()) {
                return false;
            }
            $doc = new \DOMDocument();
            $doc->loadHTML($body);
            $xpath = new \DOMXPath($doc);
            $xpath->registerNamespace('php', 'http://php.net/xpath');
            $xpath->registerPhpFunctions();
            $url = $xpath->evaluate('string(//meta[contains(php:functionString("strtolower", @http-equiv), "refresh")]/@content)');
            return preg_match($this->regex, $url) ? ValidationResult::OK : false;
        } catch (\Throwable) {
            return false;
        } finally {
            restore_error_handler();
        }
    }
}
