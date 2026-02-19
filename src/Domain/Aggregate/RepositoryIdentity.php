<?php

/**
 * This file defines the RepositoryIdentity aggregate, which encapsulates the complete identity of an OAI-PMH
 * repository.
 *
 * @author    Paul Slits <paul.slits@gmail.com>
 * @copyright (c) 2025 Paul Slits
 * @license   MIT License - https://opensource.org/licenses/MIT
 * @link      https://github.com/pslits/oai-pmh
 * @since     0.1.0
 */

declare(strict_types=1);

namespace OaiPmh\Domain\Aggregate;

use InvalidArgumentException;
use OaiPmh\Domain\Exception\InvalidConfigurationException;
use OaiPmh\Domain\ValueObject\BaseURL;
use OaiPmh\Domain\ValueObject\DeletedRecord;
use OaiPmh\Domain\ValueObject\DescriptionCollection;
use OaiPmh\Domain\ValueObject\Email;
use OaiPmh\Domain\ValueObject\EmailCollection;
use OaiPmh\Domain\ValueObject\Granularity;
use OaiPmh\Domain\ValueObject\ProtocolVersion;
use OaiPmh\Domain\ValueObject\RepositoryName;
use OaiPmh\Domain\ValueObject\UTCdatetime;

/**
 * Represents the complete identity of an OAI-PMH repository.
 *
 * According to OAI-PMH 2.0 specification section 4.2 (Identify), a repository must
 * respond to the Identify verb with a set of elements that describe itself. This
 * aggregate owns and validates all of that identity data.
 *
 * Required elements (always present):
 * - repositoryName    — human-readable name of the repository
 * - baseURL           — base URL for all OAI-PMH requests
 * - protocolVersion   — always 2.0 (hardcoded; it is a protocol constant)
 * - adminEmail(s)     — one or more administrator email addresses
 * - earliestDatestamp — datestamp of the oldest record in the repository
 * - deletedRecord     — policy for deleted records: no, transient, or persistent
 * - granularity       — finest datestamp granularity: YYYY-MM-DD or YYYY-MM-DDThh:mm:ssZ
 *
 * Optional elements:
 * - compression    — supported compression encodings (e.g. gzip, deflate)
 * - description(s) — extensible repository description containers
 *
 * This aggregate:
 * - is created exclusively via the named constructor fromConfiguration() with an injected earliestDatestamp,
 * - keeps earliestDatestamp injected (not queried here) to remain free of infrastructure concerns,
 * - hardcodes protocolVersion as 2.0 since it is a protocol constant,
 * - treats compression as a plain string array because these are infrastructure hints, not domain concepts,
 * - is immutable after creation.
 *
 * Expected configuration array shape (mirrors YAML repository section):
 * <code>
 * [
 *     'repository_name' => 'My Repository',
 *     'base_url'        => 'https://example.org/oai',
 *     'admin_emails'    => ['admin@example.org'],
 *     'deleted_record'  => 'transient',
 *     'granularity'     => 'YYYY-MM-DD',
 *     'compression'     => [],             // optional; defaults to []
 *     'descriptions'    => [],             // optional; defaults to empty collection
 * ]
 * </code>
 *
 * @see http://www.openarchives.org/OAI/openarchivesprotocol.html#Identify
 */
final class RepositoryIdentity
{
    /**
     * Configuration keys that must be present in the configuration array.
     */
    private const REQUIRED_KEYS = [
        'repository_name',
        'base_url',
        'admin_emails',
        'deleted_record',
        'granularity',
    ];

    private RepositoryName $repositoryName;
    private BaseURL $baseURL;
    private ProtocolVersion $protocolVersion;
    private EmailCollection $adminEmails;
    private UTCdatetime $earliestDatestamp;
    private DeletedRecord $deletedRecord;
    private Granularity $granularity;

    /** @var string[] */
    private array $compression;

    private DescriptionCollection $descriptions;

    /**
     * This constructor is private — use RepositoryIdentity::fromConfiguration() to create instances.
     *
     * Stores all pre-validated identity components. Callers must invoke the named
     * constructor, which runs the full configuration validation pipeline before delegating here.
     *
     * @param RepositoryName        $repositoryName    The repository name.
     * @param BaseURL               $baseURL           The repository base URL.
     * @param ProtocolVersion       $protocolVersion   The OAI-PMH protocol version (always 2.0).
     * @param EmailCollection       $adminEmails       One or more administrator email addresses.
     * @param UTCdatetime           $earliestDatestamp The datestamp of the earliest record.
     * @param DeletedRecord         $deletedRecord     The deleted-record support policy.
     * @param Granularity           $granularity       The finest supported datestamp granularity.
     * @param string[]              $compression       Supported compression encodings (may be empty).
     * @param DescriptionCollection $descriptions      Optional description containers.
     */
    private function __construct(
        RepositoryName $repositoryName,
        BaseURL $baseURL,
        ProtocolVersion $protocolVersion,
        EmailCollection $adminEmails,
        UTCdatetime $earliestDatestamp,
        DeletedRecord $deletedRecord,
        Granularity $granularity,
        array $compression,
        DescriptionCollection $descriptions
    ) {
        $this->repositoryName    = $repositoryName;
        $this->baseURL           = $baseURL;
        $this->protocolVersion   = $protocolVersion;
        $this->adminEmails       = $adminEmails;
        $this->earliestDatestamp = $earliestDatestamp;
        $this->deletedRecord     = $deletedRecord;
        $this->granularity       = $granularity;
        $this->compression       = $compression;
        $this->descriptions      = $descriptions;
    }

    /**
     * This method creates a validated RepositoryIdentity from a configuration array and an injected earliestDatestamp.
     *
     * The earliestDatestamp is supplied separately because it comes from a database query
     * (the oldest datestamp in the repository), not from the static configuration file.
     * Separating it keeps this aggregate free of infrastructure concerns.
     *
     * @param array<string, mixed> $config            The repository configuration section.
     * @param UTCdatetime          $earliestDatestamp The earliest record datestamp from the DB.
     *
     * @return self A validated, immutable RepositoryIdentity instance.
     *
     * @throws InvalidConfigurationException If a required key is missing, or a value is invalid.
     */
    public static function fromConfiguration(array $config, UTCdatetime $earliestDatestamp): self
    {
        self::assertRequiredKeys($config);

        $granularity = self::buildGranularity($config['granularity']);

        return new self(
            repositoryName:    self::buildRepositoryName($config['repository_name']),
            baseURL:           self::buildBaseURL($config['base_url']),
            protocolVersion:   new ProtocolVersion('2.0'),
            adminEmails:       self::buildEmailCollection($config['admin_emails']),
            earliestDatestamp: $earliestDatestamp,
            deletedRecord:     self::buildDeletedRecord($config['deleted_record']),
            granularity:       $granularity,
            compression:       self::buildCompression($config['compression'] ?? []),
            descriptions:      self::buildDescriptionCollection(),
        );
    }

    // -----------------------------------------------------------------------
    // Getters
    // -----------------------------------------------------------------------

    /**
     * This method returns the human-readable name of the repository.
     *
     * Provides typed access to the repository name as validated during construction.
     *
     * @return RepositoryName The repository name value object.
     */
    public function getRepositoryName(): RepositoryName
    {
        return $this->repositoryName;
    }

    /**
     * This method returns the base URL for all OAI-PMH requests.
     *
     * Provides typed access to the base URL as validated during construction.
     *
     * @return BaseURL The base URL value object.
     */
    public function getBaseURL(): BaseURL
    {
        return $this->baseURL;
    }

    /**
     * This method returns the OAI-PMH protocol version (always 2.0).
     *
     * The protocol version is hardcoded to 2.0 and is not configurable.
     *
     * @return ProtocolVersion The protocol version value object.
     */
    public function getProtocolVersion(): ProtocolVersion
    {
        return $this->protocolVersion;
    }

    /**
     * This method returns the collection of administrator email addresses.
     *
     * At least one email address is always present; the collection is never empty.
     *
     * @return EmailCollection The collection of administrator email value objects.
     */
    public function getAdminEmails(): EmailCollection
    {
        return $this->adminEmails;
    }

    /**
     * This method returns the datestamp of the earliest record in the repository.
     *
     * This value is injected at construction time from an external query and is not derived from configuration.
     *
     * @return UTCdatetime The earliest datestamp value object.
     */
    public function getEarliestDatestamp(): UTCdatetime
    {
        return $this->earliestDatestamp;
    }

    /**
     * This method returns the deleted-record support policy.
     *
     * Indicates whether the repository maintains deletion information: no, transient, or persistent.
     *
     * @return DeletedRecord The deleted-record policy value object.
     */
    public function getDeletedRecord(): DeletedRecord
    {
        return $this->deletedRecord;
    }

    /**
     * This method returns the finest datestamp granularity supported by this repository.
     *
     * Determines whether harvesters may use day-precision or second-precision datestamps in requests.
     *
     * @return Granularity The granularity value object.
     */
    public function getGranularity(): Granularity
    {
        return $this->granularity;
    }

    /**
     * This method returns the list of supported compression encodings.
     *
     * An empty array means no compression is advertised in the Identify response.
     *
     * @return string[] Supported compression encoding names (e.g. gzip, deflate).
     */
    public function getCompression(): array
    {
        return $this->compression;
    }

    /**
     * This method returns the collection of description containers.
     *
     * An empty collection means no descriptions are provided in the Identify response.
     *
     * @return DescriptionCollection The collection of description containers.
     */
    public function getDescriptions(): DescriptionCollection
    {
        return $this->descriptions;
    }

    /**
     * This method returns a string representation of this RepositoryIdentity.
     *
     * Produces a debug-friendly representation that includes the repository name, base URL, and protocol version.
     *
     * @return string A debug-friendly string in the format RepositoryIdentity(name: X, baseURL: Y, protocolVersion: Z).
     */
    public function __toString(): string
    {
        return sprintf(
            'RepositoryIdentity(name: %s, baseURL: %s, protocolVersion: %s)',
            $this->repositoryName->getRepositoryName(),
            $this->baseURL->getBaseUrl(),
            $this->protocolVersion->getProtocolVersion(),
        );
    }

    // -----------------------------------------------------------------------
    // Private factory helpers
    // -----------------------------------------------------------------------

    /**
     * This method asserts that all required configuration keys are present.
     *
     * Iterates over REQUIRED_KEYS and throws on the first missing key found.
     *
     * @param array<string, mixed> $config The raw configuration array to validate.
     *
     * @throws InvalidConfigurationException On the first missing key found.
     */
    private static function assertRequiredKeys(array $config): void
    {
        foreach (self::REQUIRED_KEYS as $key) {
            if (!array_key_exists($key, $config)) {
                throw InvalidConfigurationException::missingKey($key);
            }
        }
    }

    /**
     * This method builds a RepositoryName value object from a raw configuration value.
     *
     * Validates that the value is a non-empty string before delegating to the RepositoryName constructor.
     *
     * @param mixed $value The raw configuration value for repository_name.
     *
     * @throws InvalidConfigurationException If the value is not a non-empty string.
     */
    private static function buildRepositoryName(mixed $value): RepositoryName
    {
        if (!is_string($value) || trim($value) === '') {
            throw InvalidConfigurationException::invalidValue(
                'repository_name',
                'must be a non-empty string.'
            );
        }

        try {
            return new RepositoryName($value);
        } catch (InvalidArgumentException $e) {
            throw InvalidConfigurationException::invalidValue('repository_name', $e->getMessage());
        }
    }

    /**
     * This method builds a BaseURL value object from a raw configuration value.
     *
     * Validates that the value is a non-empty string before delegating to the BaseURL constructor.
     *
     * @param mixed $value The raw configuration value for base_url.
     *
     * @throws InvalidConfigurationException If the value is not a valid HTTP/HTTPS URL string.
     */
    private static function buildBaseURL(mixed $value): BaseURL
    {
        if (!is_string($value) || trim($value) === '') {
            throw InvalidConfigurationException::invalidValue(
                'base_url',
                'must be a non-empty string.'
            );
        }

        try {
            return new BaseURL($value);
        } catch (InvalidArgumentException $e) {
            throw InvalidConfigurationException::invalidValue('base_url', $e->getMessage());
        }
    }

    /**
     * This method builds an EmailCollection from the raw admin_emails configuration value.
     *
     * Validates that the value is a non-empty array and that each entry is a valid email address string.
     *
     * @param mixed $value The raw configuration value for admin_emails.
     *
     * @throws InvalidConfigurationException If the value is not a non-empty array of valid email address strings.
     */
    private static function buildEmailCollection(mixed $value): EmailCollection
    {
        if (!is_array($value) || count($value) === 0) {
            throw InvalidConfigurationException::invalidValue(
                'admin_emails',
                'must be a non-empty array of email address strings.'
            );
        }

        $emails = [];

        foreach ($value as $index => $raw) {
            if (!is_string($raw)) {
                throw InvalidConfigurationException::invalidValue(
                    'admin_emails',
                    sprintf('entry at index %d must be a string.', $index)
                );
            }

            try {
                $emails[] = new Email($raw);
            } catch (InvalidArgumentException $e) {
                throw InvalidConfigurationException::invalidValue(
                    'admin_emails',
                    sprintf('entry "%s" is not a valid email address: %s', $raw, $e->getMessage())
                );
            }
        }

        try {
            return new EmailCollection(...$emails);
        } catch (InvalidArgumentException $e) {
            throw InvalidConfigurationException::invalidValue('admin_emails', $e->getMessage());
        }
    }

    /**
     * This method builds a DeletedRecord value object from a raw configuration value.
     *
     * Validates that the value is a string before delegating to the DeletedRecord constructor.
     *
     * @param mixed $value The raw configuration value for deleted_record.
     *
     * @throws InvalidConfigurationException If the value is not one of no, transient, or persistent.
     */
    private static function buildDeletedRecord(mixed $value): DeletedRecord
    {
        if (!is_string($value)) {
            throw InvalidConfigurationException::invalidValue(
                'deleted_record',
                'must be one of "no", "transient", or "persistent".'
            );
        }

        try {
            return new DeletedRecord($value);
        } catch (InvalidArgumentException $e) {
            throw InvalidConfigurationException::invalidValue('deleted_record', $e->getMessage());
        }
    }

    /**
     * This method builds a Granularity value object from a raw configuration value.
     *
     * Validates that the value is a string before delegating to the Granularity constructor.
     *
     * @param mixed $value The raw configuration value for granularity.
     *
     * @throws InvalidConfigurationException If the value is not YYYY-MM-DD or YYYY-MM-DDThh:mm:ssZ.
     */
    private static function buildGranularity(mixed $value): Granularity
    {
        if (!is_string($value)) {
            throw InvalidConfigurationException::invalidValue(
                'granularity',
                'must be "YYYY-MM-DD" or "YYYY-MM-DDThh:mm:ssZ".'
            );
        }

        try {
            return new Granularity($value);
        } catch (InvalidArgumentException $e) {
            throw InvalidConfigurationException::invalidValue('granularity', $e->getMessage());
        }
    }

    /**
     * This method validates and returns the compression encoding array.
     *
     * Validates that the value is an array of non-empty strings, one per supported encoding.
     *
     * @param mixed $value The raw configuration value for compression.
     *
     * @return string[] Validated compression encoding names.
     *
     * @throws InvalidConfigurationException If the value is not an array of non-empty strings.
     */
    private static function buildCompression(mixed $value): array
    {
        if (!is_array($value)) {
            throw InvalidConfigurationException::invalidValue(
                'compression',
                'must be an array of strings (e.g. ["gzip", "deflate"]).'
            );
        }

        /** @var string[] $result */
        $result = [];

        foreach ($value as $index => $entry) {
            if (!is_string($entry) || trim($entry) === '') {
                throw InvalidConfigurationException::invalidValue(
                    'compression',
                    sprintf('entry at index %d must be a non-empty string.', $index)
                );
            }
            $result[] = $entry;
        }

        return $result;
    }

    /**
     * This method returns an empty DescriptionCollection as the default for configuration-based loading.
     *
     * Description objects are complex — each requires a DescriptionFormat and structured data —
     * and cannot be meaningfully expressed as flat YAML strings. Callers that need descriptions
     * should use withDescriptions() after initial construction.
     *
     * @return DescriptionCollection An empty collection.
     */
    private static function buildDescriptionCollection(): DescriptionCollection
    {
        return new DescriptionCollection();
    }

    /**
     * This method returns a new RepositoryIdentity with the given descriptions applied.
     *
     * Description objects are structured domain objects that cannot be expressed as flat configuration
     * values. Use this method after fromConfiguration() to attach description containers programmatically.
     *
     * @param DescriptionCollection $descriptions The description containers to attach.
     *
     * @return self A new immutable RepositoryIdentity instance with the descriptions set.
     */
    public function withDescriptions(DescriptionCollection $descriptions): self
    {
        $clone               = clone $this;
        $clone->descriptions = $descriptions;

        return $clone;
    }
}
