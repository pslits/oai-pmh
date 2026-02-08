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
 * According to OAI-PMH 2.0, several elements serve as containers for extensible
 * XML content with specific schemas:
 * - metadata (record-level descriptive metadata)
 * - about (record-level rights/provenance information)
 * - description (repository-level descriptions)
 * - setDescription (set-level descriptions)
 *
 * This abstract base class:
 * - encapsulates common properties: optional prefix, namespaces, schema URL, and root tag,
 * - is immutable and compared by value (not identity),
 * - can be extended for specific protocol containers,
 * - provides shared equality and string representation logic.
 *
 * TODO: Consider refactoring to separate concerns - format specification vs. data container.
 * See GitHub issue for Container refactoring discussion.
 */
abstract class ContainerFormat
{
    protected ?MetadataPrefix $prefix;
    protected MetadataNamespaceCollection $namespaces;
    protected AnyUri $schemaUrl;
    protected MetadataRootTag $rootTag;

    /**
     * Constructs a new ContainerFormat instance.
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
     * Get the OAI-PMH metadata prefix (may be null).
     *
     * The prefix is typically null for about, description, and setDescription containers,
     * as these are embedded rather than independently harvested.
     *
     * @return MetadataPrefix|null The metadata prefix, or null if not applicable.
     */
    public function getPrefix(): ?MetadataPrefix
    {
        return $this->prefix;
    }

    /**
     * Get the XML namespaces used in the format.
     *
     * Contains all namespace declarations required for valid XML serialization.
     *
     * @return MetadataNamespaceCollection The collection of namespaces.
     */
    public function getNamespaces(): MetadataNamespaceCollection
    {
        return $this->namespaces;
    }

    /**
     * Get the schema URL for the format.
     *
     * Points to the XSD schema that defines the structure and validation rules
     * for this container format.
     *
     * @return AnyUri The schema URL.
     */
    public function getSchemaUrl(): AnyUri
    {
        return $this->schemaUrl;
    }

    /**
     * Get the root tag for the format.
     *
     * The root element name used when serializing this container to XML.
     *
     * @return MetadataRootTag The root tag.
     */
    public function getRootTag(): MetadataRootTag
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
            ($this->prefix === null && $otherFormat->prefix === null)
            || ($this->prefix && $otherFormat->prefix && $this->prefix->equals($otherFormat->prefix));

        return (
            $isPrefixEqual
            && $this->namespaces->equals($otherFormat->namespaces)
            && $this->schemaUrl->equals($otherFormat->schemaUrl)
            && $this->rootTag->equals($otherFormat->rootTag)
        );
    }

    /**
     * Returns a string representation of the container.
     * This is useful for debugging and logging.
     * @return string A string representation of the container format.
     * The format is: ClassName(prefix: <prefix>, namespaces: <namespaces>,
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
