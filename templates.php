<?php
$page_title = 'القوالب';
require_once 'includes/header.php';

// Get category filter
$category_filter = isset($_GET['category']) ? sanitize_input($_GET['category']) : '';
$search = isset($_GET['search']) ? sanitize_input($_GET['search']) : '';

// Pagination
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;

// Build query
$where_conditions = ['t.is_active = 1'];
$params = [];

if ($category_filter) {
    $where_conditions[] = 'c.slug = :category';
    $params[':category'] = $category_filter;
}

if ($search) {
    $where_conditions[] = 't.title LIKE :search';
    $params[':search'] = '%' . $search . '%';
}

$where_clause = implode(' AND ', $where_conditions);

// Count total
$count_sql = "SELECT COUNT(*) FROM templates t JOIN categories c ON t.category_id = c.id WHERE $where_clause";
$count_stmt = $db->prepare($count_sql);
$count_stmt->execute($params);
$total_items = $count_stmt->fetchColumn();

// Pagination
$pagination = paginate($total_items, $page);

// Fetch templates
$sql = "SELECT t.*, c.name_ar as category_name, c.slug as category_slug 
        FROM templates t 
        JOIN categories c ON t.category_id = c.id 
        WHERE $where_clause 
        ORDER BY t.created_at DESC 
        LIMIT {$pagination['items_per_page']} OFFSET {$pagination['offset']}";
$stmt = $db->prepare($sql);
$stmt->execute($params);
$templates = $stmt->fetchAll();

// Fetch categories for filter
$categories = $db->query("SELECT * FROM categories WHERE is_active = 1 ORDER BY display_order")->fetchAll();
?>

<div class="container" style="padding: 3rem 0;">
    <!-- Page Header -->
    <div style="text-align: center; margin-bottom: 3rem;">
        <h1 class="section-title">تصفح القوالب</h1>
        <p style="color: var(--gray); font-size: 1.125rem;">اختر القالب المناسب لمناسبتك</p>
    </div>

    <!-- Filters -->
    <div
        style="background: white; padding: 2rem; border-radius: 12px; margin-bottom: 2rem; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
        <form method="GET" action="" style="display: flex; gap: 1rem; flex-wrap: wrap;">
            <!-- Search -->
            <input type="text" name="search" placeholder="ابحث عن قالب..."
                value="<?php echo htmlspecialchars($search); ?>" class="form-control"
                style="flex: 1; min-width: 250px;">

            <!-- Category Filter -->
            <select name="category" class="form-control" style="min-width: 200px;">
                <option value="">جميع الأقسام</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?php echo $cat['slug']; ?>" <?php echo $category_filter === $cat['slug'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($cat['name_ar']); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <button type="submit" class="btn btn-primary">بحث</button>
            <?php if ($category_filter || $search): ?>
                <a href="templates.php" class="btn btn-secondary">إعادة تعيين</a>
            <?php endif; ?>
        </form>
    </div>

    <!-- Templates Grid -->
    <?php if (!empty($templates)): ?>
        <div class="templates-grid">
            <?php foreach ($templates as $template): ?>
                <a href="<?php echo SITE_URL; ?>/editor-simple.php?template=<?php echo $template['id']; ?>" 
                   class="template-card">
                    <img src="<?php echo SITE_URL . '/uploads/templates/' . ($template['preview_image_url'] ?: $template['image_path']); ?>" 
                         alt="<?php echo htmlspecialchars($template['title']); ?>" 
                         class="template-image"
                         onerror="this.src='<?php echo SITE_URL; ?>/assets/images/placeholder.jpg'">
                    <div class="template-info">
                        <h3 class="template-title"><?php echo htmlspecialchars($template['title']); ?></h3>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>

        <!-- Pagination -->
        <?php if ($pagination['total_pages'] > 1): ?>
            <div style="display: flex; justify-content: center; gap: 0.5rem; margin-top: 3rem;">
                <?php for ($i = 1; $i <= $pagination['total_pages']; $i++): ?>
                    <a href="?page=<?php echo $i; ?><?php echo $category_filter ? '&category=' . $category_filter : ''; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>"
                        class="btn <?php echo $i === $pagination['current_page'] ? 'btn-primary' : 'btn-secondary'; ?>"
                        style="padding: 0.5rem 1rem;">
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>
            </div>
        <?php endif; ?>
    <?php else: ?>
        <div style="text-align: center; padding: 4rem; background: white; border-radius: 12px;">
            <p style="font-size: 1.25rem; color: var(--gray);">لم يتم العثور على قوالب</p>
            <a href="templates.php" class="btn btn-primary" style="margin-top: 1rem;">عرض جميع القوالب</a>
        </div>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>