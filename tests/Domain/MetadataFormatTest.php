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

use OaiPmh\Domain\MetadataFormat;

class MetadataFormatTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @test
     * As a providor of metadata formats,
     * I want to retrieve the metadata prefix, namespace and schema URL,
     * So that I can provide the necessary information for harvesers to understand the format.
     */
    public function it_exposes_basic_format_information_for_list_metadata_formats(): void
    {
        $format = new MetadataFormat(
            'oai_dc',
            'http://www.openarchives.org/OAI/2.0/oai_dc/',
            'http://www.openarchives.org/OAI/2.0/oai_dc.xsd',
            'oai_dc:dc'
        );

        $this->assertSame('oai_dc', $format->getPrefix());
        $this->assertSame('http://www.openarchives.org/OAI/2.0/oai_dc/', $format->getNamespace());
        $this->assertSame('http://www.openarchives.org/OAI/2.0/oai_dc.xsd', $format->getSchemaUrl());
    }

    /**
     * @test
     * As a metadata serializer,
     * I need to retrieve the correct XML root element,
     * So that I can serialize a record in the specified metadata format.
     */
    public function it_returns_the_xml_root_element_for_get_record_serialization(): void
    {
        $format = new MetadataFormat(
            'oai_dc',
            'namespace',
            'schema',
            'oai_dc:dc'
        );

        $this->assertSame('oai_dc:dc', $format->getXmlRootElement());
    }

    /**
     * @test
     * As a system integrator,
     * I want to retrieve the metadata prefix from the format object,
     * So that I can use it as a key to register or fetch formats from a repository.
     */
    public function it_exposes_its_metadata_prefix_for_repository_lookup(): void
    {
        $format = new MetadataFormat(
            'oai_dc',
            'http://www.openarchives.org/OAI/2.0/oai_dc/',
            'http://www.openarchives.org/OAI/2.0/oai_dc.xsd',
            'oai_dc:dc'
        );

        $this->assertSame('oai_dc', $format->getPrefix());
    }
}
