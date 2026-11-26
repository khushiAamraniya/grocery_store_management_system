<?php

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
   DEFAULT STORE VALUES
   ========================================================= */

$store_name = "Shreenath Grocery Store";
$contact_number = "+91 98765 43210";
$email = "info@shreenathgrocery.com";
$address = "Upleta, Gujarat";


/* =========================================================
   GET STORE INFORMATION FROM ADMIN SETTINGS
   ========================================================= */

$sql = "SELECT store_name, contact_number, email, address
        FROM admin_settings
        WHERE id = 1
        LIMIT 1";

$result = $conn->query($sql);

if (!$result) {
    die("Settings Query Error: " . $conn->error);
}

if ($result->num_rows > 0) {

    $row = $result->fetch_assoc();

    if (!empty($row['store_name'])) {
        $store_name = $row['store_name'];
    }

    if (!empty($row['contact_number'])) {
        $contact_number = $row['contact_number'];
    }

    if (!empty($row['email'])) {
        $email = $row['email'];
    }

    if (!empty($row['address'])) {
        $address = $row['address'];
    }
}


/* =========================================================
   GET CATEGORIES FROM DATABASE
   ========================================================= */

$categories = [];

$category_sql = "SELECT category_name
                 FROM categories
                 WHERE active = 1
                 AND deleted_at IS NULL
                 ORDER BY id ASC";

$category_result = $conn->query($category_sql);

if (!$category_result) {
    die("Category Query Error: " . $conn->error);
}

if ($category_result->num_rows > 0) {

    while ($category_row = $category_result->fetch_assoc()) {

        if (!empty($category_row['category_name'])) {

            $categories[] = $category_row['category_name'];

        }
    }
}


/* =========================================================
   CLOSE DATABASE
   ========================================================= */

$conn->close();

?>


<!-- =========================================================
     FOOTER
     ========================================================= -->

<footer class="footer">

<link rel="stylesheet" href="style.css">

<link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">


<div class="footer-container">


    <!-- =================================================
         LOGO / STORE INFORMATION
         ================================================= -->

    <div class="footer-box">

        <h2>

            <i class="fa-solid fa-cart-shopping"></i>

            <?php echo htmlspecialchars($store_name); ?>

        </h2>


        <p>

            We provide fresh groceries, vegetables, fruits, dairy products,
            beverages, and household essentials at affordable prices with
            fast and reliable delivery.

        </p>


        <div class="social">


            <a href="#">
                <i class="fab fa-facebook-f"></i>
            </a>


            <a href="#">
                <i class="fab fa-instagram"></i>
            </a>


            <a href="#">
                <i class="fab fa-twitter"></i>
            </a>


            <a href="#">
                <i class="fab fa-youtube"></i>
            </a>


        </div>

    </div>



    <!-- =================================================
         QUICK LINKS
         ================================================= -->

    <div class="footer-box">

        <h3>Quick Links</h3>


        <ul>


            <li>

                <a href="index.php">
                    Home
                </a>

            </li>


            <li>

                <a href="product.php">
                    Categories
                </a>

            </li>


            <li>

                <a href="#about">
                    About Us
                </a>

            </li>


            <li>

                <a href="#contact">
                    Contact Us
                </a>

            </li>


        </ul>

    </div>



    <!-- =================================================
         CATEGORIES
         ================================================= -->

    <div class="footer-box">

        <h3>Categories</h3>


        <ul>


            <?php if (!empty($categories)) { ?>


                <?php foreach ($categories as $category) { ?>


                    <li>

                        <a href="#">

                            <?php echo htmlspecialchars($category); ?>

                        </a>

                    </li>


                <?php } ?>


            <?php } else { ?>


                <li>

                    <a href="#">
                        No Categories Available
                    </a>

                </li>


            <?php } ?>


        </ul>

    </div>



    <!-- =================================================
         CONTACT INFORMATION
         ================================================= -->

    <div class="footer-box">

        <h3>Contact Info</h3>


        <p>

            <i class="fa-solid fa-location-dot"></i>

            <?php echo htmlspecialchars($address); ?>

        </p>


        <p>

            <i class="fa-solid fa-phone"></i>

            <?php echo htmlspecialchars($contact_number); ?>

        </p>


        <p>

            <i class="fa-solid fa-envelope"></i>

            <?php echo htmlspecialchars($email); ?>

        </p>


    </div>


</div>



<hr>



<!-- =================================================
     FOOTER BOTTOM
     ================================================= -->

<div class="footer-bottom">


    © 2026


    <strong>

        <?php echo htmlspecialchars($store_name); ?>

    </strong>


    . All Rights Reserved. |


    Designed by

    <strong>
        Khushi Amraniya
    </strong>


</div>


</footer>

<script src="script.js"></script>

</body>

</html>