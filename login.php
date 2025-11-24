<?php
$page_title = 'تسجيل الدخول';
require_once 'includes/header.php';

if (is_logged_in()) {
    redirect(SITE_URL . '/index.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize_input($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = 'يرجى إدخال البريد الإلكتروني وكلمة المرور';
    } else {
        $stmt = $db->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['username'];
            $_SESSION['user_role'] = $user['role'];

            set_flash('success', 'مرحباً بك ' . $user['username']);

            // Redirect based on role
            if ($user['role'] === 'admin') {
                redirect(SITE_URL . '/admin/');
            } else {
                redirect(SITE_URL . '/index.php');
            }
        } else {
            $error = 'البريد الإلكتروني أو كلمة المرور غير صحيحة';
        }
    }
}
?>

<div class="container" style="padding: 4rem 0;">
    <div
        style="max-width: 450px; margin: 0 auto; background: white; padding: 3rem; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
        <h1 style="text-align: center; margin-bottom: 2rem;">تسجيل الدخول</h1>

        <?php if ($error): ?>
            <div style="background: #fee2e2; color: #991b1b; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem;">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label class="form-label">البريد الإلكتروني</label>
                <input type="email" name="email" class="form-control" required>
            </div>

            <div class="form-group">
                <label class="form-label">كلمة المرور</label>
                <input type="password" name="password" class="form-control" required>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; padding: 1rem;">
                تسجيل الدخول
            </button>
        </form>

        <p style="text-align: center; margin-top: 1.5rem; color: var(--gray);">
            ليس لديك حساب؟
            <a href="<?php echo SITE_URL; ?>/register.php" style="color: var(--primary-color); font-weight: 600;">
                سجل الآن
            </a>
        </p>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>