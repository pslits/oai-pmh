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

namespace OaiPmh\Tests;

use OaiPmh\Domain\AnyUri;
use OaiPmh\Domain\MetadataFormat;
use OaiPmh\Domain\MetadataPrefix;
use OaiPmh\Domain\MetadataRootTag;
use OaiPmh\Domain\MetadataNamespace;
use PHPUnit\Framework\MockObject\MockObject;

class MetadataFormatTest extends \PHPUnit\Framework\TestCase
{
    //+-----------------------------------------------------------------------+
    //| Public methods
    //+-----------------------------------------------------------------------+

    /**
     * @test
     * As a providor of metadata formats,
     * I want to retrieve the metadata prefix, namespace and schema URL,
     * So that I can provide the necessary information for harvesers to understand the format.
     */
    public function it_exposes_basic_format_information_for_list_metadata_formats(): void
    {
        $formatAndMocks = $this->createMetadataWithMockes();

        // Mock the MetadataPrefix
        $formatAndMocks['mocks']['prefixMock']
            ->method('getPrefix')
            ->willReturn('oai_dc');

        // namespace 1
        $formatAndMocks['mocks']['metadataNamespaceMock'][0]
            ->method('getPrefix')
            ->willReturn('oai_dc');
        $formatAndMocks['mocks']['metadataNamespaceMock'][0]
            ->method('getUri')
            ->willReturn('http://www.openarchives.org/OAI/2.0/oai_dc/');

        // namespace 2
        $formatAndMocks['mocks']['metadataNamespaceMock'][1]
            ->method('getPrefix')
            ->willReturn('dc');
        $formatAndMocks['mocks']['metadataNamespaceMock'][1]
            ->method('getUri')
            ->willReturn('http://purl.org/dc/elements/1.1/');

        // namespace 3
        $formatAndMocks['mocks']['metadataNamespaceMock'][2]
            ->method('getPrefix')
            ->willReturn('xsi');
        $formatAndMocks['mocks']['metadataNamespaceMock'][2]
            ->method('getUri')
            ->willReturn('http://www.w3.org/2001/XMLSchema-instance');

        $formatAndMocks['mocks']['anyUriMock']
            ->method('getUri')
            ->willReturn('http://www.openarchives.org/OAI/2.0/oai_dc.xsd');


        $this->assertSame('oai_dc', $formatAndMocks['format']->getPrefix());
        $this->assertSame([
            'oai_dc' => 'http://www.openarchives.org/OAI/2.0/oai_dc/',
            'dc' => 'http://purl.org/dc/elements/1.1/',
            'xsi' => 'http://www.w3.org/2001/XMLSchema-instance',
        ], $formatAndMocks['format']->getNamespaces());
        $this->assertSame('http://www.openarchives.org/OAI/2.0/oai_dc.xsd', $formatAndMocks['format']->getSchemaUrl());
    }

    /**
     * @test
     * As a metadata serializer,
     * I need to retrieve the correct XML root tag, namespaces, and schema URL
     * So that I can serialize a record in the specified metadata format.
     */
    public function it_returns_the_xml_root_element_for_get_record_serialization(): void
    {
        $formatAndMocks = $this->createMetadataWithMockes();

        // namespace 1
        $formatAndMocks['mocks']['metadataNamespaceMock'][0]
            ->method('getPrefix')
            ->willReturn('oai_dc');
        $formatAndMocks['mocks']['metadataNamespaceMock'][0]
            ->method('getUri')
            ->willReturn('http://www.openarchives.org/OAI/2.0/oai_dc/');

        // namespace 2
        $formatAndMocks['mocks']['metadataNamespaceMock'][1]
            ->method('getPrefix')
            ->willReturn('dc');
        $formatAndMocks['mocks']['metadataNamespaceMock'][1]
            ->method('getUri')
            ->willReturn('http://purl.org/dc/elements/1.1/');

        // namespace 3
        $formatAndMocks['mocks']['metadataNamespaceMock'][2]
            ->method('getPrefix')
            ->willReturn('xsi');
        $formatAndMocks['mocks']['metadataNamespaceMock'][2]
            ->method('getUri')
            ->willReturn('http://www.w3.org/2001/XMLSchema-instance');

        $formatAndMocks['mocks']['anyUriMock']
            ->method('getUri')
            ->willReturn('http://www.openarchives.org/OAI/2.0/oai_dc.xsd');

        $formatAndMocks['mocks']['rootTagMock']
            ->method('getRootTag')
            ->willReturn('oai_dc:dc');

        $this->assertSame('oai_dc:dc', $formatAndMocks['format']->getRootTag());
        $this->assertSame([
            'oai_dc' => 'http://www.openarchives.org/OAI/2.0/oai_dc/',
            'dc' => 'http://purl.org/dc/elements/1.1/',
            'xsi' => 'http://www.w3.org/2001/XMLSchema-instance',
        ], $formatAndMocks['format']->getNamespaces());
        $this->assertSame('http://www.openarchives.org/OAI/2.0/oai_dc.xsd', $formatAndMocks['format']->getSchemaUrl());
    }

    /**
     * @test
     * As a system integrator,
     * I want to retrieve the metadata prefix from the format object,
     * So that I can use it as a key to register or fetch formats from a repository.
     */
    public function it_exposes_its_metadata_prefix_for_repository_lookup(): void
    {
        $formatAndMocks = $this->createMetadataWithMockes();

        // Mock the MetadataPrefix
        $formatAndMocks['mocks']['prefixMock']
            ->method('getPrefix')
            ->willReturn('oai_dc');

        $this->assertSame('oai_dc', $formatAndMocks['format']->getPrefix());
    }

    //+-----------------------------------------------------------------------+
    //| Protected methods
    //+-----------------------------------------------------------------------+

    //+-----------------------------------------------------------------------+
    //| Private methods
    //+-----------------------------------------------------------------------+

    /**
     * Creates a MetadataFormat instance with mocked dependencies.
     *
     * @return array{
     *     format: \OaiPmh\Domain\MetadataFormat,
     *     mocks: array{
     *         prefixMock: \OaiPmh\Domain\MetadataPrefix&\PHPUnit\Framework\MockObject\MockObject,
     *         metadataNamespaceMock: array<int, \OaiPmh\Domain\MetadataNamespace&\PHPUnit\Framework\MockObject\MockObject>,
     *         anyUriMock: \OaiPmh\Domain\AnyUri&\PHPUnit\Framework\MockObject\MockObject,
     *         rootTagMock: \OaiPmh\Domain\MetadataRootTag&\PHPUnit\Framework\MockObject\MockObject
     *     }
     * }
     */
    private function createMetadataWithMockes(): array
    {
        /** @var MetadataPrefix&\PHPUnit\Framework\MockObject\MockObject $prefixMock */
        $prefixMock = $this->createMock(MetadataPrefix::class);
        /** @var MetadataNamespace&\PHPUnit\Framework\MockObject\MockObject $metadataNamespaceMock1 */
        $metadataNamespaceMock1 = $this->createMock(MetadataNamespace::class);
        /** @var MetadataNamespace&\PHPUnit\Framework\MockObject\MockObject $metadataNamespaceMock2 */
        $metadataNamespaceMock2 = $this->createMock(MetadataNamespace::class);
        /** @var MetadataNamespace&\PHPUnit\Framework\MockObject\MockObject $metadataNamespaceMock3 */
        $metadataNamespaceMock3 = $this->createMock(MetadataNamespace::class);
        /** @var AnyUri&\PHPUnit\Framework\MockObject\MockObject $anyUriMock */
        $anyUriMock = $this->createMock(AnyUri::class);
        /** @var MetadataRootTag&\PHPUnit\Framework\MockObject\MockObject $rootTagMock */
        $rootTagMock = $this->createMock(MetadataRootTag::class);

        // Create the MetadataFormat instance with mocks
        $format = new MetadataFormat(
            $prefixMock,
            [$metadataNamespaceMock1, $metadataNamespaceMock2, $metadataNamespaceMock3],
            $anyUriMock,
            $rootTagMock
        );

        $formatAndMocks = [
            'format' => $format,
            'mocks' => [
                'prefixMock' => $prefixMock,
                'metadataNamespaceMock' => [
                    $metadataNamespaceMock1,
                    $metadataNamespaceMock2,
                    $metadataNamespaceMock3
                ],
                'anyUriMock' => $anyUriMock,
                'rootTagMock' => $rootTagMock,
            ]
        ];

        return $formatAndMocks;
    }
}
