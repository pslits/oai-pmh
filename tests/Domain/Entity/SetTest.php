<?php

/**
 * @author    Paul Slits <paul.slits@gmail.com>
 * @copyright (c) 2025 Paul Slits
 * @license   MIT License - https://opensource.org/licenses/MIT
 * @link      https://github.com/pslits/oai-pmh
 * @since     0.1.0
 */

namespace OaiPmh\Tests\Domain\Entity;

use OaiPmh\Domain\Entity\Set;
use OaiPmh\Domain\ValueObject\SetSpec;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for Set entity.
 *
 * Tests the OAI-PMH set implementation following the specification
 * section 4.6 (SelectiveHarvesting). Sets are optional groupings of items.
 *
 * @covers \OaiPmh\Domain\Entity\Set
 */
final class SetTest extends TestCase
{
    /**
     * @test
     * Given valid set data without optional description
     * When I create a Set
     * Then it should be created successfully
     */
    public function testConstructWithMinimalData(): void
    {
        // Given
        $setSpec = new SetSpec('mathematics');
        $setName = 'Mathematics Collection';

        // When
        $set = new Set($setSpec, $setName);

        // Then
        $this->assertInstanceOf(Set::class, $set);
        $this->assertSame($setSpec, $set->getSetSpec());
        $this->assertSame($setName, $set->getSetName());
        $this->assertNull($set->getSetDescription());
    }

    /**
     * @test
     * Given a set with description
     * When I create a Set
     * Then it should include the description
     */
    public function testConstructWithDescription(): void
    {
        // Given
        $setSpec = new SetSpec('mathematics');
        $setName = 'Mathematics Collection';
        $description = 'A collection of mathematical research papers and theses.';

        // When
        $set = new Set($setSpec, $setName, $description);

        // Then
        $this->assertSame($description, $set->getSetDescription());
    }

    /**
     * @test
     * Given a hierarchical set specification
     * When I create a Set
     * Then it should be created successfully
     */
    public function testConstructWithHierarchicalSetSpec(): void
    {
        // Given
        $setSpec = new SetSpec('science:mathematics:algebra');
        $setName = 'Algebra';

        // When
        $set = new Set($setSpec, $setName);

        // Then
        $this->assertSame($setSpec, $set->getSetSpec());
    }

    /**
     * @test
     * Given a set with all fields
     * When I create a Set
     * Then all fields should be accessible
     */
    public function testConstructWithAllFields(): void
    {
        // Given
        $setSpec = new SetSpec('cs:AI');
        $setName = 'Artificial Intelligence';
        $description = 'Computer Science papers on AI, machine learning, and neural networks.';

        // When
        $set = new Set($setSpec, $setName, $description);

        // Then
        $this->assertSame($setSpec, $set->getSetSpec());
        $this->assertSame($setName, $set->getSetName());
        $this->assertSame($description, $set->getSetDescription());
    }

    /**
     * @test
     * Given two Sets with the same setSpec
     * When I compare them using equals()
     * Then equals() should return true
     */
    public function testEqualsSameSetSpec(): void
    {
        // Given
        $setSpec = new SetSpec('mathematics');
        $set1 = new Set($setSpec, 'Mathematics');
        $set2 = new Set($setSpec, 'Math Collection');

        // When/Then
        $this->assertTrue($set1->equals($set2));
        $this->assertTrue($set2->equals($set1));
    }

    /**
     * @test
     * Given two Sets with different setSpecs
     * When I compare them using equals()
     * Then equals() should return false
     */
    public function testEqualsDifferentSetSpec(): void
    {
        // Given
        $set1 = new Set(new SetSpec('mathematics'), 'Mathematics');
        $set2 = new Set(new SetSpec('physics'), 'Physics');

        // When/Then
        $this->assertFalse($set1->equals($set2));
        $this->assertFalse($set2->equals($set1));
    }

    /**
     * @test
     * Given a Set
     * When I call __toString()
     * Then it should return a string representation
     */
    public function testToString(): void
    {
        // Given
        $setSpec = new SetSpec('mathematics');
        $setName = 'Mathematics Collection';
        $set = new Set($setSpec, $setName);

        // When
        $string = (string) $set;

        // Then
        $this->assertStringContainsString('Set', $string);
        $this->assertStringContainsString('mathematics', $string);
        $this->assertStringContainsString('Mathematics Collection', $string);
    }

    /**
     * @test
     * Given a Set with Unicode characters in name
     * When I create a Set
     * Then it should be created successfully
     */
    public function testConstructWithUnicodeSetName(): void
    {
        // Given
        $setSpec = new SetSpec('french-lit');
        $setName = 'Littérature Française';

        // When
        $set = new Set($setSpec, $setName);

        // Then
        $this->assertSame($setName, $set->getSetName());
    }

    /**
     * @test
     * Given a Set with long description
     * When I create a Set
     * Then it should store the complete description
     */
    public function testConstructWithLongDescription(): void
    {
        // Given
        $setSpec = new SetSpec('biology');
        $setName = 'Biology Research';
        $description = 'This comprehensive collection includes peer-reviewed research papers, '
            . 'dissertations, and theses covering all aspects of biological sciences including '
            . 'molecular biology, genetics, ecology, evolution, and biochemistry.';

        // When
        $set = new Set($setSpec, $setName, $description);

        // Then
        $this->assertSame($description, $set->getSetDescription());
    }

    /**
     * @test
     * Given a Set with empty description string
     * When I create a Set
     * Then description should be null
     */
    public function testConstructWithEmptyDescriptionTreatedAsNull(): void
    {
        // Given
        $setSpec = new SetSpec('mathematics');
        $setName = 'Mathematics';
        $description = '';

        // When
        $set = new Set($setSpec, $setName, $description);

        // Then
        $this->assertNull($set->getSetDescription());
    }
}
