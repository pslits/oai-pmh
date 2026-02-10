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
 * Represents the OAI-PMH deletedRecord support policy as a value object.
 *
 * According to OAI-PMH 2.0 specification section 3.5 (Deleted Records), repositories
 * must declare how they handle deleted records using one of three values:
 * - 'no': repository does not maintain information about deletions
 * - 'transient': repository maintains deletion info but not persistently/completely
 * - 'persistent': repository maintains complete deletion info with no time limit
 *
 * This value object:
 * - encapsulates a validated deletedRecord value,
 * - is immutable and compared by value (not identity),
 * - ensures only allowed deletedRecord values are accepted,
 * - is required in the OAI-PMH Identify response.
 */
final class DeletedRecord
{
    public const NO = 'no';
    public const TRANSIENT = 'transient';
    public const PERSISTENT = 'persistent';

    private const ALLOWED = [
        self::NO,
        self::TRANSIENT,
        self::PERSISTENT,
    ];

    private string $value;

    /**
     * Constructs a new DeletedRecord instance.
     *
     * Validates that the value is one of the three allowed values defined
     * by the OAI-PMH specification.
     *
     * @param string $deletedRecord The deletedRecord policy value ('no', 'transient', or 'persistent').
     * @throws InvalidArgumentException If the value is not one of the allowed values.
     */
    public function __construct(string $deletedRecord)
    {
        $this->validateDeletedRecord($deletedRecord);
        $this->value = $deletedRecord;
    }

    /**
     * Returns the deletedRecord value.
     *
     * @return string The deletedRecord policy value ('no', 'transient', or 'persistent').
     */
    public function getDeletedRecord(): string
    {
        return $this->value;
    }

    /**
     * Checks if this DeletedRecord is equal to another.
     *
     * Two DeletedRecord instances are equal if they represent the same policy.
     *
     * @param DeletedRecord $otherDeletedRecord The other DeletedRecord to compare with.
     * @return bool True if both DeletedRecord objects have the same value, false otherwise.
     */
    public function equals(self $otherDeletedRecord): bool
    {
        return $this->value === $otherDeletedRecord->value;
    }

    /**
     * Returns a string representation of the DeletedRecord object.
     *
     * Provides a human-readable representation useful for debugging and logging.
     *
     * @return string A string representation of the DeletedRecord.
     */
    public function __toString(): string
    {
        return sprintf('DeletedRecord(value: %s)', $this->value);
    }

    /**
     * Validates the deletedRecord value.
     *
     * Ensures the value is one of the three allowed values defined in the
     * OAI-PMH 2.0 specification.
     *
     * @param string $deletedRecord The deletedRecord value to validate.
     * @throws InvalidArgumentException If the value is not one of: 'no', 'transient', 'persistent'.
     */
    private function validateDeletedRecord(string $deletedRecord): void
    {
        if (!in_array($deletedRecord, self::ALLOWED, true)) {
            throw new InvalidArgumentException(
                sprintf(
                    'Invalid deletedRecord value: %s. Allowed values are: %s',
                    $deletedRecord,
                    implode(', ', self::ALLOWED)
                )
            );
        }
    }
}
