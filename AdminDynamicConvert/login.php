<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

include "config.php";

$error = "";

if (isset($_POST['login'])) {

    $username = trim($_POST['username']);
    $password = trim($_POST['password']);


    /* =====================================================
       STEP 1 : CHECK SUPER ADMIN
       admin table
    ===================================================== */

    $query = "SELECT * FROM admin WHERE username = ?";

    $stmt = mysqli_prepare($conn, $query);

    if (!$stmt) {
        die("Query Error: " . mysqli_error($conn));
    }

    mysqli_stmt_bind_param($stmt, "s", $username);

    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);


    if (mysqli_num_rows($result) == 1) {

        $admin = mysqli_fetch_assoc($result);


        /* ================= CHECK PASSWORD ================= */

        if ($password === $admin['password']) {

            $_SESSION['admin'] = $admin['username'];
            $_SESSION['admin_type'] = "super_admin";

            header("Location: dashboard.php");
            exit();

        }

    }

    mysqli_stmt_close($stmt);


    /* =====================================================
       STEP 2 : CHECK CREATED ADMIN
       users table
    ===================================================== */

    $query = "SELECT * FROM users
              WHERE username = ?
              AND user_type = 'a'
              AND active = 1";

    $stmt = mysqli_prepare($conn, $query);

    if (!$stmt) {
        die("Query Error: " . mysqli_error($conn));
    }

    mysqli_stmt_bind_param($stmt, "s", $username);

    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);


    if (mysqli_num_rows($result) == 1) {

        $user = mysqli_fetch_assoc($result);


        /* ================= CHECK HASHED PASSWORD ================= */

        if (password_verify($password, $user['user_password'])) {

            $_SESSION['admin'] = $user['username'];
            $_SESSION['admin_name'] = $user['user_name'];
            $_SESSION['admin_email'] = $user['user_email'];
            $_SESSION['admin_type'] = "admin";
            $_SESSION['user_id'] = $user['user_id'];

            header("Location: dashboard.php");
            exit();

        }

    }


    mysqli_stmt_close($stmt);


    /* ================= INVALID LOGIN ================= */

    $error = "Invalid username or password.";

}

?>


<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Grocery Store - Admin Login</title>


    <style>

        * {
            box-sizing: border-box;
        }


        body {

            margin: 0;

            min-height: 100vh;

            font-family: Arial, sans-serif;

            background: #eef8f0;

            display: flex;

            justify-content: center;

            align-items: center;

        }


        .admin-login {

            width: 525px;

            padding: 55px 50px;

            background: white;

            border-radius: 25px;

            box-shadow: 0 10px 35px rgba(0,0,0,0.12);

        }


        .logo {

            text-align: center;

            color: #176b22;

            font-size: 36px;

            font-weight: bold;

            margin-bottom: 38px;

        }


        .logo span {

            font-size: 35px;

            margin-right: 10px;

        }


        h2 {

            margin: 0 0 5px;

            font-size: 25px;

        }


        label {

            display: block;

            font-size: 20px;

            font-weight: bold;

            margin-top: 20px;

            margin-bottom: 10px;

        }


        input {

            width: 100%;

            height: 60px;

            padding: 0 17px;

            font-size: 18px;

            border: 1px solid #ccc;

            border-radius: 13px;

            outline: none;

        }


        input:focus {

            border-color: #176b22;

        }


        button {

            width: 100%;

            height: 62px;

            margin-top: 25px;

            border: none;

            border-radius: 13px;

            background: #176b22;

            color: white;

            font-size: 23px;

            cursor: pointer;

        }


        button:hover {

            background: #12551b;

        }


        .error {

            margin-top: 15px;

            padding: 12px;

            text-align: center;

            color: #b00020;

            background: #ffe5e5;

            border-radius: 8px;

        }

    </style>

</head>


<body>


<div class="admin-login">


    <div class="logo">

        <span>🛒</span>Grocery Store

    </div>


    <h2>Admin Login</h2>


    <?php if ($error != "") { ?>

        <div class="error">

            <?php echo htmlspecialchars($error); ?>

        </div>

    <?php } ?>


    <form method="POST" autocomplete="off">


        <label>Username</label>

        <input
            type="text"
            name="username"
            placeholder="Enter Username"
            value=""
            autocomplete="off"
            required
        >


        <label>Password</label>

        <input
            type="password"
            name="password"
            placeholder="Enter Password"
            value=""
            autocomplete="new-password"
            required
        >


        <button type="submit" name="login">

            Login

        </button>


    </form>


</div>


</body>

</html>