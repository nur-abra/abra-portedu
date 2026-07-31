# Personal Portfolio Management System

A responsive, modern portfolio website with PHP 8+, MySQL, Bootstrap 5, and full admin management. Supports local development and Vercel deployment via the community `vercel-php` runtime.

## Features

### Admin Module
- Secure login with session-based authentication and role-based access control
- Forgot password and reset password flow
- Photo management (upload, preview, update, delete, categorize)
- Content management (About, Projects, Contact)
- Comment moderation (approve, reject, delete)
- Feedback management

### Viewer Module
- Public portfolio pages (Home, About, Portfolio, Contact, Feedback)
- Responsive image gallery with modal preview
- Comments with admin moderation
- Reactions (Like, Love, Helpful) with duplicate prevention via cookies/sessions
- Share buttons (Facebook, Messenger, Copy Link)
- Feedback form with star rating

## Project Structure

```
portfolio-system/
├── admin/                  # Admin panel pages
├── api/                    # Vercel front controller
├── assets/css/             # Stylesheets
├── assets/js/              # JavaScript
├── config/database.php     # PDO database connection
├── includes/               # Shared PHP includes
├── uploads/                # Uploaded images
├── index.php               # Home page
├── about.php
├── portfolio.php
├── contact.php
├── comments.php            # Comments API
├── reactions.php           # Reactions API
├── feedback.php
├── database.sql
├── vercel.json
├── .env.example
└── README.md
```

## Requirements

- PHP 8.0 or higher
- MySQL 5.7+ or MariaDB 10.3+
- Web server (Apache/Nginx) or PHP built-in server for local dev
- Node.js (for Vercel CLI deployment only)

## Installation Guide

### 1. Clone or copy the project

```bash
cd myproject
```

### 2. Configure environment

Copy the example environment file and set your database credentials:

```bash
cp .env.example .env
```

Edit `.env`:

```
DB_HOST=localhost
DB_NAME=portfolio_system
DB_USERNAME=root
DB_PASSWORD=your_password
APP_ENV=development
```

### 3. Create the database

Import the SQL schema using MySQL CLI or phpMyAdmin:

```bash
mysql -u root -p < database.sql
```

Or in MySQL:

```sql
SOURCE /path/to/database.sql;
```

### 4. Set uploads folder permissions

Ensure the `uploads/` directory is writable by the web server:

```bash
chmod 755 uploads
```

On Windows, ensure IIS/Apache has write access to the folder.

### 5. Start local development server

```bash
php -S localhost:8000
```

Open [http://localhost:8000](http://localhost:8000) in your browser.

### 6. Default admin credentials

| Field    | Value              |
|----------|--------------------|
| Username | admin              |
| Password | Admin@123          |
| Email    | admin@portfolio.com |

**Change the default password immediately after first login.**

## Vercel Deployment Guide

### Prerequisites

1. [Vercel account](https://vercel.com)
2. Remote MySQL database (PlanetScale, Railway, Aiven, or similar)
3. [Vercel CLI](https://vercel.com/docs/cli) installed

### Step 1: Prepare remote database

1. Create a MySQL database on your cloud provider
2. Import `database.sql`
3. Note the host, database name, username, and password

### Step 2: Configure environment variables on Vercel

In the Vercel project dashboard → Settings → Environment Variables, add:

| Variable      | Value                    |
|---------------|--------------------------|
| DB_HOST       | your-db-host.example.com |
| DB_NAME       | portfolio_system         |
| DB_USERNAME   | your_username            |
| DB_PASSWORD   | your_password            |
| APP_ENV       | production               |

### Step 3: Deploy

```bash
npm i -g vercel
vercel login
vercel
```

Follow the prompts. Vercel uses `vercel.json` with the `vercel-php@0.7.4` runtime and routes all requests through `api/index.php`.

### Step 4: Verify deployment

- Visit your Vercel URL
- Test public pages load correctly
- Test admin login at `/admin/login.php`
- Test comments, reactions, and feedback

### Vercel limitations

| Feature        | Local | Vercel |
|----------------|-------|--------|
| PHP pages      | Yes   | Yes    |
| MySQL (remote) | Yes   | Yes    |
| File uploads   | Yes   | **Ephemeral** — uploaded files do not persist between deployments. Use external storage (Cloudinary, S3, Vercel Blob) for production uploads, or deploy to traditional PHP hosting for full upload support. |
| Sessions       | Yes   | Works per-instance; may reset between cold starts |

For production file uploads on Vercel, integrate cloud storage or use traditional hosting (cPanel, shared hosting, VPS).

## Testing Checklist

### Authentication
- [ ] Admin can log in with valid credentials
- [ ] Invalid login shows error message
- [ ] Session expires after inactivity (1 hour)
- [ ] Logout clears session
- [ ] Forgot password generates reset token
- [ ] Reset password works with valid token
- [ ] Non-admin cannot access admin pages

### Photo Management
- [ ] Upload valid image (JPG, PNG, GIF, WEBP)
- [ ] Image preview shows before upload
- [ ] Invalid file type is rejected
- [ ] Files over 5MB are rejected
- [ ] Update photo title/description/category
- [ ] Delete photo removes file and database record
- [ ] Photos display in correct category on portfolio page

### Content Management
- [ ] Update About Me section
- [ ] Add/edit/delete portfolio projects
- [ ] Update contact information and social links

### Viewer Features
- [ ] Home page displays profile and featured projects
- [ ] About page shows skills, education, experience
- [ ] Portfolio gallery filters by category
- [ ] Image modal preview works
- [ ] Contact page shows phone and email
- [ ] Comment submission stores as pending
- [ ] Approved comments appear on home page
- [ ] Reactions increment counts
- [ ] Duplicate reactions from same visitor are blocked
- [ ] Facebook/Messenger share opens correctly
- [ ] Copy link copies URL to clipboard
- [ ] Feedback form validates and saves

### Responsive Design
- [ ] Navigation collapses on mobile
- [ ] Gallery grid adapts to screen size
- [ ] Admin sidebar stacks on mobile

## Security Checklist

- [x] **SQL Injection** — All queries use PDO prepared statements
- [x] **XSS** — Output escaped with `htmlspecialchars()` via `e()` helper
- [x] **CSRF** — Tokens on all POST forms and AJAX requests
- [x] **Password hashing** — `password_hash()` / `password_verify()`
- [x] **Session security** — HttpOnly, SameSite, secure flag on HTTPS, timeout
- [x] **File upload validation** — MIME type, extension, size, `getimagesize()` check
- [x] **Unique filenames** — Random hex prevents overwrite attacks
- [x] **Access control** — `requireAdmin()` on all admin pages
- [x] **Input validation** — Email, string length, rating range
- [x] **Path traversal** — Upload delete validates path within uploads directory
- [ ] **HTTPS** — Enable in production
- [ ] **Change default admin password** — Required after install
- [ ] **Environment variables** — Never commit `.env` to version control

## API Endpoints

| Endpoint        | Method | Description              |
|-----------------|--------|--------------------------|
| `/comments.php` | GET    | List approved comments   |
| `/comments.php` | POST   | Submit new comment       |
| `/reactions.php`| GET    | Get reaction counts      |
| `/reactions.php`| POST   | Submit reaction          |

Both POST endpoints require `csrf_token` in JSON body or form data.

## Troubleshooting

**Database connection failed**
- Verify `.env` credentials
- Ensure MySQL server is running
- Check database exists and user has privileges

**Images not displaying**
- Check `uploads/` folder permissions
- Verify image path in database matches filename in uploads folder

**404 on Vercel**
- Confirm `vercel.json` routes are configured
- Check deployment logs in Vercel dashboard

**CSRF token invalid**
- Ensure cookies are enabled
- Session must be active before form submission

## License

This project is provided for educational and portfolio purposes.
