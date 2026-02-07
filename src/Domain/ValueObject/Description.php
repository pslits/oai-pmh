<?php

/**
 * @author    Paul Slits <paul.slits@gmail.com>
 * @copyright (c) 2025 Paul Slits
 * @license   MIT License - https://opensource.org/licenses/MIT
 * @link      https://github.com/pslits/oai-pmh
 * @since     0.1.0
 */

namespace OaiPmh\Domain\ValueObject;

use InvalidArgumentException;

/**
 * Represents an OAI-PMH <description> container as a value object.
 *
 * According to OAI-PMH 2.0 specification section 4.2 (Identify), the description
 * element is an optional and repeatable container that holds data about the repository.
 * Each description must reference an XML schema via schemaLocation and can contain
 * community-defined metadata (e.g., oai-identifier, branding, rights).
 *
 * This value object:
 * - encapsulates a description format (structure) and associated data (content),
 * - is immutable and compared by value (not identity),
 * - supports flexible repository metadata using standard schemas,
 * - can be serialized to XML for OAI-PMH Identify responses.
 */
final class Description
{
    private DescriptionFormat $descriptionFormat;
    /** @var array<string, mixed> */
    private array $data;

    /**
     * Constructs a new Description instance.
     *
     * Combines a description format (defining the XML structure) with the actual
     * data content for that format.
     *
     * @param DescriptionFormat $descriptionFormat The format defining this description's structure.
     * @param array<string, mixed> $data The data associated with this description.
     */
    public function __construct(DescriptionFormat $descriptionFormat, array $data)
    {
        $this->descriptionFormat = $descriptionFormat;
        $this->data = $data;
    }

    /**
     * Returns the description format.
     *
     * The format defines the XML schema, namespaces, and root tag for this description.
     *
     * @return DescriptionFormat The description format used for this description.
     */
    public function getDescriptionFormat(): DescriptionFormat
    {
        return $this->descriptionFormat;
    }

    /**
     * Returns the data array.
     *
     * Contains the actual content for this description, structured according to
     * the associated format's schema.
     *
     * @return array<string, mixed> The data associated with this description.
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * Checks if this Description is equal to another.
     *
     * Two descriptions are equal if they have the same format and the same data.
     *
     * @param Description $otherDescription The other Description to compare with.
     * @return bool True if both Description objects have the same format and data, false otherwise.
     */
    public function equals(self $otherDescription): bool
    {
        return $this->descriptionFormat->equals($otherDescription->descriptionFormat)
            && $this->data === $otherDescription->data;
    }

    /**
     * Returns a string representation of the Description object.
     *
     * Provides a human-readable representation useful for debugging and logging.
     *
     * @return string A string representation of the Description including format and data.
     */
    public function __toString(): string
    {
        return sprintf(
            'Description(descriptionFormat: %s, data: %s)',
            (string)$this->descriptionFormat,
            json_encode($this->data)
        );
    }
}
