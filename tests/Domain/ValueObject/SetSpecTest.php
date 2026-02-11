<?php

/**
 * @author    Paul Slits <paul.slits@gmail.com>
 * @copyright (c) 2025 Paul Slits
 * @license   MIT License - https://opensource.org/licenses/MIT
 * @link      https://github.com/pslits/oai-pmh
 * @since     0.1.0
 */

namespace OaiPmh\Tests\Domain\ValueObject;

use InvalidArgumentException;
use OaiPmh\Domain\ValueObject\SetSpec;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for SetSpec value object.
 *
 * Tests the OAI-PMH setSpec implementation following the specification
 * section 4.6 (SelectiveHarvesting). SetSpec is a colon-separated list
 * that indicates the set membership of an item.
 *
 * @covers \OaiPmh\Domain\ValueObject\SetSpec
 */
final class SetSpecTest extends TestCase
{
    /**
     * @test
     * Given a valid simple setSpec
     * When I create a SetSpec
     * Then it should be created successfully
     */
    public function testConstructWithValidSimpleSetSpec(): void
    {
        // Given
        $spec = 'mathematics';

        // When
        $setSpec = new SetSpec($spec);

        // Then
        $this->assertInstanceOf(SetSpec::class, $setSpec);
        $this->assertSame($spec, $setSpec->getSetSpec());
        $this->assertSame($spec, $setSpec->getValue());
    }

    /**
     * @test
     * Given a valid hierarchical setSpec
     * When I create a SetSpec
     * Then it should be created successfully
     */
    public function testConstructWithValidHierarchicalSetSpec(): void
    {
        // Given
        $spec = 'math:algebra';

        // When
        $setSpec = new SetSpec($spec);

        // Then
        $this->assertSame($spec, $setSpec->getSetSpec());
    }

    /**
     * @test
     * Given a valid deep hierarchical setSpec
     * When I create a SetSpec
     * Then it should be created successfully
     */
    public function testConstructWithValidDeepHierarchicalSetSpec(): void
    {
        // Given
        $spec = 'science:mathematics:algebra:linear';

        // When
        $setSpec = new SetSpec($spec);

        // Then
        $this->assertSame($spec, $setSpec->getSetSpec());
    }

    /**
     * @test
     * Given a setSpec with allowed special characters
     * When I create a SetSpec
     * Then it should be created successfully
     */
    public function testConstructWithAllowedSpecialCharacters(): void
    {
        // Given - alphanumeric, hyphen, underscore, period, colon
        $spec = 'set-name_with.special:chars123';

        // When
        $setSpec = new SetSpec($spec);

        // Then
        $this->assertSame($spec, $setSpec->getSetSpec());
    }

    /**
     * @test
     * Given an empty string
     * When I create a SetSpec
     * Then it should throw InvalidArgumentException
     */
    public function testConstructWithEmptyStringThrowsException(): void
    {
        // Expect
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('SetSpec cannot be empty');

        // When
        new SetSpec('');
    }

    /**
     * @test
     * Given a whitespace-only string
     * When I create a SetSpec
     * Then it should throw InvalidArgumentException
     */
    public function testConstructWithWhitespaceOnlyThrowsException(): void
    {
        // Expect
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('SetSpec cannot be empty');

        // When
        new SetSpec('   ');
    }

    /**
     * @test
     * Given a setSpec with invalid characters (spaces)
     * When I create a SetSpec
     * Then it should throw InvalidArgumentException
     */
    public function testConstructWithSpacesThrowsException(): void
    {
        // Expect
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('SetSpec contains invalid characters');

        // When
        new SetSpec('invalid set');
    }

    /**
     * @test
     * Given a setSpec with invalid characters (special chars)
     * When I create a SetSpec
     * Then it should throw InvalidArgumentException
     */
    public function testConstructWithInvalidSpecialCharactersThrowsException(): void
    {
        // Expect
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('SetSpec contains invalid characters');

        // When
        new SetSpec('set@name!');
    }

    /**
     * @test
     * Given two SetSpecs with the same spec
     * When I compare them using equals()
     * Then equals() should return true
     */
    public function testEqualsSameSpec(): void
    {
        // Given
        $setSpec1 = new SetSpec('math:algebra');
        $setSpec2 = new SetSpec('math:algebra');

        // When/Then
        $this->assertTrue($setSpec1->equals($setSpec2));
        $this->assertTrue($setSpec2->equals($setSpec1));
    }

    /**
     * @test
     * Given two SetSpecs with different specs
     * When I compare them using equals()
     * Then equals() should return false
     */
    public function testEqualsDifferentSpec(): void
    {
        // Given
        $setSpec1 = new SetSpec('math:algebra');
        $setSpec2 = new SetSpec('math:geometry');

        // When/Then
        $this->assertFalse($setSpec1->equals($setSpec2));
        $this->assertFalse($setSpec2->equals($setSpec1));
    }

    /**
     * @test
     * Given a SetSpec
     * When I call __toString()
     * Then it should return a string representation
     */
    public function testToString(): void
    {
        // Given
        $spec = 'math:algebra';
        $setSpec = new SetSpec($spec);

        // When
        $string = (string) $setSpec;

        // Then
        $this->assertStringContainsString('SetSpec', $string);
        $this->assertStringContainsString($spec, $string);
    }

    /**
     * @test
     * Given a SetSpec
     * When I try to modify it
     * Then it should remain immutable
     */
    public function testImmutability(): void
    {
        // Given
        $spec = 'mathematics';
        $setSpec = new SetSpec($spec);

        // When/Then - SetSpec has no setters
        $this->assertSame($spec, $setSpec->getSetSpec());

        // Creating a new instance doesn't affect the original
        $newSetSpec = new SetSpec('physics');
        $this->assertSame($spec, $setSpec->getSetSpec());
        $this->assertNotSame($setSpec->getSetSpec(), $newSetSpec->getSetSpec());
    }

    /**
     * @test
     * Given a setSpec with numbers
     * When I create a SetSpec
     * Then it should be created successfully
     */
    public function testConstructWithNumbers(): void
    {
        // Given
        $spec = 'collection123:subset456';

        // When
        $setSpec = new SetSpec($spec);

        // Then
        $this->assertSame($spec, $setSpec->getSetSpec());
    }

    /**
     * @test
     * Given a setSpec that starts with a number
     * When I create a SetSpec
     * Then it should be created successfully
     */
    public function testConstructWithLeadingNumber(): void
    {
        // Given
        $spec = '2024-collection';

        // When
        $setSpec = new SetSpec($spec);

        // Then
        $this->assertSame($spec, $setSpec->getSetSpec());
    }

    /**
     * User Story:
     * As a developer,
     * I want SetSpec to reject double colons
     * So that hierarchical sets have consistent formatting.
     */
    public function testRejectsDoubleColon(): void
    {
        // Given: A setSpec with double colon (empty segment)
        $invalidSetSpec = 'math::algebra';

        // When/Then: It should throw an exception
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('SetSpec contains invalid characters');
        new SetSpec($invalidSetSpec);
    }

    /**
     * User Story:
     * As a developer,
     * I want SetSpec to reject leading colons
     * So that hierarchical sets have proper first segments.
     */
    public function testRejectsLeadingColon(): void
    {
        // Given: A setSpec with leading colon
        $invalidSetSpec = ':math';

        // When/Then: It should throw an exception
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('SetSpec contains invalid characters');
        new SetSpec($invalidSetSpec);
    }

    /**
     * User Story:
     * As a developer,
     * I want SetSpec to reject trailing colons
     * So that hierarchical sets have valid end segments.
     */
    public function testRejectsTrailingColon(): void
    {
        // Given: A setSpec with trailing colon
        $invalidSetSpec = 'math:';

        // When/Then: It should throw an exception
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('SetSpec contains invalid characters');
        new SetSpec($invalidSetSpec);
    }

    /**
     * User Story:
     * As a developer,
     * I want to create valid hierarchical sets
     * So that I can organize records into nested categories.
     */
    public function testAcceptsValidHierarchicalSet(): void
    {
        // Given: A valid hierarchical setSpec
        $validSetSpec = 'sciences:physics:quantum';

        // When: Creating a SetSpec
        $setSpec = new SetSpec($validSetSpec);

        // Then: It should be created successfully
        $this->assertInstanceOf(SetSpec::class, $setSpec);
        $this->assertSame($validSetSpec, $setSpec->getSetSpec());
    }

    /**
     * User Story:
     * As a developer,
     * I want to create multi-level hierarchical sets
     * So that I can have deep categorization.
     */
    public function testAcceptsDeepHierarchy(): void
    {
        // Given: A deeply nested hierarchical setSpec
        $deepSetSpec = 'level1:level2:level3:level4:level5';

        // When: Creating a SetSpec
        $setSpec = new SetSpec($deepSetSpec);

        // Then: It should be created successfully
        $this->assertInstanceOf(SetSpec::class, $setSpec);
        $this->assertSame($deepSetSpec, $setSpec->getSetSpec());
    }
}
