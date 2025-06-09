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
 * Represents the OAI-PMH protocol version as a value object.
 *
 * This value object:
 * - encapsulates a validated protocol version,
 * - is immutable and compared by value (not identity),
 * - ensures only allowed protocol versions are accepted.
 */
final class ProtocolVersion
{
    private string $version;

    private const ALLOWED_VERSION = '2.0';

    /**
     * Constructs a new ProtocolVersion instance.
     *
     * @param string $version The protocol version to validate and store.
     * @throws InvalidArgumentException If the version is not allowed.
     */
    public function __construct(string $version)
    {
        $this->validateVersion($version);
        $this->version = $version;
    }

    /**
     * Returns the protocol version.
     */
    public function getValue(): string
    {
        return $this->version;
    }

    /**
     * Checks if this ProtocolVersion is equal to another.
     */
    public function equals(self $other): bool
    {
        return $this->version === $other->version;
    }

    /**
     * Returns a string representation of the ProtocolVersion object.
     */
    public function __toString(): string
    {
        return sprintf('ProtocolVersion(version: %s)', $this->version);
    }

    /**
     * Validates the protocol version.
     *
     * @throws InvalidArgumentException If the version is not allowed.
     */
    private function validateVersion(string $version): void
    {
        if ($version !== self::ALLOWED_VERSION) {
            throw new InvalidArgumentException(
                sprintf('Invalid protocol version: %s. Only "%s" is allowed.', $version, self::ALLOWED_VERSION)
            );
        }
    }
}
