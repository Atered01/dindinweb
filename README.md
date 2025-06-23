# DinDin Verde - Sustainable Cashback Platform

DinDin Verde (which translates to "Green Money") is a full-stack web application designed to encourage and gamify the recycling process. The platform allows users to get rewarded with a digital currency, "DDV", for correctly discarding recyclable packaging. It features a complete ecosystem with a user-facing platform, an AI-powered classification tool, and a comprehensive admin panel for management and analytics.

---

## ✨ Key Features

### For Users
* **Secure Authentication**: Complete user registration, login, and password recovery system (using email-sent verification codes).
* **Persistent Login**: A secure "Keep me logged in" functionality using long-lived cookies and secure tokens.
* **User Dashboard (`perfil.php`)**:
    * View and update personal information, including profile picture upload.
    * Track environmental impact statistics (CO₂ saved, water and energy conserved).
    * Gamification system with user levels (e.g., Bronze, Silver, Gold) and a progress bar.
    * Manage and view "DinDin Verde" (DDV) balance.
* **Rewards System**:
    * A rewards "store" (`recompensas.php`) where users can redeem their DDV points for real-world vouchers.
    * A reward details page (`recompensa_detalhe.php`) with a full image gallery for each reward.
    * A personal "My Vouchers" section on the profile page to view redeemed rewards.
* **AI-Powered Classification (`classificar.php`)**:
    * Uses the device's webcam to identify different types of packaging in real-time.
    * Powered by a custom-trained **Teachable Machine** & **TensorFlow.js** model.
    * Allows users to "discard" a recognized item to earn DDV points and update their environmental stats.
* **Collection Point Locator (`pontos_coleta.php`)**:
    * Displays a fixed, official ecopoint on an embedded Google Map.
    * Includes a feature to calculate the straight-line distance from the user's location (obtained via CEP lookup) to the ecopoint.

### For Administrators
* **Secure Admin Dashboard (`admin_home.php`)**:
    * Protected area accessible only to admin users.
    * Displays key performance indicators (KPIs) like total users, total CO₂ avoided, and total DDV in circulation.
    * Visual charts and graphs (using Chart.js) for:
        * New user registrations over time.
        * Top 10 users by items recycled.
        * Most popular rewards redeemed.
* **Full User Management (CRUD)**:
    * View, edit, and delete users.
    * Promote regular users to administrators (and demote them).
    * Reset a user's password.
    * All critical actions are protected by an additional admin password confirmation step.
* **Rewards Management (`gerenciar_recompensas.php`)**:
    * An interface to add new rewards to the platform.
    * Includes a file upload system to add custom images for each reward.
* **B2B Dashboard Concept**: A planned-out conceptual dashboard for partner companies to track the impact of their own products within the ecosystem.

---

## 🛠️ Tech Stack

* **Backend**: PHP 8+ (with PDO for database connections)
* **Frontend**: HTML5, CSS3 (with CSS Variables for theming), JavaScript (ES6+)
* **Database**: MySQL
* **Key Libraries**:
    * [Chart.js](https://www.chartjs.org/) for data visualization in the admin panel.
    * [TensorFlow.js](https://www.tensorflow.org/js) & [Teachable Machine](https://teachablemachine.withgoogle.com/) for the AI classification module.

---

## 🚀 Getting Started

To get a local copy up and running, follow these simple steps.

### Prerequisites
You need a local server environment like XAMPP, WAMP, or MAMP, which includes:
* Apache
* MySQL / MariaDB
* PHP

### Installation
1.  **Clone the repository** or place the project files in your server's root directory (e.g., `htdocs` for XAMPP).

2.  **Database Setup**:
    * Create a new database in your MySQL admin panel (e.g., phpMyAdmin) named `embalagens_db`.
    * Import the `.sql` file provided with the project to create all the necessary tables and sample data.

3.  **Configuration**:
    * Open the file `Dindinweb/PHP/config.php`.
    * Update the database credentials to match your local environment:
        ```php
        $db_name = 'embalagens_db';
        $db_host = "localhost";
        $db_user = "root";
        $db_pass = ''; // Or your password
        ```
    * Ensure the `BASE_URL` constant is correct for your local setup (e.g., `http://localhost/Dindinweb`).

4.  **Teachable Machine Model**:
    * The AI classifier requires model files. Train your own image model at [Teachable Machine](https://teachablemachine.withgoogle.com/).
    * Export your model in **Tensorflow.js** format and download the files.
    * Place the three resulting files (`model.json`, `metadata.json`, `weights.bin`) into the `Dindinweb/my_model/` directory.

5.  **Run the Application**: Open your browser and navigate to your project's `BASE_URL` (e.g., `http://localhost/Dindinweb`).

---

## 📁 Project Structure

The project follows a standard structure for PHP applications: