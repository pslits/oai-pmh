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
use Countable;
use IteratorAggregate;
use ArrayIterator;

/**
 * Represents a collection of Description value objects for OAI-PMH Identify responses.
 *
 * According to OAI-PMH 2.0 specification section 4.2 (Identify), the description element
 * is optional and repeatable, allowing repositories to provide extensible metadata about
 * themselves using community-defined schemas (e.g., oai-identifier, branding, rights).
 *
 * This value object:
 * - encapsulates zero or more Description objects,
 * - is immutable and compared by value (not identity),
 * - implements Countable and IteratorAggregate for convenient usage,
 * - supports OAI-PMH's optional and repeatable description containers.
 *
 * @implements IteratorAggregate<int, Description>
 */
final class DescriptionCollection implements Countable, IteratorAggregate
{
    /** @var Description[] */
    private array $descriptions;

    /**
     * Constructs a new DescriptionCollection instance.
     *
     * Accepts zero or more Description objects, supporting OAI-PMH's optional and
     * repeatable description element requirement.
     *
     * @param Description ...$descriptions Zero or more Description objects for the repository.
     */
    public function __construct(Description ...$descriptions)
    {
        $this->descriptions = $descriptions;
    }

    /**
     * Returns the number of Description objects in the collection.
     *
     * This allows the collection to be used with the count() function,
     * enabling repositories to check how many description elements they have.
     *
     * @return int The count of Description objects in the collection.
     */
    public function count(): int
    {
        return count($this->descriptions);
    }

    /**
     * Returns an iterator for the Description objects.
     *
     * This allows the collection to be iterated over using foreach,
     * enabling easy traversal when serializing to XML.
     *
     * @return ArrayIterator<int, Description> An iterator for the Description objects.
     */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->descriptions);
    }

    /**
     * Returns the array of Description objects.
     *
     * This allows the collection to be converted to an array,
     * useful for serialization or further processing.
     *
     * @return Description[] The array of Description objects.
     */
    public function toArray(): array
    {
        return $this->descriptions;
    }

    /**
     * Checks if this DescriptionCollection is equal to another.
     *
     * Two collections are equal if they have the same count and each Description
     * at the same position is equal (order-sensitive comparison).
     *
     * @param DescriptionCollection $otherDescriptions The other DescriptionCollection to compare with.
     * @return bool True if both collections have the same count and all Description objects are equal
     *              to their counterparts in the other collection, false otherwise.
     */
    public function equals(self $otherDescriptions): bool
    {
        if (!$this->hasSameCount($otherDescriptions)) {
            return false;
        }
        return $this->allDescriptionsEqual($otherDescriptions);
    }

    /**
     * Checks if this collection has the same count as another.
     *
     * @param DescriptionCollection $otherDescriptions The other collection to compare.
     * @return bool True if counts are equal, false otherwise.
     */
    private function hasSameCount(self $otherDescriptions): bool
    {
        return $this->count() === $otherDescriptions->count();
    }

    /**
     * Checks if all descriptions in this collection are equal to their counterparts.
     *
     * @param DescriptionCollection $otherDescriptions The other collection to compare.
     * @return bool True if all descriptions are equal at the same positions, false otherwise.
     */
    private function allDescriptionsEqual(self $otherDescriptions): bool
    {
        foreach ($this->descriptions as $i => $desc) {
            if (!$desc->equals($otherDescriptions->descriptions[$i])) {
                return false;
            }
        }
        return true;
    }

    /**
     * Returns a string representation of the DescriptionCollection.
     *
     * Provides a human-readable representation useful for debugging and logging.
     *
     * @return string A string representation of the DescriptionCollection.
     */
    public function __toString(): string
    {
        return sprintf(
            'DescriptionCollection(%s)',
            implode(', ', array_map('strval', $this->descriptions))
        );
    }
}
