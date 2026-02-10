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
 * Represents a collection of Email value objects for OAI-PMH adminEmail elements.
 *
 * According to OAI-PMH 2.0 specification section 4.2 (Identify), the adminEmail
 * element is required and repeatable, containing e-mail addresses of repository
 * administrators. At least one adminEmail must be provided.
 *
 * This value object:
 * - encapsulates a non-empty, immutable collection of unique Email objects,
 * - supports iteration and counting via standard PHP interfaces,
 * - uses order-insensitive equality (set semantics),
 * - prevents duplicate email addresses.
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
     * Per OAI-PMH specification, at least one admin email is required.
     * Duplicate emails are not allowed.
     *
     * @param Email ...$emails One or more Email objects (at least one required).
     * @throws InvalidArgumentException If no emails are provided or duplicates exist.
     */
    public function __construct(Email ...$emails)
    {
        $this->validateNotEmpty($emails);
        $this->validateNoDuplicates($emails);
    }

    /**
     * Validates that the email collection is not empty.
     *
     * Per OAI-PMH requirement, at least one admin email must be provided.
     *
     * @param Email[] $emails The emails to validate.
     * @throws InvalidArgumentException If the array is empty.
     */
    private function validateNotEmpty(array $emails): void
    {
        if (empty($emails)) {
            throw new InvalidArgumentException('EmailCollection cannot be empty.');
        }
    }

    /**
     * Validates that there are no duplicate emails and populates the collection.
     *
     * @param Email[] $emails The emails to validate and add.
     * @throws InvalidArgumentException If duplicate emails are found.
     */
    private function validateNoDuplicates(array $emails): void
    {
        foreach ($emails as $email) {
            if (in_array($email, $this->emails, true)) {
                throw new InvalidArgumentException('EmailCollection cannot contain duplicate emails.');
            }
            $this->emails[] = $email;
        }
    }

    /**
     * Returns an iterator for the Email objects.
     *
     * Enables foreach iteration over the collection.
     *
     * @return ArrayIterator<int, Email> An iterator for the Email objects.
     */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->emails);
    }

    /**
     * Returns the count of Email objects in the collection.
     *
     * Enables the count() function to work with this collection.
     *
     * @return int The number of Email objects.
     */
    public function count(): int
    {
        return count($this->emails);
    }

    /**
     * Returns an array of Email objects.
     *
     * Useful for serialization or further processing.
     *
     * @return Email[] Array of Email objects.
     */
    public function toArray(): array
    {
        return $this->emails;
    }

    /**
     * Checks if this collection is equal to another (order-insensitive).
     *
     * Two collections are equal if they contain the same set of emails,
     * regardless of order (set semantics).
     *
     * @param EmailCollection $otherEmails The other EmailCollection to compare with.
     * @return bool True if both collections contain the same emails, false otherwise.
     */
    public function equals(self $otherEmails): bool
    {
        $thisEmails = array_map(fn ($e) => (string)$e, $this->emails);
        $otherEmailsList = array_map(fn ($e) => (string)$e, $otherEmails->emails);

        sort($thisEmails);
        sort($otherEmailsList);

        return $thisEmails === $otherEmailsList;
    }

    /**
     * Returns a string representation of the EmailCollection.
     *
     * Provides a human-readable representation useful for debugging and logging.
     *
     * @return string A string representation listing all emails.
     */
    public function __toString(): string
    {
        $emailsStr = implode(', ', array_map(fn ($e) => (string)$e, $this->emails));
        return sprintf('EmailCollection(emails: %s)', $emailsStr);
    }
}
