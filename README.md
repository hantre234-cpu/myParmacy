# 💊 MyParmacy - Pharmacy Management System

> A comprehensive full-stack pharmacy management website designed for efficient inventory, sales, and customer management.

[![PHP](https://img.shields.io/badge/PHP-777BB4?style=flat&logo=php&logoColor=white)](https://www.php.net/)
[![MySQL](https://img.shields.io/badge/MySQL-4479A1?style=flat&logo=mysql&logoColor=white)](https://www.mysql.com/)
[![JavaScript](https://img.shields.io/badge/JavaScript-F7DF1E?style=flat&logo=javascript&logoColor=black)](https://developer.mozilla.org/en-US/docs/Web/JavaScript)
[![License](https://img.shields.io/badge/License-MIT-green.svg)](LICENSE)

---

## 📋 Table of Contents

- [Overview](#-project-overview)
- [Features](#-features)
- [Technologies](#-technologies-used)
- [Project Structure](#-project-structure)
- [Prerequisites](#-prerequisites)
- [Installation](#-installation-guide)
- [Usage](#-usage)
- [Contributing](#-contributing)
- [License](#-license)

---

## 🎯 Project Overview

MyParmacy is a robust full-stack web application that simulates a modern online pharmacy management system. Developed as a final project for a Web Development course, it demonstrates professional-grade implementation of both client-side and server-side technologies.

The system enables:
- Inventory Management: Track medication stock and details
- Sales Processing: Handle customer transactions efficiently
- User Roles: Separate dashboards for sellers and cashiers
- Database Integration: Secure data storage and retrieval with MySQL

This project showcases practical skills in web application architecture, database design, and business logic implementation.

---

## ✨ Features

### Core Functionality
- 📦 Inventory Management System - Track medicines, stock levels, and expiration dates
- 🛒 Point of Sale (POS) - Process customer transactions with a user-friendly cashier interface
- 👥 Multi-User System - Separate roles for administrators, sellers, and cashiers
- 📊 Dashboard & Analytics - View sales history and inventory reports
- 🔍 Search & Filter - Quickly find medicines by name, category, or ID

### Design & UX
- 📱 Responsive Design - Seamless experience across desktop and mobile devices
- 🎨 Modern User Interface - Clean, intuitive design for better usability
- ⚡ Dynamic Pages - Interactive components powered by JavaScript and PHP
- 🔐 Input Validation - Secure data handling on client and server sides

### Technical Features
- 🗄️ Database Connectivity - Efficient MySQL operations with structured queries
- 📝 SQL Optimization - Well-structured queries for data integrity
- 🏗️ Modular Architecture - Organized code structure for maintainability
- 🔄 Session Management - User authentication and session handling

---

## 🛠️ Technologies Used

### Frontend
| Technology | Purpose |
|------------|---------|
| HTML5 | Semantic markup and structure |
| CSS3 | Styling and responsive layouts |
| JavaScript | Interactive components and client-side validation |

### Backend
| Technology | Purpose |
|------------|---------|
| PHP | Server-side logic and application processing |
| MySQL | Relational database management |
| SQL | Data querying and manipulation |

### Development Tools
| Tool | Usage |
|------|-------|
| XAMPP | Local development server |
| Apache Server | Web server hosting |
| phpMyAdmin | Database management interface |
| Git & GitHub | Version control and repository hosting |

---

## 📂 Project Structure

`
myParmacy/
│
├── 📁 assets/                 # Static resources (images, icons, etc.)
│
├── 📁 config/                 # Configuration files and database connection
│   └── db_connection.php       # Database connection setup
│
├── 📁 includes/               # Reusable PHP components
│   ├── header.php
│   ├── footer.php
│   └── navigation.php
│
├── 📁 pages/                  # Main application pages
│   ├── home.php
│   ├── products.php
│   └── checkout.php
│
├── 📁 cashier/                # Cashier module
│   ├── dashboard.php
│   ├── process_sale.php
│   └── sales_history.php
│
├── 📁 seller/                 # Seller/Admin module
│   ├── inventory.php
│   ├── add_medicine.php
│   ├── edit_medicine.php
│   └── reports.php
│
├── 📁 medicine/               # Medicine management
│   ├── medicine_list.php
│   ├── medicine_details.php
│   └── category.php
│
├── 📄 index.php               # Main entry point
├── 📄 database.sql            # Database schema and initial data
├── 📄 setup.sql               # Setup script for tables
└── 📄 README.md               # This file

---

## ⚙️ Prerequisites

Before you begin, ensure you have the following installed:

- **PHP 7.4+** - Server-side scripting language
- **MySQL 5.7+** - Relational database management system
- **Apache Server** - Web server (included with XAMPP)
- **Git** - Version control system
- **Web Browser** - Modern browser (Chrome, Firefox, Safari, Edge)

### Recommended Software
- **XAMPP** - All-in-one development environment
- **phpMyAdmin** - Web-based database administration tool
- **Visual Studio Code** - Code editor

---

## 📥 Installation Guide

### Step 1: Clone the Repository
bash
git clone https://github.com/hantre234-cpu/myParmacy.git
cd myParmacy

### Step 2: Set Up Development Environment

#### Using XAMPP:
1. Download and install [XAMPP](https://www.apachefriends.org/)
2. Start Apache and MySQL from the XAMPP Control Panel
3. Copy the `myParmacy` folder to `C:\xampp\htdocs\` (Windows) or `/Applications/XAMPP/htdocs/` (Mac)

### Step 3: Configure Database

1. Open **phpMyAdmin** in your browser
   
   http://localhost/phpmyadmin
  

2. Create a new database called `myParmacy`

3. Import the database schema:
   - Click on your `myParmacy` database
   - Go to **Import** tab
   - Select `setup.sql` and click **Go**
   - Then import `database.sql` for initial data

### Step 4: Update Database Connection (if needed)

Edit `/config/db_connection.php`:
php
<?php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'myParmacy');
?>

### Step 5: Access the Application

Open your browser and navigate to:
http://localhost/myParmacy/
`

---

## 🚀 Usage

### For Customers/General Users
1. Navigate to the homepage
2. Browse available medicines
3. Search for specific products
4. Add items to cart
5. Proceed to checkout

### For Cashiers
1. Login with cashier credentials
2. Process customer sales
3. View transaction history
4. Generate receipts

### For Sellers/Administrators
1. Login with admin credentials
2. Manage medication inventory
3. Add or update medicines
4. Remove expired or sold-out items
5. Generate sales and inventory reports

---

## 🎓 Key Learning Outcomes

This project demonstrates:
- ✅ Full-stack web development capabilities
- ✅ Database design and SQL optimization
- ✅ PHP backend development and MVC concepts
- ✅ HTML/CSS/JavaScript frontend development
- ✅ User authentication and session management
- ✅ Real-world business logic implementation
- ✅ Git version control and best practices

---

## 📈 Future Enhancements

Potential features for future versions:
- 🔐 Enhanced authentication with password hashing (bcrypt)
- 📧 Email notifications for orders and inventory alerts
- 📱 Mobile app development
- 💳 Integration with payment gateways
- 📊 Advanced analytics and reporting dashboard
- 🌍 Multi-language support
- 🔍 Barcode/QR code scanning
- 📦 Integration with delivery management systems

---

## 🤝 Contributing

Contributions are welcome! Here's how you can help:

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request
6. Please ensure your code follows these practices:
 - Clean, readable code
 - Proper comments and documentation
 - SQL injection prevention
 - XSS attack prevention

---

## 📝 License

This project is licensed under the MIT License - see the LICENSE file for details.

---

## 📧 Contact & Support

- GitHub: [hantre234-cpu](https://github.com/hantre234-cpu)
- Project URL: [MyParmacy Repository](https://github.com/hantre234-cpu/myParmacy)

For questions or support, feel free to open an issue on GitHub.

---

## 🙏 Acknowledgments

- Inspiration from real-world pharmacy management systems
- Web Development course instructors and peers
- The open-source community for tools and resources

---

Built with ❤️ as a Web Development Course Project

*Last Updated: May 2026*
