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
 * This value object:
 * - encapsulates a validated granularity string,
 * - is immutable and compared by value (not identity),
 * - ensures only allowed granularities are accepted.
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
     * @param string $granularity The granularity string to validate and store.
     * @throws InvalidArgumentException If the granularity is not allowed.
     */
    public function __construct(string $granularity)
    {
        $this->validateGranularity($granularity);
        $this->granularity = $granularity;
    }

    /**
     * Returns the granularity string.
     */
    public function getValue(): string
    {
        return $this->granularity;
    }

    /**
     * Checks if this Granularity is equal to another.
     */
    public function equals(self $other): bool
    {
        return $this->granularity === $other->granularity;
    }

    /**
     * Returns a string representation of the Granularity object.
     */
    public function __toString(): string
    {
        return sprintf('Granularity(granularity: %s)', $this->granularity);
    }

    /**
     * Validates the granularity string.
     *
     * @throws InvalidArgumentException If the granularity is not allowed.
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
