<?php
$page_title = 'الرئيسية';
require_once 'includes/header.php';

// Fetch categories - only show categories that have templates
$stmt = $db->query("
    SELECT DISTINCT c.* 
    FROM categories c
    INNER JOIN templates t ON c.id = t.category_id
    WHERE c.is_active = 1 AND t.is_active = 1
    ORDER BY c.display_order ASC 
    LIMIT 6
");
$categories = $stmt->fetchAll();

// Fetch featured templates
$stmt = $db->query("SELECT t.*, c.name_ar as category_name 
                    FROM templates t 
                    JOIN categories c ON t.category_id = c.id 
                    WHERE t.is_active = 1 
                    ORDER BY t.views DESC 
                    LIMIT 6");
$featured_templates = $stmt->fetchAll();
?>

<!-- Hero Section -->
<section class="hero">
    <div class="container">
        <h1>صمم كروت معايدة احترافية</h1>
        <p>اختر من مئات القوالب الجاهزة وخصصها حسب ذوقك</p>
        <a href="<?php echo SITE_URL; ?>/templates.php" class="btn btn-primary"
            style="font-size: 1.25rem; padding: 1rem 2rem;">
            ابدأ التصميم الآن
        </a>
    </div>
</section>

<!-- Categories Section -->
<section class="categories">
    <div class="container">
        <h2 class="section-title">تصفح حسب المناسبة</h2>
        <div class="categories-grid">
            <?php foreach ($categories as $category): ?>
                <a href="<?php echo SITE_URL; ?>/templates.php?category=<?php echo $category['slug']; ?>"
                    class="category-card">
                    <div class="category-icon">
                        <?php
                        $icons = [
                            'eid-fitr' => '🌙',
                            'eid-adha' => '🕌',
                            'ramadan' => '🌟',
                            'wedding' => '💍',
                            'success' => '🎓',
                            'baby' => '👶',
                            'general' => '🎉'
                        ];
                        echo $icons[$category['slug']] ?? '🎨';
                        ?>
                    </div>
                    <div class="category-name"><?php echo htmlspecialchars($category['name_ar']); ?></div>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Featured Templates Section -->
<section class="featured-templates" style="padding: 4rem 0; background: white;">
    <div class="container">
        <h2 class="section-title">القوالب الأكثر شعبية</h2>
        <div class="templates-grid">
            <?php foreach ($featured_templates as $template): ?>
                <a href="<?php echo SITE_URL; ?>/editor-simple.php?template=<?php echo $template['id']; ?>"
                    class="template-card">
                    <img src="<?php echo SITE_URL . '/uploads/templates/' . ($template['preview_image_url'] ?: $template['image_path']); ?>"
                        alt="<?php echo htmlspecialchars($template['title']); ?>" class="template-image"
                        onerror="this.src='<?php echo SITE_URL; ?>/assets/images/placeholder.jpg'">
                    <div class="template-info">
                        <h3 class="template-title"><?php echo htmlspecialchars($template['title']); ?></h3>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>

        <?php if (empty($featured_templates)): ?>
            <div style="text-align: center; padding: 3rem; color: var(--gray);">
                <p>لا توجد قوالب متاحة حالياً. يرجى إضافة قوالب من لوحة التحكم الإدارية.</p>
                <?php if (is_admin()): ?>
                    <a href="<?php echo SITE_URL; ?>/admin/templates.php" class="btn btn-primary" style="margin-top: 1rem;">
                        إضافة قوالب
                    </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- Features Section -->
<section class="features" style="padding: 4rem 0;">
    <div class="container">
        <h2 class="section-title">لماذا تختارنا؟</h2>
        <div class="categories-grid">
            <div class="category-card">
                <div class="category-icon">🎨</div>
                <h3 class="category-name">تصاميم احترافية</h3>
                <p style="color: var(--gray); margin-top: 0.5rem;">قوالب مصممة بعناية لجميع المناسبات</p>
            </div>
            <div class="category-card">
                <div class="category-icon">✏️</div>
                <h3 class="category-name">سهل التخصيص</h3>
                <p style="color: var(--gray); margin-top: 0.5rem;">عدل النصوص والألوان والخطوط بسهولة</p>
            </div>
            <div class="category-card">
                <div class="category-icon">📱</div>
                <h3 class="category-name">يعمل على كل الأجهزة</h3>
                <p style="color: var(--gray); margin-top: 0.5rem;">صمم من الجوال أو الكمبيوتر</p>
            </div>
            <div class="category-card">
                <div class="category-icon">⚡</div>
                <h3 class="category-name">سريع ومجاني</h3>
                <p style="color: var(--gray); margin-top: 0.5rem;">صمم وحمل كرتك في ثوانٍ</p>
            </div>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>