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
use IteratorAggregate;
use Countable;
use ArrayIterator;

/**
 * Represents a collection of Email value objects.
 *
 * This value object:
 * - encapsulates a non-empty, immutable collection of Email objects,
 * - supports iteration and counting,
 * - can be compared by value (order-sensitive).
 *
 * @implements IteratorAggregate<int, Email>
 */
final class EmailCollection implements IteratorAggregate, Countable
{
    /**
     * @var Email[]
     */
    private array $emails = [];

    /**
     * Constructs a new EmailCollection instance.
     *
     * @param Email ...$emails One or more Email objects.
     * @throws InvalidArgumentException If no emails are provided.
     */
    public function __construct(Email ...$emails)
    {
        if (empty($emails)) {
            throw new InvalidArgumentException('EmailCollection cannot be empty.');
        }

        foreach ($emails as $email) {
            if (in_array($email, $this->emails, true)) {
                throw new InvalidArgumentException('EmailCollection cannot contain duplicate emails.');
            }
            $this->emails[] = $email;
        }
    }

    /**
     * @return ArrayIterator<int, Email>
     */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->emails);
    }

    /**
     * @return int
     */
    public function count(): int
    {
        return count($this->emails);
    }

    /**
     * @return Email[]
     */
    public function toArray(): array
    {
        return $this->emails;
    }

    /**
     * Checks if this collection is equal to another (order-insensitive).
     */
    public function equals(self $other): bool
    {
        $thisEmails = array_map(fn ($e) => (string)$e, $this->emails);
        $otherEmails = array_map(fn ($e) => (string)$e, $other->emails);

        sort($thisEmails);
        sort($otherEmails);

        return $thisEmails === $otherEmails;
    }

    /**
     * Returns a string representation of the EmailCollection.
     */
    public function __toString(): string
    {
        $emailsStr = implode(', ', array_map(fn ($e) => (string)$e, $this->emails));
        return sprintf('EmailCollection(emails: %s)', $emailsStr);
    }
}
