<?php
// Reset Admin Password Script
require_once '../config/config.php';
require_once '../config/database.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_password = $_POST['new_password'] ?? '';

    if (empty($new_password)) {
        $message = 'يرجى إدخال كلمة المرور الجديدة';
    } else {
        try {
            $db = Database::getInstance()->getConnection();
            $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);

            // Update admin password
            $stmt = $db->prepare("UPDATE users SET password = :password WHERE email = 'admin@example.com'");
            $stmt->execute([':password' => $hashed_password]);

            $message = 'تم تحديث كلمة المرور بنجاح! يمكنك الآن تسجيل الدخول بكلمة المرور الجديدة.';
        } catch (Exception $e) {
            $message = 'حدث خطأ: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إعادة تعيين كلمة مرور الإدارة</title>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Cairo', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }

        .reset-box {
            background: white;
            padding: 3rem;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 500px;
        }

        h1 {
            text-align: center;
            margin-bottom: 2rem;
            color: #667eea;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
        }

        input {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            font-family: 'Cairo', sans-serif;
            font-size: 1rem;
        }

        input:focus {
            outline: none;
            border-color: #667eea;
        }

        button {
            width: 100%;
            padding: 1rem;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-family: 'Cairo', sans-serif;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
        }

        button:hover {
            opacity: 0.9;
        }

        .message {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            background: #d1fae5;
            color: #065f46;
        }

        .warning {
            background: #fef3c7;
            color: #92400e;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
        }

        .link {
            text-align: center;
            margin-top: 1.5rem;
        }

        .link a {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
        }
    </style>
</head>

<body>
    <div class="reset-box">
        <h1>🔑 إعادة تعيين كلمة المرور</h1>

        <div class="warning">
            <strong>⚠️ تحذير:</strong> هذه الصفحة لإعادة تعيين كلمة مرور حساب الإدارة. احذفها بعد الاستخدام!
        </div>

        <?php if ($message): ?>
            <div class="message"><?php echo $message; ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label>كلمة المرور الجديدة</label>
                <input type="text" name="new_password" required placeholder="اكتب كلمة المرور الجديدة">
                <small style="color: #64748b; display: block; margin-top: 0.5rem;">
                    مثال: MyNewPassword123
                </small>
            </div>

            <button type="submit">تحديث كلمة المرور</button>
        </form>

        <div class="link">
            <a href="login.php">← العودة لتسجيل الدخول</a>
        </div>

        <div style="margin-top: 2rem; padding: 1rem; background: #f1f5f9; border-radius: 8px; font-size: 0.875rem;">
            <strong>ملاحظة:</strong> بعد تحديث كلمة المرور، احذف هذا الملف (reset-password.php) من السيرفر للأمان!
        </div>
    </div>
</body>

</html>