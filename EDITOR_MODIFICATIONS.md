# تعديلات المحرر المطلوبة

## 1. حذف Header غير المطلوب
في ملف `editor-simple.php`، احذف الجزء دا (حوالي سطر 460-470):

```html
<!-- احذف هذا الجزء -->
<div class="editor-header">
    <h1><?php echo htmlspecialchars($template['title']); ?></h1>
    <p style="color: var(--gray);">صمم كارتك بسهولة</p>
</div>
```

## 2. إضافة حقل نص الإهداء
أضف هذا الكود في قسم الـ Controls (بعد حقل الاسم):

```html
<!-- نص الإهداء -->
<div class="controls-section">
    <label class="control-label">💌 نص الإهداء (سيظهر مع الكارت عند المشاركة)</label>
    <textarea id="dedicationText" class="form-control" rows="3" 
        placeholder="مثال: كل عام وأنت بخير يا أغلى الناس"><?php echo htmlspecialchars($template['default_dedication_text'] ?? ''); ?></textarea>
</div>
```

## 3. إضافة أزرار المشاركة
استبدل زر "تحميل" بمجموعة أزرار:

```html
<!-- أزرار المشاركة والتحميل -->
<div class="share-buttons" style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; margin-top: 1rem;">
    <button onclick="shareCard()" class="btn btn-primary" style="padding: 1rem; font-size: 1rem;">
        📤 حفظ ومشاركة
    </button>
    <button onclick="downloadCard()" class="btn btn-secondary" style="padding: 1rem; font-size: 1rem;">
        ⬇️ تحميل
    </button>
</div>
```

## 4. إضافة JavaScript للمشاركة
أضف هذا الكود في نهاية الملف (قبل `</script>`):

```javascript
// Share card function
async function shareCard() {
    const dedicationText = document.getElementById('dedicationText').value;
    const senderName = prompt('اكتب اسمك (اختياري):') || '';
    
    // Get card image as base64
    const cardImage = canvas.toDataURL('image/png');
    
    // Show loading
    const btn = event.target;
    const originalText = btn.innerHTML;
    btn.innerHTML = '⏳ جاري الحفظ...';
    btn.disabled = true;
    
    try {
        const response = await fetch('<?php echo SITE_URL; ?>/api/save-card.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({
                template_id: <?php echo $template_id; ?>,
                card_image: cardImage,
                dedication_text: dedicationText,
                sender_name: senderName,
                background_id: <?php echo $template['default_background_id'] ?? 'null'; ?>
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            // Redirect to share page
            window.location.href = data.share_url;
        } else {
            alert('حدث خطأ: ' + data.message);
        }
    } catch (error) {
        alert('حدث خطأ في الاتصال');
    } finally {
        btn.innerHTML = originalText;
        btn.disabled = false;
    }
}
```

## 5. تعديل دالة التحميل
غير اسم الدالة من `downloadImage` إلى `downloadCard`:

```javascript
function downloadCard() {
    const link = document.createElement('a');
    link.download = 'greeting-card.png';
    link.href = canvas.toDataURL('image/png');
    link.click();
    
    // Update downloads count
    fetch('<?php echo SITE_URL; ?>/api/update-stats.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({template_id: <?php echo $template_id; ?>, type: 'download'})
    });
}
```

---

## ملخص التعديلات:
1. ✅ حذف "اسم الكارت" و "صمم كارتك بسهولة"
2. ✅ إضافة حقل نص الإهداء
3. ✅ إضافة زر "حفظ ومشاركة"
4. ✅ تعديل زر التحميل
5. ✅ إضافة JavaScript للمشاركة

**بعد التعديلات:** المحرر هيكون فيه حقل نص الإهداء وزرين (حفظ ومشاركة + تحميل)
