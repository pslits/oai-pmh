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

/**
 * Class NamespacePrefix
 *
 * Represents a namespace prefix used in XML elements.
 * This class encapsulates the prefix and provides validation to ensure it adheres to the expected format.
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
    } // End of constructor

    /**
     * Returns the prefix used in XML elements.
     *
     * @return string The prefix.
     */
    public function getPrefix(): string
    {
        return $this->prefix;
    } // End of getPrefix

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
    } // End of validatePrefix
} // End of NamespacePrefix
