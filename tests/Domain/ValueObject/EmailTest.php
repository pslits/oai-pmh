<?php

/**
 * @author    Paul Slits <paul.slits@gmail.com>
 * @copyright (c) 2025 Paul Slits
 * @license   MIT License - https://opensource.org/licenses/MIT
 * @link      https://github.com/pslits/oai-pmh
 * @since     0.1.0
 */

namespace OaiPmh\Tests\Domain\ValueObject;

use OaiPmh\Domain\ValueObject\Email;
use PHPUnit\Framework\TestCase;
use InvalidArgumentException;

/**
 * Tests for the Email class.
 *
 * This class contains unit tests for the Email value object,
 * ensuring it behaves correctly as a value object in the OAI-PMH domain.
 */
class EmailTest extends TestCase
{
    /**
     * User Story:
     * As a developer,
     * I want to create an Email with a valid address
     * So that it can be used in OAI-PMH responses.
     */
    public function testCanInstantiateWithValidEmail(): void
    {
        // Given: A valid email address
        $email = 'user@example.com';

        // When: I create an Email instance
        $emailObj = new Email($email);

        // Then: The instance should be created successfully
        $this->assertInstanceOf(Email::class, $emailObj);
        $this->assertSame($email, $emailObj->getValue());
    }

    /**
     * User Story:
     * As a developer,
     * I want Email to throw an exception for an invalid address
     * So that only valid email addresses are accepted.
     */
    public function testThrowsExceptionForInvalidEmail(): void
    {
        // Given: An invalid email address
        $invalidEmail = 'not-an-email';

        // When: I try to create an Email instance with the invalid address
        // Then: It should throw an InvalidArgumentException
        $this->expectException(InvalidArgumentException::class);
        new Email($invalidEmail);
    }

    /**
     * User Story:
     * As a developer,
     * I want to compare two Email instances by value (case-sensitive)
     * So that value equality is supported.
     */
    public function testEqualsReturnsTrueForSameValue(): void
    {
        // Given: Two Email instances with the same value
        $email1 = new Email('user@example.com');
        $email2 = new Email('user@example.com');

        // When: I check if they are equal
        $isEqual = $email1->equals($email2);

        // Then: They should be considered equal
        $this->assertTrue($isEqual, 'Email instances with the same value should be equal.');
    }

    public function testEqualsReturnsFalseForDifferentValue(): void
    {
        // Given: Two Email instances with different values
        $email1 = new Email('user1@example.com');
        $email2 = new Email('user2@example.com');

        // When: I check if they are equal
        $isEqual = $email1->equals($email2);

        // Then: They should not be considered equal
        $this->assertFalse($isEqual, 'Email instances with different values should not be equal.');
    }

    /**
     * User Story:
     * As a developer,
     * I want to get a string representation of Email
     * So that I can log or display it.
     */
    public function testToStringReturnsExpectedFormat(): void
    {
        // Given: An Email instance
        $email = 'user@example.com';
        $emailObj = new Email($email);

        // When: I convert it to a string
        $stringRepresentation = (string)$emailObj;

        // Then: The string representation should match the expected format
        $expected = "Email(email: $email)";
        $this->assertSame(
            $expected,
            $stringRepresentation,
            'String representation of Email should match expected format.'
        );
    }

    /**
     * User Story:
     * As a developer,
     * I want to ensure that Email is immutable
     * So that its internal state cannot be changed after construction.
     *
     * TODO: Immutability in PHP 8.0 and 8.2 are different (see issue #8).
     */
    public function testIsImmutable(): void
    {
        // Given: An Email instance
        $emailObj = new Email('user@example.com');

        // When: I try to modify its properties
        $reflection = new \ReflectionClass($emailObj);

        // Then: All properties should be private, indicating immutability
        foreach ($reflection->getProperties() as $property) {
            $this->assertTrue($property->isPrivate(), "Property {$property->getName()} should be private.");
        }
    }
}
