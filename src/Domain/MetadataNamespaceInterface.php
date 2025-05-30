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
 * Describes the contract for an XML namespace used in OAI-PMH metadata formats.
 *
 * A MetadataNamespaceInterface defines access to:
 * - the namespace prefix (used in XML element qualification),
 * - the namespace URI (used for identification or schema resolution).
 *
 * This interface represents the structure of an XML namespace as it applies
 * to metadata format declarations in OAI-PMH. It is intended for use in
 * value objects that contribute to domain correctness and XML qualification.
 *
 * Implementations are expected to be:
 * - immutable,
 * - compared by value,
 * - used within the domain layer only.
 *
 * NOTE: XML serialization or formatting logic is not part of this interface
 * and must be handled outside the domain layer.
 *
 * @author    Paul Slits <paul.slits@gmail.com>
 * @copyright (c) 2025 Paul Slits
 * @license   MIT License - https://opensource.org/licenses/MIT
 * @link      https://github.com/pslits/oai-pmh
 * @since     0.1.0
 */
interface MetadataNamespaceInterface
{
    /**
     * Get the namespace prefix.
     *
     * @return NamespacePrefix
     * This prefix is used in XML elements to identify the namespace.
     */
    public function getPrefix(): NamespacePrefix;

    /**
     * Get the URI associated with the namespace.
     *
     * @return AnyUri
     * This URI is used to resolve the namespace in XML documents.
     */
    public function getUri(): AnyUri;
}
