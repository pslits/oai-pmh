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
use OaiPmh\Domain\ValueObject\EmailCollection;
use PHPUnit\Framework\TestCase;
use InvalidArgumentException;

class EmailCollectionTest extends TestCase
{
    /**
     * User Story:
     * As a developer,
     * I want to create an EmailCollection with one or more Email objects
     * So that I can represent a set of email addresses.
     */
    public function testCanInstantiateWithEmails(): void
    {
        // Given: Two valid Email objects
        $email1 = new Email('user1@example.com');
        $email2 = new Email('user2@example.com');

        // When: I create an EmailCollection with these emails
        $collection = new EmailCollection($email1, $email2);

        // Then: The collection should be created successfully
        $this->assertInstanceOf(EmailCollection::class, $collection);
        $this->assertCount(2, $collection);
        $this->assertSame([$email1, $email2], $collection->toArray());
    }

    /**
     * User Story:
     * As a developer,
     * I want EmailCollection to throw an exception if empty
     * So that there is always at least one email.
     */
    public function testThrowsExceptionForEmptyCollection(): void
    {
        // Given: No emails provided
        // When: I try to create an EmailCollection without any emails
        // Then: It should throw an InvalidArgumentException
        $this->expectException(InvalidArgumentException::class);
        new EmailCollection();
    }

    /**
     * User Story:
     * As a developer,
     * I want EmailCollection to throw an exception for duplicate emails
     * So that each email in the collection is unique.
     */
    public function testThrowsExceptionForDuplicateEmails(): void
    {
        // Given: Two Email objects with the same address
        $email = new Email('user1@example.com');

        // When: I try to create an EmailCollection with duplicate emails
        $this->expectException(InvalidArgumentException::class);
        new EmailCollection($email, $email);
    }

    /**
     * User Story:
     * As a developer,
     * I want to iterate over the EmailCollection
     * So that I can access each email in the collection.
     */
    public function testCanIterateOverCollection(): void
    {
        // Given: An EmailCollection with two Email objects
        $email1 = new Email('user1@example.com');
        $email2 = new Email('user2@example.com');
        $collection = new EmailCollection($email1, $email2);

        // When: I iterate over the collection
        // Then: I should be able to access each email
        foreach ($collection as $email) {
            $this->assertInstanceOf(Email::class, $email, 'Each item in EmailCollection should be an Email object.');
        }
    }

    /**
     * User Story:
     * As a developer,
     * I want to compare two collections for value equality (order-sensitive)
     * So that collections with the same emails in the same order are equal.
     */
    public function testEqualsReturnsTrueForSameEmailsAndOrder(): void
    {
        // Given: Two Email objects with the same addresses
        $email1 = new Email('user1@example.com');
        $email2 = new Email('user2@example.com');
        $col1 = new EmailCollection($email1, $email2);
        $col2 = new EmailCollection($email1, $email2);

        // When: I compare the two collections
        $isEqual = $col1->equals($col2);

        // Then: They should be considered equal
        $this->assertTrue(
            $isEqual,
            'EmailCollection instances with the same emails in the same order should be equal.'
        );
    }

    public function testEqualsReturnsTrueForDifferentOrder(): void
    {
        // Given: Two Email objects with the same addresses but in different order
        $email1 = new Email('user1@example.com');
        $email2 = new Email('user2@example.com');
        $col1 = new EmailCollection($email1, $email2);
        $col2 = new EmailCollection($email2, $email1);

        // When: I compare the two collections
        $isEqual = $col1->equals($col2);

        // Then: They should be considered equal
        $this->assertTrue(
            $isEqual,
            'EmailCollection instances with the same emails in different order should be equal.'
        );
    }

    public function testEqualsReturnsFalseForDifferentEmails(): void
    {
        // Given: Two Email objects with different addresses
        $email1 = new Email('user1@example.com');
        $email2 = new Email('user2@example.com');
        $email3 = new Email('user3@example.com');
        $col1 = new EmailCollection($email1, $email2);
        $col2 = new EmailCollection($email1, $email3);

        // When: I compare the two collections
        $isEqual = $col1->equals($col2);

        // Then: They should not be considered equal
        $this->assertFalse($isEqual, 'EmailCollection instances with different emails should not be equal.');
    }

    /**
     * User Story:
     * As a developer,
     * I want to get a string representation of the collection
     * So that I can log or display it.
     */
    public function testToStringReturnsExpectedFormat(): void
    {
        // Given: An EmailCollection with two Email objects
        $email1 = new Email('user1@example.com');
        $email2 = new Email('user2@example.com');
        $collection = new EmailCollection($email1, $email2);

        $expected = sprintf(
            'EmailCollection(emails: %s, %s)',
            (string)$email1,
            (string)$email2
        );
        $this->assertSame($expected, (string)$collection);
    }

    /**
     * User Story:
     * As a developer,
     * I want to ensure the collection is immutable
     * So that its internal state cannot be changed after construction.
     *
     * TODO: Immutability in PHP 8.0 and 8.2 are different (see issue #8).
     */
    public function testCollectionIsImmutable(): void
    {
        $collection = new EmailCollection(new Email('user@example.com'));
        $reflection = new \ReflectionClass($collection);
        foreach ($reflection->getProperties() as $property) {
            $this->assertTrue($property->isPrivate(), "Property {$property->getName()} should be private.");
        }
    }
}
