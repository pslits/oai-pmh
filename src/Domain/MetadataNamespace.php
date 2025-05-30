<?php

/**
 * @author    Paul Slits <paul.slits@gmail.com>
 * @copyright (c) 2025 Paul Slits
 * @license   MIT License - https://opensource.org/licenses/MIT
 * @link      https://github.com/pslits/oai-pmh
 * @since     0.1.0
 */

namespace OaiPmh\Domain;

use JsonSerializable;
use PhpParser\Node\Name;

/**
 * Represents an XML namespace used in a metadata format declaration in the OAI-PMH protocol.
 *
 * A MetadataNamespace encapsulates:
 * - the namespace prefix used in XML metadata,
 * - the namespace URI (typically an identifier or schema location).
 *
 * This class defines a single namespace used in the structure of OAI-PMH metadata.
 * It enables consistent association between element prefixes and their meaning,
 * supporting correct XML serialization and interpretation.
 *
 * As a Value Object in the domain layer, it is:
 * - immutable,
 * - compared by value,
 * - free of identity.
 *
 * While aligned with the metadataFormat container of OAI-PMH responses, this object
 * captures a single `xmlns` declaration relevant for metadata element qualification.
 *
 * NOTE: Concerns such as XML serialization or protocol transport are handled outside the domain layer.
 *
 * @author    Paul Slits <paul.slits@gmail.com>
 * @copyright (c) 2025 Paul Slits
 * @license   MIT License - https://opensource.org/licenses/MIT
 * @link      https://github.com/pslits/oai-pmh
 * @since     0.1.0
 */
final class MetadataNamespace implements MetadataNamespaceInterface, JsonSerializable
{
    private NamespacePrefix $namespacePrefix;
    private AnyUri $uri;

    /**
     * Constructs a new MetadataNamespace instance.
     *
     * @param NamespacePrefix $prefix The namespace prefix used in XML elements.
     * @param AnyUri $uri The URI associated with the namespace.
     */
    public function __construct(NamespacePrefix $prefix, AnyUri $uri)
    {
        $this->namespacePrefix = $prefix;
        $this->uri = $uri;
    }

    public function __getValue(): string
    {
        return sprintf(
            '%s: %s',
            $this->namespacePrefix->getValue(),
            $this->uri->getValue()
        );
    }


    /** @inheritDoc */
    public function getPrefix(): NamespacePrefix
    {
        return $this->namespacePrefix;
    }

    /** @inheritDoc */
    public function getUri(): AnyUri
    {
        return $this->uri;
    }

    /**
     * Serializes the MetadataNamespace to JSON format.
     *
     * @return array<string, mixed>
     * Returns an associative array representation of the MetadataNamespace.
     */
    public function jsonSerialize(): array
    {
        $arrayRepresentation = [
            '_class' => self::class,
            'prefix' => $this->getPrefix()->getValue(),
            'uri'    => $this->getUri()->getValue(),
        ];

        return $arrayRepresentation;
    }
}
