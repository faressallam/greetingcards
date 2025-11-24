<?php
require_once 'config/config.php';
require_once 'config/database.php';

$db = Database::getInstance()->getConnection();

echo "<h2>Setting up Emoji Database Tables</h2>";

try {
    // Create emoji_categories table
    $sql = "CREATE TABLE IF NOT EXISTS emoji_categories (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        display_order INT DEFAULT 0,
        is_active TINYINT(1) DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
    $db->exec($sql);
    echo "✅ Table 'emoji_categories' created successfully.<br>";

    // Create emojis table
    $sql = "CREATE TABLE IF NOT EXISTS emojis (
        id INT AUTO_INCREMENT PRIMARY KEY,
        category_id INT NOT NULL,
        file_path VARCHAR(255) NOT NULL,
        display_order INT DEFAULT 0,
        is_active TINYINT(1) DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (category_id) REFERENCES emoji_categories(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
    $db->exec($sql);
    echo "✅ Table 'emojis' created successfully.<br>";

    // Insert default categories if empty
    $stmt = $db->query("SELECT COUNT(*) FROM emoji_categories");
    if ($stmt->fetchColumn() == 0) {
        $categories = [
            ['name' => 'وجوه مبتسمة', 'order' => 1],
            ['name' => 'قلوب', 'order' => 2],
            ['name' => 'احتفالات', 'order' => 3],
            ['name' => 'زهور', 'order' => 4],
            ['name' => 'إسلامي', 'order' => 5],
            ['name' => 'أخرى', 'order' => 6]
        ];

        $stmt = $db->prepare("INSERT INTO emoji_categories (name, display_order) VALUES (?, ?)");
        foreach ($categories as $cat) {
            $stmt->execute([$cat['name'], $cat['order']]);
        }
        echo "✅ Default categories inserted (6 categories).<br>";
    } else {
        echo "ℹ️ Categories already exist.<br>";
    }

    echo "<br><h3>✅ Setup Complete!</h3>";
    echo "<p>You can now:</p>";
    echo "<ul>";
    echo "<li>Go to <a href='" . SITE_URL . "/admin/emoji_categories.php'>Emoji Categories Management</a></li>";
    echo "<li>Go to <a href='" . SITE_URL . "/admin/emojis.php'>Emoji Management</a> to upload emojis</li>";
    echo "<li>Use the <a href='" . SITE_URL . "/editor-simple.php?template=1'>Simple Editor</a></li>";
    echo "</ul>";

} catch (PDOException $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}
?>