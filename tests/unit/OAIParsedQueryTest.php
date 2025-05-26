<?php

namespace Pslits\OaiPmh\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Pslits\OaiPmh\OAIParsedQuery;
use Pslits\OaiPmh\OAIException;

class OAIParsedQueryTest extends TestCase
{
    public function testParsesSingleKeyValuePair()
    {
        $query = new OAIParsedQuery('verb=Identify');
        $this->assertEquals('Identify', $query->getFirstValue('verb'));
        $this->assertEquals(['verb'], $query->getKeys());
    }

    public function testParsesMultipleKeyValuePairs()
    {
        $query = new OAIParsedQuery('verb=ListRecords&metadataPrefix=oai_dc&set=collection');
        $this->assertEquals('ListRecords', $query->getFirstValue('verb'));
        $this->assertEquals('oai_dc', $query->getFirstValue('metadataPrefix'));
        $this->assertEquals('collection', $query->getFirstValue('set'));
    }

    public function testHandlesRepeatedKeys()
    {
        $query = new OAIParsedQuery('set=abc&set=def&set=ghi');
        $this->assertEquals(['abc', 'def', 'ghi'], $query->getValuesByKey('set'));
        $this->assertEquals(3, $query->countKeyOccurrences('set'));
    }

    public function testReturnsNullForMissingKey()
    {
        $query = new OAIParsedQuery('verb=Identify');
        $this->assertNull($query->getFirstValue('metadataPrefix'));
    }

    public function testToArrayReturnsAllPairs()
    {
        $query = new OAIParsedQuery('verb=ListSets&from=2023-01-01');
        $this->assertEquals([
            ['key' => 'verb', 'value' => 'ListSets'],
            ['key' => 'from', 'value' => '2023-01-01']
        ], $query->toArray());
    }

    public function testThrowsExceptionOnTooLongInput()
    {
        $this->expectException(OAIException::class);
        $this->expectExceptionMessage('OAI validation error');

        $longString = str_repeat('a', 1001);
        new OAIParsedQuery("verb={$longString}");
    }

    public function testHandlesUrlEncodedValues()
    {
        $query = new OAIParsedQuery('title=Hello%20World&note=foo%2Bbar');
        $this->assertEquals('Hello World', $query->getFirstValue('title'));
        $this->assertEquals('foo+bar', $query->getFirstValue('note'));
    }

    public function testIgnoresEmptyPairs()
    {
        $query = new OAIParsedQuery('verb=Identify&&metadataPrefix=oai_dc&');
        $this->assertEquals(['verb', 'metadataPrefix'], $query->getKeys());
    }
}
