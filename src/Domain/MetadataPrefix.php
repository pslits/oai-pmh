<?php

/**
 * +--------------------------------------------------------------------------+
 * | This file is part of the OAI-PMH package.                                |
 * | @link https://github.com/pslits/oai-pmh                                  |
 * +--------------------------------------------------------------------------+
 * | (c) 2025 Paul Slits <paul.slits@gmail.com>                               |
 * | This source code is licensed under the MIT license found in the LICENSE  |
 * | file in the root directory of this source tree or at the following link: |
 * | @license MIT <https://opensource.org/licenses/MIT>                       |
 * +--------------------------------------------------------------------------+
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
    } // End of constructor

    /**
     * Returns the metadata prefix.
     *
     * @return string The metadata prefix.
     */
    public function getPrefix(): string
    {
        return $this->prefix;
    } // End of getPrefix

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
    } // End of validatePrefix
}
