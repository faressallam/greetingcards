<?php
$page_title = 'إدارة القائمة';
require_once '../includes/header.php';

require_admin();

$success = '';
$error = '';
$edit_item = null;

// Handle edit mode
if (isset($_GET['edit'])) {
    $id = (int) $_GET['edit'];
    $stmt = $db->prepare("SELECT * FROM menu_items WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $edit_item = $stmt->fetch();
}

// Handle add/edit menu item
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'add' || $_POST['action'] === 'edit') {
        $title = sanitize_input($_POST['title'] ?? '');
        $url = sanitize_input($_POST['url'] ?? '');
        $parent_id = (int) ($_POST['parent_id'] ?? 0);
        $display_order = (int) ($_POST['display_order'] ?? 0);
        $is_active = isset($_POST['is_active']) ? 1 : 0;

        if (empty($title) || empty($url)) {
            $error = 'يرجى ملء جميع الحقول المطلوبة';
        } else {
            if ($_POST['action'] === 'add') {
                $stmt = $db->prepare("INSERT INTO menu_items (title, url, parent_id, display_order, is_active) VALUES (:title, :url, :parent_id, :display_order, :is_active)");
                if (
                    $stmt->execute([
                        ':title' => $title,
                        ':url' => $url,
                        ':parent_id' => $parent_id,
                        ':display_order' => $display_order,
                        ':is_active' => $is_active
                    ])
                ) {
                    $success = 'تم إضافة عنصر القائمة بنجاح';
                }
            } else {
                $id = (int) $_POST['id'];
                $stmt = $db->prepare("UPDATE menu_items SET title = :title, url = :url, parent_id = :parent_id, display_order = :display_order, is_active = :is_active WHERE id = :id");
                if (
                    $stmt->execute([
                        ':title' => $title,
                        ':url' => $url,
                        ':parent_id' => $parent_id,
                        ':display_order' => $display_order,
                        ':is_active' => $is_active,
                        ':id' => $id
                    ])
                ) {
                    $success = 'تم تحديث عنصر القائمة بنجاح';
                    $edit_item = null;
                    header('Location: menu.php');
                    exit;
                }
            }
        }
    }
}

// Handle delete
if (isset($_GET['delete'])) {
    $id = (int) $_GET['delete'];
    $db->prepare("DELETE FROM menu_items WHERE id = :id")->execute([':id' => $id]);
    $success = 'تم حذف عنصر القائمة بنجاح';
}

// Fetch all menu items
$menu_items = $db->query("SELECT * FROM menu_items ORDER BY parent_id, display_order")->fetchAll();

// Get parent menu items for dropdown
$parent_items = $db->query("SELECT * FROM menu_items WHERE parent_id = 0 ORDER BY display_order")->fetchAll();
?>

<div class="container" style="padding: 3rem 0;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <h1>إدارة قائمة الموقع</h1>
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

    <!-- Add/Edit Menu Item Form -->
    <div
        style="background: white; padding: 2rem; border-radius: 12px; margin-bottom: 3rem; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
        <h2 style="margin-bottom: 1.5rem;">
            <?php echo $edit_item ? 'تعديل عنصر القائمة' : 'إضافة عنصر جديد للقائمة'; ?>
        </h2>
        <form method="POST" action="">
            <input type="hidden" name="action" value="<?php echo $edit_item ? 'edit' : 'add'; ?>">
            <?php if ($edit_item): ?>
                <input type="hidden" name="id" value="<?php echo $edit_item['id']; ?>">
            <?php endif; ?>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                <div class="form-group">
                    <label class="form-label">عنوان العنصر</label>
                    <input type="text" name="title" class="form-control" required placeholder="مثال: الرئيسية"
                        value="<?php echo $edit_item ? htmlspecialchars($edit_item['title']) : ''; ?>">
                </div>

                <div class="form-group">
                    <label class="form-label">الرابط (URL)</label>
                    <input type="text" name="url" class="form-control" required placeholder="مثال: /index.php"
                        value="<?php echo $edit_item ? htmlspecialchars($edit_item['url']) : ''; ?>">
                </div>

                <div class="form-group">
                    <label class="form-label">العنصر الرئيسي</label>
                    <select name="parent_id" class="form-control">
                        <option value="0" <?php echo ($edit_item && $edit_item['parent_id'] == 0) ? 'selected' : ''; ?>>
                            عنصر رئيسي</option>
                        <?php foreach ($parent_items as $item): ?>
                            <option value="<?php echo $item['id']; ?>" <?php echo ($edit_item && $edit_item['parent_id'] == $item['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($item['title']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">ترتيب العرض</label>
                    <input type="number" name="display_order" class="form-control"
                        value="<?php echo $edit_item ? $edit_item['display_order'] : '0'; ?>">
                </div>
            </div>

            <div class="form-group" style="margin-top: 1rem;">
                <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                    <input type="checkbox" name="is_active" <?php echo (!$edit_item || $edit_item['is_active']) ? 'checked' : ''; ?>>
                    <span>نشط (يظهر في القائمة)</span>
                </label>
            </div>

            <div style="display: flex; gap: 1rem; margin-top: 1rem;">
                <button type="submit" class="btn btn-primary">
                    <?php echo $edit_item ? 'تحديث' : 'إضافة للقائمة'; ?>
                </button>
                <?php if ($edit_item): ?>
                    <a href="menu.php" class="btn btn-secondary">إلغاء</a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- Menu Items List -->
    <div style="background: white; padding: 2rem; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
        <h2 style="margin-bottom: 1.5rem;">عناصر القائمة الحالية (<?php echo count($menu_items); ?>)</h2>

        <table style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="background: var(--light); text-align: right;">
                    <th style="padding: 1rem;">العنوان</th>
                    <th style="padding: 1rem;">الرابط</th>
                    <th style="padding: 1rem;">النوع</th>
                    <th style="padding: 1rem;">الترتيب</th>
                    <th style="padding: 1rem;">الحالة</th>
                    <th style="padding: 1rem;">الإجراءات</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($menu_items as $item): ?>
                    <tr style="border-bottom: 1px solid var(--border);">
                        <td style="padding: 1rem;">
                            <?php if ($item['parent_id'] > 0): ?>
                                <span style="margin-left: 1rem;">└─</span>
                            <?php endif; ?>
                            <?php echo htmlspecialchars($item['title']); ?>
                        </td>
                        <td style="padding: 1rem; font-family: monospace; font-size: 0.875rem;">
                            <?php echo htmlspecialchars($item['url']); ?>
                        </td>
                        <td style="padding: 1rem;">
                            <?php if ($item['parent_id'] == 0): ?>
                                <span
                                    style="background: #8b5cf6; color: white; padding: 0.25rem 0.75rem; border-radius: 20px; font-size: 0.875rem;">رئيسي</span>
                            <?php else: ?>
                                <span
                                    style="background: #64748b; color: white; padding: 0.25rem 0.75rem; border-radius: 20px; font-size: 0.875rem;">فرعي</span>
                            <?php endif; ?>
                        </td>
                        <td style="padding: 1rem;"><?php echo $item['display_order']; ?></td>
                        <td style="padding: 1rem;">
                            <?php if ($item['is_active']): ?>
                                <span style="color: #10b981;">● نشط</span>
                            <?php else: ?>
                                <span style="color: #ef4444;">● مخفي</span>
                            <?php endif; ?>
                        </td>
                        <td style="padding: 1rem;">
                            <div style="display: flex; gap: 0.5rem;">
                                <a href="?edit=<?php echo $item['id']; ?>" class="btn btn-secondary"
                                    style="padding: 0.5rem 1rem; font-size: 0.875rem;">
                                    تعديل
                                </a>
                                <a href="?delete=<?php echo $item['id']; ?>" class="btn btn-danger"
                                    style="padding: 0.5rem 1rem; font-size: 0.875rem;"
                                    onclick="return confirm('هل أنت متأكد من حذف هذا العنصر؟')">
                                    حذف
                                </a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Preview -->
    <div
        style="background: white; padding: 2rem; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); margin-top: 2rem;">
        <h2 style="margin-bottom: 1.5rem;">معاينة القائمة</h2>
        <div style="background: #f8fafc; padding: 1rem; border-radius: 8px;">
            <nav style="display: flex; gap: 2rem; flex-wrap: wrap;">
                <?php
                $active_items = array_filter($menu_items, function ($item) {
                    return $item['is_active'] && $item['parent_id'] == 0;
                });
                foreach ($active_items as $item):
                    ?>
                    <div style="position: relative;">
                        <a href="#" style="text-decoration: none; color: var(--dark); font-weight: 500;">
                            <?php echo htmlspecialchars($item['title']); ?>
                        </a>
                        <?php
                        $children = array_filter($menu_items, function ($child) use ($item) {
                            return $child['parent_id'] == $item['id'] && $child['is_active'];
                        });
                        if (!empty($children)):
                            ?>
                            <div style="margin-top: 0.5rem; padding-right: 1rem; font-size: 0.875rem; color: var(--gray);">
                                <?php foreach ($children as $child): ?>
                                    <div>↳ <?php echo htmlspecialchars($child['title']); ?></div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </nav>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>