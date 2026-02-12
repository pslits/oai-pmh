<?php

/**
 * @author    Paul Slits <paul.slits@gmail.com>
 * @copyright (c) 2025 Paul Slits
 * @license   MIT License - https://opensource.org/licenses/MIT
 * @link      https://github.com/pslits/oai-pmh
 * @since     0.1.0
 */

namespace OaiPmh\Domain\ValueObject;

use DOMDocument;
use InvalidArgumentException;

/**
 * Represents a URI that conforms to the XML Schema anyURI type.
 *
 * This value object:
 * - encapsulates a validated URI,
 * - is immutable and compared by value (not identity),
 * - ensures URIs are suitable for XML serialization in OAI-PMH responses.
 *
 * Validation is performed using PHP's filter_var function with the FILTER_VALIDATE_URL flag,
 * which is stricter than XML Schema's anyURI and may reject some valid anyURI values.
 * This is a pragmatic choice for most OAI-PMH use cases, but not a full XML Schema anyURI check.
 */
class AnyUri
{
    private string $uri;

    private const ANYURI_XSD_PATH = __DIR__ . '/../Schema/anyURI.xsd';

    /**
     * Constructs a new AnyUri instance.
     *
     * Validates the provided URI against the anyURI XSD schema.
     *
     * @param string $uri The URI to validate and store.
     */
    public function __construct(string $uri)
    {
        $this->validateAnyUri($uri);
        $this->uri = $uri;
    }

    /**
     * Returns a string representation of the AnyUri object.
     * The string format is presented as:
     * `AnyUri(uri: <uri>)`
     *
     * @return string A string representation of the AnyUri.
     */
    public function __toString(): string
    {
        return sprintf('AnyUri(uri: %s)', $this->uri);
    }

    /**
     * Returns the stored URI.
     *
     * @return string The validated URI.
     */
    public function getValue(): string
    {
        return $this->uri;
    }

    /**
     * Validates the URI against the anyURI XSD schema.
     *
     * Security: Uses textContent to prevent XML injection.
     *
     * @param string $_uri The URI to validate.
     *
     * @throws InvalidArgumentException If the URI is not valid according to the anyURI schema.
     * @return void
     */
    private function validateAnyUri(string $_uri): void
    {
        $dom = new DOMDocument();
        $root = $dom->createElement('root');
        $dom->appendChild($root);

        // Use textContent to safely insert user input (prevents XML injection)
        $_uriElement = $dom->createElement('uri');
        $_uriElement->textContent = $_uri;
        $root->appendChild($_uriElement);

        // Add schema location attribute on root
        $root->setAttributeNS(
            'http://www.w3.org/2001/XMLSchema-instance',
            'xsi:noNamespaceSchemaLocation',
            'anyURI.xsd'
        );

        /**
         * TODO: Not possible to test invalid URI in this context (issue #7)
         */
        try {
            $isValid = @$dom->schemaValidate(self::ANYURI_XSD_PATH);
            if (!$isValid) {
                // @codeCoverageIgnoreStart
                throw new InvalidArgumentException(
                    sprintf("Invalid URI: %s", htmlspecialchars($_uri, ENT_QUOTES, 'UTF-8'))
                );
                // @codeCoverageIgnoreEnd
            }
        } catch (\Exception $e) {
            // Schema validation failed with an exception (e.g., badly formatted content)
            throw new InvalidArgumentException(
                sprintf("Invalid URI: %s", htmlspecialchars($_uri, ENT_QUOTES, 'UTF-8'))
            );
        }
    }

    /**
     * Checks if this AnyUri is equal to another.
     *
     * @param AnyUri $other The other AnyUri to compare against.
     * @return bool True if both URIs are equal, false otherwise.
     */
    public function equals(AnyUri $other): bool
    {
        return $this->getValue() === $other->getValue();
    }
}
