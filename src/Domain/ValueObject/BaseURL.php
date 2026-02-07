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
 * This value object:
 * - encapsulates the base URL where the repository responds to OAI-PMH requests,
 * - validates that the URL is a valid HTTP or HTTPS URL,
 * - is immutable and compared by value (not identity),
 * - is used in the Identify response to indicate the repository's endpoint.
 *
 * According to the OAI-PMH specification, the baseURL is the URL of the repository that
 * is used to submit requests to the repository. It must be a valid HTTP or HTTPS URL.
 *
 * Domain concerns such as XML serialization or protocol transport are handled outside this class.
 */
final class BaseURL
{
    private string $url;

    /**
     * BaseURL constructor.
     * Initializes a new instance of the BaseURL class.
     *
     * @param string $url The base URL of the OAI-PMH repository.
     * @throws InvalidArgumentException If the URL is invalid or not HTTP/HTTPS.
     */
    public function __construct(string $url)
    {
        $this->validateUrl($url);
        $this->url = $url;
    }

    /**
     * Returns a string representation of the BaseURL object.
     *
     * @return string A string representation in the format: BaseURL(url: <url>)
     */
    public function __toString(): string
    {
        return sprintf('BaseURL(url: %s)', $this->url);
    }

    /**
     * Returns the base URL.
     *
     * @return string The base URL of the repository.
     */
    public function getValue(): string
    {
        return $this->url;
    }

    /**
     * Checks if this BaseURL is equal to another.
     *
     * Two BaseURL instances are considered equal if they have the same URL value.
     *
     * @param BaseURL $other The other BaseURL to compare against.
     * @return bool True if both BaseURLs are equal, false otherwise.
     */
    public function equals(BaseURL $other): bool
    {
        return $this->url === $other->url;
    }

    /**
     * Validates the base URL.
     *
     * @param string $url The URL to validate.
     * @throws InvalidArgumentException If the URL is invalid or not HTTP/HTTPS.
     */
    private function validateUrl(string $url): void
    {
        if (empty($url)) {
            throw new InvalidArgumentException('BaseURL cannot be empty.');
        }

        // Validate that it's a valid URL
        if (filter_var($url, FILTER_VALIDATE_URL) === false) {
            throw new InvalidArgumentException(
                sprintf('Invalid URL format: %s', $url)
            );
        }

        // Validate that it uses HTTP or HTTPS protocol
        $scheme = parse_url($url, PHP_URL_SCHEME);
        if ($scheme === false || $scheme === null) {
            // This should not happen since filter_var already checks for valid URLs, but we check it just in case.
            // @codeCoverageIgnoreStart
            throw new InvalidArgumentException(
                sprintf('Unable to parse URL scheme: %s', $url)
            );
            // @codeCoverageIgnoreEnd
        }
        if (!in_array(strtolower($scheme), ['http', 'https'], true)) {
            throw new InvalidArgumentException(
                sprintf('BaseURL must use HTTP or HTTPS protocol. Given: %s', $url)
            );
        }
    }
}
