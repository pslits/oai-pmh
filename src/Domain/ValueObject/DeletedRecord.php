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
 * Represents the OAI-PMH deletedRecord support as a value object.
 *
 * This value object:
 * - encapsulates a validated deletedRecord value,
 * - is immutable and compared by value (not identity),
 * - ensures only allowed deletedRecord values are accepted.
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
     * @param string $value The deletedRecord value to validate and store.
     * @throws InvalidArgumentException If the value is not allowed.
     */
    public function __construct(string $value)
    {
        $this->validateDeletedRecord($value);
        $this->value = $value;
    }

    /**
     * Returns the deletedRecord value.
     *
     * @return string The deletedRecord value.
     */
    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * Checks if this DeletedRecord is equal to another.
     *
     * @param DeletedRecord $other The other DeletedRecord to compare with.
     * @return bool True if both DeletedRecord objects have the same value, false otherwise.
     */
    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    /**
     * Returns a string representation of the DeletedRecord object.
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
     * @param string $value The deletedRecord value to validate.
     * @throws InvalidArgumentException If the value is not allowed.
     */
    private function validateDeletedRecord(string $value): void
    {
        if (!in_array($value, self::ALLOWED, true)) {
            throw new InvalidArgumentException(
                sprintf(
                    'Invalid deletedRecord value: %s. Allowed values are: %s',
                    $value,
                    implode(', ', self::ALLOWED)
                )
            );
        }
    }
}
