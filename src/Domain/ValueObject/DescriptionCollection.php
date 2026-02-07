<?php

namespace OaiPmh\Domain\ValueObject;

use InvalidArgumentException;
use Countable;
use IteratorAggregate;
use ArrayIterator;

/**
 * Represents a collection of Description value objects.
 *
 * This value object:
 * - encapsulates an array of Description objects,
 * - is immutable and compared by value (not identity),
 * - implements Countable and IteratorAggregate for convenient usage.
 *
 * @implements IteratorAggregate<int, Description>
 */
final class DescriptionCollection implements Countable, IteratorAggregate
{
    /** @var Description[] */
    private array $descriptions;

    /**
     * @param Description ...$descriptions
     */
    public function __construct(Description ...$descriptions)
    {
        $this->descriptions = $descriptions;
    }

    /**
     * Returns the number of Description objects in the collection.
     * This allows the collection to be used in count contexts.
     * @return int The count of Description objects.
     */
    public function count(): int
    {
        return count($this->descriptions);
    }

    /**
     * Returns an iterator for the Description objects.
     * This allows the collection to be iterated over using foreach.
     * @return ArrayIterator<int, Description> An iterator for the Description objects.
     */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->descriptions);
    }

    /**
     * Returns the array of Description objects.
     * This allows the collection to be converted to an array if needed.
     * @return Description[] The array of Description objects.
     */
    public function toArray(): array
    {
        return $this->descriptions;
    }

    /**
     * Checks if this DescriptionCollection is equal to another.
     * This method compares the count and each Description object in the collection.
     * @param DescriptionCollection $other The other DescriptionCollection to compare with.
     * @return bool True if both collections have the same count and all Description objects are equal
     *              to their counterparts in the other collection, false otherwise.
     */
    public function equals(self $other): bool
    {
        if ($this->count() !== $other->count()) {
            return false;
        }
        foreach ($this->descriptions as $i => $desc) {
            if (!$desc->equals($other->descriptions[$i])) {
                return false;
            }
        }
        return true;
    }

    /**
     * Returns a string representation of the DescriptionCollection.
     * This is useful for debugging and logging.
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
