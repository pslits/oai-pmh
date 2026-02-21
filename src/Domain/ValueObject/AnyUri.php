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
 * This immutable value object validates URIs against the anyURI XSD schema, ensuring
 * they are suitable for XML serialization in OAI-PMH responses. Value equality is
 * compared by the URI string content.
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
     * @throws InvalidArgumentException If the URI is not valid according to the anyURI schema.
     */
    public function __construct(string $uri)
    {
        $this->validateAnyUri($uri);
        $this->uri = $uri;
    }

    /**
     * Returns a string representation of the AnyUri object.
     *
     * Format: `AnyUri(uri: <uri>)`
     */
    public function __toString(): string
    {
        return sprintf('AnyUri(uri: %s)', $this->uri);
    }

    /**
     * Returns the stored URI.
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
     * @throws InvalidArgumentException If the URI is not valid according to the anyURI schema.
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
     */
    public function equals(AnyUri $other): bool
    {
        return $this->getValue() === $other->getValue();
    }
}
