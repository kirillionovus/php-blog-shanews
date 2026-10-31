<div align="center">

# $HANEWS 📰
### A Modern, Editorial-Style PHP News Portal & Content Management System

[![PHP Version](https://img.shields.io/badge/PHP-%5E8.0-777BB4?style=flat-square&logo=php&logoColor=white)](https://www.php.net/)
[![Tailwind CSS](https://img.shields.io/badge/Tailwind_CSS-v4-38BDF8?style=flat-square&logo=tailwindcss&logoColor=white)](https://tailwindcss.com/)
[![MySQL](https://img.shields.io/badge/MySQL-Database-4479A1?style=flat-square&logo=mysql&logoColor=white)](https://www.mysql.com/)
[![License](https://img.shields.io/badge/License-MIT-green.svg?style=flat-square)](LICENSE)

*An elegant, high-performance web news application inspired by modern editorial layouts, featuring rich-text editing, photo essays, user profiles, interactive comments, and a comprehensive admin control panel.*

</div>

---

## ✨ Features

- **Editorial Design System**: Clean typography (`Newsreader` serif and modern sans-serif fonts) built entirely with Tailwind CSS v4.
- **Advanced Admin Panel**: 
  - Create, edit, and manage articles across multiple categories (`World`, `Business`, `Technology`, `Science`, `Culture`, `Sport`).
  - Rich-text editing powered by **Quill.js**.
  - Cover image uploads and dynamic multi-image **Photo Essay** galleries (with management/deletion constraints).
- **Interactive Reader Experience**:
  - Full-text news viewing with structured headings, blockquotes, and image modals.
  - Real-time or asynchronous **Like/Dislike reactions** via vanilla JavaScript fetch handlers.
  - Dynamic user comment threads with relative time tags.
- **User Authentication & Authorization**:
  - Secure registration, login, and password hashing (`password_hash`).
  - Session and cookie-based persistent login ("Remember Me" tokens).
  - Role-based access control (`admin` vs. `user`) with protected routing.
- **Search & Filtering**: Live search capabilities and category/sorting filters (newest/oldest first).

---

## 🛠️ Tech Stack

- **Backend**: Native PHP (8.x), PDO (PHP Data Objects) for database interactions.
- **Database**: MySQL / MariaDB (`database.sql` schema provided).
- **Frontend**: HTML5, Tailwind CSS v4 (via CDN script).
- **Libraries & Plugins**: 
  - [Quill.js](https://quilljs.com/) (v2.0.3) for rich-text article authoring.
  - Google Fonts (`Newsreader`, `Raleway`, `Inter`).

---

## 📁 Project Structure

```text
php-blog-shanews/
├── config/
│   └── db.php             # Database connection configuration (PDO)
├── assets/
│   ├── js/                # Client-side scripts
│   └── images/            # Static imagery & background placeholders
├── uploads/               # User and admin uploaded media files
├── admin_add_post.php     # Admin panel: create new stories
├── admin_edit.php         # Admin panel: update or delete existing stories
├── admin_posts.php        # Admin panel: dashboard list of all articles
├── index.php              # Front page, category feeds, and sorting logic
├── login.php              # User authentication & remember-me logic
├── register.php           # New user registration handler
├── logout.php             # Session destruction & cookie clearing
├── post.php               # Full article view, comments & photo essay gallery
├── profile.php            # User account details & activity stats
├── profile_password.php   # Security settings & password updates
├── like_handler.php       # Asynchronous API endpoint for likes/dislikes
├── search_handler.php     # Search query processor
└── database.sql           # Complete MySQL database dump
```

---

## 🚀 Getting Started

### Prerequisites

Make sure you have a local server environment set up (such as **XAMPP**, **WAMP**, or **Laragon**) running:
- **PHP** >= 8.0
- **MySQL** / **MariaDB**

### Installation

1. **Clone the repository:**
   ```bash
   git clone https://github.com/kirillionovus/php-blog-shanews.git
   ```

2. **Move the project folder** into your web server's root directory (e.g., `htdocs` in XAMPP).

3. **Import the Database:**
   - Open phpMyAdmin or your preferred MySQL client.
   - Create a new database named `php_blog_db` (or update your configuration database name).
   - Import the provided `database.sql` file into your database.

4. **Configure Database Connection:**
   - Open `config/db.php` and verify your database credentials (host, username, password, db name).

5. **Run the Application:**
   - Open your browser and navigate to:
     ```text
     http://localhost/php-blog-shanews/index.php
     ```

---

## 👥 Default Credentials

For testing administrative and user features immediately, you can use the pre-configured accounts included in the database dump:

- **Admin Account**: 
  - *Username*: `Admin`
  - *Password*: `Admin`
- **Standard User**: 
  - *Username*: `User`
  - *Password*: `User`

---

## 📄 License

Distributed under the **MIT License**. See `LICENSE` for more information.

