<?php

/**
 * @author    Paul Slits <paul.slits@gmail.com>
 * @copyright (c) 2025 Paul Slits
 * @license   MIT License - https://opensource.org/licenses/MIT
 * @link      https://github.com/pslits/oai-pmh
 * @since     0.1.0
 */

namespace OaiPmh\Tests\Domain;

use OaiPmh\Domain\AnyUri;
use OaiPmh\Domain\MetadataFormat;
use OaiPmh\Domain\MetadataPrefix;
use OaiPmh\Domain\MetadataRootTag;
use OaiPmh\Domain\NamespacePrefix;
use OaiPmh\Domain\MetadataNamespaceInterface;

/**
 * Tests for the MetadataFormat class.
 *
 * This class tests the basic functionality of the MetadataFormat class,
 * including retrieving metadata prefix, namespaces, schema URL, and root tag.
 *
 * @author    Paul Slits <paul.slits@gmail.com>
 * @copyright (c) 2025 Paul Slits
 * @license   MIT License - https://opensource.org/licenses/MIT
 * @link      https://github.com/pslits/oai-pmh
 * @since     0.1.0
 */
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
    public function itExposesBasicFormatInformationForMetadataFormats(): void
    {
        $formatAndMocks = $this->createMetadataWithMockes();

        // Mock the MetadataPrefix
        $formatAndMocks['mocks']['prefixMock']
            ->method('getValue')
            ->willReturn('oai_dc');

        // namespace 1
        $formatAndMocks['mocks']['metadataNamespaceMock'][0]
            ->method('getPrefix')
            ->willReturn(new NamespacePrefix('oai_dc'));
        $formatAndMocks['mocks']['metadataNamespaceMock'][0]
            ->method('getUri')
            ->willReturn(new AnyUri('http://www.openarchives.org/OAI/2.0/oai_dc/'));

        // namespace 2
        $formatAndMocks['mocks']['metadataNamespaceMock'][1]
            ->method('getPrefix')
            ->willReturn(new NamespacePrefix('dc'));
        $formatAndMocks['mocks']['metadataNamespaceMock'][1]
            ->method('getUri')
            ->willReturn(new AnyUri('http://purl.org/dc/elements/1.1/'));

        // namespace 3
        $formatAndMocks['mocks']['metadataNamespaceMock'][2]
            ->method('getPrefix')
            ->willReturn(new NamespacePrefix('xsi'));
        $formatAndMocks['mocks']['metadataNamespaceMock'][2]
            ->method('getUri')
            ->willReturn(new AnyUri('http://www.w3.org/2001/XMLSchema-instance'));

        $formatAndMocks['mocks']['anyUriMock']
            ->method('getValue')
            ->willReturn('http://www.openarchives.org/OAI/2.0/oai_dc.xsd');


        $this->assertSame('oai_dc', $formatAndMocks['format']->getPrefix()->getValue());

        // asser namespace 1
        $this->assertSame('oai_dc', $formatAndMocks['format']->getNamespaces()[0]->getPrefix()->getValue());
        $this->assertSame(
            'http://www.openarchives.org/OAI/2.0/oai_dc/',
            $formatAndMocks['format']->getNamespaces()[0]->getUri()->getValue()
        );
        // asser namespace 2
        $this->assertSame('dc', $formatAndMocks['format']->getNamespaces()[1]->getPrefix()->getValue());
        $this->assertSame(
            'http://purl.org/dc/elements/1.1/',
            $formatAndMocks['format']->getNamespaces()[1]->getUri()->getValue()
        );
        // asser namespace 3
        $this->assertSame('xsi', $formatAndMocks['format']->getNamespaces()[2]->getPrefix()->getValue());
        $this->assertSame(
            'http://www.w3.org/2001/XMLSchema-instance',
            $formatAndMocks['format']->getNamespaces()[2]->getUri()->getValue()
        );

        $this->assertSame(
            'http://www.openarchives.org/OAI/2.0/oai_dc.xsd',
            $formatAndMocks['format']->getSchemaUrl()->getValue()
        );
    }

    /**
     * @test
     * As a metadata serializer,
     * I need to retrieve the correct XML root tag, namespaces, and schema URL
     * So that I can serialize a record in the specified metadata format.
     */
    public function itReturnsTheXmlRootElementForGetRecordSerialization(): void
    {
        $formatAndMocks = $this->createMetadataWithMockes();

        // Mock the MetadataRootTag
        $formatAndMocks['mocks']['rootTagMock']
            ->method('getValue')
            ->willReturn('oai_dc:dc');

        // namespace 1
        $formatAndMocks['mocks']['metadataNamespaceMock'][0]
            ->method('getPrefix')
            ->willReturn(new NamespacePrefix('oai_dc'));
        $formatAndMocks['mocks']['metadataNamespaceMock'][0]
            ->method('getUri')
            ->willReturn(new AnyUri('http://www.openarchives.org/OAI/2.0/oai_dc/'));

        // namespace 2
        $formatAndMocks['mocks']['metadataNamespaceMock'][1]
            ->method('getPrefix')
            ->willReturn(new NamespacePrefix('dc'));
        $formatAndMocks['mocks']['metadataNamespaceMock'][1]
            ->method('getUri')
            ->willReturn(new AnyUri('http://purl.org/dc/elements/1.1/'));

        // namespace 3
        $formatAndMocks['mocks']['metadataNamespaceMock'][2]
            ->method('getPrefix')
            ->willReturn(new NamespacePrefix('xsi'));
        $formatAndMocks['mocks']['metadataNamespaceMock'][2]
            ->method('getUri')
            ->willReturn(new AnyUri('http://www.w3.org/2001/XMLSchema-instance'));

        // Mock the AnyUri for schema URL
        $formatAndMocks['mocks']['anyUriMock']
            ->method('getValue')
            ->willReturn('http://www.openarchives.org/OAI/2.0/oai_dc.xsd');

        $this->assertSame('oai_dc:dc', $formatAndMocks['format']->getRootTag()->getValue());

        // asser namespace 1
        $this->assertSame('oai_dc', $formatAndMocks['format']->getNamespaces()[0]->getPrefix()->getValue());
        $this->assertSame(
            'http://www.openarchives.org/OAI/2.0/oai_dc/',
            $formatAndMocks['format']->getNamespaces()[0]->getUri()->getValue()
        );
        // asser namespace 2
        $this->assertSame('dc', $formatAndMocks['format']->getNamespaces()[1]->getPrefix()->getValue());
        $this->assertSame(
            'http://purl.org/dc/elements/1.1/',
            $formatAndMocks['format']->getNamespaces()[1]->getUri()->getValue()
        );
        // asser namespace 3
        $this->assertSame('xsi', $formatAndMocks['format']->getNamespaces()[2]->getPrefix()->getValue());
        $this->assertSame(
            'http://www.w3.org/2001/XMLSchema-instance',
            $formatAndMocks['format']->getNamespaces()[2]->getUri()->getValue()
        );

        $this->assertSame(
            'http://www.openarchives.org/OAI/2.0/oai_dc.xsd',
            $formatAndMocks['format']->getSchemaUrl()->getValue()
        );
    }

    /**
     * @test
     * As a system integrator,
     * I want to retrieve the metadata prefix from the format object,
     * So that I can use it as a key to register or fetch formats from a repository.
     */
    public function itExosesItsMetadataPrefixForRepositoryRegistration(): void
    {
        $formatAndMocks = $this->createMetadataWithMockes();

        // Mock the MetadataPrefix
        $formatAndMocks['mocks']['prefixMock']
            ->method('getValue')
            ->willReturn('oai_dc');

        $this->assertSame('oai_dc', $formatAndMocks['format']->getPrefix()->getValue());
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
     *         metadataNamespaceMock: array<int,
     *             \OaiPmh\Domain\MetadataNamespaceInterface&\PHPUnit\Framework\MockObject\MockObject>,
     *         anyUriMock: \OaiPmh\Domain\AnyUri&\PHPUnit\Framework\MockObject\MockObject,
     *         rootTagMock: \OaiPmh\Domain\MetadataRootTag&\PHPUnit\Framework\MockObject\MockObject
     *     }
     * }
     */
    private function createMetadataWithMockes(): array
    {
        /** @var MetadataPrefix&\PHPUnit\Framework\MockObject\MockObject $prefixMock */
        $prefixMock = $this->createMock(MetadataPrefix::class);
        /** @var MetadataNamespaceInterface&\PHPUnit\Framework\MockObject\MockObject $metadataNamespaceMock1 */
        $metadataNamespaceMock1 = $this->createMock(MetadataNamespaceInterface::class);
        /** @var MetadataNamespaceInterface&\PHPUnit\Framework\MockObject\MockObject $metadataNamespaceMock2 */
        $metadataNamespaceMock2 = $this->createMock(MetadataNamespaceInterface::class);
        /** @var MetadataNamespaceInterface&\PHPUnit\Framework\MockObject\MockObject $metadataNamespaceMock3 */
        $metadataNamespaceMock3 = $this->createMock(MetadataNamespaceInterface::class);
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
