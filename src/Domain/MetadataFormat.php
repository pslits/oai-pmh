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

namespace App\Domain\Model;

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
} // End of MetadataFormat class
