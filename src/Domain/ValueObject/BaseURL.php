<?php

/**
 * @author    Paul Slits <paul.slits@gmail.com>
 * @copyright (c) 2025 Paul Slits
 * @license   MIT License - https://opensource.org/licenses/MIT
 * @link      https://github.com/pslits/oai-pmh
 * @since     0.1.0
 */

namespace OaiPmh\Domain\ValueObject;

use InvalidArgumentException;

/**
 * Represents the base URL of an OAI-PMH repository.
 *
 * According to OAI-PMH 2.0 specification section 4.2 (Identify), the baseURL is
 * the URL used to submit OAI-PMH requests to the repository. It must be an HTTP
 * or HTTPS URL that accepts OAI-PMH protocol requests.
 *
 * This immutable value object validates that the URL is a valid HTTP or HTTPS endpoint
 * and is used in the Identify response to indicate the repository's protocol endpoint.
 * Value equality is compared by URL string content.
 *
 * Note: BaseURL is not extended from AnyUri because it has stricter requirements
 * (HTTP/HTTPS only) compared to generic URIs, providing clearer type distinction.
 */
final class BaseURL
{
    private string $url;

    /**
     * Constructs a new BaseURL instance.
     *
     * Validates that the URL is a valid HTTP or HTTPS endpoint as required by OAI-PMH.
     *
     * @throws InvalidArgumentException If the URL is invalid or not HTTP/HTTPS.
     */
    public function __construct(string $baseUrl)
    {
        $this->validateUrl($baseUrl);
        $this->url = $baseUrl;
    }

    /**
     * Returns a string representation of the BaseURL object.
     *
     * Format: `BaseURL(url: <url>)`
     */
    public function __toString(): string
    {
        return sprintf('BaseURL(url: %s)', $this->url);
    }

    /**
     * Returns the base URL.
     *
     * This is the URL where OAI-PMH requests should be submitted to access
     * this repository's protocol endpoint.
     */
    public function getBaseUrl(): string
    {
        return $this->url;
    }

    /**
     * Checks if this BaseURL is equal to another.
     *
     * Comparison is case-sensitive and does not perform URL normalization.
     */
    public function equals(BaseURL $otherBaseUrl): bool
    {
        return $this->url === $otherBaseUrl->url;
    }

    /**
     * Validates the base URL.
     *
     * Ensures the URL meets OAI-PMH requirements:
     * - Non-empty
     * - Valid URL format
     * - HTTP or HTTPS protocol only
     *
     * @throws InvalidArgumentException If the URL is invalid or not HTTP/HTTPS.
     */
    private function validateUrl(string $baseUrl): void
    {
        $this->validateNotEmpty($baseUrl);
        $this->validateUrlFormat($baseUrl);
        $this->validateHttpProtocol($baseUrl);
    }

    /**
     * Validates that the URL is not empty.
     *
     * @throws InvalidArgumentException If the URL is empty.
     */
    private function validateNotEmpty(string $baseUrl): void
    {
        if (empty($baseUrl)) {
            throw new InvalidArgumentException('BaseURL cannot be empty.');
        }
    }

    /**
     * Validates that the URL has a valid format.
     *
     * @throws InvalidArgumentException If the URL format is invalid.
     */
    private function validateUrlFormat(string $baseUrl): void
    {
        if (filter_var($baseUrl, FILTER_VALIDATE_URL) === false) {
            throw new InvalidArgumentException(
                sprintf('Invalid URL format: %s', $baseUrl)
            );
        }
    }

    /**
     * Validates that the URL uses HTTP or HTTPS protocol.
     *
     * Per OAI-PMH specification, only HTTP and HTTPS protocols are allowed
     * for repository base URLs.
     *
     * @throws InvalidArgumentException If the URL does not use HTTP or HTTPS.
     */
    private function validateHttpProtocol(string $baseUrl): void
    {
        $scheme = parse_url($baseUrl, PHP_URL_SCHEME);
        if ($scheme === false || $scheme === null) {
            // This should not happen since filter_var already checks for valid URLs, but we check it just in case.
            // @codeCoverageIgnoreStart
            throw new InvalidArgumentException(
                sprintf('Unable to parse URL scheme: %s', $baseUrl)
            );
            // @codeCoverageIgnoreEnd
        }
        if (!in_array(strtolower($scheme), ['http', 'https'], true)) {
            throw new InvalidArgumentException(
                sprintf('BaseURL must use HTTP or HTTPS protocol. Given: %s', $baseUrl)
            );
        }
    }
}
