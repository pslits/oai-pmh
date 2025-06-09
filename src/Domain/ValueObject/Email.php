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
 * Represents an email address as a value object.
 *
 * This value object:
 * - encapsulates a validated email address,
 * - is immutable and compared by value (not identity),
 * - ensures only valid email addresses are accepted.
 */
final class Email
{
    private string $email;

    /**
     * Constructs a new Email instance.
     *
     * @param string $email The email address to validate and store.
     * @throws InvalidArgumentException If the email address is not valid.
     */
    public function __construct(string $email)
    {
        $this->validateEmail($email);
        $this->email = $email;
    }

    /**
     * Returns the email address.
     */
    public function getValue(): string
    {
        return $this->email;
    }

    /**
     * Checks if this Email is equal to another.
     */
    public function equals(self $other): bool
    {
        return $this->email === $other->email;
    }

    /**
     * Returns a string representation of the Email object.
     */
    public function __toString(): string
    {
        return sprintf('Email(email: %s)', $this->email);
    }

    /**
     * Validates the email address.
     *
     * @throws InvalidArgumentException If the email address is not valid.
     */
    private function validateEmail(string $email): void
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException(
                sprintf('Invalid email address: %s', $email)
            );
        }
    }
}
