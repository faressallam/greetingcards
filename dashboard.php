<?php
$page_title = 'لوحة التحكم';
require_once 'includes/header.php';

require_login();

// Get user's saved cards
$stmt = $db->prepare("SELECT sc.*, t.title as template_title, t.image_path 
                      FROM saved_cards sc 
                      LEFT JOIN templates t ON sc.template_id = t.id 
                      WHERE sc.user_id = :user_id 
                      ORDER BY sc.created_at DESC");
$stmt->execute([':user_id' => $_SESSION['user_id']]);
$saved_cards = $stmt->fetchAll();

// Get user info
$stmt = $db->prepare("SELECT * FROM users WHERE id = :id");
$stmt->execute([':id' => $_SESSION['user_id']]);
$user = $stmt->fetch();
?>

<div class="container" style="padding: 3rem 0;">
    <!-- Welcome Section -->
    <div
        style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 3rem; border-radius: 12px; margin-bottom: 3rem;">
        <h1 style="font-size: 2.5rem; margin-bottom: 0.5rem;">مرحباً،
            <?php echo htmlspecialchars($user['username']); ?>! 👋</h1>
        <p style="font-size: 1.125rem; opacity: 0.9;">هنا يمكنك إدارة تصاميمك المحفوظة</p>
    </div>

    <!-- Quick Actions -->
    <div
        style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 2rem; margin-bottom: 3rem;">
        <a href="<?php echo SITE_URL; ?>/templates.php"
            style="background: white; padding: 2rem; border-radius: 12px; text-decoration: none; color: inherit; box-shadow: 0 2px 8px rgba(0,0,0,0.1); transition: transform 0.3s;"
            onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform='translateY(0)'">
            <div style="font-size: 3rem; margin-bottom: 1rem;">🎨</div>
            <h3 style="margin-bottom: 0.5rem;">تصفح القوالب</h3>
            <p style="color: #64748b;">اختر من مئات القوالب الجاهزة</p>
        </a>

        <a href="<?php echo SITE_URL; ?>/editor.php"
            style="background: white; padding: 2rem; border-radius: 12px; text-decoration: none; color: inherit; box-shadow: 0 2px 8px rgba(0,0,0,0.1); transition: transform 0.3s;"
            onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform='translateY(0)'">
            <div style="font-size: 3rem; margin-bottom: 1rem;">✏️</div>
            <h3 style="margin-bottom: 0.5rem;">محرر جديد</h3>
            <p style="color: #64748b;">ابدأ تصميم من الصفر</p>
        </a>

        <a href="<?php echo SITE_URL; ?>/greetings.php"
            style="background: white; padding: 2rem; border-radius: 12px; text-decoration: none; color: inherit; box-shadow: 0 2px 8px rgba(0,0,0,0.1); transition: transform 0.3s;"
            onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform='translateY(0)'">
            <div style="font-size: 3rem; margin-bottom: 1rem;">📝</div>
            <h3 style="margin-bottom: 0.5rem;">رسائل نصية</h3>
            <p style="color: #64748b;">رسائل جاهزة للنسخ</p>
        </a>
    </div>

    <!-- Saved Designs -->
    <div style="background: white; padding: 2rem; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
        <h2 style="margin-bottom: 2rem;">تصاميمي المحفوظة (<?php echo count($saved_cards); ?>)</h2>

        <?php if (!empty($saved_cards)): ?>
            <div class="templates-grid">
                <?php foreach ($saved_cards as $card): ?>
                    <div class="template-card">
                        <?php if ($card['preview_image']): ?>
                            <img src="<?php echo $card['preview_image']; ?>" alt="تصميم محفوظ" class="template-image">
                        <?php else: ?>
                            <div class="template-image"
                                style="background: #f1f5f9; display: flex; align-items: center; justify-content: center;">
                                <span style="font-size: 3rem;">🎨</span>
                            </div>
                        <?php endif; ?>
                        <div class="template-info">
                            <h3 class="template-title"><?php echo htmlspecialchars($card['template_title'] ?? 'تصميم مخصص'); ?>
                            </h3>
                            <p class="template-category" style="font-size: 0.875rem; color: #64748b;">
                                تم الحفظ: <?php echo format_date($card['created_at']); ?>
                            </p>
                            <div style="display: flex; gap: 0.5rem; margin-top: 1rem;">
                                <a href="<?php echo SITE_URL; ?>/editor.php?load=<?php echo $card['id']; ?>"
                                    class="btn btn-primary"
                                    style="flex: 1; padding: 0.5rem; font-size: 0.875rem; text-align: center;">
                                    تحرير
                                </a>
                                <a href="?delete=<?php echo $card['id']; ?>" class="btn btn-danger"
                                    style="flex: 1; padding: 0.5rem; font-size: 0.875rem; text-align: center;"
                                    onclick="return confirm('هل أنت متأكد من حذف هذا التصميم؟')">
                                    حذف
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div style="text-align: center; padding: 4rem; color: #64748b;">
                <div style="font-size: 4rem; margin-bottom: 1rem;">📭</div>
                <h3 style="margin-bottom: 1rem;">لا توجد تصاميم محفوظة بعد</h3>
                <p style="margin-bottom: 2rem;">ابدأ بإنشاء تصميمك الأول!</p>
                <a href="<?php echo SITE_URL; ?>/templates.php" class="btn btn-primary">
                    تصفح القوالب
                </a>
            </div>
        <?php endif; ?>
    </div>

    <!-- Account Info -->
    <div
        style="background: white; padding: 2rem; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); margin-top: 2rem;">
        <h2 style="margin-bottom: 1.5rem;">معلومات الحساب</h2>
        <div style="display: grid; gap: 1rem;">
            <div>
                <strong>اسم المستخدم:</strong> <?php echo htmlspecialchars($user['username']); ?>
            </div>
            <div>
                <strong>البريد الإلكتروني:</strong> <?php echo htmlspecialchars($user['email']); ?>
            </div>
            <div>
                <strong>نوع الحساب:</strong>
                <?php if ($user['role'] === 'admin'): ?>
                    <span
                        style="background: #ef4444; color: white; padding: 0.25rem 0.75rem; border-radius: 20px; font-size: 0.875rem;">مدير</span>
                <?php else: ?>
                    <span
                        style="background: #10b981; color: white; padding: 0.25rem 0.75rem; border-radius: 20px; font-size: 0.875rem;">مستخدم</span>
                <?php endif; ?>
            </div>
            <div>
                <strong>تاريخ التسجيل:</strong> <?php echo format_date($user['created_at']); ?>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>