<?php
$page_title = 'مشاركة الكارت';
require_once 'config/database.php';

// Get unique ID from URL
$unique_id = isset($_GET['id']) ? sanitize_input($_GET['id']) : '';

if (empty($unique_id)) {
    header('Location: ' . SITE_URL);
    exit;
}

// Fetch shared card
$stmt = $db->prepare("
    SELECT sc.*, sb.background_type, sb.background_value, sb.slug as bg_slug
    FROM shared_cards sc
    LEFT JOIN share_backgrounds sb ON sc.background_id = sb.id
    WHERE sc.unique_id = :unique_id
");
$stmt->execute([':unique_id' => $unique_id]);
$card = $stmt->fetch();

if (!$card) {
    header('Location: ' . SITE_URL);
    exit;
}

// Increment views
$db->prepare("UPDATE shared_cards SET views = views + 1 WHERE unique_id = :unique_id")
    ->execute([':unique_id' => $unique_id]);

// Set page title
$page_title = $card['sender_name'] ? 'كارت من ' . htmlspecialchars($card['sender_name']) : 'كارت معايدة';

require_once 'includes/header.php';

// Get background class
$bg_class = $card['bg_slug'] ?? 'gradient-elegant';
?>

<style>
    /* Share Page - Love Letter Style */
    .share-container {
        max-width: 1000px;
        margin: 0 auto;
        padding: 2rem;
        min-height: 100vh;
    }

    .share-background {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: -1;
        opacity: 0.12;
    }

    .share-background.hearts {
        background-image: repeating-linear-gradient(45deg, transparent, transparent 35px, rgba(255, 105, 180, 0.3) 35px, rgba(255, 105, 180, 0.3) 70px),
            repeating-linear-gradient(-45deg, transparent, transparent 35px, rgba(255, 182, 193, 0.3) 35px, rgba(255, 182, 193, 0.3) 70px);
    }

    .share-background.gradient-elegant,
    .share-background.gradient-purple {
        background: linear-gradient(135deg, rgba(147, 51, 234, 0.1) 0%, rgba(79, 70, 229, 0.1) 100%);
    }

    .share-card-wrapper {
        background: white;
        border-radius: 20px;
        padding: 3rem;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
        margin-bottom: 2rem;
    }

    /* Love Letter Layout - Side by Side */
    .love-letter-content {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 3rem;
        align-items: center;
        margin-bottom: 2rem;
    }

    .card-image-section {
        text-align: center;
    }

    .share-card-image {
        width: 100%;
        max-width: 400px;
        border-radius: 12px;
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
    }

    .card-message-section {
        display: flex;
        flex-direction: column;
        justify-content: center;
    }

    .message-from {
        font-size: 1.125rem;
        color: var(--primary-color);
        font-weight: 600;
        margin-bottom: 1.5rem;
        font-style: italic;
    }

    .message-text {
        font-size: 1.5rem;
        line-height: 2;
        color: var(--dark);
        font-family: 'Cairo', serif;
        margin-bottom: 2rem;
        padding: 2rem;
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border-radius: 16px;
        border-right: 4px solid var(--primary-color);
    }

    .share-buttons {
        display: flex;
        gap: 1rem;
        justify-content: center;
        flex-wrap: wrap;
        margin-top: 2rem;
        padding-top: 2rem;
        border-top: 2px dashed #e2e8f0;
    }

    .share-btn {
        padding: 0.875rem 1.75rem;
        border-radius: 12px;
        font-weight: 600;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        transition: all 0.3s;
        border: none;
        cursor: pointer;
        font-size: 1rem;
    }

    .share-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
    }

    .share-btn-whatsapp {
        background: #25D366;
        color: white;
    }

    .share-btn-facebook {
        background: #1877F2;
        color: white;
    }

    .share-btn-download {
        background: var(--primary-color);
        color: white;
    }

    .share-btn-copy {
        background: var(--secondary-color);
        color: white;
    }

    /* CTA Button */
    .share-cta {
        background: linear-gradient(135deg, #8b5cf6 0%, #6366f1 100%);
        padding: 3rem 2rem;
        border-radius: 20px;
        text-align: center;
        margin-top: 3rem;
        box-shadow: 0 10px 40px rgba(139, 92, 246, 0.3);
        animation: pulse-glow 2s ease-in-out infinite;
    }

    @keyframes pulse-glow {

        0%,
        100% {
            box-shadow: 0 10px 40px rgba(139, 92, 246, 0.3);
        }

        50% {
            box-shadow: 0 15px 50px rgba(139, 92, 246, 0.5);
        }
    }

    .share-cta-title {
        font-size: 2rem;
        font-weight: 700;
        color: white;
        margin-bottom: 1rem;
    }

    .share-cta-subtitle {
        font-size: 1.125rem;
        color: rgba(255, 255, 255, 0.9);
        margin-bottom: 2rem;
    }

    .share-cta-button {
        background: white;
        color: var(--primary-color);
        padding: 1.25rem 3rem;
        border-radius: 50px;
        font-size: 1.25rem;
        font-weight: 700;
        text-decoration: none;
        display: inline-block;
        transition: all 0.3s;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
    }

    .share-cta-button:hover {
        transform: scale(1.05);
        box-shadow: 0 6px 25px rgba(0, 0, 0, 0.3);
    }

    @media (max-width: 768px) {
        .share-container {
            padding: 1rem;
        }

        .share-card-wrapper {
            padding: 1.5rem;
        }

        .love-letter-content {
            grid-template-columns: 1fr;
            gap: 2rem;
        }

        .message-text {
            font-size: 1.25rem;
            padding: 1.5rem;
        }

        .share-cta-title {
            font-size: 1.5rem;
        }

        .share-buttons {
            flex-direction: column;
        }

        .share-btn {
            width: 100%;
            justify-content: center;
        }
    }
</style>

<!-- Background -->
<div class="share-background <?php echo htmlspecialchars($bg_class); ?>"></div>

<div class="share-container">
    <div class="share-card-wrapper">
        <!-- Love Letter Layout -->
        <div class="love-letter-content">
            <!-- Card Image -->
            <div class="card-image-section">
                <img src="<?php echo SITE_URL . '/' . $card['card_image_url']; ?>" alt="كارت معايدة"
                    class="share-card-image" id="cardImage">
            </div>

            <!-- Message Section -->
            <div class="card-message-section">
                <?php if (!empty($card['sender_name'])): ?>
                    <div class="message-from">
                        💌 من: <?php echo htmlspecialchars($card['sender_name']); ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($card['dedication_text'])): ?>
                    <div class="message-text">
                        <?php echo nl2br(htmlspecialchars($card['dedication_text'])); ?>
                    </div>
                <?php else: ?>
                    <div class="message-text">
                        أرسل لك هذا الكارت الجميل ❤️
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Share Buttons -->
        <div class="share-buttons">
            <button onclick="downloadImage()" class="share-btn share-btn-download">
                ⬇️ حفظ الصورة
            </button>
            <a href="https://wa.me/?text=<?php echo urlencode('شاهد هذا الكارت الرائع: ' . SITE_URL . '/share.php?id=' . $unique_id); ?>"
                target="_blank" class="share-btn share-btn-whatsapp">
                📱 مشاركة على واتساب
            </a>
            <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode(SITE_URL . '/share.php?id=' . $unique_id); ?>"
                target="_blank" class="share-btn share-btn-facebook">
                📘 مشاركة على فيسبوك
            </a>
            <button onclick="copyLink()" class="share-btn share-btn-copy">
                🔗 نسخ الرابط
            </button>
        </div>
    </div>

    <!-- CTA -->
    <div class="share-cta">
        <h2 class="share-cta-title">✨ عايز تعمل كارت زي دا؟ ✨</h2>
        <p class="share-cta-subtitle">صمم كارتك الخاص في دقائق واحدة</p>
        <a href="<?php echo SITE_URL; ?>/templates.php" class="share-cta-button">
            اضغط هنا للبدء
        </a>
    </div>
</div>

<script>
    function downloadImage() {
        const link = document.createElement('a');
        link.href = document.getElementById('cardImage').src;
        link.download = 'greeting-card.png';
        link.click();

        // Track download
        fetch('<?php echo SITE_URL; ?>/api/track-download.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ unique_id: '<?php echo $unique_id; ?>' })
        });
    }

    function copyLink() {
        const url = '<?php echo SITE_URL . '/share.php?id=' . $unique_id; ?>';
        navigator.clipboard.writeText(url).then(() => {
            alert('تم نسخ الرابط! ✅');
        });
    }
</script>

<?php require_once 'includes/footer.php'; ?>