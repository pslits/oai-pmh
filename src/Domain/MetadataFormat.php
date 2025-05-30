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
 * A MetadataFormat encapsulates the essential elements of a metadata format:
 * - {@see MetadataPrefix} object,
 * - Array of {@see MetadataNamespaceInterface} objects,
 * - {@see AnyUri} object,
 * - {@see MetadataRootTag} object.
 *
 * This class implements {@see MetadataFormatInterface} but also includes additional
 * domain behavior not defined in the interface. It is the full domain representation
 * of a metadata format and enforces immutability and value-based equality.
 *
 * As a Value Object in the domain layer, it has:
 * - no identity,
 * - immutable state,
 * - equality based on value.
 *
 * This object is intended for internal domain use and supports consistent and
 * predictable handling of metadata format declarations.
 *
 * NOTE: Concerns such as XML serialization, transport formatting, or protocol I/O
 * are handled outside of the domain layer.
 *
 * @author    Paul Slits <paul.slits@gmail.com>
 * @copyright (c) 2025 Paul Slits
 * @license   MIT License - https://opensource.org/licenses/MIT
 * @link      https://github.com/pslits/oai-pmh
 * @since     0.1.0
 */
final class MetadataFormat implements MetadataFormatInterface
{
    private MetadataPrefix $prefix;
    /** @var MetadataNamespaceInterface[] An associative array of XML namespaces used in the format. */
    private array $namespaces;
    private AnyUri $schemaUrl;
    private MetadataRootTag $rootTag;

    /**
     * MetadataFormat constructor.
     * Initializes a new instance of the MetadataFormat class.
     * @param MetadataPrefix $prefix The OAI-PMH metadata prefix.
     * @param MetadataNamespaceInterface[] $namespaces An associative array of XML namespaces used in the format.
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
    }

    /** @inheritdoc */
    public function getPrefix(): MetadataPrefix
    {
        return $this->prefix;
    }

    /** @inheritdoc */
    public function getNamespaces(): array
    {
        return $this->namespaces;
    }

    /** @inheritdoc */
    public function getSchemaUrl(): AnyUri
    {
        return $this->schemaUrl;
    }

    /** @inheritdoc */
    public function getRootTag(): MetadataRootTag
    {
        return $this->rootTag;
    }
}
