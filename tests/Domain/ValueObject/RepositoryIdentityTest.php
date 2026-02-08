<?php

/**
 * @author    Paul Slits <paul.slits@gmail.com>
 * @copyright (c) 2025 Paul Slits
 * @license   MIT License - https://opensource.org/licenses/MIT
 * @link      https://github.com/pslits/oai-pmh
 * @since     0.1.0
 */

namespace OaiPmh\Tests\Domain\ValueObject;

use OaiPmh\Domain\ValueObject\AnyUri;
use OaiPmh\Domain\ValueObject\BaseURL;
use OaiPmh\Domain\ValueObject\DeletedRecord;
use OaiPmh\Domain\ValueObject\Description;
use OaiPmh\Domain\ValueObject\DescriptionCollection;
use OaiPmh\Domain\ValueObject\DescriptionFormat;
use OaiPmh\Domain\ValueObject\Email;
use OaiPmh\Domain\ValueObject\EmailCollection;
use OaiPmh\Domain\ValueObject\Granularity;
use OaiPmh\Domain\ValueObject\MetadataNamespace;
use OaiPmh\Domain\ValueObject\MetadataNamespaceCollection;
use OaiPmh\Domain\ValueObject\MetadataRootTag;
use OaiPmh\Domain\ValueObject\NamespacePrefix;
use OaiPmh\Domain\ValueObject\ProtocolVersion;
use OaiPmh\Domain\ValueObject\RepositoryIdentity;
use OaiPmh\Domain\ValueObject\RepositoryName;
use OaiPmh\Domain\ValueObject\UTCdatetime;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * Test suite for the RepositoryIdentity value object.
 *
 * This test suite verifies that RepositoryIdentity correctly encapsulates
 * all OAI-PMH Identify response elements and maintains immutability and
 * value equality semantics.
 *
 * The tests follow BDD-style Given-When-Then structure and include
 * comprehensive coverage of:
 * - Construction with all required elements
 * - Construction with optional descriptions
 * - Value equality
 * - Immutability
 * - String representation
 * - Getter methods for all properties
 */
class RepositoryIdentityTest extends TestCase
{
    /**
     * User Story:
     * As a repository administrator,
     * When I configure my OAI-PMH repository,
     * I want to create a complete repository identity with all required fields,
     * So that my repository can respond to Identify requests correctly.
     */
    public function testCanInstantiateWithRequiredFields(): void
    {
        // Given: All required OAI-PMH Identify elements
        $repositoryName = new RepositoryName('Test Digital Repository');
        $baseURL = new BaseURL('http://example.org/oai');
        $protocolVersion = new ProtocolVersion('2.0');
        $adminEmails = new EmailCollection(new Email('admin@example.org'));
        $granularity = new Granularity('YYYY-MM-DDThh:mm:ssZ');
        $earliestDatestamp = new UTCdatetime('2020-01-01T00:00:00Z', $granularity);
        $deletedRecord = new DeletedRecord('no');

        // When: I create a RepositoryIdentity instance
        $identity = new RepositoryIdentity(
            $repositoryName,
            $baseURL,
            $protocolVersion,
            $adminEmails,
            $earliestDatestamp,
            $deletedRecord,
            $granularity
        );

        // Then: The object should be created successfully
        $this->assertInstanceOf(RepositoryIdentity::class, $identity);
        $this->assertSame($repositoryName, $identity->getRepositoryName());
        $this->assertSame($baseURL, $identity->getBaseURL());
        $this->assertSame($protocolVersion, $identity->getProtocolVersion());
        $this->assertSame($adminEmails, $identity->getAdminEmails());
        $this->assertSame($earliestDatestamp, $identity->getEarliestDatestamp());
        $this->assertSame($deletedRecord, $identity->getDeletedRecord());
        $this->assertSame($granularity, $identity->getGranularity());
    }

    /**
     * User Story:
     * As a repository administrator,
     * When I don't provide optional description containers,
     * I want the repository identity to have an empty DescriptionCollection,
     * So that the identity is valid without requiring optional elements.
     */
    public function testCreatesEmptyDescriptionCollectionWhenNotProvided(): void
    {
        // Given: All required elements but no descriptions
        $identity = $this->createMinimalIdentity();

        // When: I retrieve the descriptions
        $descriptions = $identity->getDescriptions();

        // Then: It should return an empty DescriptionCollection
        $this->assertInstanceOf(DescriptionCollection::class, $descriptions);
        $this->assertCount(0, $descriptions);
    }

    /**
     * User Story:
     * As a repository administrator,
     * When I want to provide additional repository metadata,
     * I want to include description containers in the repository identity,
     * So that I can communicate community-specific information.
     */
    public function testCanInstantiateWithDescriptions(): void
    {
        // Given: A repository identity with optional descriptions
        $description = $this->createSampleDescription();
        $descriptions = new DescriptionCollection($description);

        $granularity = new Granularity('YYYY-MM-DDThh:mm:ssZ');
        $identity = new RepositoryIdentity(
            new RepositoryName('Test Repository'),
            new BaseURL('http://example.org/oai'),
            new ProtocolVersion('2.0'),
            new EmailCollection(new Email('admin@example.org')),
            new UTCdatetime('2020-01-01T00:00:00Z', $granularity),
            new DeletedRecord('no'),
            $granularity,
            $descriptions
        );

        // When: I retrieve the descriptions
        $retrievedDescriptions = $identity->getDescriptions();

        // Then: It should return the provided descriptions
        $this->assertSame($descriptions, $retrievedDescriptions);
        $this->assertCount(1, $retrievedDescriptions);
    }

    /**
     * User Story:
     * As a developer,
     * When I compare two RepositoryIdentity instances with identical values,
     * I want them to be considered equal,
     * So that I can use value-based equality in my domain logic.
     */
    public function testEqualsReturnsTrueForSameValues(): void
    {
        // Given: Two RepositoryIdentity instances with identical values
        $identity1 = $this->createMinimalIdentity();
        $identity2 = $this->createMinimalIdentity();

        // When: I compare them for equality
        $result = $identity1->equals($identity2);

        // Then: They should be equal
        $this->assertTrue($result);
    }

    /**
     * User Story:
     * As a developer,
     * When I compare two RepositoryIdentity instances with different repository names,
     * I want them to be considered not equal,
     * So that value equality correctly distinguishes different identities.
     */
    public function testEqualsReturnsFalseForDifferentRepositoryName(): void
    {
        // Given: Two identities with different repository names
        $identity1 = $this->createMinimalIdentity();

        $granularity = new Granularity('YYYY-MM-DDThh:mm:ssZ');
        $identity2 = new RepositoryIdentity(
            new RepositoryName('Different Repository'),
            new BaseURL('http://example.org/oai'),
            new ProtocolVersion('2.0'),
            new EmailCollection(new Email('admin@example.org')),
            new UTCdatetime('2020-01-01T00:00:00Z', $granularity),
            new DeletedRecord('no'),
            $granularity
        );

        // When: I compare them
        $result = $identity1->equals($identity2);

        // Then: They should not be equal
        $this->assertFalse($result);
    }

    /**
     * User Story:
     * As a developer,
     * When I compare two RepositoryIdentity instances with different base URLs,
     * I want them to be considered not equal,
     * So that value equality works correctly for all fields.
     */
    public function testEqualsReturnsFalseForDifferentBaseURL(): void
    {
        // Given: Two identities with different base URLs
        $identity1 = $this->createMinimalIdentity();

        $granularity = new Granularity('YYYY-MM-DDThh:mm:ssZ');
        $identity2 = new RepositoryIdentity(
            new RepositoryName('Test Digital Repository'),
            new BaseURL('https://different.org/oai'),
            new ProtocolVersion('2.0'),
            new EmailCollection(new Email('admin@example.org')),
            new UTCdatetime('2020-01-01T00:00:00Z', $granularity),
            new DeletedRecord('no'),
            $granularity
        );

        // When: I compare them
        $result = $identity1->equals($identity2);

        // Then: They should not be equal
        $this->assertFalse($result);
    }

    /**
     * User Story:
     * As a developer,
     * When I compare two RepositoryIdentity instances with different admin emails,
     * I want them to be considered not equal,
     * So that all properties contribute to equality.
     */
    public function testEqualsReturnsFalseForDifferentAdminEmails(): void
    {
        // Given: Two identities with different admin emails
        $identity1 = $this->createMinimalIdentity();

        $granularity = new Granularity('YYYY-MM-DDThh:mm:ssZ');
        $identity2 = new RepositoryIdentity(
            new RepositoryName('Test Digital Repository'),
            new BaseURL('http://example.org/oai'),
            new ProtocolVersion('2.0'),
            new EmailCollection(new Email('different@example.org')),
            new UTCdatetime('2020-01-01T00:00:00Z', $granularity),
            new DeletedRecord('no'),
            $granularity
        );

        // When: I compare them
        $result = $identity1->equals($identity2);

        // Then: They should not be equal
        $this->assertFalse($result);
    }

    /**
     * User Story:
     * As a developer,
     * When I compare two RepositoryIdentity instances with different descriptions,
     * I want them to be considered not equal,
     * So that optional fields are also included in equality checks.
     */
    public function testEqualsReturnsFalseForDifferentDescriptions(): void
    {
        // Given: Two identities, one with descriptions and one without
        $identity1 = $this->createMinimalIdentity();

        $description = $this->createSampleDescription();
        $granularity = new Granularity('YYYY-MM-DDThh:mm:ssZ');
        $identity2 = new RepositoryIdentity(
            new RepositoryName('Test Digital Repository'),
            new BaseURL('http://example.org/oai'),
            new ProtocolVersion('2.0'),
            new EmailCollection(new Email('admin@example.org')),
            new UTCdatetime('2020-01-01T00:00:00Z', $granularity),
            new DeletedRecord('no'),
            $granularity,
            new DescriptionCollection($description)
        );

        // When: I compare them
        $result = $identity1->equals($identity2);

        // Then: They should not be equal
        $this->assertFalse($result);
    }

    /**
     * User Story:
     * As a developer,
     * When I need to log or debug repository identity information,
     * I want a clear string representation of the identity,
     * So that I can easily see all its values.
     */
    public function testToStringReturnsExpectedFormat(): void
    {
        // Given: A repository identity
        $identity = $this->createMinimalIdentity();

        // When: I convert it to a string
        $result = (string)$identity;

        // Then: It should contain all the relevant information
        $this->assertStringContainsString('RepositoryIdentity', $result);
        $this->assertStringContainsString('Test Digital Repository', $result);
        $this->assertStringContainsString('http://example.org/oai', $result);
        $this->assertStringContainsString('2.0', $result);
        $this->assertStringContainsString('admin@example.org', $result);
        $this->assertStringContainsString('2020-01-01T00:00:00Z', $result);
    }

    /**
     * User Story:
     * As a developer,
     * When I create a RepositoryIdentity instance,
     * I want it to be immutable,
     * So that I can safely use it throughout my application without fear of modification.
     */
    public function testIsImmutable(): void
    {
        // Given: A RepositoryIdentity instance
        $identity = $this->createMinimalIdentity();

        // When: I check if all properties are private
        $reflection = new ReflectionClass($identity);
        $properties = $reflection->getProperties();

        // Then: All properties should be private
        foreach ($properties as $property) {
            $this->assertTrue(
                $property->isPrivate(),
                sprintf('Property %s should be private', $property->getName())
            );
        }

        // And: The class should be final
        $this->assertTrue($reflection->isFinal(), 'RepositoryIdentity should be final');
    }

    /**
     * User Story:
     * As a developer,
     * When I create a repository identity with multiple admin emails,
     * I want all emails to be accessible,
     * So that I can contact all repository administrators.
     */
    public function testCanInstantiateWithMultipleAdminEmails(): void
    {
        // Given: Multiple admin emails
        $adminEmails = new EmailCollection(
            new Email('admin1@example.org'),
            new Email('admin2@example.org'),
            new Email('admin3@example.org')
        );

        $granularity = new Granularity('YYYY-MM-DDThh:mm:ssZ');
        $identity = new RepositoryIdentity(
            new RepositoryName('Test Repository'),
            new BaseURL('http://example.org/oai'),
            new ProtocolVersion('2.0'),
            $adminEmails,
            new UTCdatetime('2020-01-01T00:00:00Z', $granularity),
            new DeletedRecord('no'),
            $granularity
        );

        // When: I retrieve the admin emails
        $retrievedEmails = $identity->getAdminEmails();

        // Then: All emails should be present
        $this->assertCount(3, $retrievedEmails);
        $this->assertSame($adminEmails, $retrievedEmails);
    }

    /**
     * User Story:
     * As a repository administrator,
     * When I configure my repository with deleted record support,
     * I want the deleted record policy to be correctly stored,
     * So that harvesters know how deletions are handled.
     */
    public function testStoresDeletedRecordPolicyCorrectly(): void
    {
        // Given: Different deleted record policies
        $policies = ['no', 'persistent', 'transient'];

        foreach ($policies as $policyValue) {
            $deletedRecord = new DeletedRecord($policyValue);
            $granularity = new Granularity('YYYY-MM-DDThh:mm:ssZ');

            $identity = new RepositoryIdentity(
                new RepositoryName('Test Repository'),
                new BaseURL('http://example.org/oai'),
                new ProtocolVersion('2.0'),
                new EmailCollection(new Email('admin@example.org')),
                new UTCdatetime('2020-01-01T00:00:00Z', $granularity),
                $deletedRecord,
                $granularity
            );

            // When: I retrieve the deleted record policy
            $retrieved = $identity->getDeletedRecord();

            // Then: It should match the provided policy
            $this->assertSame($deletedRecord, $retrieved);
            $this->assertSame($policyValue, $retrieved->getDeletedRecord());
        }
    }

    /**
     * User Story:
     * As a repository administrator,
     * When I configure my repository with different granularity levels,
     * I want the granularity to be correctly stored,
     * So that harvesters know the precision of datestamps.
     */
    public function testStoresGranularityCorrectly(): void
    {
        // Given: Different granularity formats
        $granularityDay = new Granularity('YYYY-MM-DD');
        $granularitySecond = new Granularity('YYYY-MM-DDThh:mm:ssZ');

        $identity1 = new RepositoryIdentity(
            new RepositoryName('Test Repository'),
            new BaseURL('http://example.org/oai'),
            new ProtocolVersion('2.0'),
            new EmailCollection(new Email('admin@example.org')),
            new UTCdatetime('2020-01-01', $granularityDay),
            new DeletedRecord('no'),
            $granularityDay
        );

        $identity2 = new RepositoryIdentity(
            new RepositoryName('Test Repository'),
            new BaseURL('http://example.org/oai'),
            new ProtocolVersion('2.0'),
            new EmailCollection(new Email('admin@example.org')),
            new UTCdatetime('2020-01-01T00:00:00Z', $granularitySecond),
            new DeletedRecord('no'),
            $granularitySecond
        );

        // When: I retrieve the granularity
        $retrieved1 = $identity1->getGranularity();
        $retrieved2 = $identity2->getGranularity();

        // Then: They should match the provided values
        $this->assertSame($granularityDay, $retrieved1);
        $this->assertSame($granularitySecond, $retrieved2);
        $this->assertSame('YYYY-MM-DD', $retrieved1->getValue());
        $this->assertSame('YYYY-MM-DDThh:mm:ssZ', $retrieved2->getValue());
    }

    /**
     * Helper method to create a minimal RepositoryIdentity for testing.
     */
    private function createMinimalIdentity(): RepositoryIdentity
    {
        $granularity = new Granularity('YYYY-MM-DDThh:mm:ssZ');
        return new RepositoryIdentity(
            new RepositoryName('Test Digital Repository'),
            new BaseURL('http://example.org/oai'),
            new ProtocolVersion('2.0'),
            new EmailCollection(new Email('admin@example.org')),
            new UTCdatetime('2020-01-01T00:00:00Z', $granularity),
            new DeletedRecord('no'),
            $granularity
        );
    }

    /**
     * Helper method to create a sample Description for testing.
     */
    private function createSampleDescription(): Description
    {
        $namespace = new MetadataNamespace(
            new NamespacePrefix('oai-identifier'),
            new AnyUri('http://www.openarchives.org/OAI/2.0/oai-identifier')
        );

        $format = new DescriptionFormat(
            null,
            new MetadataNamespaceCollection($namespace),
            new AnyUri('http://www.openarchives.org/OAI/2.0/oai-identifier.xsd'),
            new MetadataRootTag('oai-identifier')
        );

        $data = [
            'scheme' => 'oai',
            'repositoryIdentifier' => 'example.org',
            'delimiter' => ':',
            'sampleIdentifier' => 'oai:example.org:12345'
        ];

        return new Description($format, $data);
    }
}
