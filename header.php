<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Shreenath Grocery Store</title>

<link rel="stylesheet" href="style.css">

<link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>

/* ================= HEADER ================= */

header {
    width: 100%;
    min-height: 80px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 15px 6%;
    background: #176b35;
}


/* ================= LOGO ================= */

.logo {
    margin: 0;
}

.logo a {
    display: flex;
    align-items: center;
    gap: 10px;
    color: #fff;
    text-decoration: none;
    font-size: 30px;
    font-weight: 700;
}

.logo a:hover,
.logo a:focus,
.logo a:active,
.logo a:visited {
    color: #fff;
    text-decoration: none;
}

.logo i {
    font-size: 30px;
    color: #fff;
}

.logo h2 {
    margin: 0;
    color: #fff;
    font-size: 25px;
    font-weight: 700;
}


/* ================= NAVIGATION ================= */

nav ul {
    list-style: none;
    display: flex;
    align-items: center;
    gap: 30px;
    margin: 0;
    padding: 0;
}

nav ul li {
    margin: 0;
    padding: 0;
}

nav ul li a {
    color: #fff;
    text-decoration: none;
    font-size: 18px;
    font-weight: 600;
    transition: .2s;
}

nav ul li a:hover,
nav ul li a:focus,
nav ul li a:active,
nav ul li a:visited {
    color: #fff;
    text-decoration: none;
}


/* ================= BUTTON AREA ================= */

.btn {
    display: flex;
    align-items: center;
    gap: 15px;
}

.btn a {
    text-decoration: none;
    font-size: 16px;
    font-weight: 600;
    padding: 9px 18px;
    border-radius: 7px;
}


/* ================= CART ================= */

.cart-link {
    color: #fff;
    text-decoration: none;
    font-size: 16px;
    font-weight: 600;
    padding: 9px 5px !important;
}

.cart-link:hover,
.cart-link:focus,
.cart-link:active,
.cart-link:visited {
    color: #fff;
    text-decoration: none;
}


/* ================= LOGIN ================= */

.login {
    color: #fff;
    border: 1px solid #fff;
}

.login:hover,
.login:focus,
.login:active,
.login:visited {
    color: #fff;
    text-decoration: none;
}


/* ================= REGISTER ================= */

.register {
    background: #fff;
    color: #176b35;
}

.register:hover,
.register:focus,
.register:active,
.register:visited {
    background: #fff;
    color: #176b35;
    text-decoration: none;
}


/* ================= WELCOME USER ================= */

.welcome-user {
    color: #fff;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    border: none;
    background: transparent;
    padding: 0;
}

.welcome-user i {
    font-size: 11px;
    margin-left: 5px;
}


/* ================= DROPDOWN ================= */

.user-dropdown {
    position: relative;
}

.dropdown-menu {
    display: none;
    position: absolute;
    top: 35px;
    right: 0;
    width: 200px;
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 8px 20px rgba(0,0,0,.25);
    overflow: hidden;
    z-index: 9999;
}

.dropdown-menu.show {
    display: block;
}

.dropdown-menu a {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 13px 16px;
    color: #333 !important;
    text-decoration: none;
    font-size: 15px;
    font-weight: 600;
    border: none !important;
    border-radius: 0 !important;
}

.dropdown-menu a:hover {
    background: #f2f2f2;
    color: #176b35 !important;
}

.dropdown-menu a i {
    width: 20px;
    color: #176b35;
    font-size: 16px;
}


/* ================= MOBILE ================= */

@media(max-width:650px) {

    header {
        padding: 12px 4%;
    }

    .logo i {
        font-size: 23px;
    }

    .logo h2 {
        font-size: 18px;
    }

    nav ul {
        gap: 15px;
    }

    nav ul li a {
        font-size: 14px;
    }

    .btn {
        gap: 5px;
    }

    .btn a {
        padding: 7px 10px;
        font-size: 13px;
    }

    .welcome-user {
        font-size: 13px;
    }

    .cart-link {
        font-size: 13px;
    }

    .dropdown-menu {
        width: 180px;
    }

}

</style>

</head>


<body>


<!-- ================= HEADER ================= -->

<header>


    <!-- LOGO -->

    <div class="logo">

        <a href="index.php">

            <i class="fa-solid fa-cart-shopping"></i>

            <h2>Shreenath Grocery Store</h2>

        </a>

    </div>


    <!-- ================= NAVIGATION ================= -->

    <nav>

        <ul>

            <li>
                <a href="index.php#home">
                    Home
                </a>
            </li>

            <li>
                <a href="categories.php">
                    Categories
                </a>
            </li>

            <li>
                <a href="index.php#about">
                    About Us
                </a>
            </li>

            <li>
                <a href="index.php#contact">
                    Contact Us
                </a>
            </li>

        </ul>

    </nav>


    <!-- ================= CART + USER ================= -->

    <div class="btn">


        <!-- CART -->

        <a href="cart.php" class="cart-link">

            <i class="fa-solid fa-cart-shopping"></i>

            Cart

        </a>


        <?php if (isset($_SESSION["user_id"])) { ?>


            <!-- USER DROPDOWN -->

            <div class="user-dropdown">

                <button
                    type="button"
                    class="welcome-user"
                    id="welcomeButton"
                >

                    Welcome

                    <?php
                    echo htmlspecialchars($_SESSION["username"]);
                    ?>

                    <i class="fa-solid fa-chevron-down"></i>

                </button>


                <div
                    class="dropdown-menu"
                    id="dropdownMenu"
                >

                    <a href="profile.php">

                        <i class="fa-solid fa-user"></i>

                        <span>Your Profile</span>

                    </a>


                    <a href="orders.php">

                        <i class="fa-solid fa-box"></i>

                        <span>Your Orders</span>

                    </a>


                    <a href="changePassword.php">

                        <i class="fa-solid fa-lock"></i>

                        <span>Change Password</span>

                    </a>

                </div>

            </div>


            <!-- LOGOUT -->

            <a href="logout.php" class="login">

                Logout

            </a>


        <?php } else { ?>


            <!-- LOGIN -->

            <a href="login.php" class="login">

                Login

            </a>


            <!-- REGISTER -->

            <a href="register.php" class="register">

                Register

            </a>


        <?php } ?>


    </div>


</header>


<!-- ================= DROPDOWN JAVASCRIPT ================= -->

<script>

const welcomeButton =
    document.getElementById("welcomeButton");

const dropdownMenu =
    document.getElementById("dropdownMenu");


if (welcomeButton && dropdownMenu) {

    welcomeButton.addEventListener(
        "click",
        function(event) {

            event.stopPropagation();

            dropdownMenu.classList.toggle("show");

        }
    );


    document.addEventListener(
        "click",
        function(event) {

            if (
                !dropdownMenu.contains(event.target) &&
                !welcomeButton.contains(event.target)
            ) {

                dropdownMenu.classList.remove("show");

            }

        }
    );

}

</script>


