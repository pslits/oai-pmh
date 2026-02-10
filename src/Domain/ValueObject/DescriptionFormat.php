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
 * Represents a description format for OAI-PMH repository descriptions.
 *
 * According to OAI-PMH 2.0 specification section 4.2 (Identify), description elements
 * must reference an XML schema via schemaLocation. Common description formats include:
 * - oai-identifier: Repository identifier scheme
 * - branding: Repository branding and logos
 * - rights: Usage rights and licensing
 * - provenance: Content provenance information
 *
 * This value object extends ContainerFormat and represents description format metadata
 * without the actual description data (which is handled by the Description class).
 *
 * Unlike MetadataFormat, DescriptionFormat has no prefix since descriptions are
 * embedded in Identify responses rather than independently harvested.
 *
 * This class is final and immutable, compared by value (not identity).
 *
 * TODO: After Container refactoring discussion, this class may be merged or refactored.
 * See related GitHub issue for architectural review.
 */
final class DescriptionFormat extends ContainerFormat
{
}
