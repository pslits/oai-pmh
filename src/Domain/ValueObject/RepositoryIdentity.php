<?php

/**
 * @author    Paul Slits <paul.slits@gmail.com>
 * @copyright (c) 2025 Paul Slits
 * @license   MIT License - https://opensource.org/licenses/MIT
 * @link      https://github.com/pslits/oai-pmh
 * @since     0.1.0
 */

namespace OaiPmh\Domain\ValueObject;

/**
 * Represents the complete identity information of an OAI-PMH repository.
 *
 * According to OAI-PMH 2.0 specification section 4.2 (Identify), repositories
 * must respond to the Identify request with a specific set of required and
 * optional elements that describe the repository's characteristics and capabilities.
 *
 * This value object:
 * - aggregates all repository identification value objects,
 * - is immutable and compared by value (not identity),
 * - ensures all required OAI-PMH Identify elements are present,
 * - supports optional description containers for extended metadata,
 * - serves as the domain representation of an Identify response.
 *
 * Required OAI-PMH Identify elements (per specification section 4.2):
 * - repositoryName: A human-readable name for the repository
 * - baseURL: The base URL of the repository (HTTP/HTTPS)
 * - protocolVersion: The version of the OAI-PMH protocol supported (currently '2.0')
 * - adminEmail: At least one email address of a repository administrator
 * - earliestDatestamp: The guaranteed lower limit of all datestamps (UTC)
 * - deletedRecord: The deleted record support policy (no/transient/persistent)
 * - granularity: The finest harvesting granularity supported (date/datetime)
 *
 * Optional OAI-PMH Identify elements (per specification section 4.2):
 * - description: Zero or more extensible containers for community-specific metadata
 *
 * @see http://www.openarchives.org/OAI/openarchivesprotocol.html#Identify
 * @see OAI-PMH 2.0 Specification Section 4.2
 */
final class RepositoryIdentity
{
    private RepositoryName $repositoryName;
    private BaseURL $baseURL;
    private ProtocolVersion $protocolVersion;
    private EmailCollection $adminEmails;
    private UTCdatetime $earliestDatestamp;
    private DeletedRecord $deletedRecord;
    private Granularity $granularity;
    private DescriptionCollection $descriptions;

    /**
     * Constructs a new RepositoryIdentity instance.
     *
     * This represents the complete identity of an OAI-PMH repository,
     * including all required metadata and optional description containers.
     *
     * @param RepositoryName $repositoryName A human-readable name for the repository.
     * @param BaseURL $baseURL The base URL of the repository.
     * @param ProtocolVersion $protocolVersion The OAI-PMH protocol version(s) supported.
     * @param EmailCollection $adminEmails Administrative email address(es).
     * @param UTCdatetime $earliestDatestamp The guaranteed lower limit on all datestamps.
     * @param DeletedRecord $deletedRecord The deleted record support policy.
     * @param Granularity $granularity The finest temporal granularity supported.
     * @param DescriptionCollection|null $descriptions Optional repository descriptions.
     */
    public function __construct(
        RepositoryName $repositoryName,
        BaseURL $baseURL,
        ProtocolVersion $protocolVersion,
        EmailCollection $adminEmails,
        UTCdatetime $earliestDatestamp,
        DeletedRecord $deletedRecord,
        Granularity $granularity,
        ?DescriptionCollection $descriptions = null
    ) {
        $this->repositoryName = $repositoryName;
        $this->baseURL = $baseURL;
        $this->protocolVersion = $protocolVersion;
        $this->adminEmails = $adminEmails;
        $this->earliestDatestamp = $earliestDatestamp;
        $this->deletedRecord = $deletedRecord;
        $this->granularity = $granularity;
        $this->descriptions = $descriptions ?? new DescriptionCollection();
    }

    /**
     * Get the repository name.
     *
     * @return RepositoryName The human-readable name of the repository.
     */
    public function getRepositoryName(): RepositoryName
    {
        return $this->repositoryName;
    }

    /**
     * Get the base URL.
     *
     * @return BaseURL The base URL of the repository.
     */
    public function getBaseURL(): BaseURL
    {
        return $this->baseURL;
    }

    /**
     * Get the protocol version.
     *
     * @return ProtocolVersion The OAI-PMH protocol version supported.
     */
    public function getProtocolVersion(): ProtocolVersion
    {
        return $this->protocolVersion;
    }

    /**
     * Get the administrative emails.
     *
     * @return EmailCollection The collection of administrative email addresses.
     */
    public function getAdminEmails(): EmailCollection
    {
        return $this->adminEmails;
    }

    /**
     * Get the earliest datestamp.
     *
     * @return UTCdatetime The guaranteed lower limit on all datestamps in the repository.
     */
    public function getEarliestDatestamp(): UTCdatetime
    {
        return $this->earliestDatestamp;
    }

    /**
     * Get the deleted record policy.
     *
     * @return DeletedRecord The deleted record support policy.
     */
    public function getDeletedRecord(): DeletedRecord
    {
        return $this->deletedRecord;
    }

    /**
     * Get the granularity.
     *
     * @return Granularity The finest temporal granularity supported by the repository.
     */
    public function getGranularity(): Granularity
    {
        return $this->granularity;
    }

    /**
     * Get the description collection.
     *
     * @return DescriptionCollection The collection of optional repository descriptions.
     */
    public function getDescriptions(): DescriptionCollection
    {
        return $this->descriptions;
    }

    /**
     * Checks if this RepositoryIdentity is equal to another.
     *
     * Two RepositoryIdentity instances are considered equal if all their
     * constituent value objects are equal.
     *
     * @param RepositoryIdentity $other The other RepositoryIdentity to compare with.
     * @return bool True if both instances have equal values, false otherwise.
     */
    public function equals(self $other): bool
    {
        return $this->repositoryName->equals($other->repositoryName)
            && $this->baseURL->equals($other->baseURL)
            && $this->protocolVersion->equals($other->protocolVersion)
            && $this->adminEmails->equals($other->adminEmails)
            && $this->earliestDatestamp->equals($other->earliestDatestamp)
            && $this->deletedRecord->equals($other->deletedRecord)
            && $this->granularity->equals($other->granularity)
            && $this->descriptions->equals($other->descriptions);
    }

    /**
     * Returns a string representation of the RepositoryIdentity.
     *
     * This is useful for debugging and logging purposes.
     *
     * @return string A string representation of the repository identity.
     */
    public function __toString(): string
    {
        return sprintf(
            'RepositoryIdentity(repositoryName: %s, baseURL: %s, protocolVersion: %s, ' .
            'adminEmails: %s, earliestDatestamp: %s, deletedRecord: %s, granularity: %s, descriptions: %s)',
            (string)$this->repositoryName,
            (string)$this->baseURL,
            (string)$this->protocolVersion,
            (string)$this->adminEmails,
            (string)$this->earliestDatestamp,
            (string)$this->deletedRecord,
            (string)$this->granularity,
            (string)$this->descriptions
        );
    }
}
