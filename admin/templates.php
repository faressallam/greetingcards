<?php
$page_title = 'إدارة القوالب';
require_once '../includes/header.php';

require_admin();

$success = '';
$error = '';

// Handle template upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $title = sanitize_input($_POST['title'] ?? '');
    $category_id = (int) ($_POST['category_id'] ?? 0);

    // Position fields
    $name_x = (int) ($_POST['name_x'] ?? 400);
    $name_y = (int) ($_POST['name_y'] ?? 300);
    $name_size = (int) ($_POST['name_size'] ?? 40);
    $name_color = sanitize_input($_POST['name_color'] ?? '#000000');
    $emoji_x = (int) ($_POST['emoji_x'] ?? 200);
    $emoji_y = (int) ($_POST['emoji_y'] ?? 150);
    $emoji_size = (int) ($_POST['emoji_size'] ?? 60);
    $photo_x = (int) ($_POST['photo_x'] ?? 600);
    $photo_y = (int) ($_POST['photo_y'] ?? 150);
    $photo_size = (int) ($_POST['photo_size'] ?? 100);

    // New fields for share feature
    $default_dedication_text = sanitize_input($_POST['default_dedication_text'] ?? '');
    $default_background_id = !empty($_POST['default_background_id']) ? (int) $_POST['default_background_id'] : null;

    if (empty($title) || !$category_id) {
        $error = 'يرجى ملء جميع الحقول';
    } elseif (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
        $error = 'يرجى رفع صورة القالب';
    } else {
        $upload_result = upload_image($_FILES['image'], TEMPLATE_PATH);

        if ($upload_result['success']) {
            $stmt = $db->prepare("INSERT INTO templates (title, category_id, image_path, name_x, name_y, name_size, name_color, emoji_x, emoji_y, emoji_size, photo_x, photo_y, photo_size, default_dedication_text, default_background_id) VALUES (:title, :category_id, :image_path, :name_x, :name_y, :name_size, :name_color, :emoji_x, :emoji_y, :emoji_size, :photo_x, :photo_y, :photo_size, :default_dedication_text, :default_background_id)");

            if (
                $stmt->execute([
                    ':title' => $title,
                    ':category_id' => $category_id,
                    ':image_path' => $upload_result['filename'],
                    ':name_x' => $name_x,
                    ':name_y' => $name_y,
                    ':name_size' => $name_size,
                    ':name_color' => $name_color,
                    ':emoji_x' => $emoji_x,
                    ':emoji_y' => $emoji_y,
                    ':emoji_size' => $emoji_size,
                    ':photo_x' => $photo_x,
                    ':photo_y' => $photo_y,
                    ':photo_size' => $photo_size,
                    ':default_dedication_text' => $default_dedication_text,
                    ':default_background_id' => $default_background_id
                ])
            ) {
                $success = 'تم إضافة القالب بنجاح';
            } else {
                $error = 'حدث خطأ أثناء حفظ القالب';
            }
        } else {
            $error = $upload_result['message'];
        }
    }
}

// Handle delete
if (isset($_GET['delete'])) {
    $id = (int) $_GET['delete'];
    $stmt = $db->prepare("SELECT image_path FROM templates WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $template = $stmt->fetch();

    if ($template) {
        // Delete file
        $file_path = TEMPLATE_PATH . $template['image_path'];
        if (file_exists($file_path)) {
            unlink($file_path);
        }

        // Delete from database
        $db->prepare("DELETE FROM templates WHERE id = :id")->execute([':id' => $id]);
        $success = 'تم حذف القالب بنجاح';
    }
}

// Fetch templates
$templates = $db->query("SELECT t.*, c.name_ar as category_name 
                         FROM templates t 
                         JOIN categories c ON t.category_id = c.id 
                         ORDER BY t.created_at DESC")->fetchAll();

// Fetch categories
$categories = $db->query("SELECT * FROM categories ORDER BY display_order")->fetchAll();

// Fetch backgrounds for dropdown
$backgrounds = $db->query("SELECT * FROM share_backgrounds WHERE is_active = 1 ORDER BY display_order")->fetchAll();
?>

<div class="container" style="padding: 3rem 0;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <h1>إدارة القوالب</h1>
        <a href="<?php echo SITE_URL; ?>/admin/" class="btn btn-secondary">← العودة للوحة التحكم</a>
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

    <!-- Add Template Form -->
    <div
        style="background: white; padding: 2rem; border-radius: 12px; margin-bottom: 3rem; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
        <h2 style="margin-bottom: 1.5rem;">إضافة قالب جديد</h2>
        <form method="POST" action="" enctype="multipart/form-data">
            <input type="hidden" name="action" value="add">

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                <div class="form-group">
                    <label class="form-label">عنوان القالب</label>
                    <input type="text" name="title" class="form-control" required>
                </div>

                <div class="form-group">
                    <label class="form-label">القسم</label>
                    <select name="category_id" class="form-control" required>
                        <option value="">اختر القسم</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['name_ar']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">صورة القالب</label>
                <input type="file" name="image" class="form-control" accept="image/*" required>
                <small style="color: var(--gray);">الحجم الموصى به: 800x600 بكسل</small>
            </div>

            <div class="form-group">
                <label class="form-label">💌 نص الإهداء الافتراضي (اختياري)</label>
                <textarea name="default_dedication_text" class="form-control" rows="3"
                    placeholder="مثال: كل عام وأنت بخير يا أغلى الناس"></textarea>
                <small style="color: var(--gray);">هذا النص سيظهر مع الكارت عند المشاركة ويمكن للمستخدم تعديله</small>
            </div>

            <div class="form-group">
                <label class="form-label">🎨 خلفية صفحة المشاركة (اختياري)</label>
                <select name="default_background_id" class="form-control">
                    <option value="">بدون خلفية</option>
                    <?php foreach ($backgrounds as $bg): ?>
                        <option value="<?php echo $bg['id']; ?>"><?php echo htmlspecialchars($bg['name']); ?></option>
                    <?php endforeach; ?>
                </select>
                <small style="color: var(--gray);">الخلفية التي ستظهر في صفحة المشاركة</small>
            </div>

            <hr style="margin: 2rem 0;">
            <h3 style="margin-bottom: 1rem;">📍 تحديد مواضع العناصر (المحرر البسيط)</h3>

            <div style="background: #f8fafc; padding: 1.5rem; border-radius: 8px; margin-bottom: 1.5rem;">
                <h4 style="margin-bottom: 1rem;">📝 موضع الاسم</h4>
                <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 1rem;">
                    <div class="form-group">
                        <label class="form-label">X</label>
                        <input type="number" name="name_x" class="form-control" value="400">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Y</label>
                        <input type="number" name="name_y" class="form-control" value="300">
                    </div>
                    <div class="form-group">
                        <label class="form-label">الحجم</label>
                        <input type="number" name="name_size" class="form-control" value="40">
                    </div>
                    <div class="form-group">
                        <label class="form-label">اللون</label>
                        <input type="color" name="name_color" class="form-control" value="#000000">
                    </div>
                </div>
            </div>

            <div style="background: #f8fafc; padding: 1.5rem; border-radius: 8px; margin-bottom: 1.5rem;">
                <h4 style="margin-bottom: 1rem;">😊 موضع الإيموجي</h4>
                <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem;">
                    <div class="form-group">
                        <label class="form-label">X</label>
                        <input type="number" name="emoji_x" class="form-control" value="200">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Y</label>
                        <input type="number" name="emoji_y" class="form-control" value="150">
                    </div>
                    <div class="form-group">
                        <label class="form-label">الحجم</label>
                        <input type="number" name="emoji_size" class="form-control" value="60">
                    </div>
                </div>
            </div>

            <div style="background: #f8fafc; padding: 1.5rem; border-radius: 8px; margin-bottom: 1.5rem;">
                <h4 style="margin-bottom: 1rem;">📷 موضع الصورة الشخصية</h4>
                <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem;">
                    <div class="form-group">
                        <label class="form-label">X</label>
                        <input type="number" name="photo_x" class="form-control" value="600">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Y</label>
                        <input type="number" name="photo_y" class="form-control" value="150">
                    </div>
                    <div class="form-group">
                        <label class="form-label">الحجم (قطر الدائرة)</label>
                        <input type="number" name="photo_size" class="form-control" value="100">
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-primary" style="padding: 1rem 2rem; font-size: 1.125rem;">إضافة
                القالب</button>
        </form>
    </div>

    <!-- Templates List -->
    <div style="background: white; padding: 2rem; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
        <h2 style="margin-bottom: 1.5rem;">القوالب الموجودة (<?php echo count($templates); ?>)</h2>

        <?php if (!empty($templates)): ?>
            <div class="templates-grid">
                <?php foreach ($templates as $template): ?>
                    <div class="template-card">
                        <img src="<?php echo SITE_URL . '/uploads/templates/' . $template['image_path']; ?>"
                            alt="<?php echo htmlspecialchars($template['title']); ?>" class="template-image">
                        <div class="template-info">
                            <h3 class="template-title"><?php echo htmlspecialchars($template['title']); ?></h3>
                            <p class="template-category"><?php echo htmlspecialchars($template['category_name']); ?></p>
                            <div style="display: flex; gap: 0.5rem; margin-top: 1rem; font-size: 0.875rem; color: var(--gray);">
                                <span>👁️ <?php echo $template['views']; ?></span>
                                <span>⬇️ <?php echo $template['downloads']; ?></span>
                            </div>
                            <div style="display: flex; gap: 0.5rem; margin-top: 1rem;">
                                <a href="<?php echo SITE_URL; ?>/editor-simple.php?template=<?php echo $template['id']; ?>"
                                    class="btn btn-primary" style="flex: 1; padding: 0.5rem; font-size: 0.875rem;">
                                    ✨ محرر بسيط
                                </a>
                                <a href="<?php echo SITE_URL; ?>/editor.php?template=<?php echo $template['id']; ?>"
                                    class="btn btn-secondary" style="flex: 1; padding: 0.5rem; font-size: 0.875rem;">
                                    🎨 محرر متقدم
                                </a>
                            </div>
                            <div style="display: flex; gap: 0.5rem; margin-top: 0.5rem;">
                                <a href="<?php echo SITE_URL; ?>/admin/edit-template.php?id=<?php echo $template['id']; ?>"
                                    class="btn btn-secondary" style="flex: 1; padding: 0.5rem; font-size: 0.875rem;">
                                    ⚙️ تعديل
                                </a>
                                <a href="?delete=<?php echo $template['id']; ?>" class="btn btn-danger"
                                    style="flex: 1; padding: 0.5rem; font-size: 0.875rem;"
                                    onclick="return confirm('هل أنت متأكد من حذف هذا القالب؟')">
                                    🗑️ حذف
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p style="text-align: center; color: var(--gray); padding: 2rem;">لا توجد قوالب بعد</p>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>