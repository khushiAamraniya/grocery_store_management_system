<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


/* ================= CHECK LOGIN ================= */

if (!isset($_SESSION["user_id"]) && !isset($_SESSION["username"])) {
    header("Location: login.php");
    exit;
}


/* ================= DATABASE CONNECTION ================= */

$conn = new mysqli(
    "localhost",
    "root",
    "root",
    "grocery_store",
    3307
);

if ($conn->connect_error) {
    die("Database Connection Failed: " . $conn->connect_error);
}


/* ================= GET USER ================= */

$user_id = (int)($_SESSION["user_id"] ?? 0);

if ($user_id <= 0) {
    header("Location: login.php");
    exit;
}

$stmt = $conn->prepare(
    "SELECT id, full_name AS username, email, mobile_number FROM user_register WHERE id = ? LIMIT 1"
);

$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    session_destroy();
    header("Location: login.php");
    exit;
}

$user = $result->fetch_assoc();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
      content="width=device-width, initial-scale=1.0">

<title>My Profile - Shreenath Grocery Store</title>

<style>

/* ================= GENERAL ================= */

* {
    box-sizing: border-box;
}

body {
    margin: 0;
    font-family: Arial, sans-serif;
    background: #f5f7f5;
    color: #222;
}


/* ================= MAIN ================= */

.profile-wrapper {
    width: 100%;
    max-width: 1100px;
    margin: 45px auto;
    padding: 0 20px;
}


/* ================= PAGE TITLE ================= */

.page-heading {
    margin-bottom: 25px;
}

.page-heading h1 {
    margin: 0;
    color: #176b35;
    font-size: 30px;
}

.page-heading p {
    margin-top: 7px;
    color: #777;
    font-size: 15px;
}


/* ================= PROFILE LAYOUT ================= */

.profile-layout {
    display: grid;
    grid-template-columns: 280px 1fr;
    gap: 25px;
}


/* ================= SIDEBAR ================= */

.profile-sidebar {
    background: #ffffff;
    border-radius: 14px;
    padding: 25px 20px;
    box-shadow: 0 4px 18px rgba(0,0,0,0.08);
    height: fit-content;
}


/* ================= USER ================= */

.user-box {
    text-align: center;
    padding-bottom: 20px;
    border-bottom: 1px solid #eeeeee;
    margin-bottom: 15px;
}

.user-avatar {
    width: 85px;
    height: 85px;
    margin: 0 auto 12px;
    border-radius: 50%;
    background: #176b35;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 38px;
}

.user-box h3 {
    margin: 5px 0;
    color: #222;
}

.user-box p {
    margin: 0;
    color: #777;
    font-size: 14px;
}


/* ================= MENU ================= */

.profile-menu {
    display: flex;
    flex-direction: column;
    gap: 7px;
}

.profile-menu a {
    text-decoration: none;
    color: #333;
    padding: 13px 14px;
    border-radius: 8px;
    font-size: 15px;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 12px;
    transition: 0.2s;
}

.profile-menu a:hover {
    background: #eef7f0;
    color: #176b35;
}

.profile-menu a.active {
    background: #176b35;
    color: white;
}

.profile-menu a.logout {
    color: #d93025;
}

.profile-menu a.logout:hover {
    background: #fff0ef;
    color: #d93025;
}


/* ================= RIGHT CONTENT ================= */

.profile-content {
    display: flex;
    flex-direction: column;
    gap: 20px;
}


/* ================= ACCOUNT CARD ================= */

.account-card {
    background: #ffffff;
    border-radius: 14px;
    padding: 28px;
    box-shadow: 0 4px 18px rgba(0,0,0,0.08);
}

.card-title {
    margin: 0 0 22px;
    color: #176b35;
    font-size: 21px;
}


/* ================= INFORMATION ================= */

.info-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 18px;
}

.info-box {
    background: #f8f9f8;
    border: 1px solid #eeeeee;
    border-radius: 9px;
    padding: 17px;
}

.info-label {
    color: #777;
    font-size: 13px;
    margin-bottom: 7px;
}

.info-value {
    color: #222;
    font-size: 16px;
    font-weight: 600;
}


/* ================= QUICK ACTIONS ================= */

.action-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 15px;
}

.action-card {
    text-decoration: none;
    background: #ffffff;
    border-radius: 12px;
    padding: 22px 15px;
    text-align: center;
    box-shadow: 0 4px 15px rgba(0,0,0,0.07);
    transition: 0.2s;
}

.action-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 7px 20px rgba(0,0,0,0.12);
}

.action-icon {
    font-size: 30px;
    margin-bottom: 10px;
}

.action-card h3 {
    margin: 0;
    color: #222;
    font-size: 16px;
}

.action-card p {
    margin: 6px 0 0;
    color: #777;
    font-size: 13px;
}


/* ================= BACK HOME ================= */

.home-button {
    display: inline-block;
    text-decoration: none;
    background: #176b35;
    color: white;
    padding: 12px 22px;
    border-radius: 8px;
    font-weight: 600;
    margin-top: 5px;
}

.home-button:hover {
    background: #12552a;
}


/* ================= MOBILE ================= */

@media (max-width: 800px) {

    .profile-layout {
        grid-template-columns: 1fr;
    }

    .profile-sidebar {
        width: 100%;
    }

    .profile-menu {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
    }

}


@media (max-width: 550px) {

    .profile-wrapper {
        margin: 25px auto;
        padding: 0 12px;
    }

    .page-heading h1 {
        font-size: 25px;
    }

    .info-grid {
        grid-template-columns: 1fr;
    }

    .action-grid {
        grid-template-columns: 1fr;
    }

    .profile-menu {
        grid-template-columns: 1fr;
    }

    .account-card {
        padding: 20px;
    }

}

</style>

</head>


<body>


<div class="profile-wrapper">


    <!-- ================= PAGE HEADING ================= -->

    <div class="page-heading">

        <h1>My Profile</h1>

        <p>
            Manage your account and shopping activities
        </p>

    </div>



    <div class="profile-layout">


        <!-- ================= SIDEBAR ================= -->

        <div class="profile-sidebar">


            <div class="user-box">

                <div class="user-avatar">
                    👤
                </div>

                <h3>
                    <?php echo htmlspecialchars($user["username"]); ?>
                </h3>

                <p>
                    Grocery Store Customer
                </p>

            </div>



            <div class="profile-menu">


                <a href="profile.php" class="active">
                    👤
                    My Profile
                </a>


                <a href="orders.php">
                    📦
                    My Orders
                </a>


                <a href="cart.php">
                    🛒
                    My Cart
                </a>


                <a href="changePassword.php">
                    🔐
                    Change Password
                </a>


                <a href="index.php">
                    🏠
                    Home
                </a>


                <a href="logout.php" class="logout">
                    🚪
                    Logout
                </a>


            </div>

        </div>



        <!-- ================= RIGHT CONTENT ================= -->

        <div class="profile-content">


            <!-- ================= ACCOUNT INFORMATION ================= -->

            <div class="account-card">

                <h2 class="card-title">
                    Account Information
                </h2>


                <div class="info-grid">


                    <div class="info-box">

                        <div class="info-label">
                            Username
                        </div>

                        <div class="info-value">
                            <?php echo htmlspecialchars($user["username"]); ?>
                        </div>

                    </div>



                    <div class="info-box">

                        <div class="info-label">
                            Account Type
                        </div>

                        <div class="info-value">
                            Customer
                        </div>

                    </div>



                    <div class="info-box">

                        <div class="info-label">
                            Account Status
                        </div>

                        <div class="info-value">
                            Active
                        </div>

                    </div>



                    <div class="info-box">

                        <div class="info-label">
                            Store
                        </div>

                        <div class="info-value">
                            Shreenath Grocery Store
                        </div>

                    </div>


                </div>

            </div>



            <!-- ================= QUICK ACTIONS ================= -->

            <div class="account-card">

                <h2 class="card-title">
                    Quick Actions
                </h2>


                <div class="action-grid">


                    <a href="orders.php" class="action-card">

                        <div class="action-icon">
                            📦
                        </div>

                        <h3>
                            My Orders
                        </h3>

                        <p>
                            View your orders
                        </p>

                    </a>



                    <a href="cart.php" class="action-card">

                        <div class="action-icon">
                            🛒
                        </div>

                        <h3>
                            My Cart
                        </h3>

                        <p>
                            View cart items
                        </p>

                    </a>



                    <a href="changePassword.php" class="action-card">

                        <div class="action-icon">
                            🔐
                        </div>

                        <h3>
                            Change Password
                        </h3>

                        <p>
                            Update your password
                        </p>

                    </a>


                </div>

            </div>



            <!-- ================= HOME ================= -->

            <div>

                <a href="index.php" class="home-button">
                    ← Continue Shopping
                </a>

            </div>


        </div>

    </div>

</div>


</body>

</html>


<?php
$conn->close();
?>