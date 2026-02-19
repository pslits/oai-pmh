<?php

/**
 * @author    Paul Slits <paul.slits@gmail.com>
 * @copyright (c) 2025 Paul Slits
 * @license   MIT License - https://opensource.org/licenses/MIT
 * @link      https://github.com/pslits/oai-pmh
 * @since     0.1.0
 */

declare(strict_types=1);

namespace OaiPmh\Tests\Domain\Aggregate;

use InvalidArgumentException;
use OaiPmh\Domain\Aggregate\OaiRequest;
use OaiPmh\Domain\Exception\BadArgumentException;
use OaiPmh\Domain\Exception\BadVerbException;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for OaiRequest aggregate.
 *
 * Verifies that OaiRequest correctly parses and validates all six OAI-PMH verbs,
 * their required/optional/exclusive argument rules, and that the appropriate
 * BadVerbException or BadArgumentException is thrown for every protocol violation.
 *
 * Test structure mirrors the OAI-PMH 2.0 spec argument table:
 * - Identify             : accepts no arguments
 * - ListMetadataFormats  : optional identifier
 * - ListSets             : exclusive resumptionToken
 * - GetRecord            : requires identifier + metadataPrefix
 * - ListIdentifiers      : requires metadataPrefix (or exclusive resumptionToken)
 * - ListRecords          : requires metadataPrefix (or exclusive resumptionToken)
 *
 * @see https://www.openarchives.org/OAI/openarchivesprotocol.html#ProtocolMessages
 */
class OaiRequestTest extends TestCase
{
    // -----------------------------------------------------------------------
    // Verb validation
    // -----------------------------------------------------------------------

    /**
     * @test
     * Given a request with no verb key
     * When fromQueryParameters is called
     * Then BadVerbException with "badVerb" code is thrown
     */
    public function testFromQueryParameters_MissingVerb_ThrowsBadVerbException(): void
    {
        $this->expectException(BadVerbException::class);
        $this->expectExceptionMessage('The request does not contain a verb argument.');

        OaiRequest::fromQueryParameters([]);
    }

    /**
     * @test
     * Given a request with an unrecognised verb
     * When fromQueryParameters is called
     * Then BadVerbException with "badVerb" code is thrown
     */
    public function testFromQueryParameters_UnknownVerb_ThrowsBadVerbException(): void
    {
        $this->expectException(BadVerbException::class);
        $this->expectExceptionMessage('"Harvest"');

        OaiRequest::fromQueryParameters(['verb' => 'Harvest']);
    }

    /**
     * @test
     * Given a request with a verb that differs only in casing
     * When fromQueryParameters is called
     * Then BadVerbException is thrown (verbs are case-sensitive)
     */
    public function testFromQueryParameters_WrongCaseVerb_ThrowsBadVerbException(): void
    {
        $this->expectException(BadVerbException::class);

        OaiRequest::fromQueryParameters(['verb' => 'identify']);
    }

    /**
     * @test
     * Given a missing verb
     * When getOaiErrorCode is called on the caught exception
     * Then it returns "badVerb"
     */
    public function testBadVerbException_CarriesBadVerbErrorCode(): void
    {
        try {
            OaiRequest::fromQueryParameters([]);
            $this->fail('Expected BadVerbException was not thrown.');
        } catch (BadVerbException $e) {
            $this->assertSame('badVerb', $e->getOaiErrorCode());
        }
    }

    // -----------------------------------------------------------------------
    // Identify — accepts no arguments
    // -----------------------------------------------------------------------

    /**
     * @test
     * Given verb=Identify with no extra arguments
     * When fromQueryParameters is called
     * Then a valid OaiRequest is returned
     */
    public function testFromQueryParameters_Identify_NoArguments_ReturnsRequest(): void
    {
        $request = OaiRequest::fromQueryParameters(['verb' => 'Identify']);

        $this->assertSame('Identify', $request->getVerb()->getVerb());
        $this->assertSame([], $request->getArguments());
    }

    /**
     * @test
     * Given verb=Identify with an extra argument
     * When fromQueryParameters is called
     * Then BadArgumentException with "badArgument" code is thrown
     */
    public function testFromQueryParameters_Identify_WithExtraArgument_ThrowsBadArgumentException(): void
    {
        $this->expectException(BadArgumentException::class);
        $this->expectExceptionMessage('"identifier"');
        $this->expectExceptionMessage('"Identify"');

        OaiRequest::fromQueryParameters(['verb' => 'Identify', 'identifier' => 'oai:example.org:1']);
    }

    // -----------------------------------------------------------------------
    // ListMetadataFormats — optional identifier
    // -----------------------------------------------------------------------

    /**
     * @test
     * Given verb=ListMetadataFormats with no arguments
     * When fromQueryParameters is called
     * Then a valid OaiRequest is returned
     */
    public function testFromQueryParameters_ListMetadataFormats_NoArguments_ReturnsRequest(): void
    {
        $request = OaiRequest::fromQueryParameters(['verb' => 'ListMetadataFormats']);

        $this->assertSame('ListMetadataFormats', $request->getVerb()->getVerb());
        $this->assertFalse($request->hasArgument('identifier'));
    }

    /**
     * @test
     * Given verb=ListMetadataFormats with optional identifier
     * When fromQueryParameters is called
     * Then a valid OaiRequest is returned with the identifier
     */
    public function testFromQueryParameters_ListMetadataFormats_WithIdentifier_ReturnsRequest(): void
    {
        $request = OaiRequest::fromQueryParameters([
            'verb'       => 'ListMetadataFormats',
            'identifier' => 'oai:example.org:1',
        ]);

        $this->assertTrue($request->hasArgument('identifier'));
        $this->assertSame('oai:example.org:1', $request->getArgument('identifier'));
    }

    /**
     * @test
     * Given verb=ListMetadataFormats with an illegal argument
     * When fromQueryParameters is called
     * Then BadArgumentException is thrown
     */
    public function testFromQueryParameters_ListMetadataFormats_WithIllegalArgument_ThrowsBadArgumentException(): void
    {
        $this->expectException(BadArgumentException::class);
        $this->expectExceptionMessage('"metadataPrefix"');

        OaiRequest::fromQueryParameters([
            'verb'           => 'ListMetadataFormats',
            'metadataPrefix' => 'oai_dc',
        ]);
    }

    // -----------------------------------------------------------------------
    // ListSets — exclusive resumptionToken
    // -----------------------------------------------------------------------

    /**
     * @test
     * Given verb=ListSets with no arguments
     * When fromQueryParameters is called
     * Then a valid OaiRequest is returned
     */
    public function testFromQueryParameters_ListSets_NoArguments_ReturnsRequest(): void
    {
        $request = OaiRequest::fromQueryParameters(['verb' => 'ListSets']);

        $this->assertSame('ListSets', $request->getVerb()->getVerb());
    }

    /**
     * @test
     * Given verb=ListSets with resumptionToken alone
     * When fromQueryParameters is called
     * Then a valid OaiRequest is returned
     */
    public function testFromQueryParameters_ListSets_WithResumptionTokenAlone_ReturnsRequest(): void
    {
        $request = OaiRequest::fromQueryParameters([
            'verb'             => 'ListSets',
            'resumptionToken'  => 'abc123',
        ]);

        $this->assertTrue($request->hasArgument('resumptionToken'));
        $this->assertSame('abc123', $request->getArgument('resumptionToken'));
    }

    /**
     * @test
     * Given verb=ListSets with resumptionToken combined with an illegal argument
     * When fromQueryParameters is called
     * Then BadArgumentException is thrown
     *
     * Note: ListSets only allows resumptionToken as an optional argument.
     * Any second argument is therefore illegal for this verb and is caught by
     * the illegal-argument check before the exclusive check is reached.
     * Both paths produce BadArgumentException with error code "badArgument".
     */
    public function testFromQueryParameters_ListSets_ResumptionTokenWithOtherArg_ThrowsBadArgumentException(): void
    {
        $this->expectException(BadArgumentException::class);

        OaiRequest::fromQueryParameters([
            'verb'             => 'ListSets',
            'resumptionToken'  => 'abc123',
            'identifier'       => 'oai:example.org:1',
        ]);
    }

    // -----------------------------------------------------------------------
    // GetRecord — requires identifier + metadataPrefix
    // -----------------------------------------------------------------------

    /**
     * @test
     * Given verb=GetRecord with identifier and metadataPrefix
     * When fromQueryParameters is called
     * Then a valid OaiRequest is returned
     */
    public function testFromQueryParameters_GetRecord_WithRequiredArguments_ReturnsRequest(): void
    {
        $request = OaiRequest::fromQueryParameters([
            'verb'           => 'GetRecord',
            'identifier'     => 'oai:example.org:1',
            'metadataPrefix' => 'oai_dc',
        ]);

        $this->assertSame('GetRecord', $request->getVerb()->getVerb());
        $this->assertSame('oai:example.org:1', $request->getArgument('identifier'));
        $this->assertSame('oai_dc', $request->getArgument('metadataPrefix'));
    }

    /**
     * @test
     * Given verb=GetRecord with only metadataPrefix (missing identifier)
     * When fromQueryParameters is called
     * Then BadArgumentException for missing required argument is thrown
     */
    public function testFromQueryParameters_GetRecord_MissingIdentifier_ThrowsBadArgumentException(): void
    {
        $this->expectException(BadArgumentException::class);
        $this->expectExceptionMessage('"identifier"');

        OaiRequest::fromQueryParameters([
            'verb'           => 'GetRecord',
            'metadataPrefix' => 'oai_dc',
        ]);
    }

    /**
     * @test
     * Given verb=GetRecord with only identifier (missing metadataPrefix)
     * When fromQueryParameters is called
     * Then BadArgumentException for missing required argument is thrown
     */
    public function testFromQueryParameters_GetRecord_MissingMetadataPrefix_ThrowsBadArgumentException(): void
    {
        $this->expectException(BadArgumentException::class);
        $this->expectExceptionMessage('"metadataPrefix"');

        OaiRequest::fromQueryParameters([
            'verb'       => 'GetRecord',
            'identifier' => 'oai:example.org:1',
        ]);
    }

    /**
     * @test
     * Given verb=GetRecord with no arguments
     * When fromQueryParameters is called
     * Then BadArgumentException for missing required argument is thrown
     */
    public function testFromQueryParameters_GetRecord_NoArguments_ThrowsBadArgumentException(): void
    {
        $this->expectException(BadArgumentException::class);

        OaiRequest::fromQueryParameters(['verb' => 'GetRecord']);
    }

    /**
     * @test
     * Given verb=GetRecord with required arguments plus an illegal argument
     * When fromQueryParameters is called
     * Then BadArgumentException for the illegal argument is thrown
     */
    public function testFromQueryParameters_GetRecord_WithIllegalArgument_ThrowsBadArgumentException(): void
    {
        $this->expectException(BadArgumentException::class);
        $this->expectExceptionMessage('"set"');

        OaiRequest::fromQueryParameters([
            'verb'           => 'GetRecord',
            'identifier'     => 'oai:example.org:1',
            'metadataPrefix' => 'oai_dc',
            'set'            => 'physics',
        ]);
    }

    // -----------------------------------------------------------------------
    // ListRecords — requires metadataPrefix, exclusive resumptionToken
    // -----------------------------------------------------------------------

    /**
     * @test
     * Given verb=ListRecords with metadataPrefix
     * When fromQueryParameters is called
     * Then a valid OaiRequest is returned
     */
    public function testFromQueryParameters_ListRecords_WithMetadataPrefix_ReturnsRequest(): void
    {
        $request = OaiRequest::fromQueryParameters([
            'verb'           => 'ListRecords',
            'metadataPrefix' => 'oai_dc',
        ]);

        $this->assertSame('ListRecords', $request->getVerb()->getVerb());
        $this->assertSame('oai_dc', $request->getArgument('metadataPrefix'));
    }

    /**
     * @test
     * Given verb=ListRecords with metadataPrefix and all optional arguments
     * When fromQueryParameters is called
     * Then a valid OaiRequest is returned with all arguments
     */
    public function testFromQueryParameters_ListRecords_WithAllOptionalArguments_ReturnsRequest(): void
    {
        $request = OaiRequest::fromQueryParameters([
            'verb'           => 'ListRecords',
            'metadataPrefix' => 'oai_dc',
            'from'           => '2020-01-01',
            'until'          => '2026-01-01',
            'set'            => 'physics',
        ]);

        $this->assertTrue($request->hasArgument('from'));
        $this->assertTrue($request->hasArgument('until'));
        $this->assertTrue($request->hasArgument('set'));
        $this->assertSame('physics', $request->getArgument('set'));
    }

    /**
     * @test
     * Given verb=ListRecords with resumptionToken alone
     * When fromQueryParameters is called
     * Then a valid OaiRequest is returned (token replaces required args)
     */
    public function testFromQueryParameters_ListRecords_WithResumptionTokenAlone_ReturnsRequest(): void
    {
        $request = OaiRequest::fromQueryParameters([
            'verb'             => 'ListRecords',
            'resumptionToken'  => 'xyz789',
        ]);

        $this->assertTrue($request->hasArgument('resumptionToken'));
        $this->assertFalse($request->hasArgument('metadataPrefix'));
    }

    /**
     * @test
     * Given verb=ListRecords with no arguments
     * When fromQueryParameters is called
     * Then BadArgumentException for missing metadataPrefix is thrown
     */
    public function testFromQueryParameters_ListRecords_NoArguments_ThrowsBadArgumentException(): void
    {
        $this->expectException(BadArgumentException::class);
        $this->expectExceptionMessage('"metadataPrefix"');

        OaiRequest::fromQueryParameters(['verb' => 'ListRecords']);
    }

    /**
     * @test
     * Given verb=ListRecords with resumptionToken combined with metadataPrefix
     * When fromQueryParameters is called
     * Then BadArgumentException for exclusive violation is thrown
     */
    public function testFromQueryParameters_ListRecords_ResumptionTokenWithMetadataPrefix_Throws(): void
    {
        $this->expectException(BadArgumentException::class);
        $this->expectExceptionMessage('exclusive');

        OaiRequest::fromQueryParameters([
            'verb'             => 'ListRecords',
            'resumptionToken'  => 'xyz789',
            'metadataPrefix'   => 'oai_dc',
        ]);
    }

    // -----------------------------------------------------------------------
    // ListIdentifiers — same rules as ListRecords
    // -----------------------------------------------------------------------

    /**
     * @test
     * Given verb=ListIdentifiers with metadataPrefix
     * When fromQueryParameters is called
     * Then a valid OaiRequest is returned
     */
    public function testFromQueryParameters_ListIdentifiers_WithMetadataPrefix_ReturnsRequest(): void
    {
        $request = OaiRequest::fromQueryParameters([
            'verb'           => 'ListIdentifiers',
            'metadataPrefix' => 'oai_dc',
        ]);

        $this->assertSame('ListIdentifiers', $request->getVerb()->getVerb());
    }

    /**
     * @test
     * Given verb=ListIdentifiers with resumptionToken alone
     * When fromQueryParameters is called
     * Then a valid OaiRequest is returned
     */
    public function testFromQueryParameters_ListIdentifiers_WithResumptionTokenAlone_ReturnsRequest(): void
    {
        $request = OaiRequest::fromQueryParameters([
            'verb'             => 'ListIdentifiers',
            'resumptionToken'  => 'tok987',
        ]);

        $this->assertTrue($request->hasArgument('resumptionToken'));
    }

    /**
     * @test
     * Given verb=ListIdentifiers with no arguments
     * When fromQueryParameters is called
     * Then BadArgumentException for missing metadataPrefix is thrown
     */
    public function testFromQueryParameters_ListIdentifiers_NoArguments_ThrowsBadArgumentException(): void
    {
        $this->expectException(BadArgumentException::class);
        $this->expectExceptionMessage('"metadataPrefix"');

        OaiRequest::fromQueryParameters(['verb' => 'ListIdentifiers']);
    }

    // -----------------------------------------------------------------------
    // Getters
    // -----------------------------------------------------------------------

    /**
     * @test
     * Given a valid request
     * When getArgument is called with a non-existent argument name
     * Then InvalidArgumentException is thrown
     */
    public function testGetArgument_NonExistentArgument_ThrowsInvalidArgumentException(): void
    {
        $request = OaiRequest::fromQueryParameters(['verb' => 'Identify']);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('"identifier"');

        $request->getArgument('identifier');
    }

    /**
     * @test
     * Given a valid request
     * When __toString is called
     * Then a descriptive string containing the verb is returned
     */
    public function testToString_ContainsVerbName(): void
    {
        $request = OaiRequest::fromQueryParameters(['verb' => 'Identify']);

        $this->assertStringContainsString('Identify', (string) $request);
    }

    /**
     * @test
     * Given a valid GetRecord request
     * When __toString is called
     * Then the string contains both verb and argument names
     */
    public function testToString_ContainsArguments(): void
    {
        $request = OaiRequest::fromQueryParameters([
            'verb'           => 'GetRecord',
            'identifier'     => 'oai:example.org:42',
            'metadataPrefix' => 'oai_dc',
        ]);

        $result = (string) $request;
        $this->assertStringContainsString('GetRecord', $result);
        $this->assertStringContainsString('identifier', $result);
        $this->assertStringContainsString('metadataPrefix', $result);
    }

    /**
     * @test
     * Given a bad argument
     * When getOaiErrorCode is called on the caught exception
     * Then it returns "badArgument"
     */
    public function testBadArgumentException_CarriesBadArgumentErrorCode(): void
    {
        try {
            OaiRequest::fromQueryParameters(['verb' => 'GetRecord']);
            $this->fail('Expected BadArgumentException was not thrown.');
        } catch (BadArgumentException $e) {
            $this->assertSame('badArgument', $e->getOaiErrorCode());
        }
    }
}
