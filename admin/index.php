<?php
$page_title = 'لوحة التحكم الإدارية';
require_once '../includes/header.php';

require_admin();

// Get statistics
$stats = [
    'users' => $db->query("SELECT COUNT(*) FROM users")->fetchColumn(),
    'templates' => $db->query("SELECT COUNT(*) FROM templates")->fetchColumn(),
    'categories' => $db->query("SELECT COUNT(*) FROM categories")->fetchColumn(),
    'saved_cards' => $db->query("SELECT COUNT(*) FROM saved_cards")->fetchColumn()
];

// Recent templates
$recent_templates = $db->query("SELECT t.*, c.name_ar as category_name 
                                FROM templates t 
                                JOIN categories c ON t.category_id = c.id 
                                ORDER BY t.created_at DESC 
                                LIMIT 5")->fetchAll();
?>

<div class="container" style="padding: 3rem 0;">
    <h1 style="margin-bottom: 2rem;">لوحة التحكم الإدارية</h1>

    <!-- Statistics Cards -->
    <div
        style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 2rem; margin-bottom: 3rem;">
        <div
            style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 2rem; border-radius: 12px;">
            <h3 style="font-size: 2.5rem; margin-bottom: 0.5rem;"><?php echo $stats['users']; ?></h3>
            <p style="opacity: 0.9;">إجمالي المستخدمين</p>
        </div>

        <div
            style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white; padding: 2rem; border-radius: 12px;">
            <h3 style="font-size: 2.5rem; margin-bottom: 0.5rem;"><?php echo $stats['templates']; ?></h3>
            <p style="opacity: 0.9;">القوالب</p>
        </div>

        <div
            style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); color: white; padding: 2rem; border-radius: 12px;">
            <h3 style="font-size: 2.5rem; margin-bottom: 0.5rem;"><?php echo $stats['categories']; ?></h3>
            <p style="opacity: 0.9;">الأقسام</p>
        </div>

        <div
            style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); color: white; padding: 2rem; border-radius: 12px;">
            <h3 style="font-size: 2.5rem; margin-bottom: 0.5rem;"><?php echo $stats['saved_cards']; ?></h3>
            <p style="opacity: 0.9;">التصاميم المحفوظة</p>
        </div>
    </div>

    <!-- Quick Actions -->
    <div
        style="background: white; padding: 2rem; border-radius: 12px; margin-bottom: 3rem; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
        <h2 style="margin-bottom: 1.5rem;">إجراءات سريعة</h2>
        <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
            <a href="<?php echo SITE_URL; ?>/admin/templates.php" class="btn btn-primary">
                📋 إدارة القوالب
            </a>
            <a href="<?php echo SITE_URL; ?>/admin/categories.php" class="btn btn-secondary">
                📁 إدارة الأقسام
            </a>
            <a href="<?php echo SITE_URL; ?>/admin/users.php" class="btn btn-secondary">
                👥 إدارة المستخدمين
            </a>
            <a href="<?php echo SITE_URL; ?>/admin/menu.php" class="btn btn-secondary">
                🔗 إدارة القائمة
            </a>
            <a href="<?php echo SITE_URL; ?>/admin/emojis.php" class="btn btn-secondary">
                😊 إدارة الإيموجي
            </a>
            <a href="<?php echo SITE_URL; ?>/admin/emoji_categories.php" class="btn btn-secondary">
                📂 أقسام الإيموجي
            </a>
        </div>
    </div>

    <!-- Recent Templates -->
    <div style="background: white; padding: 2rem; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
        <h2 style="margin-bottom: 1.5rem;">آخر القوالب المضافة</h2>
        <table style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="background: var(--light); text-align: right;">
                    <th style="padding: 1rem;">العنوان</th>
                    <th style="padding: 1rem;">القسم</th>
                    <th style="padding: 1rem;">المشاهدات</th>
                    <th style="padding: 1rem;">التحميلات</th>
                    <th style="padding: 1rem;">التاريخ</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recent_templates as $template): ?>
                    <tr style="border-bottom: 1px solid var(--border);">
                        <td style="padding: 1rem;"><?php echo htmlspecialchars($template['title']); ?></td>
                        <td style="padding: 1rem;"><?php echo htmlspecialchars($template['category_name']); ?></td>
                        <td style="padding: 1rem;"><?php echo $template['views']; ?></td>
                        <td style="padding: 1rem;"><?php echo $template['downloads']; ?></td>
                        <td style="padding: 1rem;"><?php echo format_date($template['created_at']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>