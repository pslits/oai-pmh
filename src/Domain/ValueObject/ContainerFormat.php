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
 * Base class for OAI-PMH XML container formats:
 * - metadata (record-level)
 * - about (record-level)
 * - description (repository-level)
 * - setDescription (set-level)
 *
 * This value object:
 * - encapsulates an optional metadata prefix, namespaces, schema URL, and root tag,
 * - is immutable and compared by value (not identity),
 * - can be extended for specific protocol containers.
 */
abstract class ContainerFormat
{
    protected ?MetadataPrefix $prefix;
    protected MetadataNamespaceCollection $namespaces;
    protected AnyUri $schemaUrl;
    protected MetadataRootTag $rootTag;

    /**
     * @param MetadataPrefix|null $prefix The OAI-PMH metadata prefix (optional for about/description/setDescription).
     * @param MetadataNamespaceCollection $namespaces
     * @param AnyUri $schemaUrl
     * @param MetadataRootTag $rootTag
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
     * This is typically null for about, description, and setDescription containers.
     * @return MetadataPrefix|null The metadata prefix, or null if not applicable.
     */
    public function getPrefix(): ?MetadataPrefix
    {
        return $this->prefix;
    }

    /**
     * Get the XML namespaces used in the format.
     * This collection contains all namespaces required for the XML representation.
     * @return MetadataNamespaceCollection The collection of namespaces.
     */
    public function getNamespaces(): MetadataNamespaceCollection
    {
        return $this->namespaces;
    }

    /**
     * Get the schema URL for the format.
     * This is the fully qualified URI of the XSD schema defining the format structure.
     * @return AnyUri The schema URL.
     */
    public function getSchemaUrl(): AnyUri
    {
        return $this->schemaUrl;
    }

    /**
     * Get the root tag for the format.
     * This is the root element of the XML representation for this format.
     * @return MetadataRootTag The root tag.
     */
    public function getRootTag(): MetadataRootTag
    {
        return $this->rootTag;
    }

    /**
     * Checks if this container is equal to another.
     * This method compares the prefix, namespaces, schema URL, and root tag.
     * @param ContainerFormat $other The other container to compare with.
     * @return bool True if both containers have the same properties, false otherwise.
     */
    public function equals(self $other): bool
    {
        $isPrefixEqual =
            ($this->prefix === null && $other->prefix === null)
            || ($this->prefix && $other->prefix && $this->prefix->equals($other->prefix));

        return (
            $isPrefixEqual
            && $this->namespaces->equals($other->namespaces)
            && $this->schemaUrl->equals($other->schemaUrl)
            && $this->rootTag->equals($other->rootTag)
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
