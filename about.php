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
   GET ABOUT SETTINGS
   ========================================================= */

$result = $conn->query("
    SELECT
        about_title,
        about_description
    FROM admin_settings
    ORDER BY id ASC
    LIMIT 1
");

if (!$result) {
    die("About Query Error: " . $conn->error);
}

$about = $result->fetch_assoc();


/* =========================================================
   DEFAULT VALUES
   ========================================================= */

$aboutTitle = !empty($about['about_title'])
    ? $about['about_title']
    : "About Shreenath Grocery Store";

$aboutDescription = !empty($about['about_description'])
    ? $about['about_description']
    : "Shreenath Grocery Store provides fresh vegetables, fruits, dairy products, groceries, beverages, personal care products, and daily essentials at the best prices.";


$conn->close();

?>


<section class="about" id="about">

    <div class="about-text">

        <h2>

            <?php echo htmlspecialchars($aboutTitle); ?>

        </h2>


        <p>

            <?php echo htmlspecialchars($aboutDescription); ?>

        </p>


        <p>

            Our mission is to deliver quality products with
            excellent customer service and affordable pricing.

        </p>

    </div>


    <div class="about-image">

        <img
            src="dist/images/about1.jpg"
            alt="Shreenath Grocery Store"
        >

    </div>

</section>


<script src="script.js"></script>