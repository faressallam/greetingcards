</main>

<!-- Footer -->
<footer class="footer">
    <div class="container">
        <div class="footer-content">
            <div class="footer-section">
                <h3>عن المنصة</h3>
                <p>منصة كروت المعايدة توفر لك قوالب احترافية لتصميم كروت معايدة للمناسبات العربية</p>
            </div>

            <div class="footer-section">
                <h3>روابط سريعة</h3>
                <ul class="footer-links">
                    <li><a href="<?php echo SITE_URL; ?>">الرئيسية</a></li>
                    <li><a href="<?php echo SITE_URL; ?>/templates.php">القوالب</a></li>
                    <li><a href="<?php echo SITE_URL; ?>/greetings.php">رسائل نصية</a></li>
                    <li><a href="<?php echo SITE_URL; ?>/blog.php">المدونة</a></li>
                </ul>
            </div>

            <div class="footer-section">
                <h3>تواصل معنا</h3>
                <p>البريد الإلكتروني: <?php echo ADMIN_EMAIL; ?></p>
            </div>
        </div>

        <div class="footer-bottom">
            <p>&copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?>. جميع الحقوق محفوظة.</p>
        </div>
    </div>
</footer>

<!-- Scripts -->
<script src="<?php echo SITE_URL; ?>/assets/js/main.js"></script>

<?php if (isset($extra_js)): ?>
    <?php foreach ($extra_js as $js): ?>
        <script src="<?php echo SITE_URL . '/assets/js/' . $js; ?>"></script>
    <?php endforeach; ?>
<?php endif; ?>

<script>
    // Auto-hide flash messages
    const flashMessage = document.getElementById('flash-message');
    if (flashMessage) {
        setTimeout(() => {
            flashMessage.style.opacity = '0';
            setTimeout(() => flashMessage.remove(), 300);
        }, 3000);
    }
</script>
</body>

</html>