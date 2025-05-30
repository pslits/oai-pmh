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
}
