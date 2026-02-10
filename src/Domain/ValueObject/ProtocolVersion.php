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
 * According to OAI-PMH 2.0 specification section 4.2 (Identify), the protocolVersion
 * element indicates the version of the OAI-PMH protocol supported by the repository.
 * Currently, only version '2.0' is defined.
 *
 * This value object:
 * - encapsulates a validated protocol version,
 * - is immutable and compared by value (not identity),
 * - ensures only allowed protocol versions ('2.0') are accepted,
 * - is required in the OAI-PMH Identify response.
 */
final class ProtocolVersion
{
    private string $version;

    private const ALLOWED_VERSION = '2.0';

    /**
     * Constructs a new ProtocolVersion instance.
     *
     * Validates that the version is '2.0' as required by the current
     * OAI-PMH specification.
     *
     * @param string $protocolVersion The protocol version (must be '2.0').
     * @throws InvalidArgumentException If the version is not '2.0'.
     */
    public function __construct(string $protocolVersion)
    {
        $this->validateVersion($protocolVersion);
        $this->version = $protocolVersion;
    }

    /**
     * Returns the protocol version.
     *
     * @return string The protocol version ('2.0').
     */
    public function getProtocolVersion(): string
    {
        return $this->version;
    }

    /**
     * Checks if this ProtocolVersion is equal to another.
     *
     * Two ProtocolVersion instances are equal if they have the same version.
     *
     * @param ProtocolVersion $otherVersion The other ProtocolVersion to compare with.
     * @return bool True if both have the same version, false otherwise.
     */
    public function equals(self $otherVersion): bool
    {
        return $this->version === $otherVersion->version;
    }

    /**
     * Returns a string representation of the ProtocolVersion object.
     *
     * Provides a human-readable representation useful for debugging and logging.
     *
     * @return string A string representation of the ProtocolVersion.
     */
    public function __toString(): string
    {
        return sprintf('ProtocolVersion(version: %s)', $this->version);
    }

    /**
     * Validates the protocol version.
     *
     * Ensures the version is '2.0' as required by the current OAI-PMH specification.
     *
     * @param string $protocolVersion The version to validate.
     * @throws InvalidArgumentException If the version is not '2.0'.
     */
    private function validateVersion(string $protocolVersion): void
    {
        if ($protocolVersion !== self::ALLOWED_VERSION) {
            throw new InvalidArgumentException(
                sprintf('Invalid protocol version: %s. Only "%s" is allowed.', $protocolVersion, self::ALLOWED_VERSION)
            );
        }
    }
}
