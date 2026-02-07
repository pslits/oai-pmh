<?php

/**
 * @author    Paul Slits <paul.slits@gmail.com>
 * @copyright (c) 2025 Paul Slits
 * @license   MIT License - https://opensource.org/licenses/MIT
 * @link      https://github.com/pslits/oai-pmh
 * @since     0.1.0
 */

namespace OaiPmh\Domain\ValueObject;

/**
 * Represents a description format as a value object.
 *
 * This value object:
 * - encapsulates a validated description format,
 * - is immutable and compared by value (not identity),
 * - ensures only allowed description formats are accepted.
 */
final class DescriptionFormat extends ContainerFormat
{
}
