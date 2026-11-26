<?php

// ======================================================
// CURRENT PAGE
// ======================================================

$current_page = basename($_SERVER['PHP_SELF']);

?>

<aside class="sidebar">


    <!-- ==================================================
         LOGO
         ================================================== -->

    <div class="logo">

        <i class="fa-solid fa-cart-shopping"></i>

        <h2>
            Shreenath Grocery
        </h2>

        <span>
            Admin Panel
        </span>

    </div>



    <!-- ==================================================
         MENU
         ================================================== -->

    <ul>


        <!-- ==================================================
             DASHBOARD
             ================================================== -->

        <li class="<?php
            echo ($current_page == 'dashboard.php')
                ? 'active'
                : '';
        ?>">

            <i class="fa-solid fa-house"></i>

            <a href="dashboard.php">
                Dashboard
            </a>

        </li>



        <!-- ==================================================
             USERS
             ================================================== -->

        <li class="<?php
            echo ($current_page == 'users.php')
                ? 'active'
                : '';
        ?>">

            <i class="fa-solid fa-user"></i>

            <a href="users.php">
                Users
            </a>

        </li>



        <!-- ==================================================
             CATEGORIES
             ================================================== -->

        <li class="<?php
            echo ($current_page == 'categories.php')
                ? 'active'
                : '';
        ?>">

            <i class="fa-solid fa-layer-group"></i>

            <a href="categories.php">
                Categories
            </a>

        </li>



        <!-- ==================================================
             PRODUCTS
             ================================================== -->

        <li class="<?php
            echo ($current_page == 'product.php')
                ? 'active'
                : '';
        ?>">

            <i class="fa-solid fa-box-open"></i>

            <a href="product.php">
                Products
            </a>

        </li>



        <!-- ==================================================
             ORDERS
             ================================================== -->

        <li class="<?php
            echo ($current_page == 'orders.php')
                ? 'active'
                : '';
        ?>">

            <i class="fa-solid fa-cart-shopping"></i>

            <a href="orders.php">
                Orders
            </a>

        </li>



        <!-- ==================================================
             CUSTOMERS
             ================================================== -->

        <li class="<?php
            echo ($current_page == 'customer.php')
                ? 'active'
                : '';
        ?>">

            <i class="fa-solid fa-users"></i>

            <a href="customer.php">
                Customers
            </a>

        </li>



        <!-- ==================================================
             LOGOUT
             ================================================== -->

        <li>

            <i class="fa-solid fa-right-from-bracket"></i>

            <a
                href="logout.php"
                onclick="return confirm('Are you sure you want to logout?');"
            >
                Logout
            </a>

        </li>


    </ul>

</aside>