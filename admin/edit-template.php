<?php
$page_title = 'تعديل القالب';
require_once '../includes/header.php';

require_admin();

$success = '';
$error = '';
$template = null;

// Get template ID
$template_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($template_id) {
    $stmt = $db->prepare("SELECT * FROM templates WHERE id = :id");
    $stmt->execute([':id' => $template_id]);
    $template = $stmt->fetch();
}

if (!$template) {
    redirect(SITE_URL . '/admin/templates.php');
}

// Handle update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update') {
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

    if (empty($title) || !$category_id) {
        $error = 'يرجى ملء جميع الحقول';
    } else {
        $stmt = $db->prepare("UPDATE templates SET title = :title, category_id = :category_id, name_x = :name_x, name_y = :name_y, name_size = :name_size, name_color = :name_color, emoji_x = :emoji_x, emoji_y = :emoji_y, emoji_size = :emoji_size, photo_x = :photo_x, photo_y = :photo_y, photo_size = :photo_size WHERE id = :id");

        if (
            $stmt->execute([
                ':title' => $title,
                ':category_id' => $category_id,
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
                ':id' => $template_id
            ])
        ) {
            $success = 'تم تحديث القالب بنجاح';
            // Refresh template data
            $stmt = $db->prepare("SELECT * FROM templates WHERE id = :id");
            $stmt->execute([':id' => $template_id]);
            $template = $stmt->fetch();
        } else {
            $error = 'حدث خطأ أثناء التحديث';
        }
    }
}

// Fetch categories
$categories = $db->query("SELECT * FROM categories ORDER BY display_order")->fetchAll();
?>

<style>
    .position-picker {
        position: relative;
        display: inline-block;
        margin: 2rem 0;
        border: 2px solid var(--border);
        border-radius: 8px;
        overflow: hidden;
    }

    .position-picker img {
        display: block;
        max-width: 100%;
        height: auto;
    }

    .position-marker {
        position: absolute;
        width: 20px;
        height: 20px;
        border-radius: 50%;
        border: 3px solid white;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
        cursor: move;
        transform: translate(-50%, -50%);
    }

    .marker-name {
        background: #ef4444;
    }

    .marker-emoji {
        background: #f59e0b;
    }

    .marker-photo {
        background: #10b981;
    }

    .marker-label {
        position: absolute;
        top: -30px;
        left: 50%;
        transform: translateX(-50%);
        background: rgba(0, 0, 0, 0.8);
        color: white;
        padding: 0.25rem 0.5rem;
        border-radius: 4px;
        font-size: 0.75rem;
        white-space: nowrap;
    }
</style>

<div class="container" style="padding: 3rem 0;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <h1>تعديل القالب: <?php echo htmlspecialchars($template['title']); ?></h1>
        <a href="<?php echo SITE_URL; ?>/admin/templates.php" class="btn btn-secondary">← العودة</a>
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

    <div style="background: white; padding: 2rem; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
        <form method="POST" action="">
            <input type="hidden" name="action" value="update">

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 2rem;">
                <div class="form-group">
                    <label class="form-label">عنوان القالب</label>
                    <input type="text" name="title" class="form-control"
                        value="<?php echo htmlspecialchars($template['title']); ?>" required>
                </div>

                <div class="form-group">
                    <label class="form-label">القسم</label>
                    <select name="category_id" class="form-control" required>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo $cat['id']; ?>" <?php echo $cat['id'] == $template['category_id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat['name_ar']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <hr style="margin: 2rem 0;">

            <h3 style="margin-bottom: 1rem;">🎯 أداة تحديد المواضع</h3>
            <p style="color: var(--gray); margin-bottom: 1rem;">اسحب النقاط على الصورة لتحديد مواضع العناصر</p>

            <!-- Position Picker -->
            <div class="position-picker" id="position-picker">
                <img src="<?php echo SITE_URL . '/uploads/templates/' . $template['image_path']; ?>"
                    alt="<?php echo htmlspecialchars($template['title']); ?>" id="template-image">

                <div class="position-marker marker-name" id="marker-name"
                    style="left: <?php echo $template['name_x'] ?? 400; ?>px; top: <?php echo $template['name_y'] ?? 300; ?>px;">
                    <div class="marker-label">الاسم</div>
                </div>

                <div class="position-marker marker-emoji" id="marker-emoji"
                    style="left: <?php echo $template['emoji_x'] ?? 200; ?>px; top: <?php echo $template['emoji_y'] ?? 150; ?>px;">
                    <div class="marker-label">الإيموجي</div>
                </div>

                <div class="position-marker marker-photo" id="marker-photo"
                    style="left: <?php echo $template['photo_x'] ?? 600; ?>px; top: <?php echo $template['photo_y'] ?? 150; ?>px;">
                    <div class="marker-label">الصورة</div>
                </div>
            </div>

            <hr style="margin: 2rem 0;">

            <!-- Manual Input Fields -->
            <div style="background: #f8fafc; padding: 1.5rem; border-radius: 8px; margin-bottom: 1.5rem;">
                <h4 style="margin-bottom: 1rem;">📝 موضع الاسم</h4>
                <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 1rem;">
                    <div class="form-group">
                        <label class="form-label">X</label>
                        <input type="number" name="name_x" id="name_x" class="form-control"
                            value="<?php echo $template['name_x'] ?? 400; ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Y</label>
                        <input type="number" name="name_y" id="name_y" class="form-control"
                            value="<?php echo $template['name_y'] ?? 300; ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label">الحجم</label>
                        <input type="number" name="name_size" class="form-control"
                            value="<?php echo $template['name_size'] ?? 40; ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label">اللون</label>
                        <input type="color" name="name_color" class="form-control"
                            value="<?php echo $template['name_color'] ?? '#000000'; ?>">
                    </div>
                </div>
            </div>

            <div style="background: #f8fafc; padding: 1.5rem; border-radius: 8px; margin-bottom: 1.5rem;">
                <h4 style="margin-bottom: 1rem;">😊 موضع الإيموجي</h4>
                <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem;">
                    <div class="form-group">
                        <label class="form-label">X</label>
                        <input type="number" name="emoji_x" id="emoji_x" class="form-control"
                            value="<?php echo $template['emoji_x'] ?? 200; ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Y</label>
                        <input type="number" name="emoji_y" id="emoji_y" class="form-control"
                            value="<?php echo $template['emoji_y'] ?? 150; ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label">الحجم</label>
                        <input type="number" name="emoji_size" class="form-control"
                            value="<?php echo $template['emoji_size'] ?? 60; ?>">
                    </div>
                </div>
            </div>

            <div style="background: #f8fafc; padding: 1.5rem; border-radius: 8px; margin-bottom: 1.5rem;">
                <h4 style="margin-bottom: 1rem;">📷 موضع الصورة الشخصية</h4>
                <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem;">
                    <div class="form-group">
                        <label class="form-label">X</label>
                        <input type="number" name="photo_x" id="photo_x" class="form-control"
                            value="<?php echo $template['photo_x'] ?? 600; ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Y</label>
                        <input type="number" name="photo_y" id="photo_y" class="form-control"
                            value="<?php echo $template['photo_y'] ?? 150; ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label">الحجم (قطر الدائرة)</label>
                        <input type="number" name="photo_size" class="form-control"
                            value="<?php echo $template['photo_size'] ?? 100; ?>">
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-primary" style="padding: 1rem 2rem; font-size: 1.125rem;">حفظ
                التعديلات</button>
        </form>
    </div>
</div>

<script>
    // Draggable markers
    const picker = document.getElementById('position-picker');
    const image = document.getElementById('template-image');
    const markers = {
        name: document.getElementById('marker-name'),
        emoji: document.getElementById('marker-emoji'),
        photo: document.getElementById('marker-photo')
    };

    const inputs = {
        name: { x: document.getElementById('name_x'), y: document.getElementById('name_y') },
        emoji: { x: document.getElementById('emoji_x'), y: document.getElementById('emoji_y') },
        photo: { x: document.getElementById('photo_x'), y: document.getElementById('photo_y') }
    };

    Object.keys(markers).forEach(type => {
        const marker = markers[type];
        let isDragging = false;

        marker.addEventListener('mousedown', (e) => {
            isDragging = true;
            e.preventDefault();
        });

        document.addEventListener('mousemove', (e) => {
            if (!isDragging) return;

            const rect = picker.getBoundingClientRect();
            let x = e.clientX - rect.left;
            let y = e.clientY - rect.top;

            // Constrain to image bounds
            x = Math.max(0, Math.min(x, rect.width));
            y = Math.max(0, Math.min(y, rect.height));

            marker.style.left = x + 'px';
            marker.style.top = y + 'px';

            // Update input fields
            inputs[type].x.value = Math.round(x);
            inputs[type].y.value = Math.round(y);
        });

        document.addEventListener('mouseup', () => {
            isDragging = false;
        });
    });

    // Update markers when input fields change
    Object.keys(inputs).forEach(type => {
        inputs[type].x.addEventListener('input', () => {
            markers[type].style.left = inputs[type].x.value + 'px';
        });

        inputs[type].y.addEventListener('input', () => {
            markers[type].style.top = inputs[type].y.value + 'px';
        });
    });
</script>

<?php require_once '../includes/footer.php'; ?>