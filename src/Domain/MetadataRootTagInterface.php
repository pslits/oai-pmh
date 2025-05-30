<?php

/**
 * @author    Paul Slits <paul.slits@gmail.com>
 * @copyright (c) 2025 Paul Slits
 * @license   MIT License - https://opensource.org/licenses/MIT
 * @link      https://github.com/pslits/oai-pmh
 * @since     0.1.0
 */

namespace OaiPmh\Domain;

interface MetadataRootTagInterface
{
    /**
     * Returns the root tag used in XML elements.
     *
     * @return string The root tag.
     */
    public function getRootTag(): string;
}
