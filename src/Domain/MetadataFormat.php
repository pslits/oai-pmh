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
 * Class MetadataFormat
 *
 * Represents a metadata format used in OAI-PMH (Open Archives Initiative Protocol for Metadata Harvesting).
 * This class encapsulates the metadata prefix, XML namespace, schema URL, and XML root element for a specific format.
 */
class MetadataFormat
{
    private string $prefix;
    private string $namespace;
    private string $schemaUrl;
    private string $xmlRootElement;

    private const ANYURI_XSD_PATH = __DIR__ . '/Schema/anyURI.xsd';
    private const PREFIX_PATTERN = '/^[A-Za-z0-9\-_\.!~\*\'\(\)]+$/';

    /**
     * MetadataFormat constructor.
     * Initializes a new instance of the MetadataFormat class.
     *
     * @param string $prefix OAI-PMH metadata prefix (e.g., oai_dc) used in protocol verbs.
     * @param string $namespace XML namespace associated with this format.
     * @param string $schemaUrl Fully qualified URI of the XSD schema defining the format structure.
     * @param string $xmlRootElement The root element of the XML representation for this format.
     */
    public function __construct(
        string $prefix,
        string $namespace,
        string $schemaUrl,
        string $xmlRootElement
    ) {
        $this->validatePrefix($prefix);
        $this->validateAnyUri($namespace);
        $this->validateAnyUri($schemaUrl);


        $this->prefix = $prefix;
        $this->namespace = $namespace;
        $this->schemaUrl = $schemaUrl;
        $this->xmlRootElement = $xmlRootElement;
    } // End of constructor

    /**
     * Get the OAI-PMH metadata prefix.
     *
     * @return string The metadata prefix.
     */
    public function getPrefix(): string
    {
        return $this->prefix;
    } // End of getPrefix

    /**
     * Get the XML namespace associated with this format.
     *
     * @return string The XML namespace.
     */
    public function getNamespace(): string
    {
        return $this->namespace;
    } // End of getNamespace

    /**
     * Get the fully qualified URI of the XSD schema defining the format structure.
     *
     * @return string The schema URL.
     */
    public function getSchemaUrl(): string
    {
        return $this->schemaUrl;
    } // End of getSchemaUrl

    /**
     * Get the root element of the XML representation for this format.
     *
     * @return string The XML root element.
     */
    public function getXmlRootElement(): string
    {
        return $this->xmlRootElement;
    } // End of getXmlRootElement

    /**
     * Validate the OAI-PMH metadata prefix.
     *
     * @param string $prefix The metadata prefix to validate.
     * @throws \InvalidArgumentException If the prefix does not match the required pattern.
     */
    private function validatePrefix(string $prefix): void
    {
        if (!preg_match(self::PREFIX_PATTERN, $prefix)) {
            throw new \InvalidArgumentException(
                sprintf('Invalid OAI-PMH metadata prefix: "%s".', $prefix)
            );
        }
    } // End of validatePrefix

    /**
     * Validate that a string is a valid anyURI per XML Schema (xs:anyURI)
     *
     * @param string $uri
     * @throws InvalidArgumentException if not a valid anyURI
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
    }

} // End of MetadataFormat class
