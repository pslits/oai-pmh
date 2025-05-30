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
 * Describes a metadata format as defined by the OAI-PMH protocol.
 *
 * A MetadataFormat encapsulates the essential elements of a metadata format declaration:
 * - {@see MetadataPrefix} object,
 * - Array of {@see MetadataNamespaceInterface} objects,
 * - {@see AnyUri} object,
 * - {@see MetadataRootTag} object.
 *
 * It serves as a Value Object in the domain layer, meaning:
 * - It is immutable,
 * - It is compared by value,
 * - It has no identity.
 *
 * This concept is part of the OAI-PMH domain model and supports consistent and
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
interface MetadataFormatInterface
{
    /**
     * Get the OAI-PMH metadata prefix.
     *
     * @return MetadataPrefix The qualified metadata prefix used in the OAI-PMH protocol.
     * The prefix is used to identify the metadata format in OAI-PMH requests and responses.
     * For example, "oai_dc" for the Dublin Core metadata format.
     */
    public function getPrefix(): MetadataPrefix;

    /**
     * Get the XML namespaces used in the metadata format.
     *
     * @return array<MetadataNamespaceInterface> The qualified XML namespaces associated with this metadata format.
     * Each namespace is represented by a MetadataNamespaceInterface object, which includes
     * the namespace prefix and URI. These namespaces are used to qualify XML elements in the metadata format.
     */
    public function getNamespaces(): array;

    /**
     * Get the schema URL for the metadata format.
     *
     * @return AnyUri The qualified URI of the XSD schema defining the format structure.
     * This URL points to the schema that describes the XML structure and elements used in the metadata format.
     * It is used by harvesters to validate the metadata format.
     */
    public function getSchemaUrl(): AnyUri;

    /**
     * Get the root tag for the metadata format.
     *
     * @return MetadataRootTag The qualified name of the root element in the XML representation for this format.
     * This is the top-level element that encapsulates all metadata elements in the XML document.
     * It is used to identify the metadata format in XML responses.
     * @see MetadataRootTag
     */
    public function getRootTag(): MetadataRootTag;
}
