<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


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
   PRODUCT ID
   ========================================================= */

$product_id = (int)($_POST['product_id'] ?? 0);


if ($product_id <= 0) {

    $conn->close();

    $_SESSION['cart_message'] = "Invalid Product.";

    header("Location: product.php");

    exit();
}


/* =========================================================
   GET PRODUCT
   ========================================================= */

$stmt = $conn->prepare("
    SELECT
        id,
        product_name,
        price,
        product_image,
        quantity
    FROM products
    WHERE id = ?
    LIMIT 1
");


if (!$stmt) {

    die("Prepare Error: " . $conn->error);
}


$stmt->bind_param(
    "i",
    $product_id
);


$stmt->execute();


$result = $stmt->get_result();


/* =========================================================
   PRODUCT NOT FOUND
   ========================================================= */

if ($result->num_rows == 0) {

    $stmt->close();

    $conn->close();

    $_SESSION['cart_message'] =
        "Product not found.";

    header("Location: product.php");

    exit();
}


$product = $result->fetch_assoc();


$stmt->close();


/* =========================================================
   CHECK STOCK
   ========================================================= */

$stock = (int)$product['quantity'];


if ($stock <= 0) {

    $conn->close();

    $_SESSION['cart_message'] =
        $product['product_name'] . " is out of stock.";

    header(
        "Location: productdetalis.php?id=" . $product_id
    );

    exit();
}


/* =========================================================
   CREATE CART
   ========================================================= */

if (
    !isset($_SESSION['cart']) ||
    !is_array($_SESSION['cart'])
) {

    $_SESSION['cart'] = [];
}


/* =========================================================
   CHECK EXISTING PRODUCT
   ========================================================= */

$productExists = false;


foreach ($_SESSION['cart'] as $index => $item) {


    /* -----------------------------------------------------
       OLD FORMAT
       ----------------------------------------------------- */

    if (is_array($item)) {

        $cartProductId =
            (int)($item['product_id'] ?? 0);

        $cartQuantity =
            (int)($item['quantity'] ?? 1);

    } else {

        $cartProductId =
            (int)$item;

        $cartQuantity = 1;
    }


    /* -----------------------------------------------------
       SAME PRODUCT
       ----------------------------------------------------- */

    if ($cartProductId === $product_id) {

        $productExists = true;


        /* =================================================
           CHECK MAX STOCK
           ================================================= */

        if ($cartQuantity < $stock) {

            $cartQuantity++;


            $_SESSION['cart'][$index] = [

                'product_id' => $product_id,

                'quantity' => $cartQuantity

            ];


            $_SESSION['cart_message'] =
                $product['product_name'] .
                " quantity increased in cart.";

        } else {

            $_SESSION['cart_message'] =
                "Maximum stock already added.";
        }


        break;
    }
}


/* =========================================================
   ADD NEW PRODUCT
   ========================================================= */

if (!$productExists) {

    $_SESSION['cart'][] = [

        'product_id' => $product_id,

        'quantity' => 1

    ];


    $_SESSION['cart_message'] =
        $product['product_name'] .
        " added to cart successfully.";
}


/* =========================================================
   CLOSE DATABASE
   ========================================================= */

$conn->close();


/* =========================================================
   GO TO CART
   ========================================================= */

header("Location: cart.php");

exit();

?>