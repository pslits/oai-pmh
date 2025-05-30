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

class MetadataPrefix
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
     * Returns the metadata prefix.
     *
     * @return string The metadata prefix.
     */
    public function getValue(): string
    {
        return $this->prefix;
    }

    /**
     * Validates the metadata prefix against the expected pattern: It must consist of:
     * - alphanumeric characters: A-Z, a-z, 0-9
     * - hyphens: -
     * - underscores: _
     * - dots: .
     * - exclamation marks: !
     * - tildes: ~
     * - asterisks: *
     * - single quotes: '
     * - parentheses: ()
     * * The prefix must not contain any other characters or whitespace.
     * - The prefix can be any combination of these characters, but must not be empty.
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
}
