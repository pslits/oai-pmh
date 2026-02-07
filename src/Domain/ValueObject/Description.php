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
 * This value object:
 * - encapsulates a description format, which includes a metadata prefix, namespaces, schema URL, and root tag,
 * - is immutable and compared by value (not identity),
 * - can serialize its data to XML for OAI-PMH responses.
 */
final class Description
{
    private DescriptionFormat $descriptionFormat;
    /** @var array<string, mixed> */
    private array $data;

    /**
     * Constructs a new Description instance.
     * This represents a repository-level description in OAI-PMH.
     * @param DescriptionFormat $descriptionFormat The description format defining how the description is structured.
     * @param array<string, mixed> $data The data associated with this description.
     */
    public function __construct(DescriptionFormat $descriptionFormat, array $data)
    {
        $this->descriptionFormat = $descriptionFormat;
        $this->data = $data;
    }

    /**
     * Returns the description format.
     * This format defines how the description is structured and serialized.
     * @return DescriptionFormat The description format used for this description.
     */
    public function getDescriptionFormat(): DescriptionFormat
    {
        return $this->descriptionFormat;
    }

    /**
     * Returns the data array.
     * This array contains the actual data for the description,
     * @return array<string, mixed> The data associated with this description.
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * Checks if this Description is equal to another.
     * This method compares the description format and data.
     * @param Description $other The other Description to compare with.
     * @return bool True if both Description objects have the same format and data, false otherwise.
     */
    public function equals(self $other): bool
    {
        return $this->descriptionFormat->equals($other->descriptionFormat)
            && $this->data === $other->data;
    }

    /**
     * Returns a string representation of the Description object.
     * This is useful for debugging and logging.
     * @return string A string representation of the Description.
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
