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

/**
 * Thrown when an OAI-PMH request contains an illegal or missing verb.
 *
 * According to OAI-PMH 2.0 specification section 3.6, the "badVerb" error code
 * is used when the verb argument is missing from the request, or when its value
 * is not one of the six legal OAI-PMH verbs:
 * Identify, ListMetadataFormats, ListSets, GetRecord, ListIdentifiers, ListRecords.
 *
 * The caller is expected to catch this exception and return an OAI-PMH XML error
 * response with error code "badVerb".
 *
 * @see https://www.openarchives.org/OAI/openarchivesprotocol.html#ErrorConditions
 */
final class BadVerbException extends OaiProtocolException
{
    /**
     * Constructs a new BadVerbException.
     *
     * @param string $message A human-readable description of the bad verb error.
     */
    public function __construct(string $message)
    {
        parent::__construct('badVerb', $message);
    }

    /**
     * Creates an exception for a request that contains no verb argument.
     *
     * @return self
     */
    public static function missingVerb(): self
    {
        return new self('The request does not contain a verb argument.');
    }

    /**
     * Creates an exception for a request that contains an unrecognised verb.
     *
     * @param string $verb The illegal verb value as received in the request.
     * @return self
     */
    public static function illegalVerb(string $verb): self
    {
        return new self(
            sprintf(
                'The value "%s" of the verb argument is not a legal OAI-PMH verb.',
                $verb,
            )
        );
    }
}
