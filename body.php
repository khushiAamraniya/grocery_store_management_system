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
   GET STORE SETTINGS
   ========================================================= */

$result = $conn->query("
    SELECT
        store_name
    FROM admin_settings
    ORDER BY id ASC
    LIMIT 1
");

if (!$result) {
    die("Settings Query Error: " . $conn->error);
}

$settings = $result->fetch_assoc();

$storeName = !empty($settings['store_name'])
    ? $settings['store_name']
    : "Shreenath Grocery Store";

$conn->close();

?>


<section class="hero" id="home">

    <div class="hero-text">

        <h3>Welcome to</h3>

        <h1>
            <?php echo htmlspecialchars($storeName); ?>
        </h1>

        <p>
            Buy fresh vegetables, fruits, groceries,
            milk, bakery items and daily essentials
            at the best price.
        </p>

        

    </div>


   
</section>


<script>

document.addEventListener("DOMContentLoaded", function () {

    const shopBtn = document.querySelector(".shop");

    if (shopBtn) {

        shopBtn.addEventListener("click", function (e) {

            e.preventDefault();

            const productsSection =
                document.querySelector("#products");

            if (productsSection) {

                productsSection.scrollIntoView({
                    behavior: "smooth"
                });

            }

        });

    }


    const exploreBtn = document.querySelector(".explore");

    if (exploreBtn) {

        exploreBtn.addEventListener("click", function (e) {

            e.preventDefault();

            const categoriesSection =
                document.querySelector("#categories");

            if (categoriesSection) {

                categoriesSection.scrollIntoView({
                    behavior: "smooth"
                });

            }

        });

    }

});

</script>