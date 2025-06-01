<?php

/**
 * @copyright (c) 2025 Paul Slits
 * @license   MIT License - https://opensource.org/licenses/MIT
 * @author    Paul Slits <paul.slits@gmail.com>
 * @package   OaiPmh\Domain
 * @link      https://github.com/pslits/oai-pmh
 * @since     0.1.0
 */

namespace OaiPmh\Domain;

use DOMDocument;
use InvalidArgumentException;

/**
 * The AnyUri class represents a URI that conforms to the anyURI type defined
 * in XML Schema. It validates the URI against the anyURI schema and provides
 * access to the validated URI. This class is used to ensure that URIs in OAI-PMH
 * implementations are valid and conform to the expected format.
 *
 * @copyright (c) 2025 Paul Slits
 * @license   MIT License - https://opensource.org/licenses/MIT
 * @author    Paul Slits <paul.slits@gmail.com>
 * @package   OaiPmh\Domain
 * @link      https://github.com/pslits/oai-pmh
 * @since     0.1.0
 */
class AnyUri
{
    private string $uri;

    private const ANYURI_XSD_PATH = __DIR__ . '/Schema/anyURI.xsd';

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

        $_uriElement = $dom->createElement('uri', $_uri);
        $root->appendChild($_uriElement);

        // Add schema location attribute on root
        $root->setAttributeNS(
            'http://www.w3.org/2001/XMLSchema-instance',
            'xsi:noNamespaceSchemaLocation',
            'anyURI.xsd'
        );

        if (!$dom->schemaValidate(self::ANYURI_XSD_PATH)) {
            throw new InvalidArgumentException("Invalid URI: $_uri");
        }
    }

    /**
     * Serializes the AnyUri to a JSON-compatible array.
     *
     * @return array<string, string> An associative array with the URI.
     */
    public function jsonSerialize(): array
    {
        return ['uri' => $this->getValue()];
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
