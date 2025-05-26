<?php

/**
 * +--------------------------------------------------------------------------+
 * | This file is part of the OAI-PMH package.                                |
 * | @link https://github.com/pslits/oai-pmh                                  |
 * +--------------------------------------------------------------------------+
 * | (c) 2025 Paul Slits <paul.slits@gmail.com>                               |
 * | This source code is licensed under the MIT license found in the LICENSE  |
 * | file in the root directory of this source tree or at the following link: |
 * | @license MIT <https://opensource.org/licenses/MIT>                       |
 * +--------------------------------------------------------------------------+
 */

namespace OaiPmh\Domain;

use DOMDocument;
use InvalidArgumentException;

/**
 * Class AnyUri
 *
 * Represents a URI that conforms to the anyURI type defined in XML Schema.
 * This class validates the URI against the anyURI schema and provides access to the validated URI.
 */
class AnyUri
{
    private string $uri;

    private const ANYURI_XSD_PATH = __DIR__ . '/Schema/anyURI.xsd';

    /**
     * AnyUri constructor.
     * Initializes a new instance of the AnyUri class.
     *
     * @param string $uri The URI to validate and store.
     */
    public function __construct(string $uri)
    {
        $this->validateAnyUri($uri);
        $this->uri = $uri;
    } // End of constructor

    /**
     * Returns the stored URI.
     *
     * @return string The validated URI.
     */
    public function getUri(): string
    {
        return $this->uri;
    } // End of getUri

    /**
     * Validates the URI against the anyURI XSD schema.
     *
     * @param string $uri The URI to validate.
     * @throws InvalidArgumentException If the URI is not valid according to the anyURI schema.
     */
    private function validateAnyUri(string $uri): void
    {
        $dom = new DOMDocument();
        $root = $dom->createElement('root');
        $dom->appendChild($root);

        $uriElement = $dom->createElement('uri', $uri);
        $root->appendChild($uriElement);

        // Add schema location attribute on root
        $root->setAttributeNS(
            'http://www.w3.org/2001/XMLSchema-instance',
            'xsi:noNamespaceSchemaLocation',
            'anyURI.xsd'
        );

        if (!$dom->schemaValidate(self::ANYURI_XSD_PATH)) {
            throw new InvalidArgumentException("Invalid URI: $uri");
        }
    } // End of validateAnyUri
} // End of class AnyUri
