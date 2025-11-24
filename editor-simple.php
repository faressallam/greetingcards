<?php
$page_title = 'المحرر البسيط';
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
        $db->prepare("UPDATE templates SET views = views + 1 WHERE id = :id")->execute([':id' => $template_id]);
    }
}

if (!$template) {
    redirect(SITE_URL . '/templates.php');
}

// Fetch emojis from database organized by category
$emoji_categories = [];
$emojis_by_category = [];
try {
    $emoji_categories = $db->query("SELECT * FROM emoji_categories WHERE is_active = 1 ORDER BY display_order ASC")->fetchAll();
    foreach ($emoji_categories as $category) {
        $stmt = $db->prepare("SELECT * FROM emojis WHERE category_id = :cat_id AND is_active = 1 ORDER BY display_order ASC");
        $stmt->execute([':cat_id' => $category['id']]);
        $emojis_by_category[$category['id']] = [
            'name' => $category['name'],
            'emojis' => $stmt->fetchAll()
        ];
    }
} catch (PDOException $e) {
    $emoji_categories = [];
    $emojis_by_category = [];
}

// Fonts list
$fonts = [
    'Cairo' => 'القاهرة',
    'Tajawal' => 'تجوال',
    'Almarai' => 'المراعي',
    'El Messiri' => 'المسيري',
    'Changa' => 'تشانجا',
    'Lalezar' => 'لاليزار',
    'Reem Kufi' => 'ريم كوفي',
    'Amiri' => 'أميري',
    'Lateef' => 'لطيف',
    'Scheherazade New' => 'شهرزاد'
];

// Colors list
$colors = [
    '#000000' => 'أسود',
    '#ffffff' => 'أبيض',
    '#1f2937' => 'رمادي داكن',
    '#64748b' => 'رمادي',
    '#f1f5f9' => 'رمادي فاتح',
    '#dc2626' => 'أحمر',
    '#ef4444' => 'أحمر فاتح',
    '#7f1d1d' => 'أحمر داكن',
    '#2563eb' => 'أزرق',
    '#3b82f6' => 'أزرق فاتح',
    '#1e3a8a' => 'أزرق داكن',
    '#16a34a' => 'أخضر',
    '#22c55e' => 'أخضر فاتح',
    '#14532d' => 'أخضر داكن',
    '#d97706' => 'ذهبي',
    '#fbbf24' => 'أصفر',
    '#7c3aed' => 'بنفسجي',
    '#a855f7' => 'بنفسجي فاتح',
    '#db2777' => 'وردي',
    '#ec4899' => 'وردي فاتح'
];

// Gradients
$gradients = [
    'linear-gradient(to right, #fcd34d, #d97706)' => 'ذهبي لامع',
    'linear-gradient(to right, #f59e0b, #b45309)' => 'ذهبي داكن',
    'linear-gradient(to right, #667eea, #764ba2)' => 'بنفسجي أزرق',
    'linear-gradient(to right, #a18cd1, #fbc2eb)' => 'حلم بنفسجي',
    'linear-gradient(to right, #ff9a9e, #fecfef)' => 'وردي ناعم',
    'linear-gradient(to right, #ff9a9e, #fad0c4)' => 'خوخي',
    'linear-gradient(to right, #fbc2eb, #a6c1ee)' => 'سماء هادئة',
    'linear-gradient(to right, #84fab0, #8fd3f4)' => 'أزرق محيطي',
    'linear-gradient(to right, #a1c4fd, #c2e9fb)' => 'غيوم زرقاء',
    'linear-gradient(to right, #ffecd2, #fcb69f)' => 'غروب الشمس',
    'linear-gradient(to right, #fa709a, #fee140)' => 'غروب استوائي',
    'linear-gradient(to right, #30cfd0, #330867)' => 'ليل صيفي',
    'linear-gradient(to right, #a8edea, #fed6e3)' => 'حلوى ناعمة',
    'linear-gradient(to right, #ff6e7f, #bfe9ff)' => 'شفق',
    'linear-gradient(to right, #e0c3fc, #8ec5fc)' => 'سحاب بنفسجي',
    'linear-gradient(to right, #f093fb, #f5576c)' => 'نار وردية',
    'linear-gradient(to right, #4facfe, #00f2fe)' => 'جليد أزرق',
    'linear-gradient(to right, #43e97b, #38f9d7)' => 'نعناع',
    'linear-gradient(to right, #fa8bff, #2bd2ff, #2bff88)' => 'قوس قزح',
    'linear-gradient(to right, #ff0844, #ffb199)' => 'شروق'
];
?>

<link
    href="https://fonts.googleapis.com/css2?family=Almarai:wght@400;700&family=Amiri:wght@400;700&family=Cairo:wght@400;700&family=Changa:wght@400;700&family=El+Messiri:wght@400;700&family=Lalezar&family=Lateef&family=Reem+Kufi:wght@400;700&family=Scheherazade+New:wght@400;700&family=Tajawal:wght@400;700&display=swap"
    rel="stylesheet">

<style>
    .simple-editor-container {
        max-width: 1400px;
        margin: 0.5rem auto;
        padding: 0 0.5rem;
    }

    .editor-card {
        background: white;
        border-radius: 16px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        overflow: hidden;
        padding: 1rem;
    }

    .editor-header {
        margin-bottom: 0.75rem;
        text-align: center;
    }

    .editor-header h1 {
        font-size: 1.5rem;
        margin-bottom: 0.25rem;
    }

    .editor-body {
        display: grid;
        grid-template-columns: 1fr 400px;
        gap: 0.75rem;
        align-items: start;
    }

    .canvas-preview {
        background: #f8fafc;
        border-radius: 12px;
        padding: 0.5rem;
        display: block;
        overflow: hidden;
        max-width: 100%;
        line-height: 0;
    }

    .canvas-preview canvas {
        max-width: 100% !important;
        width: 100% !important;
        height: auto !important;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        display: block;
        margin: 0;
    }

    .controls-section {
        background: #f8fafc;
        padding: 1rem;
        border-radius: 12px;
        margin-bottom: 0.75rem;
    }

    .control-label {
        font-weight: 600;
        margin-bottom: 0.5rem;
        display: block;
        color: #1e293b;
        font-size: 1rem;
    }

    .form-control {
        width: 100%;
        padding: 0.75rem;
        border: 2px solid #e2e8f0;
        border-radius: 8px;
        font-family: 'Cairo', sans-serif;
        font-size: 1rem;
        transition: border-color 0.3s;
    }

    .form-control:focus {
        outline: none;
        border-color: #667eea;
    }

    .emoji-category-tabs {
        display: flex;
        gap: 0.5rem;
        margin-bottom: 0.75rem;
        flex-wrap: wrap;
    }

    .emoji-category-tab {
        padding: 0.4rem 0.8rem;
        border: 1px solid #e2e8f0;
        border-radius: 6px;
        background: white;
        cursor: pointer;
        font-size: 0.8rem;
        transition: all 0.2s;
    }

    .emoji-category-tab:hover {
        border-color: #667eea;
        background: #f0f4ff;
    }

    .emoji-category-tab.active {
        border-color: #667eea;
        background: #667eea;
        color: white;
    }

    .emoji-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(35px, 1fr));
        gap: 0.4rem;
        max-height: 150px;
        overflow-y: auto;
        padding: 0.5rem;
        background: white;
        border-radius: 8px;
        border: 1px solid #e2e8f0;
    }

    .emoji-grid.hidden {
        display: none;
    }

    .emoji-btn {
        padding: 0.4rem;
        font-size: 1.1rem;
        border: 1px solid #e2e8f0;
        border-radius: 6px;
        background: white;
        cursor: pointer;
        transition: all 0.2s;
    }

    .emoji-btn:hover {
        transform: scale(1.1);
        border-color: #667eea;
    }

    .emoji-btn.selected {
        border-color: #667eea;
        background: #f0f4ff;
    }

    .emoji-btn img {
        width: 100%;
        height: auto;
        display: block;
    }

    .color-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(28px, 1fr));
        gap: 0.4rem;
        margin-bottom: 0.75rem;
    }

    .color-btn {
        width: 28px;
        height: 28px;
        border-radius: 50%;
        border: 2px solid #e2e8f0;
        cursor: pointer;
        transition: transform 0.2s;
    }

    .color-btn:hover {
        transform: scale(1.1);
    }

    .color-btn.selected {
        border-color: #667eea;
        box-shadow: 0 0 0 2px white, 0 0 0 4px #667eea;
    }

    .photo-upload {
        border: 2px dashed #cbd5e1;
        border-radius: 12px;
        padding: 1.5rem;
        text-align: center;
        cursor: pointer;
        transition: all 0.3s;
        background: white;
    }

    .photo-upload:hover {
        border-color: #667eea;
        background: #f0f4ff;
    }

    .photo-preview {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        object-fit: cover;
        margin: 0.5rem auto;
        display: none;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }

    .photo-preview.show {
        display: block;
    }

    .checkbox-wrapper {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        margin-top: 0.75rem;
        cursor: pointer;
    }

    .share-buttons {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
        gap: 0.75rem;
        margin-top: 1rem;
        grid-column: 1 / -1;
    }

    .share-btn {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        padding: 0.75rem 1rem;
        border-radius: 10px;
        border: none;
        cursor: pointer;
        font-family: 'Cairo', sans-serif;
        font-weight: 600;
        font-size: 0.9rem;
        transition: all 0.3s;
        text-decoration: none;
        color: white;
    }

    .share-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
    }

    .share-btn.facebook {
        background: #1877f2;
    }

    .share-btn.twitter {
        background: #1da1f2;
    }

    .share-btn.whatsapp {
        background: #25d366;
    }

    .share-btn.telegram {
        background: #0088cc;
    }

    .share-btn.instagram {
        background: linear-gradient(45deg, #f09433 0%, #e6683c 25%, #dc2743 50%, #cc2366 75%, #bc1888 100%);
    }

    .share-btn.download {
        background: #64748b;
        grid-column: 1 / -1;
        font-size: 0.85rem;
        padding: 0.6rem;
        opacity: 0.7;
    }

    .share-btn.download:hover {
        opacity: 1;
    }

    /* Mobile Responsive */
    @media (max-width: 992px) {
        .simple-editor-container {
            padding: 0 0.25rem;
            margin: 0.25rem auto;
        }

        .editor-card {
            padding: 0.75rem;
        }

        .editor-header {
            margin-bottom: 0.5rem;
        }

        .editor-header h1 {
            font-size: 1.25rem;
        }

        .editor-body {
            grid-template-columns: 1fr;
            gap: 0.5rem;
        }

        .canvas-preview {
            order: -1;
            padding: 0.25rem;
        }

        .controls-section {
            padding: 0.75rem;
            margin-bottom: 0.5rem;
        }

        .share-buttons {
            grid-template-columns: 1fr;
            gap: 0.6rem;
            margin-top: 0.75rem;
        }
    }

    @media (max-width: 480px) {
        .simple-editor-container {
            padding: 0 0.2rem;
            margin: 0.2rem auto;
        }

        .editor-card {
            padding: 0.5rem;
        }

        .canvas-preview {
            padding: 0.15rem;
        }

        .controls-section {
            padding: 0.6rem;
            margin-bottom: 0.4rem;
        }

        .color-btn {
            width: 26px;
            height: 26px;
        }
    }
</style>

<div class="simple-editor-container">
    <div class="editor-card">
        <div class="editor-header">
            <h1>✨ صمم كرتك بسهولة</h1>
            <p style="opacity: 0.9;"><?php echo htmlspecialchars($template['title']); ?></p>
        </div>

        <div class="editor-body">
            <div class="canvas-preview">
                <canvas id="canvas"></canvas>
            </div>

            <div>
                <!-- Name Input -->
                <div class="controls-section">
                    <label class="control-label">✍️ اكتب اسمك</label>
                    <input type="text" id="name-input" class="form-control" placeholder="اكتب اسمك هنا" maxlength="50">

                    <div style="margin-top: 1rem;">
                        <label style="font-size: 0.9rem; margin-bottom: 0.5rem; display: block;">نوع الخط</label>
                        <select id="font-select" class="form-control">
                            <?php foreach ($fonts as $key => $name): ?>
                                <option value="<?php echo $key; ?>" style="font-family: '<?php echo $key; ?>'">
                                    <?php echo $name; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div style="margin-top: 1rem;">
                        <label style="font-size: 0.9rem; margin-bottom: 0.5rem; display: block;">لون النص</label>
                        <div class="color-grid" id="solid-colors">
                            <?php foreach ($colors as $hex => $name): ?>
                                <div class="color-btn" style="background-color: <?php echo $hex; ?>"
                                    data-color="<?php echo $hex; ?>" title="<?php echo $name; ?>"></div>
                            <?php endforeach; ?>
                        </div>
                        <label style="font-size: 0.9rem; margin-bottom: 0.5rem; display: block;">تدرجات لونية</label>
                        <div class="color-grid" id="gradient-colors">
                            <?php foreach ($gradients as $css => $name): ?>
                                <div class="color-btn" style="background: <?php echo $css; ?>"
                                    data-gradient="<?php echo $css; ?>" title="<?php echo $name; ?>"></div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <label class="checkbox-wrapper">
                        <input type="checkbox" id="shadow-check" checked>
                        <span>إضافة ظل للنص</span>
                    </label>
                </div>

                <!-- Decorations -->
                <div class="controls-section">
                    <label class="control-label">🎨 إضافات وتزيين</label>

                    <div style="margin-bottom: 1.5rem;">
                        <label style="font-size: 0.9rem; margin-bottom: 0.5rem; display: block;">اختر إيموجي</label>
                        <?php if (!empty($emojis_by_category)): ?>
                            <div class="emoji-category-tabs">
                                <?php $first = true;
                                foreach ($emojis_by_category as $cat_id => $cat_data): ?>
                                    <button class="emoji-category-tab <?php echo $first ? 'active' : ''; ?>"
                                        data-category="<?php echo $cat_id; ?>">
                                        <?php echo htmlspecialchars($cat_data['name']); ?>
                                    </button>
                                    <?php $first = false; ?>
                                <?php endforeach; ?>
                            </div>
                            <?php $first = true;
                            foreach ($emojis_by_category as $cat_id => $cat_data): ?>
                                <div class="emoji-grid <?php echo !$first ? 'hidden' : ''; ?>"
                                    data-category-grid="<?php echo $cat_id; ?>">
                                    <?php foreach ($cat_data['emojis'] as $emoji): ?>
                                        <button class="emoji-btn"
                                            data-emoji-url="<?php echo SITE_URL . '/uploads/emojis/' . $emoji['file_path']; ?>">
                                            <img src="<?php echo SITE_URL . '/uploads/emojis/' . $emoji['file_path']; ?>"
                                                alt="Emoji">
                                        </button>
                                    <?php endforeach; ?>
                                </div>
                                <?php $first = false; ?>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p
                                style="color: #64748b; font-size: 0.875rem; padding: 1rem; text-align: center; background: #f8fafc; border-radius: 8px;">
                                لا توجد إيموجي متاحة حالياً.
                            </p>
                        <?php endif; ?>
                    </div>

                    <div>
                        <label style="font-size: 0.9rem; margin-bottom: 0.5rem; display: block;">صورة شخصية</label>
                        <div class="photo-upload" onclick="document.getElementById('photo-input').click()">
                            <img id="photo-preview" class="photo-preview" alt="معاينة الصورة">
                            <div id="upload-text">
                                <div style="font-size: 2rem; margin-bottom: 0.5rem;">📸</div>
                                <p style="color: #64748b; font-size: 0.9rem;">اضغط لرفع صورتك</p>
                            </div>
                            <input type="file" id="photo-input" accept="image/*" style="display: none;">
                        </div>

                        <div style="margin-top: 1rem; border-top: 1px solid #e2e8f0; padding-top: 1rem;">
                            <label style="font-size: 0.9rem; margin-bottom: 0.5rem; display: block;">حدود الصورة</label>
                            <div style="display: flex; gap: 0.5rem; align-items: center;">
                                <input type="range" id="border-width" min="0" max="20" value="2" style="flex: 1;">
                                <span id="border-val">2px</span>
                            </div>
                            <div style="margin-top: 0.5rem;">
                                <input type="color" id="border-color" value="#000000"
                                    style="width: 100%; height: 30px; border: none; cursor: pointer; border-radius: 6px;">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Share Buttons -->
            <div class="share-buttons">
                <button class="share-btn facebook" onclick="shareToFacebook()">📘 فيسبوك</button>
                <button class="share-btn twitter" onclick="shareToTwitter()">🐦 تويتر</button>
                <button class="share-btn whatsapp" onclick="shareToWhatsApp()">💬 واتساب</button>
                <button class="share-btn telegram" onclick="shareToTelegram()">✈️ تليجرام</button>
                <button class="share-btn instagram" onclick="shareToInstagram()">📷 إنستجرام</button>
                <button id="download-btn" class="share-btn download">⬇️ تحميل الصورة</button>
            </div>
        </div>
    </div>
</div>

<script>
    const TEMPLATE_DATA = <?php echo json_encode([
        'id' => $template['id'],
        'image_path' => SITE_URL . '/uploads/templates/' . $template['image_path'],
        'name_x' => $template['name_x'] ?? 400,
        'name_y' => $template['name_y'] ?? 300,
        'name_size' => $template['name_size'] ?? 40,
        'name_color' => $template['name_color'] ?? '#000000',
        'name_font' => $template['name_font'] ?? 'Cairo',
        'emoji_x' => $template['emoji_x'] ?? 200,
        'emoji_y' => $template['emoji_y'] ?? 150,
        'emoji_size' => $template['emoji_size'] ?? 60,
        'photo_x' => $template['photo_x'] ?? 600,
        'photo_y' => $template['photo_y'] ?? 150,
        'photo_size' => $template['photo_size'] ?? 100
    ]); ?>;

    const SITE_URL = '<?php echo SITE_URL; ?>';
    let canvas, nameText = null, emojiImage = null, photoGroup = null;
    let currentSettings = {
        text: '', font: TEMPLATE_DATA.name_font, color: TEMPLATE_DATA.name_color,
        gradient: null, shadow: true, emojiUrl: '', photo: null, borderWidth: 2, borderColor: '#000000'
    };
    let canvasScale = 1; // النسبة اللي صغرنا بيها الصورة
    const deleteIcon = "data:image/svg+xml,%3C%3Fxml version='1.0' encoding='utf-8'%3F%3E%3C!DOCTYPE svg PUBLIC '-//W3C//DTD SVG 1.1//EN' 'http://www.w3.org/Graphics/SVG/1.1/DTD/svg11.dtd'%3E%3Csvg version='1.1' id='Ebene_1' xmlns='http://www.w3.org/2000/svg' xmlns:xlink='http://www.w3.org/1999/xlink' x='0px' y='0px' width='595.275px' height='595.275px' viewBox='200 215 230 470' xml:space='preserve'%3E%3Ccircle style='fill:%23F44336;' cx='299.76' cy='435.375' r='217.169'/%3E%3Cg%3E%3Crect x='267.162' y='307.978' transform='matrix(0.7071 -0.7071 0.7071 0.7071 -222.6202 340.6915)' style='fill:white;' width='65.545' height='262.18'/%3E%3Crect x='266.988' y='308.153' transform='matrix(0.7071 0.7071 -0.7071 0.7071 398.3889 -83.3116)' style='fill:white;' width='65.544' height='262.179'/%3E%3C/g%3E%3C/svg%3E";
    const deleteImg = document.createElement('img');
    deleteImg.src = deleteIcon;

    document.addEventListener('DOMContentLoaded', initializeCanvas);

    function initializeCanvas() {
        const tempImg = new Image();
        tempImg.crossOrigin = 'anonymous';
        tempImg.onload = function () {
            const containerWidth = document.querySelector('.canvas-preview').offsetWidth - 20;
            const maxWidth = Math.min(containerWidth, 800);

            let canvasWidth = tempImg.width;
            let canvasHeight = tempImg.height;

            // حساب scale factor
            canvasScale = 1; // reset
            if (canvasWidth > maxWidth) {
                canvasScale = maxWidth / canvasWidth;
                canvasWidth = maxWidth;
                canvasHeight = tempImg.height * canvasScale;
            }

            canvas = new fabric.Canvas('canvas', {
                width: canvasWidth,
                height: canvasHeight,
                backgroundColor: '#ffffff',
                selection: true
            });

            fabric.Image.fromURL(TEMPLATE_DATA.image_path, img => {
                img.set({
                    left: 0,
                    top: 0,
                    scaleX: canvasScale,
                    scaleY: canvasScale,
                    selectable: false,
                    evented: false
                });
                canvas.add(img);
                canvas.sendToBack(img);
                canvas.renderAll();
            }, { crossOrigin: 'anonymous' });

            fabric.Object.prototype.set({
                transparentCorners: false,
                cornerColor: '#667eea',
                cornerStrokeColor: '#ffffff',
                borderColor: '#667eea',
                cornerSize: 15,
                padding: 0,
                cornerStyle: 'circle'
            });

            fabric.Object.prototype.controls.deleteControl = new fabric.Control({
                x: 0.5, y: -0.5,
                offsetY: -16,
                offsetX: 16,
                cursorStyle: 'pointer',
                mouseUpHandler: deleteObject,
                render: renderIcon,
                cornerSize: 24
            });

            setupEventListeners();
            document.getElementById('font-select').value = TEMPLATE_DATA.name_font;
            document.getElementById('shadow-check').checked = true;
        };
        tempImg.onerror = () => alert('حدث خطأ في تحميل صورة القالب.');
        tempImg.src = TEMPLATE_DATA.image_path;
    }

    function deleteObject(eventData, transform) {
        const target = transform.target;
        if (target === nameText) {
            nameText = null;
            document.getElementById('name-input').value = '';
        } else if (target === emojiImage) {
            emojiImage = null;
            document.querySelectorAll('.emoji-btn').forEach(b => b.classList.remove('selected'));
        } else if (target === photoGroup) {
            photoGroup = null;
            document.getElementById('photo-preview').classList.remove('show');
            document.getElementById('upload-text').style.display = 'block';
            document.getElementById('photo-input').value = '';
        }
        target.canvas.remove(target);
        target.canvas.requestRenderAll();
    }

    function renderIcon(ctx, left, top, styleOverride, fabricObject) {
        const size = this.cornerSize;
        ctx.save();
        ctx.translate(left, top);
        ctx.rotate(fabric.util.degreesToRadians(fabricObject.angle));
        ctx.drawImage(deleteImg, -size / 2, -size / 2, size, size);
        ctx.restore();
    }

    function loadTemplateImage() {
        fabric.Image.fromURL(TEMPLATE_DATA.image_path, img => {
            img.set({ left: 0, top: 0, selectable: false, evented: false });
            canvas.add(img);
            canvas.sendToBack(img);
            canvas.renderAll();
        }, { crossOrigin: 'anonymous' });
    }

    function setupEventListeners() {
        document.getElementById('name-input').addEventListener('input', e => {
            currentSettings.text = e.target.value;
            updateName();
        });
        document.getElementById('font-select').addEventListener('change', e => {
            currentSettings.font = e.target.value;
            updateName();
        });
        document.querySelectorAll('.color-btn').forEach(btn => {
            btn.addEventListener('click', function () {
                document.querySelectorAll('.color-btn').forEach(b => b.classList.remove('selected'));
                this.classList.add('selected');
                if (this.dataset.color) {
                    currentSettings.color = this.dataset.color;
                    currentSettings.gradient = null;
                } else if (this.dataset.gradient) {
                    currentSettings.gradient = this.dataset.gradient;
                }
                updateName();
            });
        });
        document.getElementById('shadow-check').addEventListener('change', e => {
            currentSettings.shadow = e.target.checked;
            updateName();
            updatePhoto();
        });
        document.querySelectorAll('.emoji-category-tab').forEach(tab => {
            tab.addEventListener('click', function () {
                const categoryId = this.dataset.category;
                document.querySelectorAll('.emoji-category-tab').forEach(t => t.classList.remove('active'));
                this.classList.add('active');
                document.querySelectorAll('.emoji-grid').forEach(grid => grid.classList.add('hidden'));
                document.querySelector(`[data-category-grid="${categoryId}"]`).classList.remove('hidden');
            });
        });
        document.querySelectorAll('.emoji-btn').forEach(btn => {
            btn.addEventListener('click', function () {
                document.querySelectorAll('.emoji-btn').forEach(b => b.classList.remove('selected'));
                this.classList.add('selected');
                currentSettings.emojiUrl = this.dataset.emojiUrl;
                updateEmoji();
            });
        });
        document.getElementById('photo-input').addEventListener('change', e => {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = event => {
                    currentSettings.photo = event.target.result;
                    const preview = document.getElementById('photo-preview');
                    preview.src = currentSettings.photo;
                    preview.classList.add('show');
                    document.getElementById('upload-text').style.display = 'none';
                    updatePhoto();
                };
                reader.readAsDataURL(file);
            }
        });
        document.getElementById('border-width').addEventListener('input', e => {
            currentSettings.borderWidth = parseInt(e.target.value);
            document.getElementById('border-val').innerText = currentSettings.borderWidth + 'px';
            updatePhoto();
        });
        document.getElementById('border-color').addEventListener('input', e => {
            currentSettings.borderColor = e.target.value;
            updatePhoto();
        });
        document.getElementById('download-btn').addEventListener('click', downloadCanvas);
    }

    function updateName() {
        if (currentSettings.text.trim()) {
            if (!nameText) {
                nameText = new fabric.Text(currentSettings.text, {
                    left: TEMPLATE_DATA.name_x * canvasScale, // ضرب في scale
                    top: TEMPLATE_DATA.name_y * canvasScale,  // ضرب في scale
                    fontSize: TEMPLATE_DATA.name_size * canvasScale, // ضرب في scale
                    fontFamily: currentSettings.font,
                    fontWeight: 'bold',
                    fill: currentSettings.color,
                    originX: 'center',
                    originY: 'center',
                    selectable: true
                });
                canvas.add(nameText);
            } else {
                nameText.set({
                    text: currentSettings.text,
                    fontFamily: currentSettings.font,
                    fontSize: TEMPLATE_DATA.name_size * canvasScale
                });
            }

            if (currentSettings.gradient) {
                const matches = currentSettings.gradient.match(/#[a-fA-F0-9]{6}/g);
                if (matches && matches.length >= 2) {
                    const gradient = new fabric.Gradient({
                        type: 'linear',
                        gradientUnits: 'percentage',
                        coords: { x1: 0, y1: 0, x2: 1, y2: 0 },
                        colorStops: [
                            { offset: 0, color: matches[0] },
                            { offset: 1, color: matches[1] }
                        ]
                    });
                    nameText.set('fill', gradient);
                }
            } else {
                nameText.set('fill', currentSettings.color);
            }

            if (currentSettings.shadow) {
                nameText.set('shadow', new fabric.Shadow({
                    color: 'rgba(0,0,0,0.3)',
                    blur: 5 * canvasScale,
                    offsetX: 2 * canvasScale,
                    offsetY: 2 * canvasScale
                }));
            } else {
                nameText.set('shadow', null);
            }

            canvas.setActiveObject(nameText);
            canvas.renderAll();
        } else if (nameText) {
            canvas.remove(nameText);
            nameText = null;
            canvas.renderAll();
        }
    }


    function updateEmoji() {
        if (currentSettings.emojiUrl) {
            let prevLeft = TEMPLATE_DATA.emoji_x * canvasScale;
            let prevTop = TEMPLATE_DATA.emoji_y * canvasScale;
            let prevScale = 1;

            if (emojiImage) {
                prevLeft = emojiImage.left;
                prevTop = emojiImage.top;
                prevScale = emojiImage.scaleX;
                canvas.remove(emojiImage);
            }

            fabric.Image.fromURL(currentSettings.emojiUrl, img => {
                let scale = prevScale;
                if (!emojiImage) {
                    scale = (TEMPLATE_DATA.emoji_size * canvasScale) / Math.max(img.width, img.height);
                }

                emojiImage = img;
                emojiImage.set({
                    left: prevLeft,
                    top: prevTop,
                    scaleX: scale,
                    scaleY: scale,
                    originX: 'center',
                    originY: 'center',
                    selectable: true
                });
                canvas.add(emojiImage);
                canvas.renderAll();
            }, { crossOrigin: 'anonymous' });
        } else if (emojiImage) {
            canvas.remove(emojiImage);
            emojiImage = null;
            canvas.renderAll();
        }
    }


    function updatePhoto() {
        if (currentSettings.photo) {
            let prevLeft = TEMPLATE_DATA.photo_x * canvasScale;
            let prevTop = TEMPLATE_DATA.photo_y * canvasScale;
            let prevScale = 1;

            if (photoGroup) {
                prevLeft = photoGroup.left;
                prevTop = photoGroup.top;
                prevScale = photoGroup.scaleX;
                canvas.remove(photoGroup);
            }

            fabric.Image.fromURL(currentSettings.photo, img => {
                let scale = 1;
                if (!photoGroup) {
                    scale = (TEMPLATE_DATA.photo_size * canvasScale) / Math.min(img.width, img.height);
                } else {
                    scale = prevScale;
                }

                const radius = Math.min(img.width, img.height) / 2;
                const clipPath = new fabric.Circle({
                    radius,
                    originX: 'center',
                    originY: 'center',
                    left: 0,
                    top: 0
                });
                img.set({ originX: 'center', originY: 'center', clipPath });

                const border = new fabric.Circle({
                    radius,
                    originX: 'center',
                    originY: 'center',
                    fill: 'transparent',
                    stroke: currentSettings.borderColor,
                    strokeWidth: currentSettings.borderWidth / scale,
                    left: 0,
                    top: 0
                });

                photoGroup = new fabric.Group([img, border], {
                    left: prevLeft,
                    top: prevTop,
                    scaleX: scale,
                    scaleY: scale,
                    originX: 'center',
                    originY: 'center',
                    selectable: true
                });

                if (currentSettings.shadow) {
                    photoGroup.set('shadow', new fabric.Shadow({
                        color: 'rgba(0,0,0,0.3)',
                        blur: 10 * canvasScale,
                        offsetX: 5 * canvasScale,
                        offsetY: 5 * canvasScale
                    }));
                } else {
                    photoGroup.set('shadow', null);
                }

                canvas.add(photoGroup);
                canvas.bringToFront(photoGroup);
                canvas.renderAll();
            });
        } else if (photoGroup) {
            canvas.remove(photoGroup);
            photoGroup = null;
            canvas.renderAll();
        }
    }

    function downloadCanvas() {
        canvas.discardActiveObject();
        canvas.renderAll();
        const dataURL = canvas.toDataURL({ format: 'png', quality: 1, multiplier: 2 });
        const link = document.createElement('a');
        link.download = 'greeting-card.png';
        link.href = dataURL;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }

    function shareToFacebook() {
        alert('لمشاركة الصورة على فيسبوك:\n1. حمّل الصورة أولاً\n2. ارفعها على فيسبوك من جهازك');
        downloadCanvas();
    }

    function shareToTwitter() {
        const text = encodeURIComponent('صممت كرت معايدة جميل! 🎉');
        const url = encodeURIComponent(window.location.href);
        window.open(`https://twitter.com/intent/tweet?text=${text}&url=${url}`, '_blank');
    }

    function shareToWhatsApp() {
        const text = encodeURIComponent('صممت كرت معايدة جميل! شاهده هنا: ' + window.location.href);
        window.open(`https://wa.me/?text=${text}`, '_blank');
    }

    function shareToTelegram() {
        const text = encodeURIComponent('صممت كرت معايدة جميل!');
        const url = encodeURIComponent(window.location.href);
        window.open(`https://t.me/share/url?url=${url}&text=${text}`, '_blank');
    }

    function shareToInstagram() {
        alert('لمشاركة الصورة على إنستجرام:\n1. حمّل الصورة أولاً\n2. افتح تطبيق إنستجرام\n3. ارفع الصورة من معرض الصور');
        downloadCanvas();
    }
</script>

<?php require_once 'includes/footer.php'; ?>