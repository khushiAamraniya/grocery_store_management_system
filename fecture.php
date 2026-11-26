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
   GET FEATURES DATA
   ========================================================= */

$sql = "SELECT 
            feature1_title,
            feature1_description,
            feature2_title,
            feature2_description,
            feature3_title,
            feature3_description,
            feature4_title,
            feature4_description,
            feature5_title,
            feature5_description
        FROM admin_settings
        WHERE id = 1
        LIMIT 1";

$result = $conn->query($sql);


/* =========================================================
   DEFAULT VALUES
   ========================================================= */

$feature1_title = "Fresh Products";
$feature1_description = "100% Fresh & Hygienic Products";

$feature2_title = "Best Quality";
$feature2_description = "Premium Quality Guaranteed";

$feature3_title = "Best Prices";
$feature3_description = "Affordable Prices Everyday";

$feature4_title = "Fast Delivery";
$feature4_description = "Quick & Safe Delivery";

$feature5_title = "24×7 Support";
$feature5_description = "Always Ready To Help";


/* =========================================================
   FETCH DATA
   ========================================================= */

if ($result && $result->num_rows > 0) {

    $row = $result->fetch_assoc();

    if (!empty($row['feature1_title'])) {
        $feature1_title = $row['feature1_title'];
    }

    if (!empty($row['feature1_description'])) {
        $feature1_description = $row['feature1_description'];
    }

    if (!empty($row['feature2_title'])) {
        $feature2_title = $row['feature2_title'];
    }

    if (!empty($row['feature2_description'])) {
        $feature2_description = $row['feature2_description'];
    }

    if (!empty($row['feature3_title'])) {
        $feature3_title = $row['feature3_title'];
    }

    if (!empty($row['feature3_description'])) {
        $feature3_description = $row['feature3_description'];
    }

    if (!empty($row['feature4_title'])) {
        $feature4_title = $row['feature4_title'];
    }

    if (!empty($row['feature4_description'])) {
        $feature4_description = $row['feature4_description'];
    }

    if (!empty($row['feature5_title'])) {
        $feature5_title = $row['feature5_title'];
    }

    if (!empty($row['feature5_description'])) {
        $feature5_description = $row['feature5_description'];
    }
}


/* =========================================================
   CLOSE DATABASE
   ========================================================= */

$conn->close();

?>

<!-- =========================================================
     FEATURES SECTION
     ========================================================= -->

<section class="features">


    <!-- FEATURE 1 -->

    <div class="feature-box">

        <i class="fa-solid fa-leaf"></i>

        <h3>
            <?php echo htmlspecialchars($feature1_title); ?>
        </h3>

        <p>
            <?php echo htmlspecialchars($feature1_description); ?>
        </p>

    </div>


    <!-- FEATURE 2 -->

    <div class="feature-box">

        <i class="fa-solid fa-award"></i>

        <h3>
            <?php echo htmlspecialchars($feature2_title); ?>
        </h3>

        <p>
            <?php echo htmlspecialchars($feature2_description); ?>
        </p>

    </div>


    <!-- FEATURE 3 -->

    <div class="feature-box">

        <i class="fa-solid fa-tags"></i>

        <h3>
            <?php echo htmlspecialchars($feature3_title); ?>
        </h3>

        <p>
            <?php echo htmlspecialchars($feature3_description); ?>
        </p>

    </div>


    <!-- FEATURE 4 -->

    <div class="feature-box">

        <i class="fa-solid fa-truck-fast"></i>

        <h3>
            <?php echo htmlspecialchars($feature4_title); ?>
        </h3>

        <p>
            <?php echo htmlspecialchars($feature4_description); ?>
        </p>

    </div>


    <!-- FEATURE 5 -->

    <div class="feature-box">

        <i class="fa-solid fa-headset"></i>

        <h3>
            <?php echo htmlspecialchars($feature5_title); ?>
        </h3>

        <p>
            <?php echo htmlspecialchars($feature5_description); ?>
        </p>

    </div>


</section>


<script src="script.js"></script>