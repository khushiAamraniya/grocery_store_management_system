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
   REMOVE PRODUCT
   ========================================================= */

if (isset($_GET['remove'])) {

    $removeIndex = (int)$_GET['remove'];

    if (
        isset($_SESSION['cart']) &&
        isset($_SESSION['cart'][$removeIndex])
    ) {

        unset($_SESSION['cart'][$removeIndex]);

        $_SESSION['cart'] = array_values($_SESSION['cart']);
    }

    header("Location: cart.php");
    exit();
}


/* =========================================================
   PLUS / MINUS
   ========================================================= */

if (
    isset($_POST['quantity_action']) &&
    isset($_POST['cart_index'])
) {

    $action = $_POST['quantity_action'];
    $index  = (int)$_POST['cart_index'];


    if (
        isset($_SESSION['cart']) &&
        is_array($_SESSION['cart']) &&
        isset($_SESSION['cart'][$index])
    ) {

        if (is_array($_SESSION['cart'][$index])) {

            $product_id = (int)(
                $_SESSION['cart'][$index]['product_id'] ?? 0
            );

            $currentQty = (int)(
                $_SESSION['cart'][$index]['quantity'] ?? 1
            );

        } else {

            $product_id = (int)$_SESSION['cart'][$index];

            $currentQty = 1;
        }


        /* GET STOCK */

        $stmt = $conn->prepare("
            SELECT quantity
            FROM products
            WHERE id = ?
            LIMIT 1
        ");

        $stmt->bind_param(
            "i",
            $product_id
        );

        $stmt->execute();

        $result = $stmt->get_result();


        if ($result->num_rows > 0) {

            $row = $result->fetch_assoc();

            $stock = (int)$row['quantity'];


            /* PLUS */

            if ($action === "plus") {

                if ($currentQty < $stock) {

                    $currentQty++;
                }
            }


            /* MINUS */

            if ($action === "minus") {

                if ($currentQty > 1) {

                    $currentQty--;
                }
            }


            /* SAVE */

            $_SESSION['cart'][$index] = [

                'product_id' => $product_id,

                'quantity' => $currentQty
            ];
        }

        $stmt->close();
    }


    header("Location: cart.php");
    exit();
}


/* =========================================================
   CART
   ========================================================= */

$cart = $_SESSION['cart'] ?? [];

$subtotal = 0;

$delivery = 40;

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>

<title>My Shopping Cart</title>

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


.cart-container {

    width: 94%;

    max-width: 1450px;

    margin: 35px auto 70px;
}


.cart-container h1 {

    color: #176b35;

    margin-bottom: 40px;
}


.cart-items {

    width: calc(100% - 640px);

    float: left;
}


.cart-summary {

    width: 595px;

    float: right;

    background: white;

    padding: 35px;

    border-radius: 18px;

    box-shadow: 0 8px 25px rgba(0,0,0,.08);
}


.cart-container::after {

    content: "";

    display: table;

    clear: both;
}


/* =========================================================
   NOTIFICATION
   ========================================================= */

.cart-notification {

    position: fixed;

    top: 100px;

    right: 30px;

    background: #176b35;

    color: white;

    padding: 15px 25px;

    border-radius: 8px;

    font-size: 16px;

    font-weight: bold;

    z-index: 99999;

    box-shadow: 0 5px 15px rgba(0,0,0,.2);

    animation: notificationShow .3s ease;
}


.cart-notification i {

    margin-right: 8px;
}


@keyframes notificationShow {

    from {

        opacity: 0;

        transform: translateX(50px);

    }

    to {

        opacity: 1;

        transform: translateX(0);

    }

}


/* =========================================================
   CART ITEM
   ========================================================= */

.cart-item {

    display: grid;

    grid-template-columns: 180px 1fr;

    align-items: center;

    background: white;

    padding: 25px;

    margin-bottom: 25px;

    border-radius: 18px;

    box-shadow: 0 8px 25px rgba(0,0,0,.08);
}


.cart-image {

    width: 150px;

    height: 145px;

    overflow: hidden;

    border-radius: 10px;

    background: #f1f1f1;

    display: flex;

    align-items: center;

    justify-content: center;
}


.cart-image img {

    width: 100%;

    height: 100%;

    object-fit: cover;
}


.cart-info h2 {

    color: #176b35;

    font-size: 28px;

    margin: 0 0 15px;
}


.price {

    font-size: 20px;

    font-weight: bold;

    margin: 10px 0;
}


/* =========================================================
   QUANTITY
   ========================================================= */

.quantity-box {

    display: flex;

    align-items: center;

    gap: 12px;

    margin: 15px 0;
}


.quantity-form {

    margin: 0;

    padding: 0;
}


.quantity-btn {

    width: 42px;

    height: 42px;

    border-radius: 50%;

    border: none;

    display: inline-flex;

    align-items: center;

    justify-content: center;

    font-size: 18px;

    font-weight: bold;

    cursor: pointer;

    transition: .2s;
}


.minus-btn {

    background: #e53935;

    color: white;
}


.minus-btn:hover {

    background: #c62828;

    transform: scale(1.05);
}


.plus-btn {

    background: #176b35;

    color: white;
}


.plus-btn:hover {

    background: #0f5228;

    transform: scale(1.05);
}


.disabled-btn {

    opacity: .5;

    cursor: not-allowed;
}


.quantity-number {

    min-width: 45px;

    text-align: center;

    font-size: 20px;

    font-weight: bold;

    color: #176b35;
}


.stock-message {

    color: #777;

    font-size: 14px;

    margin-top: 5px;
}


.cart-buttons {

    display: flex;

    align-items: center;

    gap: 10px;

    margin-top: 15px;
}


.remove {

    display: inline-flex;

    align-items: center;

    gap: 7px;

    padding: 10px 18px;

    background: #e53935;

    color: white;

    text-decoration: none;

    border-radius: 20px;

    font-weight: bold;
}


.remove:hover {

    background: #c62828;
}


/* =========================================================
   SUMMARY
   ========================================================= */

.summary-row {

    display: flex;

    justify-content: space-between;

    margin: 20px 0;

    font-size: 20px;
}


.grand {

    color: #176b35;

    font-weight: bold;

    font-size: 22px;
}


.checkout-btn {

    display: block;

    text-align: center;

    padding: 20px;

    background: #ff6500;

    color: white;

    text-decoration: none;

    border-radius: 15px;

    font-size: 22px;

    font-weight: bold;

    margin-top: 25px;
}


.checkout-btn:hover {

    background: #e85600;
}


/* =========================================================
   EMPTY CART
   ========================================================= */

.empty-cart {

    background: white;

    padding: 70px;

    text-align: center;

    border-radius: 18px;

    box-shadow: 0 8px 25px rgba(0,0,0,.08);
}


.empty-cart h2 {

    color: #176b35;
}


.empty-cart a {

    display: inline-block;

    padding: 12px 25px;

    background: #176b35;

    color: white;

    text-decoration: none;

    border-radius: 8px;

    margin-top: 15px;
}


/* =========================================================
   RESPONSIVE
   ========================================================= */

@media(max-width:1100px) {

    .cart-items,
    .cart-summary {

        width: 100%;

        float: none;
    }

    .cart-summary {

        margin-top: 30px;
    }
}


@media(max-width:600px) {

    .cart-container {

        width: 92%;
    }

    .cart-item {

        grid-template-columns: 1fr;

        text-align: center;
    }

    .cart-image {

        margin: 0 auto 20px;
    }

    .cart-info h2 {

        font-size: 23px;
    }

    .cart-summary {

        padding: 25px;
    }

    .cart-buttons {

        justify-content: center;

        flex-wrap: wrap;
    }

    .quantity-box {

        justify-content: center;
    }

    .cart-notification {

        right: 10px;

        left: 10px;

        top: 90px;

        text-align: center;
    }

}

</style>

</head>


<body>


<?php include 'header.php'; ?>


<!-- =========================================================
     NOTIFICATION
     ========================================================= -->

<?php

if (isset($_SESSION['cart_message'])) {

?>

<div
    id="cartNotification"
    class="cart-notification"
>

    <i class="fa-solid fa-circle-check"></i>

    <?php

    echo htmlspecialchars(
        $_SESSION['cart_message']
    );

    ?>

</div>

<?php

    unset($_SESSION['cart_message']);

}

?>


<div class="cart-container">


<h1>

    <i class="fa-solid fa-cart-shopping"></i>

    My Shopping Cart

</h1>


<?php

if (empty($cart)) {

?>

<div class="empty-cart">

    <h2>
        Your cart is empty 😔
    </h2>

    <p>
        Add some products to your cart.
    </p>

    <a href="productListing.php">
        Continue Shopping
    </a>

</div>


<?php

} else {

?>


<div class="cart-items">


<?php

foreach ($cart as $index => $cartItem) {


    /* =====================================================
       GET CART DATA
       ===================================================== */

    if (is_array($cartItem)) {

        $product_id =
            (int)($cartItem['product_id'] ?? 0);

        $cartQuantity =
            (int)($cartItem['quantity'] ?? 1);

    } else {

        $product_id =
            (int)$cartItem;

        $cartQuantity = 1;
    }


    if ($product_id <= 0) {
        continue;
    }


    /* =====================================================
       PRODUCT
       ===================================================== */

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


    $stmt->bind_param(
        "i",
        $product_id
    );


    $stmt->execute();


    $result =
        $stmt->get_result();


    if ($result->num_rows == 0) {

        $stmt->close();

        continue;
    }


    $product =
        $result->fetch_assoc();


    $productName =
        $product['product_name'];

    $price =
        (float)$product['price'];

    $image =
        $product['product_image'];

    $stock =
        (int)$product['quantity'];


    /* =====================================================
       FIX QUANTITY
       ===================================================== */

    if ($cartQuantity < 1) {

        $cartQuantity = 1;
    }


    if ($stock > 0 && $cartQuantity > $stock) {

        $cartQuantity = $stock;
    }


    $_SESSION['cart'][$index] = [

        'product_id' => $product_id,

        'quantity' => $cartQuantity

    ];


    /* =====================================================
       ITEM TOTAL
       ===================================================== */

    $itemTotal =
        $price * $cartQuantity;


    $subtotal +=
        $itemTotal;

?>


<div class="cart-item">


    <!-- IMAGE -->

    <div class="cart-image">

        <?php if (!empty($image)) { ?>

            <img
                src="<?php echo htmlspecialchars($image); ?>"
                alt="<?php echo htmlspecialchars($productName); ?>"
            >

        <?php } else { ?>

            <span>No Image</span>

        <?php } ?>

    </div>


    <!-- INFO -->

    <div class="cart-info">


        <h2>

            <?php

            echo htmlspecialchars(
                $productName
            );

            ?>

        </h2>


        <p class="price">

            ₹<?php

            echo number_format(
                $price,
                2
            );

            ?>

        </p>


        <!-- QUANTITY -->

        <div class="quantity-box">


            <!-- MINUS -->

            <form
                method="POST"
                action="cart.php"
                class="quantity-form"
            >

                <input
                    type="hidden"
                    name="cart_index"
                    value="<?php echo $index; ?>"
                >

                <input
                    type="hidden"
                    name="quantity_action"
                    value="minus"
                >


                <?php if ($cartQuantity > 1) { ?>

                    <button
                        type="submit"
                        class="quantity-btn minus-btn"
                        title="Decrease Quantity"
                    >

                        <i class="fa-solid fa-minus"></i>

                    </button>

                <?php } else { ?>

                    <button
                        type="button"
                        class="quantity-btn minus-btn disabled-btn"
                        disabled
                    >

                        <i class="fa-solid fa-minus"></i>

                    </button>

                <?php } ?>

            </form>


            <!-- NUMBER -->

            <span class="quantity-number">

                <?php echo $cartQuantity; ?>

            </span>


            <!-- PLUS -->

            <form
                method="POST"
                action="cart.php"
                class="quantity-form"
            >

                <input
                    type="hidden"
                    name="cart_index"
                    value="<?php echo $index; ?>"
                >

                <input
                    type="hidden"
                    name="quantity_action"
                    value="plus"
                >


                <?php if ($cartQuantity < $stock) { ?>

                    <button
                        type="submit"
                        class="quantity-btn plus-btn"
                        title="Increase Quantity"
                    >

                        <i class="fa-solid fa-plus"></i>

                    </button>

                <?php } else { ?>

                    <button
                        type="button"
                        class="quantity-btn plus-btn disabled-btn"
                        disabled
                    >

                        <i class="fa-solid fa-plus"></i>

                    </button>

                <?php } ?>

            </form>


        </div>


        <p class="stock-message">

            Available Stock:

            <strong>

                <?php echo $stock; ?>

            </strong>

        </p>


        <p>

            <strong>

                Item Total:

                ₹<?php

                echo number_format(
                    $itemTotal,
                    2
                );

                ?>

            </strong>

        </p>


        <!-- REMOVE -->

        <div class="cart-buttons">

            <a
                class="remove"
                href="cart.php?remove=<?php echo $index; ?>"
                onclick="return confirm('Are you sure you want to remove this product?');"
            >

                <i class="fa-solid fa-trash"></i>

                Remove

            </a>

        </div>


    </div>


</div>


<?php

    $stmt->close();

}

?>


</div>


<?php


/* =========================================================
   TOTAL
   ========================================================= */

$totalBeforeGST =
    $subtotal + $delivery;

$gst =
    $totalBeforeGST * 0.05;

$grandTotal =
    $totalBeforeGST + $gst;

?>


<div class="cart-summary">


    <h2>

        <i class="fa-solid fa-receipt"></i>

        Order Summary

    </h2>


    <div class="summary-row">

        <span>
            Subtotal
        </span>

        <strong>

            ₹<?php

            echo number_format(
                $subtotal,
                2
            );

            ?>

        </strong>

    </div>


    <div class="summary-row">

        <span>
            Delivery
        </span>

        <strong>

            ₹<?php

            echo number_format(
                $delivery,
                2
            );

            ?>

        </strong>

    </div>


    <div class="summary-row">

        <span>
            GST (5%)
        </span>

        <strong>

            ₹<?php

            echo number_format(
                $gst,
                2
            );

            ?>

        </strong>

    </div>


    <hr>


    <div class="summary-row grand">

        <span>
            Grand Total
        </span>

        <strong>

            ₹<?php

            echo number_format(
                $grandTotal,
                2
            );

            ?>

        </strong>

    </div>


    <a
        href="chatout.php"
        class="checkout-btn"
    >

        <i class="fa-solid fa-credit-card"></i>

        Proceed To Checkout

    </a>


</div>


<?php

}

?>


</div>


<?php include 'footer1.php'; ?>


<!-- =========================================================
     NOTIFICATION AUTO HIDE
     ========================================================= -->

<script>

setTimeout(function() {

    const notification =
        document.getElementById("cartNotification");

    if (notification) {

        notification.style.opacity = "0";

        notification.style.transform =
            "translateX(50px)";

        setTimeout(function() {

            notification.remove();

        }, 300);

    }

}, 3000);

</script>


</body>

</html>