<?php
require_once 'config/config.php';
require_once 'config/database.php';

$db = Database::getInstance()->getConnection();

echo "<h2>Fixing Emoji Database Tables</h2>";

try {
    // Drop old tables
    echo "<h3>Dropping old tables...</h3>";
    $db->exec("DROP TABLE IF EXISTS emojis");
    echo "✅ Dropped 'emojis' table<br>";

    $db->exec("DROP TABLE IF EXISTS emoji_categories");
    echo "✅ Dropped 'emoji_categories' table<br>";

    // Create emoji_categories table with correct structure
    echo "<h3>Creating new tables...</h3>";
    $sql = "CREATE TABLE emoji_categories (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        display_order INT DEFAULT 0,
        is_active TINYINT(1) DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    $db->exec($sql);
    echo "✅ Created 'emoji_categories' table<br>";

    // Create emojis table with correct structure
    $sql = "CREATE TABLE emojis (
        id INT AUTO_INCREMENT PRIMARY KEY,
        category_id INT NOT NULL,
        file_path VARCHAR(255) NOT NULL,
        display_order INT DEFAULT 0,
        is_active TINYINT(1) DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (category_id) REFERENCES emoji_categories(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    $db->exec($sql);
    echo "✅ Created 'emojis' table<br>";

    // Insert default categories
    echo "<h3>Adding default categories...</h3>";
    $categories = [
        ['name' => 'وجوه مبتسمة', 'order' => 1],
        ['name' => 'قلوب', 'order' => 2],
        ['name' => 'احتفالات', 'order' => 3],
        ['name' => 'زهور', 'order' => 4],
        ['name' => 'إسلامي', 'order' => 5],
        ['name' => 'أخرى', 'order' => 6]
    ];

    $stmt = $db->prepare("INSERT INTO emoji_categories (name, display_order, is_active) VALUES (?, ?, 1)");
    foreach ($categories as $cat) {
        $stmt->execute([$cat['name'], $cat['order']]);
    }
    echo "✅ Added " . count($categories) . " default categories<br>";

    echo "<br><h3>✅ Fix Complete!</h3>";
    echo "<p>All tables have been recreated with the correct structure.</p>";
    echo "<ul>";
    echo "<li>Go to <a href='" . SITE_URL . "/admin/emoji_categories.php'>Emoji Categories Management</a></li>";
    echo "<li>Go to <a href='" . SITE_URL . "/admin/emojis.php'>Emoji Management</a> to upload emojis</li>";
    echo "<li>Use the <a href='" . SITE_URL . "/editor-simple.php?template=1'>Simple Editor</a></li>";
    echo "</ul>";

    echo "<p style='background: #fff3cd; padding: 1rem; border-radius: 8px; margin-top: 1rem;'>";
    echo "⚠️ <strong>ملاحظة:</strong> تم حذف جميع الإيموجي القديمة. يرجى رفع الإيموجي من جديد.";
    echo "</p>";

} catch (PDOException $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}
?>