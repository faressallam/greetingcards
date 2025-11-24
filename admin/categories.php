<?php
$page_title = 'إدارة الأقسام';
require_once '../includes/header.php';

require_admin();

$success = '';
$error = '';

// Handle add/edit category
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'add' || $_POST['action'] === 'edit') {
        $name_ar = sanitize_input($_POST['name_ar'] ?? '');
        $slug = sanitize_input($_POST['slug'] ?? '');
        $parent_id = (int) ($_POST['parent_id'] ?? 0);
        $display_order = (int) ($_POST['display_order'] ?? 0);
        $is_active = isset($_POST['is_active']) ? 1 : 0;

        if (empty($name_ar) || empty($slug)) {
            $error = 'يرجى ملء جميع الحقول المطلوبة';
        } else {
            if ($_POST['action'] === 'add') {
                $stmt = $db->prepare("INSERT INTO categories (name_ar, name_en, slug, parent_id, display_order, is_active) VALUES (:name_ar, :name_en, :slug, :parent_id, :display_order, :is_active)");
                if (
                    $stmt->execute([
                        ':name_ar' => $name_ar,
                        ':name_en' => $name_ar, // هياخد نفس الاسم العربي
                        ':slug' => $slug,
                        ':parent_id' => $parent_id,
                        ':display_order' => $display_order,
                        ':is_active' => $is_active
                    ])
                ) {
                    $success = 'تم إضافة القسم بنجاح';
                }
            } else {
                $id = (int) $_POST['id'];
                $stmt = $db->prepare("UPDATE categories SET name_ar = :name_ar, slug = :slug, parent_id = :parent_id, display_order = :display_order, is_active = :is_active WHERE id = :id");
                if (
                    $stmt->execute([
                        ':name_ar' => $name_ar,
                        ':slug' => $slug,
                        ':parent_id' => $parent_id,
                        ':display_order' => $display_order,
                        ':is_active' => $is_active,
                        ':id' => $id
                    ])
                ) {
                    $success = 'تم تحديث القسم بنجاح';
                }
            }
        }
    }
}

// Handle delete
if (isset($_GET['delete'])) {
    $id = (int) $_GET['delete'];
    $db->prepare("DELETE FROM categories WHERE id = :id")->execute([':id' => $id]);
    $success = 'تم حذف القسم بنجاح';
}

// Fetch all categories
$categories = $db->query("SELECT * FROM categories ORDER BY parent_id, display_order")->fetchAll();

// Get parent categories for dropdown
$parent_categories = $db->query("SELECT * FROM categories WHERE parent_id = 0 ORDER BY display_order")->fetchAll();
?>

<div class="container" style="padding: 3rem 0;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <h1>إدارة الأقسام</h1>
        <a href="<?php echo SITE_URL; ?>/admin/" class="btn btn-secondary">← العودة</a>
    </div>

    <?php if ($success): ?>
        <div style="background: #d1fae5; color: #065f46; padding: 1rem; border-radius: 8px; margin-bottom: 2rem;">
            <?php echo $success; ?>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div style="background: #fee2e2; color: #991b1b; padding: 1rem; border-radius: 8px; margin-bottom: 2rem;">
            <?php echo $error; ?>
        </div>
    <?php endif; ?>

    <!-- Add Category Form -->
    <div
        style="background: white; padding: 2rem; border-radius: 12px; margin-bottom: 3rem; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
        <h2 style="margin-bottom: 1.5rem;">إضافة قسم جديد</h2>
        <form method="POST" action="">
            <input type="hidden" name="action" value="add">

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                <div class="form-group">
                    <label class="form-label">اسم القسم</label>
                    <input type="text" name="name_ar" class="form-control" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Slug (رابط القسم)</label>
                    <input type="text" name="slug" class="form-control" required placeholder="مثال: eid-fitr">
                </div>

                <div class="form-group">
                    <label class="form-label">القسم الرئيسي</label>
                    <select name="parent_id" class="form-control">
                        <option value="0">قسم رئيسي</option>
                        <?php foreach ($parent_categories as $cat): ?>
                            <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['name_ar']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">ترتيب العرض</label>
                    <input type="number" name="display_order" class="form-control" value="0">
                </div>
            </div>

            <div class="form-group">
                <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                    <input type="checkbox" name="is_active" checked>
                    <span>نشط</span>
                </label>
            </div>

            <button type="submit" class="btn btn-primary">إضافة القسم</button>
        </form>
    </div>

    <!-- Categories List -->
    <div style="background: white; padding: 2rem; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
        <h2 style="margin-bottom: 1.5rem;">الأقسام الموجودة (<?php echo count($categories); ?>)</h2>

        <table style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="background: var(--light); text-align: right;">
                    <th style="padding: 1rem;">الاسم</th>
                    <th style="padding: 1rem;">Slug</th>
                    <th style="padding: 1rem;">النوع</th>
                    <th style="padding: 1rem;">الترتيب</th>
                    <th style="padding: 1rem;">الحالة</th>
                    <th style="padding: 1rem;">الإجراءات</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($categories as $category): ?>
                    <tr style="border-bottom: 1px solid var(--border);">
                        <td style="padding: 1rem;">
                            <?php if ($category['parent_id'] > 0): ?>
                                <span style="margin-left: 1rem;">└─</span>
                            <?php endif; ?>
                            <?php echo htmlspecialchars($category['name_ar']); ?>
                        </td>
                        <td style="padding: 1rem;"><?php echo htmlspecialchars($category['slug']); ?></td>
                        <td style="padding: 1rem;">
                            <?php if ($category['parent_id'] == 0): ?>
                                <span
                                    style="background: #8b5cf6; color: white; padding: 0.25rem 0.75rem; border-radius: 20px; font-size: 0.875rem;">رئيسي</span>
                            <?php else: ?>
                                <span
                                    style="background: #64748b; color: white; padding: 0.25rem 0.75rem; border-radius: 20px; font-size: 0.875rem;">فرعي</span>
                            <?php endif; ?>
                        </td>
                        <td style="padding: 1rem;"><?php echo $category['display_order']; ?></td>
                        <td style="padding: 1rem;">
                            <?php if ($category['is_active']): ?>
                                <span style="color: #10b981;">● نشط</span>
                            <?php else: ?>
                                <span style="color: #ef4444;">● غير نشط</span>
                            <?php endif; ?>
                        </td>
                        <td style="padding: 1rem;">
                            <a href="?delete=<?php echo $category['id']; ?>" class="btn btn-danger"
                                style="padding: 0.5rem 1rem; font-size: 0.875rem;"
                                onclick="return confirm('هل أنت متأكد من حذف هذا القسم؟')">
                                حذف
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>