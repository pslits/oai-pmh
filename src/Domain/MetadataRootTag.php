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

class MetadataRootTag
{
    private string $rootTag;
    private const ROOT_TAG_PATTERN = '/^[A-Za-z_][A-Za-z0-9_.-]*(:[A-Za-z_][A-Za-z0-9_.-]*)?$/';

    /**
     * MetadataRootTag constructor.
     * Initializes a new instance of the MetadataRootTag class.
     *
     * @param string $rootTag The root tag used in XML elements.
     * @throws \InvalidArgumentException If the root tag does not match the expected pattern.
     */
    public function __construct(string $rootTag)
    {
        $this->validateRootTag($rootTag);
        $this->rootTag = $rootTag;
    } // End of constructor

    /**
     * Returns the root tag used in XML elements.
     *
     * @return string The root tag.
     */
    public function getRootTag(): string
    {
        return $this->rootTag;
    } // End of getRootTag

    /**
     * Validates the root tag format.
     *
     * @param string $rootTag The root tag to validate.
     * @throws InvalidArgumentException If the root tag does not match the expected pattern.
     */
    private function validateRootTag(string $rootTag): void
    {
        if (!preg_match(self::ROOT_TAG_PATTERN, $rootTag)) {
            throw new InvalidArgumentException("Invalid metadata root tag: '$rootTag'.");
        }
    } // End of validateRootTag
}
