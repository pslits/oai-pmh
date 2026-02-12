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
 * According to OAI-PMH 2.0 specification section 4.2 (Identify), the adminEmail
 * element contains the e-mail address of a repository administrator. This element
 * may be repeated to provide multiple contacts.
 *
 * This value object:
 * - encapsulates a validated email address per RFC 5322,
 * - is immutable and compared by value (not identity),
 * - ensures only valid email addresses are accepted,
 * - is used in the required adminEmail element of Identify responses.
 */
final class Email
{
    private string $email;

    /**
     * Constructs a new Email instance.
     *
     * Validates the email address format using PHP's filter_var with FILTER_VALIDATE_EMAIL,
     * which provides RFC 5322 compliant validation.
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
     *
     * @return string The validated email address.
     */
    public function getValue(): string
    {
        return $this->email;
    }

    /**
     * Checks if this Email is equal to another.
     *
     * Two Email instances are equal if they have the same email address.
     * Comparison is case-insensitive as most email systems treat addresses
     * case-insensitively in practice (though RFC 5321 local-part is technically
     * case-sensitive).
     *
     * @param Email $otherEmail The other Email to compare with.
     * @return bool True if both Email objects have the same address (case-insensitive), false otherwise.
     */
    public function equals(self $otherEmail): bool
    {
        return strtolower($this->email) === strtolower($otherEmail->email);
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
