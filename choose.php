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
   GET CHOOSE US DATA
   ========================================================= */

$sql = "SELECT choose_title, choose_description
        FROM admin_settings
        WHERE id = 1
        LIMIT 1";

$result = $conn->query($sql);


/* =========================================================
   DEFAULT VALUES
   ========================================================= */

$choose_title = "Why Choose Us?";
$choose_description = "";


/* =========================================================
   FETCH DATA
   ========================================================= */

if ($result && $result->num_rows > 0) {

    $row = $result->fetch_assoc();

    if (!empty($row['choose_title'])) {
        $choose_title = $row['choose_title'];
    }

    if (!empty($row['choose_description'])) {
        $choose_description = $row['choose_description'];
    }
}


/* =========================================================
   CLOSE DATABASE
   ========================================================= */

$conn->close();

?>

<!-- =========================================================
     CHOOSE US SECTION
     ========================================================= -->

<section class="choose">

    <div class="choose-text">

        <!-- TITLE -->

        <h2>
            <?php echo htmlspecialchars($choose_title); ?>
        </h2>


        <!-- DESCRIPTION -->

        <?php if (!empty($choose_description)) { ?>

            <p class="choose-description">
                <?php echo htmlspecialchars($choose_description); ?>
            </p>

        <?php } ?>


        <!-- CHOOSE US BOXES -->

        <div class="choose-list">


            <!-- BOX 1 -->

            <div class="choose-box">

                <i class="fa-solid fa-check-circle"></i>

                <h3>
                    Fresh & Healthy Products
                </h3>

                <p>
                    Daily fresh groceries with premium quality.
                </p>

            </div>


            <!-- BOX 2 -->

            <div class="choose-box">

                <i class="fa-solid fa-wallet"></i>

                <h3>
                    Affordable Prices
                </h3>

                <p>
                    Quality products at reasonable prices.
                </p>

            </div>


            <!-- BOX 3 -->

            <div class="choose-box">

                <i class="fa-solid fa-truck"></i>

                <h3>
                    Fast Delivery
                </h3>

                <p>
                    Get your grocery products delivered quickly.
                </p>

            </div>


            <!-- BOX 4 -->

            <div class="choose-box">

                <i class="fa-solid fa-headset"></i>

                <h3>
                    Easy Support
                </h3>

                <p>
                    We are always ready to help our customers.
                </p>

            </div>


        </div>

    </div>

</section>