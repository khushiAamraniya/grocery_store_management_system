<?php

session_start();


// ================= DATABASE CONNECTION =================

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


// ================= GET CATEGORY ID =================

$category_id = isset($_GET['category_id'])
    ? (int)$_GET['category_id']
    : 0;


// ================= PRODUCT LIMIT =================

// Category 1 to 5 = 8 products
// Category 6 to 8 = 10 products

if ($category_id >= 6 && $category_id <= 8) {

    $limit = 10;

} else {

    $limit = 8;

}


// ================= GET PRODUCTS =================

if ($category_id > 0) {

    $sql = "SELECT id, product_name, price, product_image, quantity
            FROM products
            WHERE category_id = $category_id
            ORDER BY id ASC
            LIMIT $limit";

} else {

    $sql = "SELECT id, product_name, price, product_image, quantity
            FROM products
            ORDER BY id ASC";

}

$result = $conn->query($sql);

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Products</title>

<link
    rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"
>

<style>

* {
    box-sizing: border-box;
}

body {
    margin: 0;
    background: #f7f8fa;
    font-family: Arial, sans-serif;
}


/* ================= PRODUCTS CONTAINER ================= */

.products-container {

    width: 94%;

    max-width: 1450px;

    margin: 40px auto 70px;

}


/* ================= TITLE ================= */

.products-container h1 {

    color: #176b35;

    margin-bottom: 35px;

}


/* ================= PRODUCT GRID ================= */

.product-grid {

    display: grid;

    grid-template-columns:
        repeat(3, 1fr);

    gap: 30px;

}


/* ================= PRODUCT CARD ================= */

.product-card {

    background: white;

    border-radius: 18px;

    padding: 25px;

    box-shadow:
        0 8px 25px rgba(0,0,0,.08);

    text-align: center;

    transition: .3s;

}

.product-card:hover {

    transform: translateY(-5px);

}


/* ================= IMAGE ================= */

.product-image {

    width: 100%;

    height: 230px;

    background: #f3f3f3;

    border-radius: 15px;

    overflow: hidden;

    display: flex;

    align-items: center;

    justify-content: center;

}

.product-image img {

    width: 100%;

    height: 100%;

    object-fit: cover;

}


/* ================= PRODUCT NAME ================= */

.product-card h2 {

    color: #176b35;

    font-size: 24px;

    margin: 20px 0 10px;

}


/* ================= PRICE ================= */

.product-price {

    font-size: 20px;

    font-weight: bold;

    margin-bottom: 20px;

}


/* ================= QUANTITY ================= */

.product-quantity {

    margin-bottom: 15px;

    color: #777;

}


/* ================= ADD CART ================= */

.add-cart-btn {

    width: 100%;

    border: none;

    background: #176b35;

    color: white;

    padding: 14px;

    border-radius: 10px;

    cursor: pointer;

    font-size: 17px;

    font-weight: bold;

}

.add-cart-btn:hover {

    background: #0f5228;

}


/* ================= EMPTY ================= */

.no-products {

    background: white;

    padding: 50px;

    text-align: center;

    border-radius: 15px;

}


/* ================= RESPONSIVE ================= */

@media(max-width:900px) {

    .product-grid {

        grid-template-columns:
            repeat(2, 1fr);

    }

}


@media(max-width:600px) {

    .product-grid {

        grid-template-columns: 1fr;

    }

}


/* =========================
   TWO BUTTONS
========================= */

.product-buttons {

    display: flex;

    gap: 10px;

    width: 100%;

    margin-top: 10px;

}


.product-buttons form {

    flex: 1;

    margin: 0;

}


.product-btn {

    width: 100%;

    height: 48px;

    border: none;

    border-radius: 10px;

    background: #176b35;

    color: white;

    font-size: 15px;

    font-weight: bold;

    cursor: pointer;

    text-decoration: none;

    display: flex;

    align-items: center;

    justify-content: center;

    gap: 7px;

}


.product-btn:hover {

    background: #0f5228;

}


.view-btn {

    flex: 1;

}

</style>

</head>


<body>


<!-- ================= HEADER ================= -->

<?php include 'header.php'; ?>


<div class="products-container">


<h1>

    <?php

    if ($category_id > 0) {

        $category_sql = "SELECT category_name
                         FROM categories
                         WHERE category_id = $category_id
                         LIMIT 1";

        $category_result = $conn->query($category_sql);

        if ($category_result && $category_result->num_rows > 0) {

            $category = $category_result->fetch_assoc();

            echo htmlspecialchars($category['category_name']);

        } else {

            echo "Products";

        }

    } else {

        echo "All Products";

    }

    ?>

</h1>


<div class="product-grid">


<?php

if ($result && $result->num_rows > 0) {


    while ($product = $result->fetch_assoc()) {


?>

    <!-- ================= PRODUCT CARD ================= -->

    <div class="product-card">


        <!-- IMAGE -->

        <div class="product-image">


            <?php if (!empty($product['product_image'])) { ?>


                <img

                    src="<?php

                        echo htmlspecialchars(

                            $product['product_image']

                        );

                    ?>"

                    alt="<?php

                        echo htmlspecialchars(

                            $product['product_name']

                        );

                    ?>"

                >


            <?php } else { ?>


                <span>

                    No Image

                </span>


            <?php } ?>


        </div>


        <!-- PRODUCT NAME -->

        <h2>

            <?php

            echo htmlspecialchars(

                $product['product_name']

            );

            ?>

        </h2>


        <!-- PRICE -->

        <p class="product-price">

            ₹

            <?php

            echo number_format(

                $product['price'],

                2

            );

            ?>

        </p>


        <!-- QUANTITY -->

        <p class="product-quantity">

            <?php

            echo htmlspecialchars(

                $product['quantity']

            );

            ?>

        </p>


        <!-- ================= ADD TO CART ================= -->

        <div class="product-buttons">


            <form

                action="add_to_cart.php"

                method="POST"

            >


                <input

                    type="hidden"

                    name="product_id"

                    value="<?php echo $product['id']; ?>"

                >


                <button

                    type="submit"

                    name="add_to_cart"

                    class="product-btn"

                >


                    <i class="fa-solid fa-cart-shopping"></i>


                    Add To Cart


                </button>


            </form>


            <!-- ================= VIEW DETAILS ================= -->

            <a

                href="productdetalis.php?id=<?php echo $product['id']; ?>"

                class="product-btn view-btn"

            >


                <i class="fa-solid fa-eye"></i>


                View Details


            </a>


        </div>


    </div>


<?php

    }

} else {

?>


    <div class="no-products">


        <h2>

            No Products Found

        </h2>


        <p>

            Please add products from database.

        </p>


    </div>


<?php

}

?>


</div>


<!-- ================= FOOTER ================= -->

<?php include 'footer1.php'; ?>


</body>

</html>