# ModernNews — Multi-Source Digital Newspaper CMS

A production-ready PHP newspaper CMS with reporter workflow, RSS news aggregation, and admin content management.

![PHP](https://img.shields.io/badge/PHP-8.0+-blue) ![MySQL](https://img.shields.io/badge/MySQL-5.7+-orange) ![Bootstrap](https://img.shields.io/badge/Bootstrap-5.3-purple) ![License](https://img.shields.io/badge/License-MIT-green)

## Features

- **Reporter System** — Register, write articles, save drafts, submit for approval
- **Admin CMS** — Approve/reject articles, manage categories, users, and RSS feeds
- **RSS Aggregation** — Fetch external news via cron (excerpts only, links to original source)
- **Role-Based Access** — Admin, Editor, Reporter with distinct permissions
- **SEO Optimized** — Clean URLs, Schema.org markup, Open Graph, Twitter Cards
- **Mobile-First** — Responsive Bootstrap 5 layout
- **Secure** — bcrypt passwords, PDO prepared statements, CSRF protection

## Tech Stack

| Component | Technology |
|-----------|-----------|
| Backend | PHP 8+ (Core PHP) |
| Database | MySQL 5.7+ |
| Frontend | Bootstrap 5, HTML5, CSS3 |
| Fonts | Google Fonts (Inter, Playfair Display) |
| Icons | Bootstrap Icons |

## Installation (aaPanel)

### 1. Create Website in aaPanel
- Go to **Website → Add Site**
- Enter your domain name
- Select **PHP 8.0+** and **MySQL**
- Click **Submit**

### 2. Clone Repository
```bash
cd /www/wwwroot/your-domain.com
git clone https://github.com/edge-tec/newspaper.git .
```

### 3. Import Database
- Go to **Database** in aaPanel
- Create a new database (e.g., `newspaper_db`)
- Click **phpMyAdmin** → Select the database → **Import** → Upload `newspaper_db.sql`

### 4. Configure
Edit `config/config.php`:
```php
define('SITE_URL', 'https://your-domain.com');
define('DB_HOST', 'localhost');
define('DB_USER', 'your_db_user');
define('DB_PASS', 'your_db_password');
define('DB_NAME', 'newspaper_db');
```

### 5. Set Permissions
```bash
chmod -R 755 /www/wwwroot/your-domain.com
chmod -R 777 assets/uploads
```

### 6. Configure URL Rewriting
In aaPanel → Website → Your Site → **URL Rewrite**, paste:

**For Apache** (already included in `.htaccess`):
```apache
RewriteEngine On
RewriteRule ^news/([a-zA-Z0-9-]+)/?$ news.php?slug=$1 [L,QSA]
RewriteRule ^category/([a-zA-Z0-9-]+)/?$ category.php?slug=$1 [L,QSA]
RewriteRule ^external/([0-9]+)/?$ external-news.php?id=$1 [L,QSA]
```

**For Nginx** (paste in site config):
```nginx
location / {
    try_files $uri $uri/ /index.php?$query_string;
}

location ~ ^/news/([a-zA-Z0-9-]+)/?$ {
    rewrite ^/news/([a-zA-Z0-9-]+)/?$ /news.php?slug=$1 last;
}

location ~ ^/category/([a-zA-Z0-9-]+)/?$ {
    rewrite ^/category/([a-zA-Z0-9-]+)/?$ /category.php?slug=$1 last;
}

location ~ ^/external/([0-9]+)/?$ {
    rewrite ^/external/([0-9]+)/?$ /external-news.php?id=$1 last;
}
```

### 7. Set Up RSS Cron Job
In aaPanel → **Cron**, add a Shell Script task:
```bash
/usr/bin/php /www/wwwroot/your-domain.com/cron/fetch_rss.php >> /www/wwwlogs/rss-fetch.log 2>&1
```
Set it to run **every 30 minutes**.

### 8. Login
- **Admin**: `admin@newspaper.com` / `password123`
- **⚠️ Change this password immediately after first login!**

## User Roles

| Role | Capabilities |
|------|-------------|
| **Admin** | Full control: manage users, categories, posts, RSS feeds, approve articles |
| **Editor** | Approve/reject reporter articles, edit and publish posts |
| **Reporter** | Register, write articles, save drafts, submit for review, track article status |

## Project Structure

```
├── config/           # Database connection, site config, helper functions
├── admin/            # Admin panel (dashboard, posts, categories, users, RSS)
├── reporter/         # Reporter dashboard (write, edit, track articles)
├── includes/         # Frontend header & footer
├── cron/             # RSS feed fetcher (cron job)
├── assets/           # CSS, JS, uploaded images
├── index.php         # Homepage
├── news.php          # Single article page
├── category.php      # Category archive
├── search.php        # Search results
├── external-news.php # External RSS article view
├── register.php      # Reporter registration
└── newspaper_db.sql  # Database schema
```

## RSS Aggregation Rules

- ✅ Fetches **title + excerpt** only from RSS/Atom feeds
- ✅ Always links back to original source
- ✅ **Never copies full article content**
- ✅ Clearly labeled as "External Source" on frontend
- ✅ Deduplicates by original URL
- ✅ Supports RSS 2.0 and Atom feed formats

## License

MIT License — feel free to use and modify.
