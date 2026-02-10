<?php

/**
 * @author    Paul Slits <paul.slits@gmail.com>
 * @copyright (c) 2025 Paul Slits
 * @license   MIT License - https://opensource.org/licenses/MIT
 * @link      https://github.com/pslits/oai-pmh
 * @since     0.1.0
 */

namespace OaiPmh\Domain\ValueObject;

use DateTimeImmutable;
use InvalidArgumentException;

/**
 * Represents a date/time value with specific OAI-PMH granularity.
 *
 * According to OAI-PMH 2.0 specification section 3.3 (Datestamps), all date and time
 * values must be expressed in UTC format following ISO 8601. The repository's declared
 * granularity (date-only or date-time) determines the format of datestamps.
 *
 * This value object:
 * - encapsulates a validated date/time string in UTC,
 * - is immutable and compared by value (not identity),
 * - ensures the date/time matches the required granularity,
 * - supports both YYYY-MM-DD and YYYY-MM-DDThh:mm:ssZ formats.
 *
 * Note: The regex validation for YYYY-MM-DDThh:mm:ssZ uses 'Z' (not '\Z') as it
 * represents the UTC timezone indicator, not end-of-string anchor.
 */
final class UTCdatetime
{
    private DateTimeImmutable $dateTime;
    private Granularity $granularity;

    /**
     * Constructs a new UTCdatetime instance.
     *
     * Validates that the date/time string matches the granularity format and can be
     * parsed as a valid date/time value in UTC.
     *
     * @param string $dateTime The date/time string in UTC (format depends on granularity).
     * @param Granularity $granularity The required granularity for this datestamp.
     * @throws InvalidArgumentException If the date/time does not match the granularity format.
     */
    public function __construct(string $dateTime, Granularity $granularity)
    {
        $this->validateDateTime($dateTime, $granularity);

        if ($granularity->getValue() === Granularity::DATE) {
            // Parse as date only
            $dt = DateTimeImmutable::createFromFormat('Y-m-d', $dateTime, new \DateTimeZone('UTC'));
        } else {
            $dt = DateTimeImmutable::createFromFormat('Y-m-d\TH:i:s\Z', $dateTime, new \DateTimeZone('UTC'));
        }

        // Although this statement can never be reached, it is kept for clarity.
        if ($dt === false) {
            // @codeCoverageIgnoreStart
            throw new InvalidArgumentException('Invalid date/time format or value.');
            // @codeCoverageIgnoreEnd
        }

        $this->dateTime = $dt;
        $this->granularity = $granularity;
    }

    /**
     * Returns the date/time as a formatted string according to the granularity.
     *
     * @return string The formatted date/time string (YYYY-MM-DD or YYYY-MM-DDThh:mm:ssZ).
     */
    public function getDateTime(): string
    {
        if ($this->granularity->getValue() === Granularity::DATE) {
            return $this->dateTime->format('Y-m-d');
        }
        return $this->dateTime->format('Y-m-d\TH:i:s\Z');
    }

    /**
     * Returns the DateTimeImmutable object (alias for getDateTime).
     *
     * Provides access to the underlying immutable DateTime for advanced operations.
     *
     * @return DateTimeImmutable The internal DateTimeImmutable representation.
     */
    public function getDateTimeImmutable(): DateTimeImmutable
    {
        return $this->dateTime;
    }

    /**
     * Returns the granularity of this datestamp.
     *
     * @return Granularity The granularity (date-only or date-time).
     */
    public function getGranularity(): Granularity
    {
        return $this->granularity;
    }

    /**
     * Checks if this UTCdatetime is equal to another.
     *
     * Two UTCdatetime instances are equal if they have the same granularity
     * and the same date/time value at that granularity.
     *
     * @param UTCdatetime $otherDateTime The other UTCdatetime to compare with.
     * @return bool True if both have the same granularity and value, false otherwise.
     */
    public function equals(self $otherDateTime): bool
    {
        if ($this->granularity->getValue() !== $otherDateTime->granularity->getValue()) {
            return false; // Different granularities cannot be equal
        }
        if ($this->granularity->getValue() === Granularity::DATE) {
            // Compare date only
            return $this->dateTime->format('Y-m-d') === $otherDateTime->dateTime->format('Y-m-d');
        }
        // Compare date and time
        return $this->dateTime->format('Y-m-d\TH:i:s\Z') === $otherDateTime->dateTime->format('Y-m-d\TH:i:s\Z');
    }

    /**
     * Returns a string representation of the UTCdatetime object.
     *
     * Provides a human-readable representation useful for debugging and logging.
     *
     * @return string A string representation including the formatted date/time and granularity.
     */
    public function __toString(): string
    {
        if ($this->granularity->getValue() === Granularity::DATE) {
            // Format as date only
            $formattedDate = $this->dateTime->format('Y-m-d');
        } else {
            // Format as date and time
            $formattedDate = $this->dateTime->format('Y-m-d\TH:i:s\Z');
        }

        return sprintf(
            'UTCdatetime(dateTime: %s, granularity: %s)',
            $formattedDate,
            (string)$this->granularity
        );
    }

    /**
     * Validates the date/time string against the granularity.
     *
     * Ensures the format matches either YYYY-MM-DD (date only) or
     * YYYY-MM-DDThh:mm:ssZ (date and time with UTC indicator).
     *
     * @param string $dateTime The date/time string to validate.
     * @param Granularity $granularity The required granularity.
     * @throws InvalidArgumentException If the date/time does not match the granularity format.
     */
    private function validateDateTime(string $dateTime, Granularity $granularity): void
    {
        if ($granularity->getValue() === Granularity::DATE) {
            // YYYY-MM-DD
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateTime)) {
                throw new InvalidArgumentException(
                    sprintf('DateTime "%s" does not match granularity "%s".', $dateTime, Granularity::DATE)
                );
            }
        } elseif ($granularity->getValue() === Granularity::DATE_TIME_SECOND) {
            // YYYY-MM-DDThh:mm:ssZ
            if (!preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}Z$/', $dateTime)) {
                throw new InvalidArgumentException(
                    sprintf('DateTime "%s" does not match granularity "%s".', $dateTime, Granularity::DATE_TIME_SECOND)
                );
            }
        } else {
            // Although this case should never be reached due to the constructor's type hinting,
            // it is included for completeness.
            // @codeCoverageIgnoreStart
            throw new InvalidArgumentException(
                sprintf('Unknown granularity: %s', $granularity->getValue())
            );
            // @codeCoverageIgnoreEnd
        }
    }
}
