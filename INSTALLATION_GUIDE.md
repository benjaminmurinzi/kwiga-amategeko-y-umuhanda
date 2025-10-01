# 🚀 Traffic Learning Platform - Installation Guide

## Prerequisites

Before you start, make sure you have:

1. **Web Server** (Apache or Nginx)
2. **PHP 7.4+** with the following extensions:
   - PDO
   - PDO_MySQL
   - mbstring
   - OpenSSL
3. **MySQL 5.7+** or **MariaDB 10.2+**
4. **Composer** (optional, for future dependencies)

## Step-by-Step Installation

### Step 1: Download and Extract Files

1. Create a folder for your project:
   ```bash
   mkdir traffic-learning-platform
   cd traffic-learning-platform
   ```

2. Create the directory structure as shown in the project structure artifact

### Step 2: Database Setup

1. **Open phpMyAdmin** or MySQL command line

2. **Create the database:**
   ```sql
   CREATE DATABASE traffic_learning CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```

3. **Import the database schema:**
   - Copy the entire SQL from the "Database Schema SQL" artifact
   - Paste and execute it in phpMyAdmin or command line:
   ```bash
   mysql -u root -p traffic_learning < sql/database.sql
   ```

4. **Verify the database:**
   - Check that all tables are created
   - You should see 18 tables including users, lessons, quizzes, etc.

### Step 3: Configuration

1. **Edit `config/database.php`:**
   ```php
   define('DB_HOST', 'localhost');        // Your database host
   define('DB_NAME', 'traffic_learning'); // Your database name
   define('DB_USER', 'root');             // Your database username
   define('DB_PASS', '');                 // Your database password
   ```

2. **Edit `config/config.php`:**
   ```php
   // Update the site URL to match your setup
   define('SITE_URL', 'http://localhost/traffic-learning-platform');
   
   // For production, set these:
   error_reporting(0);
   ini_set('display_errors', 0);
   ```

3. **Create required directories:**
   ```bash
   mkdir uploads
   mkdir uploads/lessons
   mkdir uploads/quizzes
   mkdir uploads/profiles
   mkdir logs
   chmod 755 uploads
   chmod 755 logs
   ```

### Step 4: File Structure Setup

Create all the PHP files in their respective directories as shown in the artifacts:

```
traffic-learning-platform/
├── config/
│   ├── database.php    ← Created
│   ├── config.php      ← Created
│   └── language.php
├── includes/
│   ├── functions.php   ← Created
│   └── session.php     ← Created
├── classes/
│   ├── Database.php    (included in database.php)
│   ├── User.php        ← Created
│   ├── Lesson.php      (to be created)
│   ├── Quiz.php        (to be created)
│   └── ...
├──
