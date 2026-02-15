# Blog Management System
This project is created as part of my Task 2 of Web Development Internship at ApexPlanet Software Pvt Ltd.
A complete CRUD (Create, Read, Update, Delete) web application with user authentication for managing blog posts. Built with PHP, MySQL, and modern CSS.

## Tools Used:
- XAMPP (Apache, MySQL)
- PHP
- HTML
- CSS
- Notepad
- PHPMyAdmin
- GitHub

## 📁 Project Structure
- index.php : Homepage - displays all posts
- register.php : User registration
- login.php : User login
- logout.php : Logout and session destroy
- dashboard.php : User dashboard after login
- create.php : Create new post
- edit.php : Edit existing post
- delete.php : Delete post
- style.css : Main stylesheet
- README.md : Project documentation
- uploads/ : Folder for uploaded files

## 🌟 Features:

# 🔐 User Authentication
- User registration with password hashing
- Secure login system with session management
- Protected routes for authenticated users only
- Logout functionality

# 📝 Blog Post Management (CRUD):
- Create: Add new blog posts with titles, content, and file attachments
- Read: View all blog posts in an elegant grid layout
- Update: Edit existing posts and attached files
- Delete: Remove posts with confirmation

# 📁 File Upload System:
- Upload images, PDFs, and documents with posts
- Automatic file type validation (jpg, png, gif, pdf, doc, docx, txt)
- File size limit: 5MB
- Unique filenames to prevent conflicts
- Download links for attached files