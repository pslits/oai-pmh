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

use OaiPmh\Domain\Aggregate\RepositoryIdentity;
use OaiPmh\Domain\Exception\InvalidConfigurationException;
use OaiPmh\Domain\ValueObject\DescriptionCollection;
use OaiPmh\Domain\ValueObject\Granularity;
use OaiPmh\Domain\ValueObject\UTCdatetime;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for the RepositoryIdentity aggregate.
 *
 * @see https://www.openarchives.org/OAI/openarchivesprotocol.html#Identify
 */
class RepositoryIdentityTest extends TestCase
{
    /**
     * Returns a minimal valid configuration array (required keys only).
     *
     * @return array<string, mixed>
     */
    private function minimalConfig(): array
    {
        return [
            'repository_name' => 'Test Repository',
            'base_url'        => 'https://example.org/oai',
            'admin_emails'    => ['admin@example.org'],
            'deleted_record'  => 'transient',
            'granularity'     => 'YYYY-MM-DD',
        ];
    }

    private function earliestDatestamp(): UTCdatetime
    {
        return new UTCdatetime('2000-01-01', new Granularity('YYYY-MM-DD'));
    }

    /** @test */
    public function testFromConfiguration_MinimalConfig_ReturnsInstance(): void
    {
        $identity = RepositoryIdentity::fromConfiguration(
            $this->minimalConfig(),
            $this->earliestDatestamp(),
        );
        $this->assertInstanceOf(RepositoryIdentity::class, $identity);
    }

    /** @test */
    public function testFromConfiguration_ReturnsCorrectRepositoryName(): void
    {
        $identity = RepositoryIdentity::fromConfiguration(
            $this->minimalConfig(),
            $this->earliestDatestamp(),
        );
        $this->assertSame('Test Repository', $identity->getRepositoryName()->getRepositoryName());
    }

    /** @test */
    public function testFromConfiguration_ReturnsCorrectBaseURL(): void
    {
        $identity = RepositoryIdentity::fromConfiguration(
            $this->minimalConfig(),
            $this->earliestDatestamp(),
        );
        $this->assertSame('https://example.org/oai', $identity->getBaseURL()->getBaseUrl());
    }

    /** @test */
    public function testFromConfiguration_ProtocolVersionIsAlways20(): void
    {
        $identity = RepositoryIdentity::fromConfiguration(
            $this->minimalConfig(),
            $this->earliestDatestamp(),
        );
        $this->assertSame('2.0', $identity->getProtocolVersion()->getProtocolVersion());
    }

    /** @test */
    public function testFromConfiguration_ReturnsCorrectAdminEmails(): void
    {
        $identity = RepositoryIdentity::fromConfiguration(
            $this->minimalConfig(),
            $this->earliestDatestamp(),
        );
        $this->assertCount(1, $identity->getAdminEmails());
    }

    /** @test */
    public function testFromConfiguration_MultipleAdminEmails_ReturnsAllInCollection(): void
    {
        /** @var array<string, mixed> $config */
        $config = array_merge($this->minimalConfig(), [
            'admin_emails' => ['admin@example.org', 'backup@example.org'],
        ]);
        $identity = RepositoryIdentity::fromConfiguration($config, $this->earliestDatestamp());
        $this->assertCount(2, $identity->getAdminEmails());
    }

    /** @test */
    public function testFromConfiguration_EarliestDatestampIsInjected(): void
    {
        $earliest = $this->earliestDatestamp();
        $identity = RepositoryIdentity::fromConfiguration($this->minimalConfig(), $earliest);
        $this->assertSame($earliest, $identity->getEarliestDatestamp());
    }

    /** @test */
    public function testFromConfiguration_ReturnsCorrectDeletedRecord(): void
    {
        $identity = RepositoryIdentity::fromConfiguration(
            $this->minimalConfig(),
            $this->earliestDatestamp(),
        );
        $this->assertSame('transient', $identity->getDeletedRecord()->getDeletedRecord());
    }

    /** @test */
    public function testFromConfiguration_ReturnsCorrectGranularity(): void
    {
        $identity = RepositoryIdentity::fromConfiguration(
            $this->minimalConfig(),
            $this->earliestDatestamp(),
        );
        $this->assertSame('YYYY-MM-DD', $identity->getGranularity()->getValue());
    }

    /** @test */
    public function testFromConfiguration_NoCompressionKey_ReturnsEmptyArray(): void
    {
        $identity = RepositoryIdentity::fromConfiguration(
            $this->minimalConfig(),
            $this->earliestDatestamp(),
        );
        $this->assertSame([], $identity->getCompression());
    }

    /** @test */
    public function testFromConfiguration_WithCompression_ReturnsCompressionList(): void
    {
        /** @var array<string, mixed> $config */
        $config = array_merge($this->minimalConfig(), [
            'compression' => ['gzip', 'deflate'],
        ]);
        $identity = RepositoryIdentity::fromConfiguration($config, $this->earliestDatestamp());
        $this->assertSame(['gzip', 'deflate'], $identity->getCompression());
    }

    /** @test */
    public function testFromConfiguration_DescriptionsDefaultToEmptyCollection(): void
    {
        $identity = RepositoryIdentity::fromConfiguration(
            $this->minimalConfig(),
            $this->earliestDatestamp(),
        );
        $this->assertCount(0, $identity->getDescriptions());
    }

    /** @test */
    public function testWithDescriptions_ReturnsNewInstance_LeavingOriginalUnchanged(): void
    {
        $original = RepositoryIdentity::fromConfiguration(
            $this->minimalConfig(),
            $this->earliestDatestamp(),
        );
        $updated = $original->withDescriptions(new DescriptionCollection());
        $this->assertNotSame($original, $updated);
        $this->assertCount(0, $original->getDescriptions());
        $this->assertCount(0, $updated->getDescriptions());
    }

    /**
     * @test
     * @dataProvider provideRequiredKeys
     */
    public function testFromConfiguration_MissingRequiredKey_ThrowsInvalidConfigurationException(
        string $missingKey
    ): void {
        $config = $this->minimalConfig();
        unset($config[$missingKey]);
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage($missingKey);
        RepositoryIdentity::fromConfiguration($config, $this->earliestDatestamp());
    }

    /**
     * @return array<string, array{string}>
     */
    public function provideRequiredKeys(): array
    {
        return [
            'repository_name' => ['repository_name'],
            'base_url'        => ['base_url'],
            'admin_emails'    => ['admin_emails'],
            'deleted_record'  => ['deleted_record'],
            'granularity'     => ['granularity'],
        ];
    }

    /** @test */
    public function testFromConfiguration_EmptyRepositoryName_ThrowsInvalidConfigurationException(): void
    {
        /** @var array<string, mixed> $config */
        $config = array_merge($this->minimalConfig(), ['repository_name' => '']);
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage('repository_name');
        RepositoryIdentity::fromConfiguration($config, $this->earliestDatestamp());
    }

    /** @test */
    public function testFromConfiguration_InvalidBaseUrl_ThrowsInvalidConfigurationException(): void
    {
        /** @var array<string, mixed> $config */
        $config = array_merge($this->minimalConfig(), ['base_url' => 'not-a-url']);
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage('base_url');
        RepositoryIdentity::fromConfiguration($config, $this->earliestDatestamp());
    }

    /** @test */
    public function testFromConfiguration_EmptyAdminEmails_ThrowsInvalidConfigurationException(): void
    {
        /** @var array<string, mixed> $config */
        $config = array_merge($this->minimalConfig(), ['admin_emails' => []]);
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage('admin_emails');
        RepositoryIdentity::fromConfiguration($config, $this->earliestDatestamp());
    }

    /** @test */
    public function testFromConfiguration_InvalidEmailInAdminEmails_ThrowsInvalidConfigurationException(): void
    {
        /** @var array<string, mixed> $config */
        $config = array_merge($this->minimalConfig(), ['admin_emails' => ['not-an-email']]);
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage('admin_emails');
        RepositoryIdentity::fromConfiguration($config, $this->earliestDatestamp());
    }

    /** @test */
    public function testFromConfiguration_InvalidDeletedRecord_ThrowsInvalidConfigurationException(): void
    {
        /** @var array<string, mixed> $config */
        $config = array_merge($this->minimalConfig(), ['deleted_record' => 'maybe']);
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage('deleted_record');
        RepositoryIdentity::fromConfiguration($config, $this->earliestDatestamp());
    }

    /** @test */
    public function testFromConfiguration_InvalidGranularity_ThrowsInvalidConfigurationException(): void
    {
        /** @var array<string, mixed> $config */
        $config = array_merge($this->minimalConfig(), ['granularity' => 'daily']);
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage('granularity');
        RepositoryIdentity::fromConfiguration($config, $this->earliestDatestamp());
    }

    /** @test */
    public function testFromConfiguration_InvalidCompression_ThrowsInvalidConfigurationException(): void
    {
        /** @var array<string, mixed> $config */
        $config = array_merge($this->minimalConfig(), ['compression' => [42]]);
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage('compression');
        RepositoryIdentity::fromConfiguration($config, $this->earliestDatestamp());
    }

    /** @test */
    public function testToString_ContainsRepositoryNameAndBaseURL(): void
    {
        $identity = RepositoryIdentity::fromConfiguration(
            $this->minimalConfig(),
            $this->earliestDatestamp(),
        );
        $result = (string) $identity;
        $this->assertStringContainsString('Test Repository', $result);
        $this->assertStringContainsString('https://example.org/oai', $result);
        $this->assertStringContainsString('2.0', $result);
    }

    /**
     * @test
     * @dataProvider provideValidDeletedRecordValues
     */
    public function testFromConfiguration_AllValidDeletedRecordValues_AreAccepted(
        string $value
    ): void {
        /** @var array<string, mixed> $config */
        $config = array_merge($this->minimalConfig(), ['deleted_record' => $value]);
        $identity = RepositoryIdentity::fromConfiguration($config, $this->earliestDatestamp());
        $this->assertSame($value, $identity->getDeletedRecord()->getDeletedRecord());
    }

    /**
     * @return array<string, array{string}>
     */
    public function provideValidDeletedRecordValues(): array
    {
        return [
            'no'         => ['no'],
            'transient'  => ['transient'],
            'persistent' => ['persistent'],
        ];
    }

    /** @test */
    public function testFromConfiguration_DateTimeGranularity_IsAccepted(): void
    {
        /** @var array<string, mixed> $config */
        $config = array_merge($this->minimalConfig(), [
            'granularity' => 'YYYY-MM-DDThh:mm:ssZ',
        ]);
        $earliest = new UTCdatetime(
            '2000-01-01T00:00:00Z',
            new Granularity('YYYY-MM-DDThh:mm:ssZ'),
        );
        $identity = RepositoryIdentity::fromConfiguration($config, $earliest);
        $this->assertSame('YYYY-MM-DDThh:mm:ssZ', $identity->getGranularity()->getValue());
    }
}
