# CLAUDE.md - Greeting Cards Platform

## Project Overview

This is a PHP-based greeting cards platform (منصة كروت المعايدة) that allows users to create, customize, and share greeting cards for various Arabic occasions. The platform features a professional card editor using Fabric.js, template management, user authentication, and viral sharing capabilities.

**Primary Language**: Arabic (RTL interface)
**Tech Stack**: PHP 7.4+, MySQL 5.7+, Fabric.js, Vanilla JavaScript
**Target Audience**: Arabic-speaking users for occasions like Eid, weddings, birthdays, etc.

---

## Directory Structure

```
greetingcards/
├── config/                 # Configuration files
│   ├── config.php         # Main configuration (DB, site settings, paths)
│   ├── database.php       # PDO singleton database class
│   ├── *.sql              # Migration scripts
│   └── run_migration.php  # Migration runner
│
├── includes/              # Shared PHP includes
│   ├── header.php         # Site header with navigation
│   ├── footer.php         # Site footer
│   └── functions.php      # Utility functions (security, auth, pagination, etc.)
│
├── admin/                 # Admin dashboard
│   ├── index.php          # Admin dashboard home
│   ├── login.php          # Admin login
│   ├── templates.php      # Template management
│   ├── categories.php     # Category management
│   ├── emojis.php         # Emoji management
│   ├── emoji_categories.php # Emoji category management
│   ├── users.php          # User management
│   └── menu.php           # Admin navigation menu
│
├── api/                   # REST API endpoints
│   ├── save-card.php      # Save and generate share URL
│   ├── track-download.php # Track download statistics
│   └── increment-download.php # Increment download counter
│
├── assets/                # Static assets
│   ├── css/
│   │   ├── style.css      # Main stylesheet
│   │   └── editor.css     # Editor-specific styles
│   └── js/
│       ├── main.js        # Main JavaScript
│       └── editor.js      # Editor functionality
│
├── uploads/               # User-generated content (755 permissions)
│   ├── templates/         # Template images
│   ├── shared/            # Shared card images
│   └── emojis/            # Emoji images
│
├── index.php              # Homepage with categories and featured templates
├── templates.php          # Template gallery/browser
├── editor-simple.php      # Main card editor (Fabric.js)
├── share.php              # Share page with themed backgrounds
├── login.php              # User login
├── register.php           # User registration
├── logout.php             # Logout handler
└── dashboard.php          # User dashboard

```

---

## Database Schema

### Core Tables

1. **users** - User accounts
   - Fields: id, name, email, password (bcrypt), role (user/admin), created_at
   - Roles: 'user', 'admin'

2. **categories** - Template categories
   - Fields: id, name_ar, name_en, slug, icon, is_active, display_order, created_at
   - Examples: eid-fitr, eid-adha, wedding, birthday, success, baby, general

3. **templates** - Card templates
   - Fields: id, category_id, title, image_path, preview_image_url, aspect_ratio (default: '4:5'), is_active, views, downloads, created_at
   - Note: preview_image_url used for vertical display, falls back to image_path

4. **shared_cards** - User-generated shared cards
   - Fields: id, unique_id (32-char hex), template_id, user_id (nullable), card_image_url, dedication_text, sender_name, background_id, views, downloads, created_at
   - Purpose: Stores customized cards for viral sharing

5. **share_backgrounds** - Themed backgrounds for share page
   - Fields: id, name_ar, name_en, slug, category, background_type (pattern/gradient/image), background_value (CSS), preview_image, is_active, display_order, created_at
   - Categories: wedding, condolence, birthday, eid, general

6. **emojis** - Emoji library for editor
   - Fields: id, category_id, unicode, image_path, keywords, is_active, display_order, created_at

7. **emoji_categories** - Emoji categories
   - Fields: id, name, icon, is_active, display_order, created_at

---

## Configuration

### config/config.php

**Critical Constants:**
- `DB_HOST`, `DB_USER`, `DB_PASS`, `DB_NAME` - Database credentials
- `SITE_NAME` - Arabic site name
- `SITE_URL` - Full site URL (https://go.demaghfull.com)
- `ADMIN_EMAIL` - Admin contact email
- `UPLOAD_PATH`, `TEMPLATE_PATH`, `EMOJI_PATH` - File system paths
- `SESSION_NAME` - Session identifier
- `ITEMS_PER_PAGE` - Pagination (12)

**Security Settings:**
- `HASH_ALGO` - PASSWORD_BCRYPT for password hashing
- Error reporting enabled (should be 0 in production)

**Timezone:**
- Africa/Cairo

---

## Key Features

### 1. Card Editor (editor-simple.php)
- **Technology**: Fabric.js canvas library
- **Capabilities**:
  - Add/edit text with Arabic fonts (Cairo, Tajawal, Almarai, etc.)
  - Rich color palette and gradient support
  - Emoji picker organized by categories
  - Text styling: bold, italic, alignment, shadow
  - Layer management: bring forward, send backward
  - Real-time preview
  - Export to PNG
  - Share with dedication text

**Editor Flow:**
1. Load template from database
2. Initialize Fabric.js canvas with template image
3. User adds text/emojis
4. User customizes styling
5. User can download OR share (saves to database)

### 2. Share System
- **Purpose**: Viral growth through shareable cards
- **Features**:
  - Unique URL per card (share.php?id={unique_id})
  - 8 themed backgrounds (hearts, balloons, floral, gradients, etc.)
  - Social sharing: WhatsApp, Facebook, copy link
  - Download tracking
  - View tracking
  - CTA button to create own card

**Share Flow:**
1. User completes card in editor
2. Clicks "حفظ ومشاركة" (Save & Share)
3. Card saved via api/save-card.php
4. Generates unique_id and saves PNG to uploads/shared/
5. Redirects to share.php with themed background
6. Recipient can download or create their own

### 3. Admin Panel
- **Access**: /admin/ (role check required)
- **Features**:
  - Template CRUD with image upload
  - Category management
  - Emoji library management
  - User management
  - Statistics dashboard
  - Activity logs

### 4. Authentication
- **Login**: Email + bcrypt password
- **Sessions**: Custom session name
- **Roles**: user, admin
- **CSRF Protection**: Token-based (csrf_token() function)
- **XSS Protection**: htmlspecialchars on all outputs
- **SQL Injection**: PDO prepared statements

---

## Security Practices

### Input Validation
- `sanitize_input()` - XSS protection in includes/functions.php:3
- File upload validation in includes/functions.php:52
  - Allowed types: image/jpeg, image/png, image/gif, image/webp
  - Max size: 5MB
  - Unique filename generation

### Authentication
- `is_logged_in()` - Check user session (includes/functions.php:25)
- `is_admin()` - Check admin role (includes/functions.php:31)
- `require_login()` - Force login redirect (includes/functions.php:36)
- `require_admin()` - Force admin redirect (includes/functions.php:43)

### CSRF Protection
- `csrf_token()` - Generate/retrieve token (includes/functions.php:12)
- `verify_csrf_token()` - Validate token (includes/functions.php:19)
- Must be included in all forms

### Password Security
- All passwords hashed with PASSWORD_BCRYPT
- No plaintext storage
- Secure password reset flow

---

## API Endpoints

### POST /api/save-card.php
**Purpose**: Save customized card and generate share URL

**Request Body** (JSON):
```json
{
  "template_id": 123,
  "card_image": "data:image/png;base64,...",
  "dedication_text": "كل عام وأنت بخير",
  "sender_name": "أحمد",
  "background_id": 1
}
```

**Response**:
```json
{
  "success": true,
  "share_url": "https://go.demaghfull.com/share.php?id=abc123...",
  "unique_id": "abc123...",
  "preview_url": "https://go.demaghfull.com/share.php?id=abc123..."
}
```

**Process**:
1. Validates required fields
2. Decodes base64 image
3. Generates unique_id (32-char hex)
4. Saves PNG to uploads/shared/
5. Inserts record in shared_cards table
6. Returns share URL

### POST /api/track-download.php
**Purpose**: Increment download counter

**Request Body** (JSON):
```json
{
  "type": "template",  // or "shared"
  "id": 123
}
```

### POST /api/increment-download.php
**Purpose**: Similar to track-download, increment counters

---

## Common Development Tasks

### Adding a New Template
1. Login to admin panel (/admin/login.php)
2. Navigate to "إدارة القوالب" (Template Management)
3. Click "إضافة قالب جديد"
4. Upload image (800x600 recommended, 4:5 aspect ratio)
5. Enter title, select category
6. Set display order and active status

**Database**: INSERT into `templates` table

### Adding a New Category
1. Admin panel → "إدارة الأقسام" (Category Management)
2. Enter name_ar, name_en, slug (URL-friendly)
3. Choose emoji icon
4. Set display order

**Database**: INSERT into `categories` table

### Adding Emojis to Editor
1. Admin panel → "إدارة الرموز التعبيرية" (Emoji Management)
2. Upload emoji PNG image
3. Enter unicode representation
4. Add keywords for search
5. Select category

**Database**: INSERT into `emojis` table

### Running Database Migrations
1. Upload migration SQL file to config/
2. Open browser: https://yourdomain.com/config/run_migration.php
3. Or execute SQL directly in phpMyAdmin

**Example**: config/migration_share_feature.sql (adds share_backgrounds and shared_cards tables)

### Modifying the Editor
**Key Files**:
- editor-simple.php - Main editor HTML/PHP
- assets/js/editor.js - Fabric.js logic
- assets/css/editor.css - Editor styling

**Common Modifications**:
- Add new control: Modify editor-simple.php controls section
- Add new font: Update $fonts array in editor-simple.php:45
- Add new color: Update $colors array in editor-simple.php:60
- Change canvas size: Modify Fabric.js initialization

### Updating Share Backgrounds
**Database**: `share_backgrounds` table

**Add via SQL**:
```sql
INSERT INTO share_backgrounds (name_ar, name_en, slug, category, background_type, background_value, display_order)
VALUES ('اسم عربي', 'English Name', 'slug-name', 'wedding', 'gradient', 'linear-gradient(...)', 10);
```

**Categories**: wedding, condolence, birthday, eid, success, general

**Background Types**:
- `pattern` - CSS pattern (repeating-linear-gradient, radial-gradient)
- `gradient` - Linear/radial gradients
- `image` - URL to image file

---

## Coding Conventions

### PHP
- **File Encoding**: UTF-8
- **Naming**: snake_case for variables/functions, PascalCase for classes
- **Database**: PDO with prepared statements ALWAYS
- **Output**: Always use htmlspecialchars() for user data
- **Sessions**: Start with session_start() in header.php
- **Includes**: Use require_once for critical files
- **Error Handling**: Try-catch for database operations

### Database Queries
**Pattern**:
```php
$stmt = $db->prepare("SELECT * FROM table WHERE id = :id");
$stmt->execute([':id' => $id]);
$result = $stmt->fetch(); // or fetchAll()
```

**Never**:
```php
$query = "SELECT * FROM table WHERE id = " . $_GET['id']; // SQL injection!
```

### JavaScript
- **Style**: camelCase for variables/functions
- **Fabric.js**: Global `canvas` object for editor
- **AJAX**: Use fetch() with async/await
- **Error Handling**: Try-catch with user-friendly alerts

### CSS
- **Variables**: CSS custom properties in :root
- **RTL**: Direction: rtl for Arabic layout
- **Units**: rem for typography, px for borders
- **Mobile First**: Responsive design with media queries

### Arabic (RTL) Considerations
- Text direction: right-to-left
- Flex/grid layouts must account for RTL
- Text alignment: start/end instead of left/right
- Icons/buttons: mirror positioning

---

## File Upload Requirements

### Required Directories
```bash
mkdir -p uploads/templates uploads/shared uploads/emojis
chmod 755 uploads uploads/templates uploads/shared uploads/emojis
```

### Upload Validation
- **Function**: upload_image() in includes/functions.php:52
- **Allowed MIME**: image/jpeg, image/png, image/gif, image/webp
- **Max Size**: 5MB
- **Filename**: Unique ID + extension (uniqid() . '.' . $extension)

---

## Deployment Checklist

### 1. Server Requirements
- [ ] PHP 7.4+ with extensions: PDO, GD, mbstring
- [ ] MySQL 5.7+ or MariaDB 10.3+
- [ ] Apache with mod_rewrite enabled
- [ ] HTTPS configured

### 2. File Setup
- [ ] Upload all files via FTP/SFTP
- [ ] Set directory permissions (755 for uploads/)
- [ ] Create uploads/shared directory if missing

### 3. Database Setup
- [ ] Create MySQL database
- [ ] Import base schema (database.sql if exists)
- [ ] Run migrations in config/*.sql
- [ ] Create admin user

### 4. Configuration
- [ ] Edit config/config.php with production values
- [ ] Set DB_HOST, DB_USER, DB_PASS, DB_NAME
- [ ] Set SITE_URL to production domain
- [ ] Set error_reporting(0) in production
- [ ] Verify ADMIN_EMAIL

### 5. Security
- [ ] Change default admin password immediately
- [ ] Verify .htaccess is working (URL rewriting)
- [ ] Test CSRF protection on forms
- [ ] Verify file upload restrictions
- [ ] Enable HTTPS (force SSL)

### 6. Testing
- [ ] Homepage loads with categories
- [ ] Template browsing works
- [ ] Editor loads and can add text
- [ ] Card sharing generates unique URL
- [ ] Share page displays correctly
- [ ] Download tracking works
- [ ] Admin panel accessible

---

## Troubleshooting

### Common Issues

**Problem**: Templates not displaying
- Check: uploads/templates/ directory exists and is readable
- Check: Database has active templates (is_active = 1)
- Check: image_path or preview_image_url is correct

**Problem**: Editor not loading
- Check: Fabric.js CDN is accessible
- Check: Template ID is valid in URL (?template=123)
- Check: JavaScript console for errors

**Problem**: Share feature not working
- Check: api/save-card.php returns JSON (not HTML error)
- Check: uploads/shared/ directory exists with write permissions
- Check: shared_cards table exists (run migration)

**Problem**: Database connection failed
- Check: config/config.php credentials are correct
- Check: MySQL server is running
- Check: Database user has proper privileges

**Problem**: 404 errors on pages
- Check: .htaccess is uploaded and mod_rewrite enabled
- Check: File paths are correct in SITE_URL

**Problem**: Arabic text displays as boxes
- Check: Database charset is utf8mb4
- Check: Page <meta charset="UTF-8"> is set
- Check: Font includes are working (Google Fonts)

---

## Development Workflow

### Local Development
1. Install XAMPP/WAMP/MAMP
2. Create database, import schema
3. Update config/config.php with localhost settings
4. Set SITE_URL to http://localhost/greetingcards
5. Enable error reporting for debugging

### Making Changes
1. **Never** edit directly on production
2. Test locally first
3. Use version control (git)
4. Document changes in commit messages
5. Deploy via FTP/SFTP or git pull

### Database Changes
1. Write migration SQL in config/*.sql
2. Test migration on local copy
3. Document in comments
4. Deploy SQL file and run via run_migration.php
5. Verify schema changes

### Adding Features
1. Plan feature requirements
2. Design database changes (if needed)
3. Create/modify PHP files
4. Update JavaScript/CSS as needed
5. Test thoroughly (all user types)
6. Document in code comments
7. Update this CLAUDE.md if architecture changes

---

## Important Notes for AI Assistants

### When Working with This Codebase:

1. **Always Use Prepared Statements**: Never concatenate SQL queries
2. **Escape Output**: Use htmlspecialchars() on all user-generated content
3. **Check Authentication**: Use is_logged_in() and is_admin() before privileged operations
4. **CSRF Tokens**: Include in all forms that modify data
5. **Arabic Text**: Remember RTL layout and UTF-8 encoding
6. **File Uploads**: Always validate type and size
7. **Error Handling**: Use try-catch for database operations
8. **Session Management**: Check session_start() is called
9. **URL Constants**: Use SITE_URL constant for all internal links
10. **Database Access**: Use $db singleton from database.php

### Code Modification Priorities:
1. **Security First**: Never compromise security for convenience
2. **Maintain RTL**: Keep Arabic/RTL compatibility
3. **Mobile Responsive**: Test on mobile devices
4. **Performance**: Optimize database queries (use LIMIT, indexes)
5. **User Experience**: Keep UI simple and intuitive

### Testing Checklist:
- [ ] User can browse templates
- [ ] User can login/register
- [ ] Editor loads and saves cards
- [ ] Share URLs work and track stats
- [ ] Admin panel functions properly
- [ ] Mobile layout works
- [ ] Arabic text displays correctly
- [ ] File uploads are validated
- [ ] SQL injection attempts fail
- [ ] XSS attempts are escaped

---

## Recent Changes & Pending Work

### Recently Implemented (per EDITOR_MODIFICATIONS.md):
- Vertical card display (4:5 aspect ratio)
- Share feature with themed backgrounds
- Dedication text field in editor
- Save & Share button
- Social sharing (WhatsApp, Facebook)
- Download tracking API

### Pending/Optional (per FILES_TO_UPLOAD.md):
- [ ] Background selection modal in editor
- [ ] Admin panel for managing share backgrounds
- [ ] Enhanced statistics dashboard
- [ ] User card gallery/history
- [ ] Email sharing option

---

## Contact & Support

**Admin Email**: faressallam@gmail.com
**Site URL**: https://go.demaghfull.com
**Database**: go_cards (user: go_cards)

---

## Version History

**Current Version**: 1.0 (as of 2025)
- Initial release with core features
- Template editor with Fabric.js
- Share system with viral growth features
- Admin panel for content management
- Arabic/RTL support
- Mobile responsive design

---

**Last Updated**: 2025-11-26
**Maintainer**: AI Assistant
**Purpose**: Guide for AI assistants working with this codebase
