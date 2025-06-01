<?php

/**
 * @author    Paul Slits <paul.slits@gmail.com>
 * @copyright (c) 2025 Paul Slits
 * @license   MIT License - https://opensource.org/licenses/MIT
 * @link      https://github.com/pslits/oai-pmh
 * @since     0.1.0
 */

namespace OaiPmh\Domain;

/**
 * Represents a metadata format declaration as defined by the OAI-PMH protocol.
 *
 * This value object:
 * - encapsulates a metadata prefix, a set of XML namespaces, a schema URI, and a root tag,
 * - is immutable and compared by value (not identity),
 * - is used to model supported metadata formats in the OAI-PMH domain.
 *
 * Typically used to represent a single metadata format supported by an OAI-PMH repository.
 * Concerns such as XML serialization, protocol transport, or I/O are handled outside the domain layer.
 *
 * @author    Paul Slits <paul.slits@gmail.com>
 * @copyright (c) 2025 Paul Slits
 * @license   MIT License - https://opensource.org/licenses/MIT
 * @link      https://github.com/pslits/oai-pmh
 * @since     0.1.0
 */
final class MetadataFormat
{
    private MetadataPrefix $prefix;
    private MetadataNamespaceCollection $namespaces;
    private AnyUri $schemaUrl;
    private MetadataRootTag $rootTag;

    /**
     * MetadataFormat constructor.
     * Initializes a new instance of the MetadataFormat class.
     * @param MetadataPrefix $prefix The OAI-PMH metadata prefix.
     * @param MetadataNamespaceCollection $namespaces A collection of MetadataNamespace objects
     * @param AnyUri $schemaUrl The fully qualified URI of the XSD schema defining the format structure.
     * @param MetadataRootTag $rootTag The root element of the XML representation for this format.
     */
    public function __construct(
        MetadataPrefix $prefix,
        MetadataNamespaceCollection $namespaces,
        AnyUri $schemaUrl,
        MetadataRootTag $rootTag
    ) {
        $this->prefix = $prefix;
        $this->namespaces = $namespaces;
        $this->schemaUrl = $schemaUrl;
        $this->rootTag = $rootTag;
    }

    /**
     * Returns a string representation of the MetadataFormat object.
     * The string format is presented as:
     * `MetadataFormat(prefix: <prefix>, namespaces: <namespaces>, schemaUrl: <schemaUrl>, rootTag: <rootTag>)`
     *
     * @return string A string representation of the MetadataFormat object.
     */
    public function __toString(): string
    {
        return sprintf(
            'MetadataFormat(prefix: %s, namespaces: %s, schemaUrl: %s, rootTag: %s)',
            (string)$this->prefix,
            (string)$this->namespaces,
            (string)$this->schemaUrl,
            (string)$this->rootTag
        );
    }

    /**
     * Get the OAI-PMH metadata prefix.
     *
     * @return MetadataPrefix The metadata prefix used in the OAI-PMH protocol.
     */
    public function getPrefix(): MetadataPrefix
    {
        return $this->prefix;
    }

    /**
     * Get the XML namespaces used in the metadata format.
     *
     * @return MetadataNamespaceCollection The collection of namespaces
     */
    public function getNamespaces(): MetadataNamespaceCollection
    {
        return $this->namespaces;
    }

    /**
     * Get the schema URL for the metadata format.
     *
     * @return AnyUri The URI of the XSD schema defining the format structure.
     */
    public function getSchemaUrl(): AnyUri
    {
        return $this->schemaUrl;
    }

    /**
     * Get the root tag for the metadata format.
     *
     * @return MetadataRootTag The name of the root element in the XML representation for this format.
     */
    public function getRootTag(): MetadataRootTag
    {
        return $this->rootTag;
    }

    /**
     * Checks if this MetadataFormat is equal to another.
     *
     * @param MetadataFormat $other The other metadata format to compare against.
     * @return bool True if the two metadata formats are equal, false otherwise.
     */
    public function equals(MetadataFormat $other): bool
    {
        if (
            !$this->prefix->equals($other->getPrefix()) ||
            !$this->namespaces->equals($other->getNamespaces()) ||
            !$this->schemaUrl->equals($other->getSchemaUrl()) ||
            !$this->rootTag->equals($other->getRootTag())
        ) {
            return false;
        }

        return true;
    }
}
