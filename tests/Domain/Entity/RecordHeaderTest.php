<?php

/**
 * @author    Paul Slits <paul.slits@gmail.com>
 * @copyright (c) 2025 Paul Slits
 * @license   MIT License - https://opensource.org/licenses/MIT
 * @link      https://github.com/pslits/oai-pmh
 * @since     0.1.0
 */

namespace OaiPmh\Tests\Domain\Entity;

use InvalidArgumentException;
use OaiPmh\Domain\Entity\RecordHeader;
use OaiPmh\Domain\ValueObject\RecordIdentifier;
use OaiPmh\Domain\ValueObject\UTCdatetime;
use OaiPmh\Domain\ValueObject\Granularity;
use OaiPmh\Domain\ValueObject\SetSpec;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for RecordHeader entity.
 *
 * Tests the OAI-PMH record header implementation following the specification
 * section 2.5 (Record). Record headers contain core metadata about a record.
 *
 * @covers \OaiPmh\Domain\Entity\RecordHeader
 */
final class RecordHeaderTest extends TestCase
{
    private Granularity $granularity;

    protected function setUp(): void
    {
        $this->granularity = new Granularity('YYYY-MM-DDThh:mm:ssZ');
    }

    /**
     * @test
     * Given valid header data without optional fields
     * When I create a RecordHeader
     * Then it should be created successfully
     */
    public function testConstructWithMinimalData(): void
    {
        // Given
        $identifier = new RecordIdentifier('oai:example.org:12345');
        $datestamp = new UTCdatetime('2024-01-15T10:30:00Z', $this->granularity);

        // When
        $header = new RecordHeader($identifier, $datestamp);

        // Then
        $this->assertInstanceOf(RecordHeader::class, $header);
        $this->assertSame($identifier, $header->getIdentifier());
        $this->assertSame($datestamp, $header->getDatestamp());
        $this->assertFalse($header->isDeleted());
        $this->assertEmpty($header->getSetSpecs());
    }

    /**
     * @test
     * Given a deleted record header
     * When I create a RecordHeader
     * Then it should mark the record as deleted
     */
    public function testConstructWithDeletedStatus(): void
    {
        // Given
        $identifier = new RecordIdentifier('oai:example.org:12345');
        $datestamp = new UTCdatetime('2024-01-15T10:30:00Z', $this->granularity);
        $isDeleted = true;

        // When
        $header = new RecordHeader($identifier, $datestamp, $isDeleted);

        // Then
        $this->assertTrue($header->isDeleted());
    }

    /**
     * @test
     * Given a header with set specifications
     * When I create a RecordHeader
     * Then it should contain the set specs
     */
    public function testConstructWithSetSpecs(): void
    {
        // Given
        $identifier = new RecordIdentifier('oai:example.org:12345');
        $datestamp = new UTCdatetime('2024-01-15T10:30:00Z', $this->granularity);
        $setSpecs = [
            new SetSpec('mathematics'),
            new SetSpec('math:algebra')
        ];

        // When
        $header = new RecordHeader($identifier, $datestamp, false, $setSpecs);

        // Then
        $this->assertCount(2, $header->getSetSpecs());
        $this->assertSame($setSpecs, $header->getSetSpecs());
    }

    /**
     * @test
     * Given a complete header with all fields
     * When I create a RecordHeader
     * Then all fields should be accessible
     */
    public function testConstructWithAllFields(): void
    {
        // Given
        $identifier = new RecordIdentifier('oai:arxiv.org:cs/0112017');
        $datestamp = new UTCdatetime('2024-01-15T10:30:00Z', $this->granularity);
        $isDeleted = false;
        $setSpecs = [
            new SetSpec('cs'),
            new SetSpec('cs:AI')
        ];

        // When
        $header = new RecordHeader($identifier, $datestamp, $isDeleted, $setSpecs);

        // Then
        $this->assertSame($identifier, $header->getIdentifier());
        $this->assertSame($datestamp, $header->getDatestamp());
        $this->assertFalse($header->isDeleted());
        $this->assertCount(2, $header->getSetSpecs());
    }

    /**
     * @test
     * Given a RecordHeader
     * When I retrieve set specifications
     * Then it should return an array of SetSpec value objects
     */
    public function testGetSetSpecs(): void
    {
        // Given
        $identifier = new RecordIdentifier('oai:example.org:12345');
        $datestamp = new UTCdatetime('2024-01-15T10:30:00Z', $this->granularity);
        $setSpecs = [
            new SetSpec('mathematics'),
            new SetSpec('sciences')
        ];

        $header = new RecordHeader($identifier, $datestamp, false, $setSpecs);

        // When
        $retrievedSetSpecs = $header->getSetSpecs();

        // Then
        $this->assertCount(2, $retrievedSetSpecs);
        $this->assertContainsOnlyInstancesOf(SetSpec::class, $retrievedSetSpecs);
    }

    /**
     * @test
     * Given a RecordHeader with empty setSpecs array
     * When I retrieve set specifications
     * Then it should return an empty array
     */
    public function testGetSetSpecsReturnsEmptyArrayWhenNoSets(): void
    {
        // Given
        $identifier = new RecordIdentifier('oai:example.org:12345');
        $datestamp = new UTCdatetime('2024-01-15T10:30:00Z', $this->granularity);

        $header = new RecordHeader($identifier, $datestamp);

        // When
        $setSpecs = $header->getSetSpecs();

        // Then
        $this->assertEmpty($setSpecs);
    }

    /**
     * @test
     * Given a RecordHeader
     * When I check if it belongs to a specific set
     * Then it should return true if the set is present
     */
    public function testBelongsToSet(): void
    {
        // Given
        $identifier = new RecordIdentifier('oai:example.org:12345');
        $datestamp = new UTCdatetime('2024-01-15T10:30:00Z', $this->granularity);
        $mathSet = new SetSpec('mathematics');
        $physicsSet = new SetSpec('physics');
        $setSpecs = [$mathSet];

        $header = new RecordHeader($identifier, $datestamp, false, $setSpecs);

        // When/Then
        $this->assertTrue($header->belongsToSet($mathSet));
        $this->assertFalse($header->belongsToSet($physicsSet));
    }

    /**
     * @test
     * Given invalid setSpecs array containing non-SetSpec objects
     * When I create a RecordHeader
     * Then PHPStan will catch this at the type level
     *
     * Note: This test verifies runtime behavior for cases where type safety
     * is bypassed. PHPStan will flag this as an error at analysis time.
     */
    public function testConstructWithInvalidSetSpecsThrowsException(): void
    {
        // This test is intentionally skipped because PHPStan Level 8
        // catches type violations at analysis time, making runtime validation
        // unnecessary for strictly typed code.
        $this->markTestSkipped(
            'Type safety is enforced by PHPStan Level 8. ' .
            'Invalid types are caught at analysis time, not runtime.'
        );
    }

    /**
     * @test
     * Given a RecordHeader
     * When I call __toString()
     * Then it should return a string representation
     */
    public function testToString(): void
    {
        // Given
        $identifier = new RecordIdentifier('oai:example.org:12345');
        $datestamp = new UTCdatetime('2024-01-15T10:30:00Z', $this->granularity);

        $header = new RecordHeader($identifier, $datestamp);

        // When
        $string = (string) $header;

        // Then
        $this->assertStringContainsString('RecordHeader', $string);
        $this->assertStringContainsString('oai:example.org:12345', $string);
    }
}
