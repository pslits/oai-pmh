<?php

// phpcs:disable PSR1.Files.SideEffects
// This is a CLI script that needs to both define functions and execute code.

/**
 * Value Object Validator Script
 *
 * Validates PHP value objects and abstract base classes against OAI-PMH project standards.
 * Outputs validation report to console with pass/fail/warning status.
 *
 * CI/CD Mode (default):
 *   - Validates code against standards
 *   - Reports findings to stdout
 *   - Exits with appropriate code (0 = pass, 1 = fail)
 *   - Does NOT modify repository
 *
 * Manual Documentation Mode (--generate-analysis):
 *   - Same as CI/CD mode, but also generates/updates analysis markdown document
 *   - Use this for one-time documentation generation, not in CI/CD pipelines
 *
 * Usage:
 *   php validate_vo.php <path-to-value-object-file> [--generate-analysis]
 *
 * Examples:
 *   # CI/CD validation (default - no file generation)
 *   php validate_vo.php src/Domain/ValueObject/BaseURL.php
 *
 *   # Generate analysis documentation (manual use only)
 *   php validate_vo.php src/Domain/ValueObject/BaseURL.php --generate-analysis
 *
 * @author    Paul Slits <paul.slits@gmail.com>
 * @copyright (c) 2025 Paul Slits
 * @license   MIT License - https://opensource.org/licenses/MIT
 * @link      https://github.com/pslits/oai-pmh
 * @since     0.1.0
 */

declare(strict_types=1);

// Constants for validation thresholds
const PASS_THRESHOLD_EXCELLENT = 90;
const PASS_THRESHOLD_GOOD = 75;
const ABSTRACT_WARNING_LIMIT_EXCELLENT = 2;
const ABSTRACT_WARNING_LIMIT_GOOD = 5;
const ABSTRACT_FAIL_LIMIT_ACCEPTABLE = 5;
const ABSTRACT_FAIL_LIMIT_GOOD = 2;

// ============================================================================
// MAIN EXECUTION
// ============================================================================

// Parse arguments
$filePath = $argv[1] ?? null;
$generateAnalysis = in_array('--generate-analysis', $argv);
$skipAnalysis = in_array('--no-analysis', $argv); // Deprecated, kept for backward compatibility

// Check if file argument provided
if (!$filePath) {
    echo "Usage: php validate_vo.php <path-to-value-object-file> [--generate-analysis]\n";
    echo "Example: php validate_vo.php src/Domain/ValueObject/BaseURL.php\n";
    echo "\n";
    echo "Options:\n";
    echo "  --generate-analysis  Generate analysis document (optional, for manual use)\n";
    echo "\n";
    echo "Note: By default, this script only validates and reports to screen.\n";
    echo "      Use --generate-analysis to create analysis documentation.\n";
    exit(1);
}

// Check if file exists
if (!file_exists($filePath)) {
    echo "Error: File not found: $filePath\n";
    exit(1);
}

// Read file content
$content = file_get_contents($filePath);
$className = basename($filePath, '.php');

// Detect class type
$isAbstract = preg_match('/abstract\s+class\s+' . preg_quote($className, '/') . '\b/', $content);
$isFinal = preg_match('/final\s+class\s+' . preg_quote($className, '/') . '\b/', $content);

if ($isAbstract) {
    $classType = 'Abstract Base Class';
} elseif ($isFinal) {
    $classType = 'Standard Value Object';
} else {
    $classType = 'Unknown (Neither abstract nor final)';
}

// Print header
echo "\n";
echo "📋 Value Object Validation Report\n";
echo "==================================\n";
echo "\n";
echo "File: $filePath\n";
echo "Class: $className\n";
echo "Class Type: $classType\n";

$checklistFile = $isAbstract
    ? 'references/abstract-base-class-checklist.md'
    : 'references/vo-checklist.md';
echo "Checklist: $checklistFile\n";
echo "Date: " . date('Y-m-d H:i:s') . "\n";
echo "\n";

$results = [];
$passed = 0;
$failed = 0;
$warnings = 0;
$criticalIssues = [];
$highPriorityIssues = [];
$lowPriorityIssues = [];

// Run validation based on class type
if ($isAbstract) {
    validateAbstractBaseClass(
        $content,
        $className,
        $results,
        $passed,
        $failed,
        $warnings,
        $criticalIssues,
        $highPriorityIssues,
        $lowPriorityIssues
    );
} elseif ($isFinal) {
    validateStandardValueObject(
        $content,
        $className,
        $results,
        $passed,
        $failed,
        $warnings,
        $criticalIssues,
        $highPriorityIssues,
        $lowPriorityIssues
    );
} else {
    echo "❌ CRITICAL: Class is neither 'abstract' nor 'final'\n";
    echo "   → Add 'final' modifier for standard value objects\n";
    echo "   → Add 'abstract' modifier for base classes\n";
    $failed++;
    $criticalIssues[] = "Class must be either 'final' or 'abstract'";
}

// Print results
echo "\n";
printResults($results);

// Print summary
$total = $passed + $failed;
$percentage = $total > 0 ? round(($passed / $total) * 100) : 0;

echo "\n";
echo "Summary\n";
echo "=======\n";
echo "✅ Passed: $passed\n";
echo "❌ Failed: $failed\n";
echo "⚠️  Warnings: $warnings\n";
echo "Score: $passed/$total ($percentage%)\n";
echo "\n";

$status = '';
$exitCode = 0;

if ($isAbstract) {
    // Qualitative assessment for abstract base classes
    if ($failed === 0 && $warnings <= ABSTRACT_WARNING_LIMIT_EXCELLENT) {
        $status = "EXCELLENT";
        echo "Assessment: ✅ $status - All focus areas well-addressed, minimal risk\n";
    } elseif ($failed <= ABSTRACT_FAIL_LIMIT_GOOD && $warnings <= ABSTRACT_WARNING_LIMIT_GOOD) {
        $status = "GOOD";
        echo "Assessment: ✅ $status - Most focus areas addressed, low risk\n";
    } elseif ($failed <= ABSTRACT_FAIL_LIMIT_ACCEPTABLE) {
        $status = "ACCEPTABLE";
        echo "Assessment: ⚠️  $status - Core focus areas addressed, medium risk\n";
        $exitCode = 0;
    } else {
        $status = "NEEDS IMPROVEMENT";
        echo "Assessment: ❌ $status - Critical focus areas missing, high risk\n";
        $exitCode = 1;
    }
} else {
    // Percentage-based for standard value objects
    if ($percentage >= PASS_THRESHOLD_EXCELLENT) {
        $status = "PASS";
        echo "Status: ✅ $status - Good compliance\n";
        $exitCode = 0;
    } elseif ($percentage >= PASS_THRESHOLD_GOOD) {
        $status = "HAS WARNINGS";
        echo "Status: ⚠️  $status - Needs improvements\n";
        $exitCode = 0;
    } else {
        $status = "FAILED";
        echo "Status: ❌ $status - Requires fixes\n";
        $exitCode = 1;
    }
}

echo "\n";

// Print priority fixes
if (!empty($criticalIssues) || !empty($highPriorityIssues) || !empty($lowPriorityIssues)) {
    echo "Priority Fixes:\n";
    echo "===============\n";
    if (!empty($criticalIssues)) {
        echo "\n🔴 CRITICAL: Must fix before merging\n";
        foreach ($criticalIssues as $issue) {
            echo "   - $issue\n";
        }
    }
    if (!empty($highPriorityIssues)) {
        echo "\n🟡 HIGH: Should fix soon\n";
        foreach ($highPriorityIssues as $issue) {
            echo "   - $issue\n";
        }
    }
    if (!empty($lowPriorityIssues)) {
        echo "\n🟢 LOW: Nice to have\n";
        foreach ($lowPriorityIssues as $issue) {
            echo "   - $issue\n";
        }
    }
    echo "\n";
}

// Generate analysis document if validation passes (before "Next Steps")
// Only when explicitly requested with --generate-analysis flag
$analysisGenerated = false;
if ($generateAnalysis && $exitCode === 0) {
    echo "\n";
    $analysisPath = "docs/analysis/ValueObject/" . strtoupper($className) . "_ANALYSIS.md";
    $fileExists = file_exists($analysisPath);

    if ($fileExists) {
        echo "Updating existing analysis document...\n";
    } else {
        echo "Generating analysis document...\n";
    }

    $generated = generateAnalysisDocument(
        $filePath,
        $className,
        $classType,
        $analysisPath,
        $results,
        $passed,
        $failed,
        $warnings
    );

    if ($generated) {
        // Show both relative and absolute paths for clarity
        $absolutePath = realpath($analysisPath);
        if ($fileExists) {
            echo "✅ Analysis document updated:\n";
        } else {
            echo "✅ Analysis document created:\n";
        }
        echo "   Location: $analysisPath\n";
        if ($absolutePath && $absolutePath !== $analysisPath) {
            echo "   Full path: $absolutePath\n";
        }
        $analysisGenerated = true;
    } else {
        echo "⚠️  Failed to write analysis document at: $analysisPath\n";
        echo "   Check directory permissions and path\n";
    }
    echo "\n";
}

// Print next steps
echo "Next Steps:\n";
echo "===========\n";
if (!empty($criticalIssues) || !empty($highPriorityIssues)) {
    echo "1. Fix priority issues listed above\n";
    echo "2. Run quality checks:\n";
} else {
    echo "1. Run quality checks:\n";
}
echo "   vendor\\bin\\phpstan analyse $filePath\n";
echo "   vendor\\bin\\phpcs $filePath\n";
echo "   vendor\\bin\\phpunit tests/Domain/ValueObject/{$className}Test.php\n";

// Suggest analysis document generation if validation passed and it wasn't just generated
if ($exitCode === 0 && !$analysisGenerated) {
    $stepNum = (!empty($criticalIssues) || !empty($highPriorityIssues)) ? "3" : "2";
    echo "{$stepNum}. Generate analysis document (optional):\n";
    echo "   php .github/skills/value-object-validator/scripts/validate_vo.php $filePath --generate-analysis\n";
} elseif ($exitCode !== 0) {
    $stepNum = (!empty($criticalIssues) || !empty($highPriorityIssues)) ? "3" : "2";
    echo "{$stepNum}. Fix validation issues before generating analysis documentation\n";
}
echo "\n";

exit($exitCode);

// ============================================================================
// VALIDATION FUNCTIONS
// ============================================================================

/**
 * Validate a standard value object (final class)
 */
function validateStandardValueObject(
    string $content,
    string $className,
    array &$results,
    int &$passed,
    int &$failed,
    int &$warnings,
    array &$criticalIssues,
    array &$highPriorityIssues,
    array &$lowPriorityIssues
): void {
    $results['File Header'] = [];
    validateFileHeader(
        $content,
        $results,
        $passed,
        $failed,
        $criticalIssues,
        $highPriorityIssues,
        $lowPriorityIssues
    );

    $results['Class Structure'] = [];
    check(
        $content,
        '/final\s+class\s+' . preg_quote($className, '/') . '\b/',
        'Class is final',
        'Class not marked as final',
        $results['Class Structure'],
        $passed,
        $failed,
        $criticalIssues
    );
    check(
        $content,
        '/private\s+(string|int|float|bool|array|\?string)\s+\$/',
        'Has private properties',
        'No private properties',
        $results['Class Structure'],
        $passed,
        $failed,
        $highPriorityIssues
    );
    checkNot(
        $content,
        '/public\s+function\s+set[A-Z]/',
        'No setter methods',
        'Has setter methods (breaks immutability)',
        $results['Class Structure'],
        $passed,
        $failed,
        $criticalIssues
    );

    $results['Domain-Specific Getter'] = [];
    $getterPattern = '/public\s+function\s+get' . $className . '\(\)\s*:\s*[\w|?]+/';
    if (preg_match($getterPattern, $content)) {
        $results['Domain-Specific Getter'][] = ['✅', "Has domain-specific getter (get{$className}())"];
        $passed++;
    } else {
        $results['Domain-Specific Getter'][] = ['❌', "Missing domain-specific getter (get{$className}())"];
        $failed++;
        $criticalIssues[] = "Add domain-specific getter: public function get{$className}()";
    }

    $results['Required Methods'] = [];
    check(
        $content,
        '/public\s+function\s+equals\(self\s+\$\w+\)\s*:\s*bool/',
        'Has equals() method',
        'Missing equals() method',
        $results['Required Methods'],
        $passed,
        $failed,
        $criticalIssues
    );
    check(
        $content,
        '/public\s+function\s+__toString\(\)\s*:\s*string/',
        'Has __toString() method',
        'Missing __toString() method',
        $results['Required Methods'],
        $passed,
        $failed,
        $highPriorityIssues
    );

    $results['Validation Logic'] = [];
    check(
        $content,
        '/(private|protected)\s+function\s+validate\(/',
        'Has validate() method',
        'Missing validate() method',
        $results['Validation Logic'],
        $passed,
        $failed,
        $criticalIssues
    );
    check(
        $content,
        '/throw\s+new\s+InvalidArgumentException/',
        'Throws InvalidArgumentException',
        'No InvalidArgumentException thrown',
        $results['Validation Logic'],
        $passed,
        $warnings,
        $lowPriorityIssues,
        true
    );
    check(
        $content,
        '/sprintf\(/',
        'Uses sprintf() for error messages',
        'Not using sprintf()',
        $results['Validation Logic'],
        $passed,
        $warnings,
        $lowPriorityIssues,
        true
    );

    $results['Documentation'] = [];
    check(
        $content,
        '/OAI-PMH\s+2\.0\s+specification/',
        'References OAI-PMH 2.0 spec',
        'No OAI-PMH spec reference',
        $results['Documentation'],
        $passed,
        $warnings,
        $lowPriorityIssues,
        true
    );
    check(
        $content,
        '/\*\s+@param\s+/',
        'Has @param documentation',
        'Missing @param tags',
        $results['Documentation'],
        $passed,
        $warnings,
        $lowPriorityIssues,
        true
    );
    check(
        $content,
        '/\*\s+@return\s+/',
        'Has @return documentation',
        'Missing @return tags',
        $results['Documentation'],
        $passed,
        $warnings,
        $lowPriorityIssues,
        true
    );
    check(
        $content,
        '/\*\s+@throws\s+InvalidArgumentException/',
        'Documents @throws',
        'Missing @throws docs',
        $results['Documentation'],
        $passed,
        $warnings,
        $lowPriorityIssues,
        true
    );

    $results['Naming Conventions'] = [];
    $genericEqualsPattern = '/public\s+function\s+equals\(self\s+\$other\)\s*:\s*bool/';
    if (preg_match($genericEqualsPattern, $content)) {
        $results['Naming Conventions'][] = ['❌', "equals() uses generic \$other parameter"];
        $failed++;
        $issue = "Use descriptive parameter name in equals() (e.g., \$other{$className})";
        $highPriorityIssues[] = $issue;
    } else {
        $results['Naming Conventions'][] = ['✅', "equals() uses descriptive parameter"];
        $passed++;
    }
}

/**
 * Validate an abstract base class
 */
function validateAbstractBaseClass(
    string $content,
    string $className,
    array &$results,
    int &$passed,
    int &$failed,
    int &$warnings,
    array &$criticalIssues,
    array &$highPriorityIssues,
    array &$lowPriorityIssues
): void {
    $results['File Header'] = [];
    validateFileHeader($content, $results, $passed, $failed, $criticalIssues, $highPriorityIssues, $lowPriorityIssues);

    $results['Class Modifiers'] = [];
    check(
        $content,
        '/abstract\s+class\s+' . preg_quote($className, '/') . '\b/',
        'Class is abstract',
        'Class not marked as abstract',
        $results['Class Modifiers'],
        $passed,
        $failed,
        $criticalIssues
    );
    check(
        $content,
        '/\*\s+Base class for/',
        'Explains base class purpose',
        'Missing base class explanation',
        $results['Class Modifiers'],
        $passed,
        $failed,
        $highPriorityIssues
    );
    check(
        $content,
        '/Known subclasses:/',
        'Lists known subclasses',
        'No subclass list',
        $results['Class Modifiers'],
        $passed,
        $warnings,
        $lowPriorityIssues,
        true
    );
    check(
        $content,
        '/OAI-PMH\s+2\.0\s+specification/',
        'References OAI-PMH spec',
        'No spec reference',
        $results['Class Modifiers'],
        $passed,
        $warnings,
        $lowPriorityIssues,
        true
    );

    $results['Property Design'] = [];
    check(
        $content,
        '/(protected|private)\s+(string|int|float|bool|array|\?string|\w+\|null)\s+\$/',
        'Has typed properties',
        'Missing type declarations',
        $results['Property Design'],
        $passed,
        $failed,
        $criticalIssues
    );
    check(
        $content,
        '/\*\s+@var\s+/',
        'Properties documented with @var',
        'Missing @var tags',
        $results['Property Design'],
        $passed,
        $warnings,
        $lowPriorityIssues,
        true
    );
    checkNot(
        $content,
        '/public\s+function\s+set[A-Z]/',
        'No setter methods (immutable)',
        'Has setters',
        $results['Property Design'],
        $passed,
        $failed,
        $highPriorityIssues
    );

    $results['Method Design'] = [];
    check(
        $content,
        '/protected\s+function\s+get\w+\(\)/',
        'Has template method getter',
        'No template method',
        $results['Method Design'],
        $passed,
        $warnings,
        $lowPriorityIssues,
        true
    );
    check(
        $content,
        '/public\s+function\s+equals\(self\s+\$\w+\)\s*:\s*bool/',
        'Has equals() method',
        'Missing equals()',
        $results['Method Design'],
        $passed,
        $failed,
        $criticalIssues
    );
    check(
        $content,
        '/get_class\(\$this\)\s+===\s+get_class\(\$\w+\)/',
        'equals() handles inheritance',
        'equals() may not handle inheritance',
        $results['Method Design'],
        $passed,
        $warnings,
        $highPriorityIssues,
        true
    );
    check(
        $content,
        '/public\s+function\s+__toString\(\)\s*:\s*string/',
        'Has __toString() method',
        'Missing __toString()',
        $results['Method Design'],
        $passed,
        $failed,
        $highPriorityIssues
    );

    $results['Validation Logic'] = [];
    check(
        $content,
        '/protected\s+function\s+validate\(/',
        'Has protected validate()',
        'No protected validate()',
        $results['Validation Logic'],
        $passed,
        $warnings,
        $highPriorityIssues,
        true
    );
    check(
        $content,
        '/throw\s+new\s+InvalidArgumentException/',
        'Throws InvalidArgumentException',
        'No exceptions thrown',
        $results['Validation Logic'],
        $passed,
        $warnings,
        $lowPriorityIssues,
        true
    );

    $results['Null Safety'] = [];
    check(
        $content,
        '/\|\s*null|\?string|\?int/',
        'Handles nullable types',
        'No nullable type handling',
        $results['Null Safety'],
        $passed,
        $warnings,
        $lowPriorityIssues,
        true
    );
    check(
        $content,
        '/\$\w+\s+===\s+null|\$\w+\s+!==\s+null|empty\(\$\w+\)/',
        'Explicit null checks',
        'No explicit null checks',
        $results['Null Safety'],
        $passed,
        $warnings,
        $lowPriorityIssues,
        true
    );

    $results['Documentation'] = [];
    check(
        $content,
        '/\*\s+@param\s+/',
        'Has @param documentation',
        'Missing @param',
        $results['Documentation'],
        $passed,
        $warnings,
        $lowPriorityIssues,
        true
    );
    check(
        $content,
        '/\*\s+@return\s+/',
        'Has @return documentation',
        'Missing @return',
        $results['Documentation'],
        $passed,
        $warnings,
        $lowPriorityIssues,
        true
    );
}

// ============================================================================
// HELPER FUNCTIONS
// ============================================================================

/**
 * Validate file header tags (shared between both validation types)
 */
function validateFileHeader(
    string $content,
    array &$results,
    int &$passed,
    int &$failed,
    array &$criticalIssues,
    array &$highPriorityIssues,
    array &$lowPriorityIssues
): void {
    check(
        $content,
        '/@author\s+Paul Slits/',
        '@author tag present',
        '@author tag missing',
        $results['File Header'],
        $passed,
        $failed,
        $criticalIssues
    );
    check(
        $content,
        '/@copyright\s+\(c\)\s+\d{4}\s+Paul Slits/',
        '@copyright tag present',
        '@copyright tag missing',
        $results['File Header'],
        $passed,
        $failed,
        $highPriorityIssues
    );
    check(
        $content,
        '/@license\s+MIT License/',
        '@license tag present',
        '@license tag missing',
        $results['File Header'],
        $passed,
        $failed,
        $highPriorityIssues
    );
    check(
        $content,
        '/@link\s+https:\/\/github\.com\/pslits\/oai-pmh/',
        '@link tag correct',
        '@link tag missing',
        $results['File Header'],
        $passed,
        $failed,
        $highPriorityIssues
    );
    check(
        $content,
        '/@since\s+[\d.]+/',
        '@since tag present',
        '@since tag missing',
        $results['File Header'],
        $passed,
        $failed,
        $lowPriorityIssues
    );
}

/**
 * Check if pattern matches
 */
function check(
    string $content,
    string $pattern,
    string $passMsg,
    string $failMsg,
    array &$results,
    int &$passed,
    int &$countRef,
    array &$issues,
    bool $isWarning = false
): void {
    if (preg_match($pattern, $content)) {
        $results[] = ['✅', $passMsg];
        $passed++;
    } else {
        $results[] = [$isWarning ? '⚠️' : '❌', $failMsg];
        $countRef++;
        if (!$isWarning) {
            $issues[] = $failMsg;
        }
    }
}

/**
 * Check if pattern does NOT match (inverse)
 */
function checkNot(
    string $content,
    string $pattern,
    string $passMsg,
    string $failMsg,
    array &$results,
    int &$passed,
    int &$failed,
    array &$issues
): void {
    if (!preg_match($pattern, $content)) {
        $results[] = ['✅', $passMsg];
        $passed++;
    } else {
        $results[] = ['❌', $failMsg];
        $failed++;
        $issues[] = $failMsg;
    }
}

/**
 * Print validation results
 */
function printResults(array $results): void
{
    foreach ($results as $category => $checks) {
        $passCount = count(array_filter($checks, fn ($c) => $c[0] === '✅'));
        $totalCount = count($checks);
        echo "$category ($passCount/$totalCount checks)\n";
        echo str_repeat('-', strlen($category) + strlen(" ($passCount/$totalCount checks)")) . "\n";
        foreach ($checks as $check) {
            echo "  {$check[0]} {$check[1]}\n";
        }
        echo "\n";
    }
}

/**
 * Generate analysis document
 */
function generateAnalysisDocument(
    string $filePath,
    string $className,
    string $classType,
    string $outputPath,
    array $results,
    int $passed,
    int $failed,
    int $warnings
): bool {
    $dir = dirname($outputPath);
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }

    $date = date('Y-m-d');
    $total = $passed + $failed;
    $percentage = $total > 0 ? round(($passed / $total) * 100) : 0;

    $content = <<<MD
# {$className} Analysis

**Analysis Date:** {$date}  
**Component:** {$className} {$classType}  
**File:** `{$filePath}`  
**OAI-PMH Version:** 2.0  
**Specification:** [OAI-PMH 2.0](https://www.openarchives.org/OAI/openarchivesprotocol.html)

---

## Executive Summary

Validation completed on {$date}.

**Validation Results:**
- ✅ Passed: {$passed}
- ❌ Failed: {$failed}
- ⚠️  Warnings: {$warnings}
- Score: {$passed}/{$total} ({$percentage}%)

**Status:** Automated validation - requires manual review for completeness.

---

## 1. OAI-PMH Requirement

[TODO: Add OAI-PMH specification context and requirements]

---

## 2. User Story

[TODO: Add user story with acceptance criteria]

---

## 3. Implementation Details

### File Structure
```
{$filePath}
tests/Domain/ValueObject/{$className}Test.php
```

### Class Design
- **Namespace:** OaiPmh\\Domain\\ValueObject  
- **Type:** {$classType}

---

## 4. Validation Results

MD;

    foreach ($results as $category => $checks) {
        $content .= "\n### {$category}\n\n";
        foreach ($checks as $check) {
            $content .= "- {$check[0]} {$check[1]}\n";
        }
    }

    $content .= <<<MD


---

## 5. Test Coverage Analysis

[TODO: Add test coverage statistics and analysis]

---

## 6. Code Examples

[TODO: Add basic usage examples]

---

## 7. Design Decisions

[TODO: Document design decisions with context and rationale]

---

## 8. Known Issues & Future Enhancements

[TODO: List any known issues and planned enhancements]

---

## 9. Comparison with Related Value Objects

[TODO: Compare with similar value objects in the library]

---

## 10. Recommendations

### For Developers
[TODO: Add recommendations for developers using this value object]

### For Repository Administrators
[TODO: Add recommendations for repository administrators]

### For Library Maintainers
[TODO: Add recommendations for maintainers]

---

## 11. References

- [OAI-PMH 2.0 Specification](https://www.openarchives.org/OAI/openarchivesprotocol.html)
- Related analysis documents (TODO)
- GitHub issues (TODO)

---

## 12. Appendix

### Automated Validation Output

```
Validation Date: {$date}
Score: {$passed}/{$total} ({$percentage}%)
Status: " . ($percentage >= 90 ? 'PASS' : ($percentage >= 75 ? 'WARNINGS' : 'FAILED')) . "
```

---

*Analysis generated automatically on {$date}. Manual review and completion required.*

MD;

    return file_put_contents($outputPath, $content) !== false;
}
