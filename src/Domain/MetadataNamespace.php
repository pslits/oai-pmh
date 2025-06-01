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
 * Represents a qualified XML namespace for use in OAI-PMH metadata.
 *
 * This value object:
 * - encapsulates a namespace prefix and URI,
 * - is immutable and compared by value (not identity),
 * - is used to qualify XML elements in OAI-PMH responses.
 *
 * Domain concerns such as XML serialization or protocol transport are handled outside this class.
 *
 * @author    Paul Slits <paul.slits@gmail.com>
 * @copyright (c) 2025 Paul Slits
 * @license   MIT License - https://opensource.org/licenses/MIT
 * @link      https://github.com/pslits/oai-pmh
 * @since     0.1.0
 */
final class MetadataNamespace
{
    private NamespacePrefix $prefix;
    private AnyUri $uri;

    /**
     * Constructs a new MetadataNamespace instance.
     *
     * @param NamespacePrefix $prefix The namespace prefix used in XML elements.
     * @param AnyUri $uri The URI associated with the namespace.
     */
    public function __construct(NamespacePrefix $prefix, AnyUri $uri)
    {
        $this->prefix = $prefix;
        $this->uri = $uri;
    }

    /**
     * Returns a string representation of the MetadataNamespace.
     *
     * @return string A string representation of the MetadataNamespace.
     */
    public function __toString(): string
    {
        return sprintf(
            'MetadataNamespace(prefix: %s, uri: %s)',
            $this->prefix->getValue(),
            $this->uri->getValue()
        );
    }

    /**
     * Returns the namespace prefix.
     *
     * @return NamespacePrefix The namespace prefix.
     */
    public function getPrefix(): NamespacePrefix
    {
        return $this->prefix;
    }

    /**
     * Returns the namespace URI.
     *
     * @return AnyUri The URI associated with the namespace.
     */
    public function getUri(): AnyUri
    {
        return $this->uri;
    }

    /**
     * Compares this MetadataNamespace with another for equality.
     *
     * @param MetadataNamespace $other The other MetadataNamespace to compare with.
     * @return bool True if both namespaces are equal, false otherwise.
     */
    public function equals(MetadataNamespace $other): bool
    {
        return $this->prefix->getValue() === $other->getPrefix()->getValue()
            && $this->uri->getValue() === $other->getUri()->getValue();
    }
}
