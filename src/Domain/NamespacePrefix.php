<?php

/**
 * @author    Paul Slits <paul.slits@gmail.com>
 * @copyright (c) 2025 Paul Slits
 * @license   MIT License - https://opensource.org/licenses/MIT
 * @link      https://github.com/pslits/oai-pmh
 * @since     0.1.0
 */

namespace OaiPmh\Domain;

use InvalidArgumentException;

/**
 * Class NamespacePrefix
 *
 * Represents a namespace prefix used in XML elements.
 *
 * This value object:
 * - encapsulates a namespace prefix,
 * - provides validation to ensure it adheres to the expected format,
 * - is immutable and compared by value (not identity).
 *
 * Domain concerns such as XML serialization or protocol transport are handled outside this class.
 */
class NamespacePrefix
{
    private string $prefix;

    private const PREFIX_PATTERN = '/^[A-Za-z_][A-Za-z0-9_.-]*$/';

    /**
     * NamespacePrefix constructor.
     * Initializes a new instance of the NamespacePrefix class.
     *
     * @param string $prefix The prefix used in XML elements.
     */
    public function __construct(string $prefix)
    {
        $this->validatePrefix($prefix);
        $this->prefix = $prefix;
    }

    /**
     * Returns a string representation of the NamespacePrefix.
     *
     * @return string A string representation of the NamespacePrefix.
     */
    public function __toString(): string
    {
        return sprintf('NamespacePrefix(prefix: %s)', $this->prefix);
    }

    /**
     * Returns the prefix used in XML elements.
     *
     * @return string The prefix.
     */
    public function getValue(): string
    {
        return $this->prefix;
    }

    /**
     * Validates the prefix format.
     *
     * @param string $prefix The prefix to validate.
     * @throws InvalidArgumentException If the prefix does not match the expected pattern.
     */
    private function validatePrefix(string $prefix): void
    {
        if (!preg_match(self::PREFIX_PATTERN, $prefix)) {
            throw new InvalidArgumentException('Invalid prefix format.');
        }
    }

    /**
     * Checks if this NamespacePrefix is equal to another.
     *
     * @param NamespacePrefix $other The other NamespacePrefix to compare against.
     * @return bool True if both prefixes are equal, false otherwise.
     */
    public function equals(NamespacePrefix $other): bool
    {
        return $this->prefix === $other->getValue();
    }
}
