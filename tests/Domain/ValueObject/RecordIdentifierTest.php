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
use OaiPmh\Domain\ValueObject\RecordIdentifier;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for RecordIdentifier value object.
 *
 * Tests the OAI-PMH record identifier implementation following the specification
 * section 2.4 (Unique Identifier). Record identifiers must be unique URIs that
 * identify items in the repository.
 *
 * @covers \OaiPmh\Domain\ValueObject\RecordIdentifier
 */
final class RecordIdentifierTest extends TestCase
{
    /**
     * @test
     * Given a valid OAI identifier
     * When I create a RecordIdentifier
     * Then it should be created successfully
     */
    public function testConstructWithValidOaiIdentifier(): void
    {
        // Given
        $identifier = 'oai:example.org:12345';

        // When
        $recordId = new RecordIdentifier($identifier);

        // Then
        $this->assertInstanceOf(RecordIdentifier::class, $recordId);
        $this->assertSame($identifier, $recordId->getIdentifier());
        $this->assertSame($identifier, $recordId->getValue());
    }

    /**
     * @test
     * Given a valid OAI identifier with path
     * When I create a RecordIdentifier
     * Then it should be created successfully
     */
    public function testConstructWithValidOaiIdentifierWithPath(): void
    {
        // Given
        $identifier = 'oai:arXiv.org:cs/0112017';

        // When
        $recordId = new RecordIdentifier($identifier);

        // Then
        $this->assertSame($identifier, $recordId->getIdentifier());
    }

    /**
     * @test
     * Given a valid HTTP URI identifier
     * When I create a RecordIdentifier
     * Then it should be created successfully
     */
    public function testConstructWithValidHttpUri(): void
    {
        // Given
        $identifier = 'http://hdl.handle.net/10222/12345';

        // When
        $recordId = new RecordIdentifier($identifier);

        // Then
        $this->assertSame($identifier, $recordId->getIdentifier());
    }

    /**
     * @test
     * Given a valid URN identifier
     * When I create a RecordIdentifier
     * Then it should be created successfully
     */
    public function testConstructWithValidUrn(): void
    {
        // Given
        $identifier = 'urn:isbn:0451450523';

        // When
        $recordId = new RecordIdentifier($identifier);

        // Then
        $this->assertSame($identifier, $recordId->getIdentifier());
    }

    /**
     * @test
     * Given an empty string
     * When I create a RecordIdentifier
     * Then it should throw InvalidArgumentException
     */
    public function testConstructWithEmptyStringThrowsException(): void
    {
        // Expect
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('RecordIdentifier cannot be empty');

        // When
        new RecordIdentifier('');
    }

    /**
     * @test
     * Given a whitespace-only string
     * When I create a RecordIdentifier
     * Then it should throw InvalidArgumentException
     */
    public function testConstructWithWhitespaceOnlyThrowsException(): void
    {
        // Expect
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('RecordIdentifier cannot be empty');

        // When
        new RecordIdentifier('   ');
    }

    /**
     * @test
     * Given two RecordIdentifiers with the same identifier
     * When I compare them using equals()
     * Then equals() should return true
     */
    public function testEqualsSameIdentifier(): void
    {
        // Given
        $recordId1 = new RecordIdentifier('oai:example.org:12345');
        $recordId2 = new RecordIdentifier('oai:example.org:12345');

        // When/Then
        $this->assertTrue($recordId1->equals($recordId2));
        $this->assertTrue($recordId2->equals($recordId1));
    }

    /**
     * @test
     * Given two RecordIdentifiers with different identifiers
     * When I compare them using equals()
     * Then equals() should return false
     */
    public function testEqualsDifferentIdentifier(): void
    {
        // Given
        $recordId1 = new RecordIdentifier('oai:example.org:12345');
        $recordId2 = new RecordIdentifier('oai:example.org:67890');

        // When/Then
        $this->assertFalse($recordId1->equals($recordId2));
        $this->assertFalse($recordId2->equals($recordId1));
    }

    /**
     * @test
     * Given a RecordIdentifier
     * When I call __toString()
     * Then it should return a string representation
     */
    public function testToString(): void
    {
        // Given
        $identifier = 'oai:example.org:12345';
        $recordId = new RecordIdentifier($identifier);

        // When
        $string = (string) $recordId;

        // Then
        $this->assertStringContainsString('RecordIdentifier', $string);
        $this->assertStringContainsString($identifier, $string);
    }

    /**
     * @test
     * Given a RecordIdentifier
     * When I try to modify it
     * Then it should remain immutable
     */
    public function testImmutability(): void
    {
        // Given
        $identifier = 'oai:example.org:12345';
        $recordId = new RecordIdentifier($identifier);

        // When/Then - RecordIdentifier has no setters
        $this->assertSame($identifier, $recordId->getIdentifier());

        // Creating a new instance doesn't affect the original
        $newRecordId = new RecordIdentifier('oai:example.org:67890');
        $this->assertSame($identifier, $recordId->getIdentifier());
        $this->assertNotSame($recordId->getIdentifier(), $newRecordId->getIdentifier());
    }

    /**
     * @test
     * Given a complex identifier with special characters
     * When I create a RecordIdentifier
     * Then it should be created successfully
     */
    public function testConstructWithSpecialCharacters(): void
    {
        // Given
        $identifier = 'oai:repository.example.com:article-2024-01_v2.0';

        // When
        $recordId = new RecordIdentifier($identifier);

        // Then
        $this->assertSame($identifier, $recordId->getIdentifier());
    }

    /**
     * @test
     * Given an identifier with Unicode characters
     * When I create a RecordIdentifier
     * Then it should be created successfully
     */
    public function testConstructWithUnicodeCharacters(): void
    {
        // Given
        $identifier = 'oai:université.fr:thèse-2024';

        // When
        $recordId = new RecordIdentifier($identifier);

        // Then
        $this->assertSame($identifier, $recordId->getIdentifier());
    }
}
