<?php
$page_title = 'إدارة المستخدمين';
require_once '../includes/header.php';

require_admin();

$success = '';
$error = '';

// Handle delete user
if (isset($_GET['delete'])) {
    $id = (int) $_GET['delete'];
    if ($id != $_SESSION['user_id']) { // Don't allow deleting yourself
        $db->prepare("DELETE FROM users WHERE id = :id")->execute([':id' => $id]);
        $success = 'تم حذف المستخدم بنجاح';
    } else {
        $error = 'لا يمكنك حذف حسابك الخاص!';
    }
}

// Handle role change
if (isset($_GET['toggle_admin'])) {
    $id = (int) $_GET['toggle_admin'];
    if ($id != $_SESSION['user_id']) {
        $stmt = $db->prepare("SELECT role FROM users WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $user = $stmt->fetch();

        $new_role = ($user['role'] === 'admin') ? 'user' : 'admin';
        $db->prepare("UPDATE users SET role = :role WHERE id = :id")->execute([
            ':role' => $new_role,
            ':id' => $id
        ]);
        $success = 'تم تحديث صلاحيات المستخدم';
    }
}

// Fetch all users
$users = $db->query("SELECT * FROM users ORDER BY created_at DESC")->fetchAll();
?>

<div class="container" style="padding: 3rem 0;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <h1>إدارة المستخدمين</h1>
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

    <!-- Users List -->
    <div style="background: white; padding: 2rem; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
        <h2 style="margin-bottom: 1.5rem;">المستخدمين المسجلين (<?php echo count($users); ?>)</h2>

        <table style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="background: var(--light); text-align: right;">
                    <th style="padding: 1rem;">الاسم</th>
                    <th style="padding: 1rem;">البريد الإلكتروني</th>
                    <th style="padding: 1rem;">الصلاحية</th>
                    <th style="padding: 1rem;">تاريخ التسجيل</th>
                    <th style="padding: 1rem;">الإجراءات</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                    <tr style="border-bottom: 1px solid var(--border);">
                        <td style="padding: 1rem;">
                            <?php echo htmlspecialchars($user['username']); ?>
                            <?php if ($user['id'] == $_SESSION['user_id']): ?>
                                <span
                                    style="background: #fbbf24; color: white; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.75rem; margin-right: 0.5rem;">أنت</span>
                            <?php endif; ?>
                        </td>
                        <td style="padding: 1rem;"><?php echo htmlspecialchars($user['email']); ?></td>
                        <td style="padding: 1rem;">
                            <?php if ($user['role'] === 'admin'): ?>
                                <span
                                    style="background: #ef4444; color: white; padding: 0.25rem 0.75rem; border-radius: 20px; font-size: 0.875rem;">مدير</span>
                            <?php else: ?>
                                <span
                                    style="background: #10b981; color: white; padding: 0.25rem 0.75rem; border-radius: 20px; font-size: 0.875rem;">مستخدم</span>
                            <?php endif; ?>
                        </td>
                        <td style="padding: 1rem;"><?php echo format_date($user['created_at']); ?></td>
                        <td style="padding: 1rem;">
                            <div style="display: flex; gap: 0.5rem;">
                                <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                    <a href="?toggle_admin=<?php echo $user['id']; ?>" class="btn btn-secondary"
                                        style="padding: 0.5rem 1rem; font-size: 0.875rem;">
                                        <?php echo $user['role'] === 'admin' ? 'إلغاء الإدارة' : 'جعله مدير'; ?>
                                    </a>
                                    <a href="?delete=<?php echo $user['id']; ?>" class="btn btn-danger"
                                        style="padding: 0.5rem 1rem; font-size: 0.875rem;"
                                        onclick="return confirm('هل أنت متأكد من حذف هذا المستخدم؟')">
                                        حذف
                                    </a>
                                <?php else: ?>
                                    <span style="color: var(--gray); font-size: 0.875rem;">لا يمكن التعديل</span>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>