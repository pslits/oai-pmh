<?php

namespace Pslits\OaiPmh\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Pslits\OaiPmh\OAIRequestDTO;
use Pslits\OaiPmh\OAIException;
use Pslits\OaiPmh\OAIParsedQuery;

/**
 * OAI-PMH Verb Validation - Unit Test Coverage
 *
 * This test class is responsible for validating the OAI-PMH verbs and ensuring that
 * the correct exceptions are thrown for invalid verbs. It uses PHPUnit for unit testing.
 *
 * Verbs to be tested:
 * - Identify
 * - GetRecord
 * - ListIdentifiers
 * - ListMetadataFormats
 * - ListRecords
 * - ListSets
 *
 * Test badVerb exceptions:
 * - Missing verb argument
 * - Invalid verb argument
 * - Repeated verb argument
 *
 */

class OAIRequestDTOTest extends TestCase
{
    public function testValidVerbIdentify()
    {
        $parsedQuery = new OAIParsedQuery('verb=Identify');
        $dto = new OAIRequestDTO($parsedQuery);

        $this->assertEquals('Identify', $dto->getVerb());
    }

    public function testValidVerbListRecords()
    {
        $parsedQuery = new OAIParsedQuery('verb=ListRecords');
        $dto = new OAIRequestDTO($parsedQuery);

        $this->assertEquals('ListRecords', $dto->getVerb());
    }

    public function testValidVerbListIdentifiers()
    {
        $parsedQuery = new OAIParsedQuery('verb=ListIdentifiers');
        $dto = new OAIRequestDTO($parsedQuery);

        $this->assertEquals('ListIdentifiers', $dto->getVerb());
    }

    public function testValidVerbListMetadataFormats()
    {
        $parsedQuery = new OAIParsedQuery('verb=ListMetadataFormats');
        $dto = new OAIRequestDTO($parsedQuery);

        $this->assertEquals('ListMetadataFormats', $dto->getVerb());
    }

    public function testValidVerbListSets()
    {
        $parsedQuery = new OAIParsedQuery('verb=ListSets');
        $dto = new OAIRequestDTO($parsedQuery);

        $this->assertEquals('ListSets', $dto->getVerb());
    }

    public function testMissingVerbThrowsException()
    {
        $this->expectException(OAIException::class);
        $this->expectExceptionMessage('OAI validation error');

        $parsedQuery = new OAIParsedQuery('');

        try {
            new OAIRequestDTO($parsedQuery);
        } catch (OAIException $e) {
            $messages = $e->getExceptionList();
            $this->assertEquals(
                'The verb argument is missing in the request',
                $messages['badVerb'][0]
            );
            throw $e; // Re-throw the exception to be caught by PHPUnit
        }
    }

    public function testInvalidVerbThrowsException()
    {
        $this->expectException(OAIException::class);
        $this->expectExceptionMessage('OAI validation error');

        $parsedQuery = new OAIParsedQuery('verb=InvalidVerb');

        try {
            new OAIRequestDTO($parsedQuery);
        } catch (OAIException $e) {
            $messages = $e->getExceptionList();
            $this->assertEquals(
                'The value "InvalidVerb" of the verb argument is not supported by the OAI-PMH protocol',
                $messages['badVerb'][0]
            );
            throw $e; // Re-throw the exception to be caught by PHPUnit
        }

        new OAIRequestDTO($parsedQuery);
    }

    public function testRepeatedVerbThrowsException()
    {
        $this->expectException(OAIException::class);
        $this->expectExceptionMessage('OAI validation error');

        $parsedQuery = new OAIParsedQuery('verb=Identify&verb=ListRecords');

        try {
            new OAIRequestDTO($parsedQuery);
        } catch (OAIException $e) {
            $messages = $e->getExceptionList();
            $this->assertEquals(
                'The verb argument is repeated in the request',
                $messages['badVerb'][0]
            );
            throw $e; // Re-throw the exception to be caught by PHPUnit
        }

        new OAIRequestDTO($parsedQuery);
    }
}
