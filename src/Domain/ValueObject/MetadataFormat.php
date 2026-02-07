<?php

/**
 * @author    Paul Slits <paul.slits@gmail.com>
 * @copyright (c) 2025 Paul Slits
 * @license   MIT License - https://opensource.org/licenses/MIT
 * @link      https://github.com/pslits/oai-pmh
 * @since     0.1.0
 */

namespace OaiPmh\Domain\ValueObject;

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
 */
final class MetadataFormat extends ContainerFormat
{
    /**
     * MetadataFormat constructor.
     * This constructor initializes the metadata format with a prefix, namespaces, schema URL, and root tag.
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
        parent::__construct($prefix, $namespaces, $schemaUrl, $rootTag);
    }

    /**
     * Get the OAI-PMH metadata prefix.
     * This prefix is used to identify the metadata format in OAI-PMH requests and responses.
     * @return MetadataPrefix The metadata prefix used in the OAI-PMH protocol.
     */
    public function getPrefix(): MetadataPrefix
    {
        // Always returns non-null for MetadataFormat
        /** @var MetadataPrefix */
        return parent::getPrefix();
    }
}
