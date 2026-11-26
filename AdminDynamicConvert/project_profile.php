<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Project Profile</title>

<link rel="stylesheet" href="admin.css">

<link
    rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"
>

<style>

.profile-box {
    margin: 30px;
    background: #fff;
    padding: 30px;
    border-radius: 12px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.08);
}

.profile-box h1 {
    color: #2e7d32;
    margin-bottom: 25px;
}

.profile-box p {
    margin: 14px 0;
    color: #555;
    font-size: 15px;
}

.profile-box strong {
    color: #333;
    display: inline-block;
    width: 130px;
}

.back {
    display: inline-block;
    margin-top: 20px;
    text-decoration: none;
    color: #2e7d32;
    font-weight: 600;
}

</style>

</head>

<body>

<div class="container">

    <?php include 'sidebar.php'; ?>

    <main class="main">

        <header class="topbar">

            <div>
                <h2>Project Profile</h2>
                <p>Grocery Store Management System</p>
            </div>

            <div class="project-profile">
                <a href="dashboard.php">
                    <i class="fa-solid fa-house"></i>
                    Dashboard
                </a>
            </div>

        </header>


        <div class="profile-box">

            <h1>
                <i class="fa-solid fa-cart-shopping"></i>
                Shreenath Grocery Store
            </h1>

            <p>
                <strong>Project Name:</strong>
                Grocery Store Management System
            </p>

            <p>
                <strong>Developer:</strong>
                Khushi Amraniya
            </p>

            <p>
                <strong>Technology:</strong>
                HTML, CSS, JavaScript, PHP, MySQL
            </p>

            <p>
                <strong>Database:</strong>
                grocery_store
            </p>

            <p>
                <strong>Project Type:</strong>
                BCA Academic Project
            </p>

            <p>
                <strong>Modules:</strong>
                Products, Categories, Cart, Orders, Customers, Admin
            </p>

            <a href="dashboard.php" class="back">
                <i class="fa-solid fa-arrow-left"></i>
                Back to Dashboard
            </a>

        </div>

    </main>

</div>

<script src="admin.js"></script>

</body>

</html>