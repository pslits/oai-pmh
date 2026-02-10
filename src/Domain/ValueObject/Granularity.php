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
 * Represents the granularity of datestamps in OAI-PMH as a value object.
 *
 * According to OAI-PMH 2.0 specification section 3.3.1 (Granularity), repositories
 * must declare the finest harvesting granularity supported. The legitimate values are:
 * - 'YYYY-MM-DD': date only (day precision)
 * - 'YYYY-MM-DDThh:mm:ssZ': date and time (second precision)
 *
 * This value object:
 * - encapsulates a validated granularity string per ISO 8601,
 * - is immutable and compared by value (not identity),
 * - ensures only allowed granularities are accepted,
 * - is required in the OAI-PMH Identify response.
 */
final class Granularity
{
    public const DATE = 'YYYY-MM-DD';
    public const DATE_TIME_SECOND = 'YYYY-MM-DDThh:mm:ssZ';

    private const ALLOWED = [
        self::DATE,
        self::DATE_TIME_SECOND,
    ];

    private string $granularity;

    /**
     * Constructs a new Granularity instance.
     *
     * Validates that the granularity is one of the two values defined by
     * the OAI-PMH specification.
     *
     * @param string $granularity The granularity string ('YYYY-MM-DD' or 'YYYY-MM-DDThh:mm:ssZ').
     * @throws InvalidArgumentException If the granularity is not one of the allowed values.
     */
    public function __construct(string $granularity)
    {
        $this->validateGranularity($granularity);
        $this->granularity = $granularity;
    }

    /**
     * Returns the granularity string.
     *
     * @return string The granularity value ('YYYY-MM-DD' or 'YYYY-MM-DDThh:mm:ssZ').
     */
    public function getValue(): string
    {
        return $this->granularity;
    }

    /**
     * Checks if this Granularity is equal to another.
     *
     * Two Granularity instances are equal if they have the same granularity value.
     *
     * @param Granularity $otherGranularity The other Granularity to compare with.
     * @return bool True if both Granularity objects have the same value, false otherwise.
     */
    public function equals(self $otherGranularity): bool
    {
        return $this->granularity === $otherGranularity->granularity;
    }

    /**
     * Returns a string representation of the Granularity object.
     *
     * Provides a human-readable representation useful for debugging and logging.
     *
     * @return string A string representation of the Granularity.
     */
    public function __toString(): string
    {
        return sprintf('Granularity(granularity: %s)', $this->granularity);
    }

    /**
     * Validates the granularity string.
     *
     * Ensures the value is one of the two granularities defined in the
     * OAI-PMH 2.0 specification.
     *
     * @param string $granularity The granularity to validate.
     * @throws InvalidArgumentException If not 'YYYY-MM-DD' or 'YYYY-MM-DDThh:mm:ssZ'.
     */
    private function validateGranularity(string $granularity): void
    {
        if (!in_array($granularity, self::ALLOWED, true)) {
            throw new InvalidArgumentException(
                sprintf(
                    'Invalid granularity: %s. Allowed values are: %s',
                    $granularity,
                    implode(', ', self::ALLOWED)
                )
            );
        }
    }
}
