<?php
$page_title = 'محرر الكروت';
require_once 'includes/header.php';

// Get template ID
$template_id = isset($_GET['template']) ? (int) $_GET['template'] : 0;

// Fetch template data
$template = null;
if ($template_id) {
    $stmt = $db->prepare("SELECT t.*, c.name_ar as category_name 
                          FROM templates t 
                          JOIN categories c ON t.category_id = c.id 
                          WHERE t.id = :id AND t.is_active = 1");
    $stmt->execute([':id' => $template_id]);
    $template = $stmt->fetch();

    if ($template) {
        // Increment views
        $db->prepare("UPDATE templates SET views = views + 1 WHERE id = :id")->execute([':id' => $template_id]);
    }
}

$extra_css = ['editor.css'];
$extra_js = ['fabric.min.js', 'editor.js'];
?>

<div class="editor-container">
    <div class="editor-sidebar">
        <h2 class="sidebar-title">
            <span style="font-size: 1.5rem;">✨</span>
            التعديل السريع
        </h2>

        <div class="form-group">
            <label class="form-label">اكتب اسمك أو رسالتك</label>
            <input type="text" id="easy-mode-text" class="form-control" placeholder="مثال: أحمد محمد">
        </div>

        <hr style="margin: 2rem 0; border: none; border-top: 1px solid var(--border);">

        <h3 class="sidebar-title">
            <span style="font-size: 1.25rem;">🎨</span>
            أدوات التصميم
        </h3>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; margin-bottom: 1.5rem;">
            <button id="add-text-btn" class="btn btn-secondary">
                <span>➕</span> إضافة نص
            </button>
            <label class="btn btn-secondary" style="cursor: pointer; margin: 0;">
                <span>🖼️</span> صورة
                <input type="file" id="upload-image" accept="image/*" style="display: none;">
            </label>
        </div>

        <div class="form-group">
            <label class="form-label">لون النص</label>
            <input type="color" id="text-color" class="form-control" value="#000000">
        </div>

        <div class="form-group">
            <label class="form-label">نوع الخط</label>
            <select id="font-family" class="form-control">
                <option value="Cairo">Cairo (أساسي)</option>
                <option value="Tajawal">Tajawal (عصري)</option>
                <option value="Amiri">Amiri (كلاسيكي)</option>
                <option value="Arial">Arial</option>
            </select>
        </div>

        <div class="form-group">
            <label class="form-label">حجم الخط</label>
            <input type="range" id="font-size" min="20" max="100" value="40" class="form-control">
            <span id="font-size-value">40</span>
        </div>

        <button id="download-btn" class="btn btn-primary"
            style="width: 100%; padding: 1rem; font-size: 1.125rem; margin-top: 2rem;">
            <span>⬇️</span> تحميل التصميم
        </button>

        <?php if (is_logged_in()): ?>
            <button id="save-btn" class="btn btn-success" style="width: 100%; padding: 1rem; margin-top: 1rem;">
                <span>💾</span> حفظ التصميم
            </button>
        <?php endif; ?>
    </div>

    <div class="editor-canvas-area">
        <?php if ($template): ?>
            <div class="template-info-bar">
                <h3><?php echo htmlspecialchars($template['title']); ?></h3>
                <span class="template-category-badge"><?php echo htmlspecialchars($template['category_name']); ?></span>
            </div>
        <?php endif; ?>

        <div class="canvas-wrapper">
            <div id="loading-spinner" class="loading-spinner">
                <div class="spinner"></div>
                <p>جاري تحميل المحرر...</p>
            </div>
            <canvas id="canvas"></canvas>
        </div>
    </div>
</div>

<script>
    // Pass template data to JavaScript
    const TEMPLATE_DATA = <?php echo json_encode([
        'id' => $template['id'] ?? null,
        'image_path' => $template ? SITE_URL . '/uploads/templates/' . $template['image_path'] : null,
        'width' => $template['width'] ?? 800,
        'height' => $template['height'] ?? 600
    ]); ?>;

    const SITE_URL = '<?php echo SITE_URL; ?>';
    const IS_LOGGED_IN = <?php echo is_logged_in() ? 'true' : 'false'; ?>;
</script>

<?php require_once 'includes/footer.php'; ?>