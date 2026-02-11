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
use OaiPmh\Domain\ValueObject\OaiVerb;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for OaiVerb value object.
 *
 * Tests the OAI-PMH verb enumeration implementation following the specification
 * section 3.1. The protocol defines 6 verbs for different operations.
 *
 * @covers \OaiPmh\Domain\ValueObject\OaiVerb
 */
final class OaiVerbTest extends TestCase
{
    /**
     * @test
     * Given the Identify verb
     * When I create an OaiVerb
     * Then it should be created successfully
     */
    public function testConstructWithIdentifyVerb(): void
    {
        // Given
        $verb = 'Identify';

        // When
        $oaiVerb = new OaiVerb($verb);

        // Then
        $this->assertInstanceOf(OaiVerb::class, $oaiVerb);
        $this->assertSame($verb, $oaiVerb->getVerb());
        $this->assertSame($verb, $oaiVerb->getValue());
    }

    /**
     * @test
     * Given the ListMetadataFormats verb
     * When I create an OaiVerb
     * Then it should be created successfully
     */
    public function testConstructWithListMetadataFormatsVerb(): void
    {
        // Given
        $verb = 'ListMetadataFormats';

        // When
        $oaiVerb = new OaiVerb($verb);

        // Then
        $this->assertSame($verb, $oaiVerb->getVerb());
    }

    /**
     * @test
     * Given the ListSets verb
     * When I create an OaiVerb
     * Then it should be created successfully
     */
    public function testConstructWithListSetsVerb(): void
    {
        // Given
        $verb = 'ListSets';

        // When
        $oaiVerb = new OaiVerb($verb);

        // Then
        $this->assertSame($verb, $oaiVerb->getVerb());
    }

    /**
     * @test
     * Given the GetRecord verb
     * When I create an OaiVerb
     * Then it should be created successfully
     */
    public function testConstructWithGetRecordVerb(): void
    {
        // Given
        $verb = 'GetRecord';

        // When
        $oaiVerb = new OaiVerb($verb);

        // Then
        $this->assertSame($verb, $oaiVerb->getVerb());
    }

    /**
     * @test
     * Given the ListIdentifiers verb
     * When I create an OaiVerb
     * Then it should be created successfully
     */
    public function testConstructWithListIdentifiersVerb(): void
    {
        // Given
        $verb = 'ListIdentifiers';

        // When
        $oaiVerb = new OaiVerb($verb);

        // Then
        $this->assertSame($verb, $oaiVerb->getVerb());
    }

    /**
     * @test
     * Given the ListRecords verb
     * When I create an OaiVerb
     * Then it should be created successfully
     */
    public function testConstructWithListRecordsVerb(): void
    {
        // Given
        $verb = 'ListRecords';

        // When
        $oaiVerb = new OaiVerb($verb);

        // Then
        $this->assertSame($verb, $oaiVerb->getVerb());
    }

    /**
     * @test
     * Given an empty string
     * When I create an OaiVerb
     * Then it should throw InvalidArgumentException
     */
    public function testConstructWithEmptyStringThrowsException(): void
    {
        // Expect
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('OaiVerb cannot be empty');

        // When
        new OaiVerb('');
    }

    /**
     * @test
     * Given an invalid verb
     * When I create an OaiVerb
     * Then it should throw InvalidArgumentException
     */
    public function testConstructWithInvalidVerbThrowsException(): void
    {
        // Expect
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid OAI-PMH verb');

        // When
        new OaiVerb('InvalidVerb');
    }

    /**
     * @test
     * Given a lowercase verb
     * When I create an OaiVerb
     * Then it should throw InvalidArgumentException (verbs are case-sensitive)
     */
    public function testConstructWithLowercaseVerbThrowsException(): void
    {
        // Expect
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid OAI-PMH verb');

        // When
        new OaiVerb('identify');
    }

    /**
     * @test
     * Given two OaiVerbs with the same verb
     * When I compare them using equals()
     * Then equals() should return true
     */
    public function testEqualsSameVerb(): void
    {
        // Given
        $oaiVerb1 = new OaiVerb('Identify');
        $oaiVerb2 = new OaiVerb('Identify');

        // When/Then
        $this->assertTrue($oaiVerb1->equals($oaiVerb2));
        $this->assertTrue($oaiVerb2->equals($oaiVerb1));
    }

    /**
     * @test
     * Given two OaiVerbs with different verbs
     * When I compare them using equals()
     * Then equals() should return false
     */
    public function testEqualsDifferentVerb(): void
    {
        // Given
        $oaiVerb1 = new OaiVerb('Identify');
        $oaiVerb2 = new OaiVerb('GetRecord');

        // When/Then
        $this->assertFalse($oaiVerb1->equals($oaiVerb2));
        $this->assertFalse($oaiVerb2->equals($oaiVerb1));
    }

    /**
     * @test
     * Given an OaiVerb
     * When I call __toString()
     * Then it should return a string representation
     */
    public function testToString(): void
    {
        // Given
        $verb = 'GetRecord';
        $oaiVerb = new OaiVerb($verb);

        // When
        $string = (string) $oaiVerb;

        // Then
        $this->assertStringContainsString('OaiVerb', $string);
        $this->assertStringContainsString($verb, $string);
    }

    /**
     * @test
     * Given an OaiVerb
     * When I try to modify it
     * Then it should remain immutable
     */
    public function testImmutability(): void
    {
        // Given
        $verb = 'Identify';
        $oaiVerb = new OaiVerb($verb);

        // When/Then - OaiVerb has no setters
        $this->assertSame($verb, $oaiVerb->getVerb());

        // Creating a new instance doesn't affect the original
        $newOaiVerb = new OaiVerb('GetRecord');
        $this->assertSame($verb, $oaiVerb->getVerb());
        $this->assertNotSame($oaiVerb->getVerb(), $newOaiVerb->getVerb());
    }

    /**
     * @test
     * Given all valid OAI-PMH verbs
     * When I create OaiVerb instances for each
     * Then all should be created successfully
     */
    public function testAllValidVerbs(): void
    {
        // Given
        $validVerbs = [
            'Identify',
            'ListMetadataFormats',
            'ListSets',
            'GetRecord',
            'ListIdentifiers',
            'ListRecords'
        ];

        // When/Then
        foreach ($validVerbs as $verb) {
            $oaiVerb = new OaiVerb($verb);
            $this->assertSame($verb, $oaiVerb->getVerb());
        }
    }
}
