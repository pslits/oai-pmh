<?php

/**
 * @author    Paul Slits <paul.slits@gmail.com>
 * @copyright (c) 2025 Paul Slits
 * @license   MIT License - https://opensource.org/licenses/MIT
 * @link      https://github.com/pslits/oai-pmh
 * @since     0.1.0
 */

namespace OaiPmh\Tests\Domain\ValueObject;

use InvalidArgumentException;
use OaiPmh\Domain\ValueObject\RepositoryName;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * Unit tests for the RepositoryName value object in the OAI-PMH domain.
 *
 * Ensures correct instantiation, validation, immutability, value equality, and string representation.
 */
class RepositoryNameTest extends TestCase
{
    /**
     * User Story:
     * As a developer,
     * I want to create a RepositoryName with a valid name
     * So that it can be used in OAI-PMH Identify responses.
     */
    public function testCanInstantiateWithValidName(): void
    {
        // Given: A valid repository name
        $name = 'Digital Library Repository';

        // When: I create a RepositoryName instance
        $repositoryName = new RepositoryName($name);

        // Then: The object should be created without error
        $this->assertInstanceOf(RepositoryName::class, $repositoryName);
        $this->assertSame($name, $repositoryName->getRepositoryName());
    }

    /**
     * User Story:
     * As a developer,
     * I want to create a RepositoryName with a simple name
     * So that repositories with short names are supported.
     */
    public function testCanInstantiateWithSimpleName(): void
    {
        // Given: A simple repository name
        $name = 'MyRepo';

        // When: I create a RepositoryName instance
        $repositoryName = new RepositoryName($name);

        // Then: The object should be created without error
        $this->assertInstanceOf(RepositoryName::class, $repositoryName);
        $this->assertSame($name, $repositoryName->getRepositoryName());
    }

    /**
     * User Story:
     * As a developer,
     * I want RepositoryName to accept names with special characters
     * So that diverse repository naming conventions are supported.
     */
    public function testCanInstantiateWithSpecialCharacters(): void
    {
        // Given: A repository name with special characters
        $name = 'University Library - Digital Archive (2025)';

        // When: I create a RepositoryName instance
        $repositoryName = new RepositoryName($name);

        // Then: The object should be created without error
        $this->assertInstanceOf(RepositoryName::class, $repositoryName);
        $this->assertSame($name, $repositoryName->getRepositoryName());
    }

    /**
     * User Story:
     * As a developer,
     * I want RepositoryName to accept names with Unicode characters
     * So that international repository names are supported.
     */
    public function testCanInstantiateWithUnicodeCharacters(): void
    {
        // Given: A repository name with Unicode characters
        $name = 'Bibliothèque Numérique 数字图书馆';

        // When: I create a RepositoryName instance
        $repositoryName = new RepositoryName($name);

        // Then: The object should be created without error
        $this->assertInstanceOf(RepositoryName::class, $repositoryName);
        $this->assertSame($name, $repositoryName->getRepositoryName());
    }

    /**
     * User Story:
     * As a developer,
     * I want RepositoryName to reject empty strings
     * So that invalid repository names are prevented.
     */
    public function testThrowsExceptionForEmptyString(): void
    {
        // Given: An empty string
        $name = '';

        // Then: It should throw an exception
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('RepositoryName cannot be empty or contain only whitespace.');

        // When: I try to create a RepositoryName instance
        new RepositoryName($name);
    }

    /**
     * User Story:
     * As a developer,
     * I want RepositoryName to reject strings with only whitespace
     * So that meaningless names are prevented.
     */
    public function testThrowsExceptionForWhitespaceOnly(): void
    {
        // Given: A string with only whitespace
        $name = '   ';

        // Then: It should throw an exception
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('RepositoryName cannot be empty or contain only whitespace.');

        // When: I try to create a RepositoryName instance
        new RepositoryName($name);
    }

    /**
     * User Story:
     * As a developer,
     * I want RepositoryName to reject strings with only tabs and newlines
     * So that invalid names are prevented.
     */
    public function testThrowsExceptionForTabsAndNewlines(): void
    {
        // Given: A string with only tabs and newlines
        $name = "\t\n\r";

        // Then: It should throw an exception
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('RepositoryName cannot be empty or contain only whitespace.');

        // When: I try to create a RepositoryName instance
        new RepositoryName($name);
    }

    /**
     * User Story:
     * As a developer,
     * I want RepositoryName to accept names with leading/trailing spaces
     * So that whitespace doesn't affect validity (it's trimmed during validation).
     */
    public function testCanInstantiateWithLeadingAndTrailingSpaces(): void
    {
        // Given: A repository name with leading and trailing spaces
        $name = '  Valid Repository Name  ';

        // When: I create a RepositoryName instance
        $repositoryName = new RepositoryName($name);

        // Then: The object should be created without error
        // Note: The original value is preserved, only validation uses trim()
        $this->assertInstanceOf(RepositoryName::class, $repositoryName);
        $this->assertSame($name, $repositoryName->getRepositoryName());
    }

    /**
     * User Story:
     * As a developer,
     * I want two RepositoryName instances with the same value to be considered equal
     * So that value equality is supported.
     */
    public function testEqualsReturnsTrueForSameValue(): void
    {
        // Given: Two RepositoryName instances with the same value
        $name = 'Digital Library';
        $repositoryName1 = new RepositoryName($name);
        $repositoryName2 = new RepositoryName($name);

        // When: I check if they are equal
        $isEqual = $repositoryName1->equals($repositoryName2);

        // Then: They should be considered equal
        $this->assertTrue($isEqual, 'RepositoryName instances with the same value should be equal.');
    }

    /**
     * User Story:
     * As a developer,
     * I want two RepositoryName instances with different values to not be equal
     * So that I can distinguish between different repositories.
     */
    public function testEqualsReturnsFalseForDifferentValue(): void
    {
        // Given: Two RepositoryName instances with different values
        $repositoryName1 = new RepositoryName('Digital Library');
        $repositoryName2 = new RepositoryName('University Archive');

        // When: I check if they are equal
        $isEqual = $repositoryName1->equals($repositoryName2);

        // Then: They should not be considered equal
        $this->assertFalse($isEqual, 'RepositoryName instances with different values should not be equal.');
    }

    /**
     * User Story:
     * As a developer,
     * I want to get a string representation of RepositoryName
     * So that I can log or display it for debugging.
     */
    public function testToStringReturnsExpectedFormat(): void
    {
        // Given: A RepositoryName instance
        $name = 'Digital Library Repository';
        $repositoryName = new RepositoryName($name);

        // When: I convert it to a string
        $stringRepresentation = (string)$repositoryName;

        // Then: The string representation should match the expected format
        $expected = "RepositoryName(name: $name)";
        $this->assertSame(
            $expected,
            $stringRepresentation,
            'String representation of RepositoryName should match expected format.'
        );
    }

    /**
     * User Story:
     * As a developer,
     * I want to ensure that RepositoryName is immutable
     * So that its internal state cannot be changed after construction.
     *
     * TODO: Immutability in PHP 8.0 and 8.2 are different (see issue #8).
     */
    public function testIsImmutable(): void
    {
        // Given: A RepositoryName instance
        $repositoryName = new RepositoryName('Digital Library');

        // When: I use reflection to inspect its properties
        $reflection = new ReflectionClass($repositoryName);

        // Then: All properties should be private
        foreach ($reflection->getProperties() as $property) {
            $this->assertTrue($property->isPrivate(), "Property {$property->getName()} should be private.");
        }
    }

    /**
     * User Story:
     * As a developer,
     * I want RepositoryName to handle long names
     * So that descriptive repository names are supported.
     */
    public function testCanInstantiateWithLongName(): void
    {
        // Given: A long repository name
        $name = 'The International Digital Library and Archives Repository ' .
                'for Academic Research and Historical Documents';

        // When: I create a RepositoryName instance
        $repositoryName = new RepositoryName($name);

        // Then: The object should be created without error
        $this->assertInstanceOf(RepositoryName::class, $repositoryName);
        $this->assertSame($name, $repositoryName->getRepositoryName());
    }

    /**
     * User Story:
     * As a developer,
     * I want RepositoryName to handle names with numbers
     * So that repositories with version numbers or years are supported.
     */
    public function testCanInstantiateWithNumbers(): void
    {
        // Given: A repository name with numbers
        $name = 'Digital Library v2.0 - 2025 Edition';

        // When: I create a RepositoryName instance
        $repositoryName = new RepositoryName($name);

        // Then: The object should be created without error
        $this->assertInstanceOf(RepositoryName::class, $repositoryName);
        $this->assertSame($name, $repositoryName->getRepositoryName());
    }
}
