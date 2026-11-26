<?php

session_start();

include 'header.php';


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
   GET PRODUCT ID
   ========================================================= */

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Product ID not found.");
}

$product_id = (int) $_GET['id'];

if ($product_id <= 0) {
    die("Invalid Product ID.");
}


/* =========================================================
   GET PRODUCT
   ========================================================= */

$stmt = $conn->prepare("
    SELECT
        p.id,
        p.product_name,
        p.price,
        p.product_image,
        p.quantity,
        p.category_id,
        c.category_name
    FROM products AS p
    LEFT JOIN categories AS c
        ON p.category_id = c.category_id
    WHERE p.id = ?
    LIMIT 1
");


if (!$stmt) {
    die("Product Query Error: " . $conn->error);
}


$stmt->bind_param("i", $product_id);

$stmt->execute();

$result = $stmt->get_result();


/* =========================================================
   PRODUCT NOT FOUND
   ========================================================= */

if ($result->num_rows == 0) {

    $stmt->close();
    $conn->close();

    die("Product not found. Product ID = " . $product_id);
}


$product = $result->fetch_assoc();

$stmt->close();


/* =========================================================
   PRODUCT DATA
   ========================================================= */

$productName  = $product['product_name'];
$price        = (float) $product['price'];
$productImage = $product['product_image'];
$quantity     = $product['quantity'];

$category = !empty($product['category_name'])
    ? $product['category_name']
    : "Grocery";


/* =========================================================
   IMAGE
   ========================================================= */

if (!empty($productImage)) {

    $image = $productImage;

} else {

    $image = "dist/images/no-image.jpg";

}

?>

<link rel="stylesheet" href="productdetalis.css">


<!-- BREADCRUMB -->

<section class="breadcrumb">

    <a href="index.php">

        <i class="fa-solid fa-house"></i>

        Home

    </a>


    <i class="fa-solid fa-angle-right"></i>


    <a href="productListing.php">

        <?php echo htmlspecialchars($category); ?>

    </a>


    <i class="fa-solid fa-angle-right"></i>


    <span>

        <?php echo htmlspecialchars($productName); ?>

    </span>

</section>



<!-- PRODUCT CONTAINER -->

<div class="product-container">


    <!-- LEFT -->

    <div class="left-section">


        <div class="main-image">

            <img
                id="mainImage"
                src="<?php echo htmlspecialchars($image); ?>"
                alt="<?php echo htmlspecialchars($productName); ?>"
            >

        </div>


        


    </div>



    <!-- RIGHT -->

    <div class="details-section">


        <h1>

            <?php echo htmlspecialchars($productName); ?>

        </h1>


        <div class="stock-category">

            <span class="stock">

                In Stock

            </span>


            <span>

                Category:

                <b>

                    <?php echo htmlspecialchars($category); ?>

                </b>

            </span>

        </div>


        <h2>

            ₹<?php echo number_format($price, 2); ?>

            <small>

                / <?php echo htmlspecialchars($quantity); ?>

            </small>

        </h2>


        <select>

            <option>

                Weight:

                <?php echo htmlspecialchars($quantity); ?>

            </option>

        </select>


        <p class="origin">

            <i class="fa-solid fa-location-dot"></i>

            Fresh Grocery Product

        </p>



        <!-- BUTTONS -->

<div class="buttons">

    <!-- ADD TO CART -->

    <form
        action="add_to_cart.php"
        method="POST"
    >

        <input
            type="hidden"
            name="product_id"
            value="<?php echo $product_id; ?>"
        >

        <button
            type="submit"
            name="add_to_cart"
            class="cart"
        >

            <i class="fa-solid fa-cart-shopping"></i>

            Add To Cart

        </button>

    </form>


    <!-- BUY NOW -->

    <a
        href="chatout.php?id=<?php echo $product_id; ?>"
        class="buy-btn"
    >

        <i class="fa-solid fa-cart-shopping"></i>

        Buy Now

    </a>

</div>        <!-- DESCRIPTION -->

        <div class="box description">

            <h3>

                <i class="fa-solid fa-leaf"></i>

                Description

            </h3>


            <p>

                Fresh and quality

                <?php echo htmlspecialchars($productName); ?>.

                Perfect for daily grocery needs.

                Carefully selected for better quality and taste.

            </p>

        </div>



        <!-- BOTTOM BOX -->

        <div class="bottom-box">


            <div class="box nutrition">

                <h3>

                    <i class="fa-solid fa-apple-whole"></i>

                    Product Information

                </h3>


                <p>✔ Fresh Quality</p>

                <p>✔ Good Product</p>

                <p>✔ Daily Grocery</p>

            </div>



            <div class="box benefits">

                <h3>

                    <i class="fa-solid fa-heart"></i>

                    Benefits

                </h3>


                <p>✔ Quality Product</p>

                <p>✔ Fresh Item</p>

                <p>✔ Easy Shopping</p>

            </div>


        </div>


    </div>

</div>



<!-- IMAGE SCRIPT -->

<script>

function changeImage(src, element) {

    document.getElementById("mainImage").src = src;

    document.querySelectorAll(".thumb").forEach(function(thumb) {

        thumb.classList.remove("active");

    });

    element.classList.add("active");

}

</script>



<?php

$conn->close();

include 'footer1.php';

?>