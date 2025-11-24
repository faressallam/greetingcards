<?php
require_once 'config/config.php';
require_once 'config/database.php';

$db = Database::getInstance()->getConnection();

echo "<h2>Updating Emoji Database Tables</h2>";

try {
    // Add missing columns to emoji_categories
    echo "<h3>Updating emoji_categories table...</h3>";

    // Check if display_order column exists
    $stmt = $db->query("SHOW COLUMNS FROM emoji_categories LIKE 'display_order'");
    if ($stmt->rowCount() == 0) {
        $db->exec("ALTER TABLE emoji_categories ADD COLUMN display_order INT DEFAULT 0 AFTER name");
        echo "✅ Added 'display_order' column to emoji_categories<br>";
    } else {
        echo "ℹ️ Column 'display_order' already exists in emoji_categories<br>";
    }

    // Check if is_active column exists
    $stmt = $db->query("SHOW COLUMNS FROM emoji_categories LIKE 'is_active'");
    if ($stmt->rowCount() == 0) {
        $db->exec("ALTER TABLE emoji_categories ADD COLUMN is_active TINYINT(1) DEFAULT 1 AFTER display_order");
        echo "✅ Added 'is_active' column to emoji_categories<br>";
    } else {
        echo "ℹ️ Column 'is_active' already exists in emoji_categories<br>";
    }

    // Add missing columns to emojis
    echo "<h3>Updating emojis table...</h3>";

    // Check if display_order column exists
    $stmt = $db->query("SHOW COLUMNS FROM emojis LIKE 'display_order'");
    if ($stmt->rowCount() == 0) {
        $db->exec("ALTER TABLE emojis ADD COLUMN display_order INT DEFAULT 0 AFTER file_path");
        echo "✅ Added 'display_order' column to emojis<br>";
    } else {
        echo "ℹ️ Column 'display_order' already exists in emojis<br>";
    }

    // Check if is_active column exists
    $stmt = $db->query("SHOW COLUMNS FROM emojis LIKE 'is_active'");
    if ($stmt->rowCount() == 0) {
        $db->exec("ALTER TABLE emojis ADD COLUMN is_active TINYINT(1) DEFAULT 1 AFTER display_order");
        echo "✅ Added 'is_active' column to emojis<br>";
    } else {
        echo "ℹ️ Column 'is_active' already exists in emojis<br>";
    }

    echo "<br><h3>✅ Update Complete!</h3>";
    echo "<p>All required columns have been added successfully.</p>";
    echo "<ul>";
    echo "<li>Go to <a href='" . SITE_URL . "/admin/emoji_categories.php'>Emoji Categories Management</a></li>";
    echo "<li>Go to <a href='" . SITE_URL . "/admin/emojis.php'>Emoji Management</a></li>";
    echo "<li>Use the <a href='" . SITE_URL . "/editor-simple.php?template=1'>Simple Editor</a></li>";
    echo "</ul>";

} catch (PDOException $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}
?>