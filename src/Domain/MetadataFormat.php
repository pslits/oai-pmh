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

/**
 * Class MetadataFormat
 *
 * Represents a metadata format used in OAI-PMH (Open Archives Initiative Protocol for Metadata Harvesting).
 * This class encapsulates the metadata prefix, XML namespace, schema URL, and XML root element for a specific format.
 */
class MetadataFormat
{
    private MetadataPrefix $prefix;
    /** @var MetadataNamespace[] An associative array of XML namespaces used in the format. */
    private array $namespaces;
    private AnyUri $schemaUrl;
    private MetadataRootTag $rootTag;

    /**
     * MetadataFormat constructor.
     * Initializes a new instance of the MetadataFormat class.
     * @param MetadataPrefix $prefix The OAI-PMH metadata prefix.
     * @param MetadataNamespace[] $namespaces An associative array of XML namespaces used in the format.
     * @param AnyUri $schemaUrl The fully qualified URI of the XSD schema defining the format structure.
     * @param MetadataRootTag $rootTag The root element of the XML representation for this format.
     */
    public function __construct(
        MetadataPrefix $prefix,
        array $namespaces,
        AnyUri $schemaUrl,
        MetadataRootTag $rootTag
    ) {
        $this->prefix = $prefix;
        $this->namespaces = $namespaces;
        $this->schemaUrl = $schemaUrl;
        $this->rootTag = $rootTag;
    } // End of constructor

    /**
     * Get the OAI-PMH metadata prefix.
     *
     * @return string The metadata prefix.
     */
    public function getPrefix(): string
    {
        return $this->prefix->getPrefix();
    } // End of getPrefix

    /**
     * Get the XML namespaces used in this format.
     *
     * @return array<string, string> An associative array of XML namespaces.
     */
    public function getNamespaces(): array
    {
        $namespaces = [];
        foreach ($this->namespaces as $namespace) {
            $namespaces[$namespace->getPrefix()] = $namespace->getUri();
        }
        return $namespaces;
    } // End of getNamespaces

    /**
     * Get the fully qualified URI of the XSD schema defining the format structure.
     *
     * @return string The schema URL.
     */
    public function getSchemaUrl(): string
    {
        return $this->schemaUrl->getUri();
    } // End of getSchemaUrl

    /**
     * Get the root element of the XML representation for this format.
     *
     * @return string The root tag object.
     */
    public function getRootTag(): string
    {
        return $this->rootTag->getRootTag();
    } // End of getRootTag
} // End of MetadataFormat class
