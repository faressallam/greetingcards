<?php
$page_title = 'حساب جديد';
require_once 'includes/header.php';

if (is_logged_in()) {
    redirect(SITE_URL . '/dashboard.php');
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize_input($_POST['username'] ?? '');
    $email = sanitize_input($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($username) || empty($email) || empty($password)) {
        $error = 'يرجى ملء جميع الحقول';
    } elseif ($password !== $confirm_password) {
        $error = 'كلمات المرور غير متطابقة';
    } elseif (strlen($password) < 6) {
        $error = 'كلمة المرور يجب أن تكون 6 أحرف على الأقل';
    } else {
        // Check if email exists
        $stmt = $db->prepare("SELECT id FROM users WHERE email = :email");
        $stmt->execute([':email' => $email]);
        if ($stmt->fetch()) {
            $error = 'البريد الإلكتروني مستخدم بالفعل';
        } else {
            // Insert user
            $hashed_password = password_hash($password, HASH_ALGO);
            $stmt = $db->prepare("INSERT INTO users (username, email, password) VALUES (:username, :email, :password)");

            if (
                $stmt->execute([
                    ':username' => $username,
                    ':email' => $email,
                    ':password' => $hashed_password
                ])
            ) {
                $success = 'تم إنشاء الحساب بنجاح! يمكنك الآن تسجيل الدخول';
            } else {
                $error = 'حدث خطأ أثناء إنشاء الحساب';
            }
        }
    }
}
?>

<div class="container" style="padding: 4rem 0;">
    <div
        style="max-width: 450px; margin: 0 auto; background: white; padding: 3rem; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
        <h1 style="text-align: center; margin-bottom: 2rem;">حساب جديد</h1>

        <?php if ($error): ?>
            <div style="background: #fee2e2; color: #991b1b; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem;">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div style="background: #d1fae5; color: #065f46; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem;">
                <?php echo $success; ?>
                <a href="login.php" style="color: #065f46; font-weight: 600; text-decoration: underline;">تسجيل الدخول</a>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label class="form-label">اسم المستخدم</label>
                <input type="text" name="username" class="form-control" required>
            </div>

            <div class="form-group">
                <label class="form-label">البريد الإلكتروني</label>
                <input type="email" name="email" class="form-control" required>
            </div>

            <div class="form-group">
                <label class="form-label">كلمة المرور</label>
                <input type="password" name="password" class="form-control" required minlength="6">
            </div>

            <div class="form-group">
                <label class="form-label">تأكيد كلمة المرور</label>
                <input type="password" name="confirm_password" class="form-control" required>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; padding: 1rem;">
                إنشاء حساب
            </button>
        </form>

        <p style="text-align: center; margin-top: 1.5rem; color: var(--gray);">
            لديك حساب بالفعل؟
            <a href="<?php echo SITE_URL; ?>/login.php" style="color: var(--primary-color); font-weight: 600;">
                تسجيل الدخول
            </a>
        </p>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>