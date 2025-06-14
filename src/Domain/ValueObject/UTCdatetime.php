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
 * Represents a date/time value with a specific OAI-PMH granularity.
 *
 * This value object:
 * - encapsulates a validated date/time string,
 * - is immutable and compared by value (not identity),
 * - ensures the date/time matches the required granularity.
 */
final class UTCdatetime
{
    private DateTimeImmutable $dateTime;
    private Granularity $granularity;

    /**
     * Constructs a new DateTime instance.
     *
     * @param string $dateTime The date/time string to validate and store.
     * @param Granularity $granularity The required granularity.
     * @throws InvalidArgumentException If the date/time does not match the granularity.
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
            throw new InvalidArgumentException('Invalid date/time format or value.');
        }

        $this->dateTime = $dt;
        $this->granularity = $granularity;
    }

    /**
     * Returns the date/time string.
     */
    public function getValue(): string
    {
        if ($this->granularity->getValue() === Granularity::DATE) {
            return $this->dateTime->format('Y-m-d');
        }
        return $this->dateTime->format('Y-m-d\TH:i:s\Z');
    }

    /**
     * Returns the DateTimeImmutable object.
     */
    public function getDateTime(): DateTimeImmutable
    {
        return $this->dateTime;
    }

    /**
     * Returns the granularity.
     */
    public function getGranularity(): Granularity
    {
        return $this->granularity;
    }

    /**
     * Checks if this DateTime is equal to another.
     */
    public function equals(self $other): bool
    {
        if ($this->granularity->getValue() !== $other->granularity->getValue()) {
            return false; // Different granularities cannot be equal
        }
        if ($this->granularity->getValue() === Granularity::DATE) {
            // Compare date only
            return $this->dateTime->format('Y-m-d') === $other->dateTime->format('Y-m-d');
        }
        // Compare date and time
        return $this->dateTime->format('Y-m-d\TH:i:s\Z') === $other->dateTime->format('Y-m-d\TH:i:s\Z');
    }

    /**
     * Returns a string representation of the DateTime object.
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
     * @throws InvalidArgumentException If the date/time does not match the granularity.
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
            throw new InvalidArgumentException(
                sprintf('Unknown granularity: %s', $granularity->getValue())
            );
        }
    }
}
