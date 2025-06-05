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
 * Class MetadataPrefix
 *
 * Represents a metadata prefix used in OAI-PMH requests and responses.
 *
 * This value object:
 * - encapsulates a metadata prefix,
 * - provides validation to ensure it adheres to the expected format,
 * - is immutable and compared by value (not identity).
 *
 * Domain concerns such as XML serialization or protocol transport are handled outside this class.
 */
final class MetadataPrefix
{
    private string $prefix;

    private const PREFIX_PATTERN = '/^[A-Za-z0-9\-_\.!~\*\'\(\)]+$/';

    /**
     * MetadataPrefix constructor.
     * Initializes a new instance of the MetadataPrefix class.
     *
     * @param string $prefix OAI-PMH metadata prefix.
     * @throws \InvalidArgumentException If the prefix does not match the expected pattern.
     */
    public function __construct(string $prefix)
    {
        $this->validatePrefix($prefix);
        $this->prefix = $prefix;
    }

    /**
     * Returns a string representation of the metadata prefix.
     *
     * @return string The metadata prefix.
     */
    public function __toString(): string
    {
        return sprintf('MetadataPrefix(prefix: %s)', $this->prefix);
    }

    /**
     * Returns the metadata prefix.
     *
     * @return string The metadata prefix.
     */
    public function getValue(): string
    {
        return $this->prefix;
    }

    /**
     * Validates the metadata prefix against the expected pattern.
     *
     * @param string $prefix The prefix to validate.
     * @throws \InvalidArgumentException If the prefix does not match the expected pattern.
     */
    private function validatePrefix(string $prefix): void
    {
        if (!preg_match(self::PREFIX_PATTERN, $prefix)) {
            throw new InvalidArgumentException("Invalid metadata prefix: '$prefix'.");
        }
    }

    /**
     * Checks if this prefix is equal to another MetadataPrefix instance.
     *
     * @param MetadataPrefix $other The other MetadataPrefix instance to compare with.
     * @return bool True if the prefixes are equal, false otherwise.
     */
    public function equals(MetadataPrefix $other): bool
    {
        return $this->prefix === $other->getValue();
    }
}
