<?php

/**
 * @author    Paul Slits <paul.slits@gmail.com>
 * @copyright (c) 2025 Paul Slits
 * @license   MIT License - https://opensource.org/licenses/MIT
 * @link      https://github.com/pslits/oai-pmh
 * @since     0.1.0
 */

declare(strict_types=1);

namespace OaiPmh\Domain\Exception;

use RuntimeException;

/**
 * Base exception for OAI-PMH protocol-level errors.
 *
 * According to OAI-PMH 2.0 specification section 3.6 (Error and Exception Conditions),
 * all protocol errors are reported using a fixed set of error codes. This base class
 * carries the OAI error code alongside the human-readable message, so callers can
 * build a well-formed OAI-PMH error XML response directly from the exception.
 *
 * OAI-PMH error codes defined in the specification:
 * - badArgument          — illegal or missing argument
 * - badResumptionToken   — invalid or expired token
 * - badVerb              — illegal or missing verb
 * - cannotDisseminateFormat — format not supported
 * - idDoesNotExist       — identifier unknown
 * - noRecordsMatch       — no results for given criteria
 * - noMetadataFormats    — no metadata formats available
 * - noSetHierarchy       — sets not supported by this repository
 *
 * @see https://www.openarchives.org/OAI/openarchivesprotocol.html#ErrorConditions
 */
abstract class OaiProtocolException extends RuntimeException
{
    /**
     * The OAI-PMH error code (e.g. "badVerb", "badArgument").
     */
    private string $oaiErrorCode;

    /**
     * Constructs a new OaiProtocolException.
     *
     * @param string $oaiErrorCode The OAI-PMH error code from the specification.
     * @param string $message      A human-readable description of the error.
     */
    public function __construct(string $oaiErrorCode, string $message)
    {
        parent::__construct($message);
        $this->oaiErrorCode = $oaiErrorCode;
    }

    /**
     * Returns the OAI-PMH error code.
     *
     * The error code is one of the fixed values defined in the OAI-PMH 2.0 specification
     * and must appear verbatim in the OAI-PMH error XML response element.
     *
     * @return string The OAI error code (e.g. "badVerb", "badArgument").
     */
    public function getOaiErrorCode(): string
    {
        return $this->oaiErrorCode;
    }
}
