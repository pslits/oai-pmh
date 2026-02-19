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
 * Base class for OAI-PMH XML container formats.
 *
 * According to OAI-PMH 2.0 specification, several elements serve as containers
 * for extensible XML content with specific schemas:
 * - metadata (section 2.1 and 3.1.1): record-level descriptive metadata
 * - about (section 2.8): record-level rights/provenance information
 * - description (section 4.2): repository-level descriptions in Identify
 * - setDescription (section 2.6): set-level descriptions
 *
 * Each container format requires namespace declarations (section 3.1.1), a schema
 * location, and a root element tag. Metadata containers also require a metadataPrefix
 * for harvesting, while embedded containers (about, description, setDescription) do not.
 *
 * This abstract base class:
 * - encapsulates common properties: optional prefix, namespaces, schema URL, and root tag,
 * - is immutable and compared by value (not identity),
 * - can be extended for specific protocol containers,
 * - provides shared equality and string representation logic.
 *
 * TODO: Consider refactoring to separate concerns - format specification vs. data container.
 * See GitHub issue for Container refactoring discussion.
 *
 * @see http://www.openarchives.org/OAI/openarchivesprotocol.html#XMLResponse
 * @see http://www.openarchives.org/OAI/openarchivesprotocol.html#ListMetadataFormats
 */
abstract class ContainerFormat
{
    protected ?MetadataPrefix $prefix;
    protected MetadataNamespaceCollection $namespaces;
    protected AnyUri $schemaUrl;
    protected MetadataRootTag $rootTag;

    /**
     * ContainerFormat constructor.
     *
     * Initializes an immutable container format with namespace declarations, schema location,
     * root element tag, and optional metadata prefix.
     *
     * @param MetadataPrefix|null $prefix The metadata prefix (required for metadata formats,
     *                                    optional for about/description/setDescription).
     * @param MetadataNamespaceCollection $namespaces The XML namespaces for this format.
     * @param AnyUri $schemaUrl The URL of the XSD schema defining the container structure.
     * @param MetadataRootTag $rootTag The root element tag for the XML container.
     */
    public function __construct(
        ?MetadataPrefix $prefix,
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
     * Returns the OAI-PMH metadata prefix (domain-specific getter).
     *
     * The prefix is typically null for about, description, and setDescription containers,
     * as these are embedded rather than independently harvested.
     *
     * @return MetadataPrefix|null The metadata prefix, or null if not applicable.
     */
    public function getMetadataPrefix(): ?MetadataPrefix
    {
        return $this->prefix;
    }

    /**
     * Returns the XML namespaces used in the format (domain-specific getter).
     *
     * Contains all namespace declarations required for valid XML serialization.
     *
     * @return MetadataNamespaceCollection The collection of namespaces.
     */
    public function getXmlNamespaces(): MetadataNamespaceCollection
    {
        return $this->namespaces;
    }

    /**
     * Returns the schema location URL for the format (domain-specific getter).
     *
     * Points to the XSD schema that defines the structure and validation rules
     * for this container format, following OAI-PMH schemaLocation terminology.
     *
     * @return AnyUri The schema location URL.
     */
    public function getSchemaLocation(): AnyUri
    {
        return $this->schemaUrl;
    }

    /**
     * Returns the XML root tag for the format (domain-specific getter).
     *
     * The root element name used when serializing this container to XML.
     *
     * @return MetadataRootTag The XML root tag.
     */
    public function getXmlRootTag(): MetadataRootTag
    {
        return $this->rootTag;
    }

    /**
     * Checks if this container format is equal to another.
     *
     * Two formats are equal if they have matching prefix, namespaces, schema URL,
     * and root tag.
     *
     * @param ContainerFormat $otherFormat The other container format to compare with.
     * @return bool True if both formats have the same properties, false otherwise.
     */
    public function equals(self $otherFormat): bool
    {
        $isPrefixEqual =
            ($this->prefix === null && $otherFormat->getMetadataPrefix() === null)
            || (
                $this->prefix
                && $otherFormat->getMetadataPrefix()
                && $this->prefix->equals($otherFormat->getMetadataPrefix())
            );

        return (
            $isPrefixEqual
            && $this->namespaces->equals($otherFormat->getXmlNamespaces())
            && $this->schemaUrl->equals($otherFormat->getSchemaLocation())
            && $this->rootTag->equals($otherFormat->getXmlRootTag())
        );
    }

    /**
     * Returns a string representation of the container format.
     *
     * Uses reflection to detect the actual subclass name, ensuring the output
     * accurately represents the concrete implementation (MetadataFormat, AboutFormat, etc.).
     * This is useful for debugging and logging.
     *
     * @return string A string representation in the format:
     *                ClassName(prefix: ..., namespaces: ..., schemaUrl: ..., rootTag: ...)
     */
    public function __toString(): string
    {
        $className = (new \ReflectionClass($this))->getShortName();

        return sprintf(
            '%s(prefix: %s, namespaces: %s, schemaUrl: %s, rootTag: %s)',
            $className,
            $this->prefix ? (string)$this->prefix : 'null',
            (string)$this->namespaces,
            (string)$this->schemaUrl,
            (string)$this->rootTag
        );
    }
}
