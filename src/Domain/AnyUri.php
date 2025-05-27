<?php

/**
 * This file is part of the OAI-PMH package.
 * PHP version 8.0
 *
 * @category  OAI-PMH
 * @package   OaiPmh\Domain
 * @author    Paul Slits <paul.slits@gmail.com>
 * @copyright 2025 Paul Slits
 * @license   MIT <https://opensource.org/licenses/MIT>
 * @link      https://github.com/pslits/oai-pmh
 * @since     0.1.0
 */

namespace OaiPmh\Domain;

use DOMDocument;
use InvalidArgumentException;

/**
 * Class AnyUri
 *
 * Represents a URI that conforms to the anyURI type defined in XML Schema.
 *
 * This class validates the URI against the anyURI schema and provides access
 * to the validated URI.
 *
 * @category  OAI-PMH
 * @package   OaiPmh\Domain
 * @author    Paul Slits <paul.slits@gmail.com>
 * @copyright 2025 Paul Slits
 * @license   MIT <https://opensource.org/licenses/MIT>
 * @link      https://github.com/pslits/oai-pmh
 * @since     0.1.0
 */
class AnyUri
{
    private string $_uri;

    private const ANYURI_XSD_PATH = __DIR__ . '/Schema/anyURI.xsd';

    /**
     * AnyUri constructor.
     * Initializes a new instance of the AnyUri class.
     *
     * @param string $uri The URI to validate and store.
     */
    public function __construct(string $uri)
    {
        $this->_validateAnyUri($uri);
        $this->_uri = $uri;
    } // End of constructor

    /**
     * Returns the stored URI.
     *
     * @return string The validated URI.
     */
    public function getUri(): string
    {
        return $this->_uri;
    } // End of getUri

    /**
     * Validates the URI against the anyURI XSD schema.
     *
     * @param string $_uri The URI to validate.
     *
     * @throws InvalidArgumentException If the URI is not valid according to
     *                                  the anyURI schema.
     * @return void
     */
    private function _validateAnyUri(string $_uri): void
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
    } // End of validateAnyUri
} // End of class AnyUri
