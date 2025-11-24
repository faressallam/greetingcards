<?php
/**
 * Migration Runner for Share Feature
 * Run this file once to apply database changes
 */

require_once __DIR__ . '/../config/database.php';

try {
    echo "Starting migration for Share Feature...\n\n";

    // Read and execute the SQL migration file
    $sql = file_get_contents(__DIR__ . '/migration_share_feature.sql');

    // Split by semicolons to execute each statement separately
    $statements = array_filter(
        array_map('trim', explode(';', $sql)),
        function ($stmt) {
            // Filter out comments and empty statements
            return !empty($stmt) &&
                !str_starts_with($stmt, '--') &&
                !str_starts_with($stmt, '/*');
        }
    );

    $success_count = 0;
    $error_count = 0;

    foreach ($statements as $statement) {
        if (empty(trim($statement)))
            continue;

        try {
            $db->exec($statement);
            $success_count++;

            // Extract table/action for logging
            if (preg_match('/CREATE TABLE.*?(\w+)/i', $statement, $matches)) {
                echo "✓ Created table: {$matches[1]}\n";
            } elseif (preg_match('/ALTER TABLE\s+(\w+)/i', $statement, $matches)) {
                echo "✓ Altered table: {$matches[1]}\n";
            } elseif (preg_match('/INSERT INTO\s+(\w+)/i', $statement, $matches)) {
                echo "✓ Inserted data into: {$matches[1]}\n";
            } else {
                echo "✓ Executed statement\n";
            }
        } catch (PDOException $e) {
            $error_count++;
            // Check if error is "already exists" - not critical
            if (
                strpos($e->getMessage(), 'already exists') !== false ||
                strpos($e->getMessage(), 'Duplicate column') !== false
            ) {
                echo "⚠ Skipped (already exists): " . substr($statement, 0, 50) . "...\n";
            } else {
                echo "✗ Error: " . $e->getMessage() . "\n";
                echo "  Statement: " . substr($statement, 0, 100) . "...\n";
            }
        }
    }

    echo "\n";
    echo "========================================\n";
    echo "Migration completed!\n";
    echo "Successful: $success_count\n";
    echo "Errors/Skipped: $error_count\n";
    echo "========================================\n";
    echo "\nYou can now use the share feature!\n";

} catch (Exception $e) {
    echo "Fatal error: " . $e->getMessage() . "\n";
    exit(1);
}
