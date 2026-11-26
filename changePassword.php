<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


/* =========================================================
   LOGIN CHECK
   ========================================================= */

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}


/* =========================================================
   DATABASE CONNECTION
   ========================================================= */

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


/* =========================================================
   GET LOGGED-IN USER
   ========================================================= */

$user_id = (int)$_SESSION["user_id"];

$stmt = $conn->prepare(
    "SELECT id, full_name, email, password
     FROM user_register
     WHERE id = ?
     LIMIT 1"
);

if (!$stmt) {
    die("Database Error: " . $conn->error);
}

$stmt->bind_param("i", $user_id);
$stmt->execute();

$result = $stmt->get_result();
$user = $result->fetch_assoc();

$stmt->close();


if (!$user) {
    die("User not found.");
}


/* =========================================================
   CHANGE PASSWORD
   ========================================================= */

$message = "";
$message_type = "";


if (isset($_POST["change_password"])) {

    $current_password = trim($_POST["current_password"] ?? "");
    $new_password = trim($_POST["new_password"] ?? "");
    $confirm_password = trim($_POST["confirm_password"] ?? "");


    /* =====================================================
       EMPTY CHECK
       ===================================================== */

    if (
        $current_password === "" ||
        $new_password === "" ||
        $confirm_password === ""
    ) {

        $message = "Please fill all password fields.";
        $message_type = "error";

    }


    /* =====================================================
       CURRENT PASSWORD CHECK
       ===================================================== */

    elseif (!password_verify($current_password, $user["password"])) {

        $message = "Current password is incorrect.";
        $message_type = "error";

    }


    /* =====================================================
       PASSWORD LENGTH CHECK
       ===================================================== */

    elseif (strlen($new_password) < 6) {

        $message = "New password must be at least 6 characters.";
        $message_type = "error";

    }


    /* =====================================================
       CONFIRM PASSWORD CHECK
       ===================================================== */

    elseif ($new_password !== $confirm_password) {

        $message = "New password and confirm password do not match.";
        $message_type = "error";

    }


    /* =====================================================
       SAME PASSWORD CHECK
       ===================================================== */

    elseif (password_verify($new_password, $user["password"])) {

        $message = "New password must be different from current password.";
        $message_type = "error";

    }


    /* =====================================================
       UPDATE PASSWORD
       ===================================================== */

    else {

        /* Create secure hashed password */
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

        // 1. user_register ટેબલ અપડેટ કરો
        $update = $conn->prepare("UPDATE user_register SET password = ? WHERE id = ?");
        if ($update) {
            $update->bind_param("si", $hashed_password, $user_id);
            $update->execute();
            $update->close();
        }

        // 2. users ટેબલ પણ અપડેટ કરો
        $update_users = $conn->prepare("UPDATE users SET user_password = ? WHERE user_email = ?");
        if ($update_users) {
            $update_users->bind_param("ss", $hashed_password, $user["email"]);
            $update_users->execute();
            $update_users->close();
        }

        $message = "Password changed successfully.";
        $message_type = "success";
        $user["password"] = $hashed_password;

    }

}
?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Change Password - Shreenath Grocery Store</title>


<style>

/* =========================================================
   RESET
   ========================================================= */

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: Arial, sans-serif;
}


/* =========================================================
   BODY
   ========================================================= */

body {
    background: #f5f7f6;
    min-height: 100vh;
}


/* =========================================================
   HEADER
   ========================================================= */

.header {
    background: #ffffff;
    height: 75px;

    display: flex;
    align-items: center;
    justify-content: space-between;

    padding: 0 7%;

    box-shadow: 0 2px 10px rgba(0,0,0,0.08);
}


/* LOGO */

.logo {
    font-size: 24px;
    font-weight: bold;
    color: #1b8f3a;
}


/* HOME BUTTON */

.home-btn {
    text-decoration: none;
    color: #1b8f3a;
    font-weight: 600;
    transition: 0.3s;
}


.home-btn:hover {
    color: #126b2a;
}


/* =========================================================
   MAIN
   ========================================================= */

.main {
    width: 100%;
    min-height: calc(100vh - 75px);

    display: flex;
    justify-content: center;
    align-items: center;

    padding: 40px 20px;
}


/* =========================================================
   CARD
   ========================================================= */

.card {
    width: 100%;
    max-width: 480px;

    background: #ffffff;

    padding: 40px;

    border-radius: 18px;

    box-shadow: 0 8px 30px rgba(0,0,0,0.10);
}


/* =========================================================
   ICON
   ========================================================= */

.icon {
    width: 65px;
    height: 65px;

    margin: 0 auto 20px;

    border-radius: 50%;

    background: #e8f7ed;

    display: flex;
    align-items: center;
    justify-content: center;

    font-size: 30px;
}


/* =========================================================
   TITLE
   ========================================================= */

.title {
    text-align: center;

    color: #222;

    font-size: 28px;

    margin-bottom: 8px;
}


/* =========================================================
   SUBTITLE
   ========================================================= */

.subtitle {
    text-align: center;

    color: #777;

    font-size: 14px;

    margin-bottom: 30px;
}


/* =========================================================
   USER INFO
   ========================================================= */

.user-info {
    background: #f5fbf7;

    border: 1px solid #dcefe2;

    border-radius: 10px;

    padding: 13px 15px;

    margin-bottom: 25px;

    color: #555;

    font-size: 14px;
}


.user-info strong {
    color: #1b8f3a;
}


/* =========================================================
   MESSAGE
   ========================================================= */

.message {
    padding: 13px 15px;

    border-radius: 8px;

    margin-bottom: 20px;

    font-size: 14px;
}


/* SUCCESS */

.message.success {
    background: #e8f7ed;

    color: #167532;

    border: 1px solid #bce3c7;
}


/* ERROR */

.message.error {
    background: #fff0f0;

    color: #c62828;

    border: 1px solid #f0baba;
}


/* =========================================================
   FORM GROUP
   ========================================================= */

.form-group {
    margin-bottom: 20px;
}


/* LABEL */

.form-group label {
    display: block;

    margin-bottom: 8px;

    color: #333;

    font-size: 14px;

    font-weight: 600;
}


/* INPUT */

.form-group input {
    width: 100%;

    height: 48px;

    border: 1px solid #d5d5d5;

    border-radius: 8px;

    padding: 0 14px;

    font-size: 15px;

    outline: none;

    transition: 0.3s;
}


/* INPUT FOCUS */

.form-group input:focus {
    border-color: #1b8f3a;

    box-shadow: 0 0 0 3px rgba(27,143,58,0.10);
}


/* =========================================================
   CHANGE BUTTON
   ========================================================= */

.change-btn {
    width: 100%;

    height: 50px;

    border: none;

    border-radius: 8px;

    background: #1b8f3a;

    color: white;

    font-size: 16px;

    font-weight: bold;

    cursor: pointer;

    transition: 0.3s;
}


/* BUTTON HOVER */

.change-btn:hover {
    background: #14752f;
}


/* =========================================================
   BACK LINK
   ========================================================= */

.back {
    display: block;

    text-align: center;

    margin-top: 20px;

    text-decoration: none;

    color: #1b8f3a;

    font-size: 14px;

    font-weight: 600;

    transition: 0.3s;
}


.back:hover {
    color: #126b2a;
}


/* =========================================================
   MOBILE
   ========================================================= */

@media (max-width: 600px) {

    .header {
        padding: 0 20px;
    }

    .logo {
        font-size: 18px;
    }

    .card {
        padding: 30px 22px;
    }

    .title {
        font-size: 24px;
    }

}

</style>

</head>


<body>


<!-- =========================================================
     HEADER
     ========================================================= -->

<div class="header">

    <div class="logo">
        🛒 Shreenath Grocery Store
    </div>

    <a href="index.php" class="home-btn">
        ← Back to Home
    </a>

</div>


<!-- =========================================================
     MAIN
     ========================================================= -->

<div class="main">

    <div class="card">


        <!-- ICON -->

        <div class="icon">
            🔐
        </div>


        <!-- TITLE -->

        <h1 class="title">
            Change Password
        </h1>


        <!-- SUBTITLE -->

        <p class="subtitle">
            Update your account password securely
        </p>


        <!-- USER -->

        <div class="user-info">

            Logged in as:

            <strong>
                <?php echo htmlspecialchars($user["full_name"]); ?>
            </strong>

        </div>


        <!-- MESSAGE -->

        <?php if ($message !== "") { ?>

            <div class="message <?php echo htmlspecialchars($message_type); ?>">

                <?php echo htmlspecialchars($message); ?>

            </div>

        <?php } ?>


        <!-- =================================================
             FORM
             ================================================= -->

        <form method="POST" action="">


            <!-- CURRENT PASSWORD -->

            <div class="form-group">

                <label for="current_password">
                    Current Password
                </label>

                <input
                    type="password"
                    id="current_password"
                    name="current_password"
                    placeholder="Enter current password"
                    required
                >

            </div>


            <!-- NEW PASSWORD -->

            <div class="form-group">

                <label for="new_password">
                    New Password
                </label>

                <input
                    type="password"
                    id="new_password"
                    name="new_password"
                    placeholder="Enter new password"
                    required
                >

            </div>


            <!-- CONFIRM PASSWORD -->

            <div class="form-group">

                <label for="confirm_password">
                    Confirm New Password
                </label>

                <input
                    type="password"
                    id="confirm_password"
                    name="confirm_password"
                    placeholder="Confirm new password"
                    required
                >

            </div>


            <!-- BUTTON -->

            <button
                type="submit"
                name="change_password"
                class="change-btn"
            >
                Change Password
            </button>


        </form>


        <!-- BACK -->

        <a href="index.php" class="back">
            ← Back to Home
        </a>


    </div>

</div>


</body>
</html>

<?php
if (isset($conn) && $conn instanceof mysqli) {
    $conn->close();
}
?>