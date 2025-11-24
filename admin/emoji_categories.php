<?php
$page_title = 'إدارة أقسام الإيموجي';
require_once '../includes/header.php';

require_admin();

$success = '';
$error = '';

// Handle add category
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'add') {
        $name = sanitize_input($_POST['name'] ?? '');
        $display_order = (int) ($_POST['display_order'] ?? 0);
        $is_active = isset($_POST['is_active']) ? 1 : 0;

        if (empty($name)) {
            $error = 'يرجى ملء اسم القسم';
        } else {
            $stmt = $db->prepare("INSERT INTO emoji_categories (name, display_order, is_active) VALUES (:name, :display_order, :is_active)");
            if (
                $stmt->execute([
                    ':name' => $name,
                    ':display_order' => $display_order,
                    ':is_active' => $is_active
                ])
            ) {
                $success = 'تم إضافة قسم الإيموجي بنجاح';
            } else {
                $error = 'حدث خطأ أثناء الإضافة';
            }
        }
    }
}

// Handle delete
if (isset($_GET['delete'])) {
    $id = (int) $_GET['delete'];
    // Check if category has emojis
    $check = $db->prepare("SELECT COUNT(*) FROM emojis WHERE category_id = :id");
    $check->execute([':id' => $id]);
    if ($check->fetchColumn() > 0) {
        $error = 'لا يمكن حذف هذا القسم لأنه يحتوي على إيموجي. يرجى حذف الإيموجي أولاً.';
    } else {
        $db->prepare("DELETE FROM emoji_categories WHERE id = :id")->execute([':id' => $id]);
        $success = 'تم حذف القسم بنجاح';
    }
}

// Fetch all categories
$categories = [];
try {
    $categories = $db->query("SELECT * FROM emoji_categories ORDER BY display_order ASC")->fetchAll();
} catch (PDOException $e) {
    $error = 'خطأ في قاعدة البيانات. تأكد من تشغيل setup_emoji_db.php أولاً.';
}
?>

<div class="container" style="padding: 3rem 0;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <h1>إدارة أقسام الإيموجي</h1>
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
                    <input type="text" name="name" class="form-control" required placeholder="مثال: وجوه مبتسمة">
                </div>

                <div class="form-group">
                    <label class="form-label">ترتيب العرض</label>
                    <input type="number" name="display_order" class="form-control" value="0">
                </div>
            </div>

            <div class="form-group" style="margin-top: 1rem;">
                <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                    <input type="checkbox" name="is_active" checked>
                    <span>نشط</span>
                </label>
            </div>

            <button type="submit" class="btn btn-primary" style="margin-top: 1rem;">إضافة القسم</button>
        </form>
    </div>

    <!-- Categories List -->
    <div style="background: white; padding: 2rem; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
        <h2 style="margin-bottom: 1.5rem;">الأقسام الموجودة (<?php echo count($categories); ?>)</h2>

        <table style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="background: var(--light); text-align: right;">
                    <th style="padding: 1rem;">الاسم</th>
                    <th style="padding: 1rem;">الترتيب</th>
                    <th style="padding: 1rem;">الحالة</th>
                    <th style="padding: 1rem;">الإجراءات</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($categories as $category): ?>
                    <tr style="border-bottom: 1px solid var(--border);">
                        <td style="padding: 1rem;">
                            <?php echo htmlspecialchars($category['name']); ?>
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
                <?php if (empty($categories)): ?>
                    <tr>
                        <td colspan="4" style="padding: 2rem; text-align: center; color: #666;">لا توجد أقسام حالياً</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>