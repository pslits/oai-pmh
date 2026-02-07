<?php

namespace OaiPmh\Tests\Domain\ValueObject;

use OaiPmh\Domain\ValueObject\UTCdatetime;
use OaiPmh\Domain\ValueObject\Granularity;
use PHPUnit\Framework\TestCase;
use InvalidArgumentException;

class UTCdatetimeTest extends TestCase
{
    /**
     * User Story:
     * As a developer,
     * I want to create a UTCdatetime with a valid date and granularity
     * So that it can be used in OAI-PMH responses.
     */
    public function testCanInstantiateWithValidDate(): void
    {
        $granularity = new Granularity(Granularity::DATE);
        $utc = new UTCdatetime('2024-06-10', $granularity);
        $this->assertInstanceOf(UTCdatetime::class, $utc);
        $this->assertSame('2024-06-10', $utc->getDateTime());
    }

    public function testCanInstantiateWithValidDateTime(): void
    {
        $granularity = new Granularity(Granularity::DATE_TIME_SECOND);
        $utc = new UTCdatetime('2024-06-10T12:34:56Z', $granularity);
        $this->assertInstanceOf(UTCdatetime::class, $utc);
        $this->assertSame('2024-06-10T12:34:56Z', $utc->getDateTime());
    }

    /**
     * User Story:
     * As a developer,
     * I want UTCdatetime to throw an exception for an invalid date/time
     * So that only valid values are accepted.
     */
    public function testThrowsExceptionForInvalidDate(): void
    {
        $granularity = new Granularity(Granularity::DATE);
        $this->expectException(InvalidArgumentException::class);
        new UTCdatetime('2024-06-10T12:34:56Z', $granularity);
    }

    public function testThrowsExceptionForInvalidDateTime(): void
    {
        $granularity = new Granularity(Granularity::DATE_TIME_SECOND);
        $this->expectException(InvalidArgumentException::class);
        new UTCdatetime('2024-06-10', $granularity);
    }

    /**
     * User Story:
     * As a developer,
     * I want to retrieve the DateTimeImmutable object from UTCdatetime
     * So that I can use it for date/time operations.
     */
    public function testGetDateTimeReturnsDateTimeImmutable(): void
    {
        $granularity = new Granularity(Granularity::DATE_TIME_SECOND);
        $utc = new UTCdatetime('2024-06-10T12:34:56Z', $granularity);
        $dateTime = $utc->getDateTimeImmutable();
        $this->assertInstanceOf(\DateTimeImmutable::class, $dateTime);
        $this->assertSame('2024-06-10T12:34:56+00:00', $dateTime->format('c'));
    }

    /**
     * User Story:
     * As a developer,
     * I want to retrieve the granularity of UTCdatetime
     * So that I can understand the precision of the date/time value.
     */
    public function testGetGranularityReturnsGranularityInstance(): void
    {
        $granularity = new Granularity(Granularity::DATE);
        $utc = new UTCdatetime('2024-06-10', $granularity);
        $this->assertSame($granularity, $utc->getGranularity());
    }

    /**
     * User Story:
     * As a developer,
     * I want to compare two UTCdatetime instances by value
     * So that value equality is supported.
     */
    public function testEqualsReturnsTrueForSameDateTimeValue(): void
    {
        $granularity = new Granularity(Granularity::DATE_TIME_SECOND);
        $a = new UTCdatetime('2024-06-10T12:34:56Z', $granularity);
        $b = new UTCdatetime('2024-06-10T12:34:56Z', $granularity);
        $this->assertTrue($a->equals($b));
    }

    public function testEqualsReturnsTrueForSameDateValue(): void
    {
        $granularity = new Granularity(Granularity::DATE);
        $a = new UTCdatetime('2024-06-10', $granularity);
        $b = new UTCdatetime('2024-06-10', $granularity);
        $this->assertTrue($a->equals($b));
    }

    public function testEqualsReturnsFalseForDifferentDateTimeValue(): void
    {
        $granularity = new Granularity(Granularity::DATE_TIME_SECOND);
        $a = new UTCdatetime('2024-06-10T12:34:56Z', $granularity);
        $b = new UTCdatetime('2024-06-10T12:34:57Z', $granularity);
        $this->assertFalse($a->equals($b));
    }

    public function testEqualsReturnsFalseForDifferentDateValue(): void
    {
        $granularity = new Granularity(Granularity::DATE);
        $a = new UTCdatetime('2024-06-10', $granularity);
        $b = new UTCdatetime('2024-06-11', $granularity);
        $this->assertFalse($a->equals($b));
    }

    public function testEqualsReturnsFalseForDifferentGranularity(): void
    {
        $granularityDate = new Granularity(Granularity::DATE);
        $granularityDateTime = new Granularity(Granularity::DATE_TIME_SECOND);
        $a = new UTCdatetime('2024-06-10', $granularityDate);
        $b = new UTCdatetime('2024-06-10T12:34:56Z', $granularityDateTime);
        $this->assertFalse($a->equals($b));
    }

    /**
     * User Story:
     * As a developer,
     * I want to get a string representation of UTCdatetime
     * So that I can log or display it.
     */
    public function testToStringReturnsExpectedDateTimeFormat(): void
    {
        $granularity = new Granularity(Granularity::DATE_TIME_SECOND);
        $utc = new UTCdatetime('2024-06-10T12:34:56Z', $granularity);
        $expected = "UTCdatetime(dateTime: 2024-06-10T12:34:56Z, " .
        "granularity: Granularity(granularity: YYYY-MM-DDThh:mm:ssZ))";
        $this->assertSame($expected, (string)$utc);
    }

    public function testToStringReturnsExpectedDateFormat(): void
    {
        $granularity = new Granularity(Granularity::DATE);
        $utc = new UTCdatetime('2024-06-10', $granularity);
        $expected = "UTCdatetime(dateTime: 2024-06-10, granularity: Granularity(granularity: YYYY-MM-DD))";
        $this->assertSame($expected, (string)$utc);
    }

    /**
     * User Story:
     * As a developer,
     * I want to ensure that UTCdatetime is immutable
     * So that its internal state cannot be changed after construction.
     */
    public function testIsImmutable(): void
    {
        $granularity = new Granularity(Granularity::DATE);
        $utc = new UTCdatetime('2024-06-10', $granularity);
        $reflection = new \ReflectionClass($utc);
        foreach ($reflection->getProperties() as $property) {
            $this->assertTrue($property->isPrivate(), "Property {$property->getName()} should be private.");
        }
    }
}
