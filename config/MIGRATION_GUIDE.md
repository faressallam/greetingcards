# ترتيب تنفيذ Migrations

## الخطوة 1: تنفيذ Migration الأساسي
افتح: `yoursite.com/config/run_migration.php`

نفذ الملف: `migration_share_feature.sql`

هذا سيضيف:
- عمود `preview_image_url` و `aspect_ratio` لجدول templates
- جدول `share_backgrounds`
- جدول `shared_cards`
- 8 خلفيات افتراضية

## الخطوة 2: تنفيذ Migration الإضافي
افتح: `yoursite.com/config/run_migration.php` مرة ثانية

نفذ الملف: `add_template_dedication_fields.sql`

هذا سيضيف:
- عمود `default_dedication_text` لجدول templates
- عمود `default_background_id` لجدول templates

## ملاحظة مهمة
⚠️ لو ظهر error "Duplicate column name 'preview_image_url'":
- معناه إن الـ migration الأول اتنفذ قبل كدا
- نفذ بس الخطوة 2

## التحقق من نجاح التنفيذ
بعد التنفيذ، تأكد إن جدول `templates` فيه الأعمدة دي:
- preview_image_url
- aspect_ratio
- default_dedication_text
- default_background_id
