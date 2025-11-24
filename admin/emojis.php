<?php
$page_title = 'إدارة الإيموجي';
require_once '../includes/header.php';

require_admin();

$success = '';
$error = '';

// Handle add emoji
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $category_id = (int) ($_POST['category_id'] ?? 0);
    $display_order = (int) ($_POST['display_order'] ?? 0);
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    if (!$category_id) {
        $error = 'يرجى اختيار القسم';
    } elseif (!isset($_FILES['emoji_file']) || $_FILES['emoji_file']['error'] !== UPLOAD_ERR_OK) {
        $error = 'يرجى رفع صورة الإيموجي';
    } else {
        // Create emoji directory if it doesn't exist
        if (!file_exists(EMOJI_PATH)) {
            mkdir(EMOJI_PATH, 0755, true);
        }

        $upload_result = upload_image($_FILES['emoji_file'], EMOJI_PATH);

        if ($upload_result['success']) {
            $stmt = $db->prepare("INSERT INTO emojis (category_id, file_path, display_order, is_active) VALUES (:category_id, :file_path, :display_order, :is_active)");
            if (
                $stmt->execute([
                    ':category_id' => $category_id,
                    ':file_path' => $upload_result['filename'],
                    ':display_order' => $display_order,
                    ':is_active' => $is_active
                ])
            ) {
                $success = 'تم إضافة الإيموجي بنجاح';
            } else {
                $error = 'حدث خطأ أثناء حفظ الإيموجي';
            }
        } else {
            $error = $upload_result['message'];
        }
    }
}

// Handle delete
if (isset($_GET['delete'])) {
    $id = (int) $_GET['delete'];
    $stmt = $db->prepare("SELECT file_path FROM emojis WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $emoji = $stmt->fetch();

    if ($emoji) {
        // Delete file
        $file_path = EMOJI_PATH . $emoji['file_path'];
        if (file_exists($file_path)) {
            unlink($file_path);
        }

        // Delete from database
        $db->prepare("DELETE FROM emojis WHERE id = :id")->execute([':id' => $id]);
        $success = 'تم حذف الإيموجي بنجاح';
    }
}

// Fetch emojis with category names
$emojis = [];
$categories = [];
try {
    $emojis = $db->query("SELECT e.*, ec.name as category_name 
                          FROM emojis e 
                          JOIN emoji_categories ec ON e.category_id = ec.id 
                          ORDER BY ec.display_order, e.display_order ASC")->fetchAll();

    // Fetch categories for dropdown
    $categories = $db->query("SELECT * FROM emoji_categories WHERE is_active = 1 ORDER BY display_order ASC")->fetchAll();
} catch (PDOException $e) {
    $error = 'خطأ في قاعدة البيانات. تأكد من تشغيل setup_emoji_db.php أولاً.';
}
?>

<div class="container" style="padding: 3rem 0;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <h1>إدارة الإيموجي</h1>
        <div style="display: flex; gap: 1rem;">
            <a href="<?php echo SITE_URL; ?>/admin/emoji_categories.php" class="btn btn-secondary">إدارة الأقسام</a>
            <a href="<?php echo SITE_URL; ?>/admin/" class="btn btn-secondary">← العودة</a>
        </div>
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

    <!-- Add Emoji Form -->
    <div
        style="background: white; padding: 2rem; border-radius: 12px; margin-bottom: 3rem; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
        <h2 style="margin-bottom: 1.5rem;">إضافة إيموجي جديد</h2>
        <form method="POST" action="" enctype="multipart/form-data">
            <input type="hidden" name="action" value="add">

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                <div class="form-group">
                    <label class="form-label">القسم</label>
                    <select name="category_id" class="form-control" required>
                        <option value="">اختر القسم</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">ترتيب العرض</label>
                    <input type="number" name="display_order" class="form-control" value="0">
                </div>
            </div>

            <div class="form-group" style="margin-top: 1rem;">
                <label class="form-label">صورة الإيموجي</label>
                <input type="file" name="emoji_file" class="form-control" accept="image/*" required>
                <small style="color: var(--gray);">الحجم الموصى به: 128x128 بكسل (PNG شفاف)</small>
            </div>

            <div class="form-group" style="margin-top: 1rem;">
                <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                    <input type="checkbox" name="is_active" checked>
                    <span>نشط</span>
                </label>
            </div>

            <button type="submit" class="btn btn-primary" style="margin-top: 1rem;">إضافة الإيموجي</button>
        </form>
    </div>

    <!-- Emojis List -->
    <div style="background: white; padding: 2rem; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
        <h2 style="margin-bottom: 1.5rem;">الإيموجي الموجودة (<?php echo count($emojis); ?>)</h2>

        <?php if (!empty($emojis)): ?>
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 1rem;">
                <?php foreach ($emojis as $emoji): ?>
                    <div
                        style="background: #f8fafc; padding: 1rem; border-radius: 8px; text-align: center; position: relative;">
                        <img src="<?php echo SITE_URL . '/uploads/emojis/' . $emoji['file_path']; ?>" alt="Emoji"
                            style="width: 64px; height: 64px; object-fit: contain; margin-bottom: 0.5rem;">
                        <div style="font-size: 0.75rem; color: var(--gray); margin-bottom: 0.5rem;">
                            <?php echo htmlspecialchars($emoji['category_name']); ?>
                        </div>
                        <div style="font-size: 0.75rem; color: var(--gray); margin-bottom: 0.5rem;">
                            ترتيب: <?php echo $emoji['display_order']; ?>
                        </div>
                        <div style="margin-bottom: 0.5rem;">
                            <?php if ($emoji['is_active']): ?>
                                <span style="color: #10b981; font-size: 0.75rem;">● نشط</span>
                            <?php else: ?>
                                <span style="color: #ef4444; font-size: 0.75rem;">● غير نشط</span>
                            <?php endif; ?>
                        </div>
                        <a href="?delete=<?php echo $emoji['id']; ?>" class="btn btn-danger"
                            style="padding: 0.25rem 0.5rem; font-size: 0.75rem; width: 100%;"
                            onclick="return confirm('هل أنت متأكد من حذف هذا الإيموجي؟')">
                            حذف
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p style="text-align: center; color: var(--gray); padding: 2rem;">لا توجد إيموجي حالياً</p>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>