<?php

/* =========================================================
   SESSION
   ========================================================= */

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
   CHECK LOGIN
   ========================================================= */

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}


/* =========================================================
   GET CURRENT USER DETAILS
   ========================================================= */

$userId = (int) $_SESSION['user_id'];

$userStmt = $conn->prepare("
    SELECT id, full_name, email
    FROM user_register
    WHERE id = ?
    LIMIT 1
");

$userStmt->bind_param("i", $userId);
$userStmt->execute();

$userResult = $userStmt->get_result();
$user = $userResult->fetch_assoc();

$userStmt->close();

if (!$user) {
    session_destroy();
    header("Location: login.php");
    exit();
}

$userName  = $user['full_name'];
$userEmail = $user['email'];

$_SESSION['username']   = $userName;
$_SESSION['user_email'] = $userEmail;


/* =========================================================
   CANCEL ORDER
   ========================================================= */

if (isset($_POST['cancel_order'])) {

    $orderId = (int) ($_POST['order_id'] ?? 0);

    if ($orderId <= 0) {

        $_SESSION['order_message'] = "Invalid order.";
        $_SESSION['order_message_type'] = "error";

        header("Location: orders.php");
        exit();
    }


    /* =====================================================
       CHECK ORDER BELONGS TO CURRENT USER
       ===================================================== */

    $orderStmt = $conn->prepare("
        SELECT id, status
        FROM orders
        WHERE id = ?
        AND LOWER(TRIM(email)) = LOWER(TRIM(?))
        LIMIT 1
    ");

    $orderStmt->bind_param(
        "is",
        $orderId,
        $userEmail
    );

    $orderStmt->execute();

    $orderResult = $orderStmt->get_result();
    $orderData = $orderResult->fetch_assoc();

    $orderStmt->close();


    if (!$orderData) {

        $_SESSION['order_message'] = "Order not found.";
        $_SESSION['order_message_type'] = "error";

        header("Location: orders.php");
        exit();
    }


    /* =====================================================
       ONLY PENDING ORDER CAN BE CANCELLED
       ===================================================== */

    if (strtolower($orderData['status']) !== 'pending') {

        $_SESSION['order_message'] =
            "Only pending orders can be cancelled.";

        $_SESSION['order_message_type'] = "error";

        header("Location: orders.php");
        exit();
    }


    /* =====================================================
       START TRANSACTION
       ===================================================== */

    $conn->begin_transaction();

    try {

        /* =================================================
           GET ORDER ITEMS
           ================================================= */

        $itemStmt = $conn->prepare("
            SELECT
                product_id,
                quantity
            FROM order_items
            WHERE order_id = ?
        ");

        $itemStmt->bind_param("i", $orderId);
        $itemStmt->execute();

        $itemResult = $itemStmt->get_result();


        /* =================================================
           RETURN STOCK
           ================================================= */

        while ($item = $itemResult->fetch_assoc()) {

            $productId = (int) $item['product_id'];

            $quantityValue = $item['quantity'];

            if (is_numeric($quantityValue)) {

                $orderedQty = (int) $quantityValue;

            } else {

                preg_match(
                    '/\d+/',
                    (string) $quantityValue,
                    $matches
                );

                $orderedQty = isset($matches[0])
                    ? (int) $matches[0]
                    : 0;
            }


            if ($productId > 0 && $orderedQty > 0) {

                /* =========================================
                   GET CURRENT STOCK
                   ========================================= */

                $stockCheckStmt = $conn->prepare("
                    SELECT quantity
                    FROM products
                    WHERE id = ?
                    LIMIT 1
                ");

                $stockCheckStmt->bind_param(
                    "i",
                    $productId
                );

                $stockCheckStmt->execute();

                $stockResult =
                    $stockCheckStmt->get_result();

                $stockData =
                    $stockResult->fetch_assoc();

                $stockCheckStmt->close();


                /* =========================================
                   UPDATE STOCK
                   ========================================= */

                if ($stockData) {

                    $currentStock =
                        $stockData['quantity'];

                    if (is_numeric($currentStock)) {

                        $newStock =
                            (int) $currentStock + $orderedQty;

                        $updateStockStmt = $conn->prepare("
                            UPDATE products
                            SET quantity = ?
                            WHERE id = ?
                        ");

                        $updateStockStmt->bind_param(
                            "ii",
                            $newStock,
                            $productId
                        );

                        $updateStockStmt->execute();
                        $updateStockStmt->close();
                    }
                }
            }
        }

        $itemStmt->close();


        /* =================================================
           UPDATE ORDER STATUS
           ================================================= */

        $cancelStmt = $conn->prepare("
            UPDATE orders
            SET status = 'Cancelled'
            WHERE id = ?
            AND LOWER(TRIM(email)) = LOWER(TRIM(?))
            AND LOWER(TRIM(status)) = 'pending'
        ");

        $cancelStmt->bind_param(
            "is",
            $orderId,
            $userEmail
        );

        $cancelStmt->execute();

        $affectedRows = $cancelStmt->affected_rows;

        $cancelStmt->close();


        if ($affectedRows <= 0) {

            throw new Exception(
                "Order could not be cancelled."
            );
        }


        /* =================================================
           COMMIT
           ================================================= */

        $conn->commit();

        $_SESSION['order_message'] =
            "Order #{$orderId} cancelled successfully.";

        $_SESSION['order_message_type'] = "success";

    } catch (Exception $e) {

        $conn->rollback();

        $_SESSION['order_message'] =
            "Order cancellation failed: " .
            $e->getMessage();

        $_SESSION['order_message_type'] = "error";
    }


    header("Location: orders.php");
    exit();
}


/* =========================================================
   MESSAGE
   ========================================================= */

$message =
    $_SESSION['order_message'] ?? "";

$messageType =
    $_SESSION['order_message_type'] ?? "";

unset($_SESSION['order_message']);
unset($_SESSION['order_message_type']);


/* =========================================================
   GET ORDERS
   ========================================================= */

$orderStmt = $conn->prepare("
    SELECT
        id,
        first_name,
        email,
        phone,
        address,
        payment_method,
        subtotal,
        gst,
        grand_total,
        status,
        created_at
    FROM orders
    WHERE LOWER(TRIM(email)) = LOWER(TRIM(?))
    ORDER BY id DESC
");

$orderStmt->bind_param(
    "s",
    $userEmail
);

$orderStmt->execute();

$orderResult = $orderStmt->get_result();


/* =========================================================
   HEADER
   ========================================================= */

include("header.php");

?>

<style>

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: Arial, Helvetica, sans-serif;
    background: #f5f7f8;
    color: #222;
}


/* =========================================================
   HEADER
   ========================================================= */

.header {
    background: #ffffff;
    padding: 22px 7%;
    border-bottom: 1px solid #eeeeee;
}

.header h1 {
    font-size: 30px;
    color: #222;
    margin-bottom: 6px;
}

.header p {
    color: #777;
    font-size: 14px;
}


/* =========================================================
   MAIN CONTAINER
   ========================================================= */

.container {
    width: 92%;
    max-width: 1200px;
    margin: 35px auto 60px;
}


/* =========================================================
   USER INFO
   ========================================================= */

.user-box {
    background: #ffffff;
    border-radius: 16px;
    padding: 20px 24px;
    margin-bottom: 25px;
    border: 1px solid #eeeeee;
}

.user-box h2 {
    font-size: 20px;
    margin-bottom: 8px;
}

.user-box p {
    color: #666;
    margin-top: 5px;
}


/* =========================================================
   MESSAGE
   ========================================================= */

.message {
    padding: 15px 18px;
    border-radius: 10px;
    margin-bottom: 20px;
    font-size: 15px;
    font-weight: 600;
}

.message.success {
    background: #eaf8ef;
    color: #187a3d;
    border: 1px solid #bde5ca;
}

.message.error {
    background: #fff0f0;
    color: #c62828;
    border: 1px solid #f1bcbc;
}


/* =========================================================
   NO ORDERS
   ========================================================= */

.no-orders {
    background: #ffffff;
    border-radius: 18px;
    padding: 60px 20px;
    text-align: center;
    border: 1px solid #eeeeee;
}

.no-orders .emoji {
    font-size: 55px;
    margin-bottom: 15px;
}

.no-orders h2 {
    font-size: 25px;
    margin-bottom: 8px;
}

.no-orders p {
    color: #777;
}


/* =========================================================
   ORDER CARD
   ========================================================= */

.order-card {
    background: #ffffff;
    border-radius: 18px;
    margin-bottom: 28px;
    border: 1px solid #e8e8e8;
    overflow: hidden;
    box-shadow: 0 5px 18px rgba(0,0,0,0.04);
}


/* =========================================================
   ORDER TOP
   ========================================================= */

.order-top {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 15px;
    padding: 20px 24px;
    border-bottom: 1px solid #eeeeee;
}

.order-number {
    font-size: 20px;
    font-weight: 700;
}

.order-date {
    color: #777;
    font-size: 13px;
    margin-top: 5px;
}


/* =========================================================
   STATUS
   ========================================================= */

.status {
    display: inline-block;
    padding: 8px 15px;
    border-radius: 30px;
    font-size: 13px;
    font-weight: 700;
}

.status.pending {
    background: #fff4d6;
    color: #a56a00;
}

.status.delivered {
    background: #e6f7ec;
    color: #218642;
}

.status.cancelled {
    background: #ffe8e8;
    color: #c62828;
}

.status.processing {
    background: #e7f0ff;
    color: #2764b8;
}


/* =========================================================
   PRODUCTS HEADER
   ========================================================= */

.products-heading {
    padding: 18px 24px 10px;
    font-size: 17px;
    font-weight: 700;
}


/* =========================================================
   PRODUCT ROW
   ========================================================= */

.product-row {
    display: grid;
    grid-template-columns:
        90px
        minmax(200px, 1fr)
        130px
        100px
        140px;

    align-items: center;
    gap: 22px;

    background: #fafafa;
    border: 1px solid #eeeeee;
    border-radius: 16px;

    padding: 14px 20px;
    min-height: 105px;

    margin: 10px 24px;
}


/* =========================================================
   PRODUCT IMAGE
   ========================================================= */

.product-image-box {
    width: 90px;
    height: 90px;

    background: #ffffff;
    border-radius: 12px;

    display: flex;
    align-items: center;
    justify-content: center;

    overflow: hidden;
    border: 1px solid #eeeeee;
}

.product-image-box img {
    width: 100%;
    height: 100%;
    object-fit: contain;
    padding: 6px;
}

.no-image {
    color: #aaa;
    font-size: 12px;
    text-align: center;
}


/* =========================================================
   PRODUCT NAME
   ========================================================= */

.product-name {
    min-width: 0;
}

.product-name h3 {
    font-size: 16px;
    margin-bottom: 7px;
    white-space: normal;
    word-break: break-word;
}

.product-name p {
    font-size: 13px;
    color: #777;
}


/* =========================================================
   PRODUCT PRICE
   ========================================================= */

.product-price {
    text-align: center;
}

.product-price span {
    display: block;
    font-size: 12px;
    color: #888;
    margin-bottom: 5px;
}

.product-price strong {
    font-size: 16px;
}


/* =========================================================
   PRODUCT QUANTITY
   ========================================================= */

.product-qty {
    text-align: center;
}

.product-qty span {
    display: block;
    font-size: 12px;
    color: #888;
    margin-bottom: 5px;
}

.product-qty strong {
    font-size: 16px;
}


/* =========================================================
   ITEM TOTAL
   ========================================================= */

.item-total {
    text-align: right;
}

.item-total span {
    display: block;
    font-size: 12px;
    color: #888;
    margin-bottom: 5px;
}

.item-total strong {
    font-size: 18px;
}


/* =========================================================
   CUSTOMER DETAILS
   ========================================================= */

.details-box {
    margin: 20px 24px;
    background: #ffffff;
    border: 1px solid #eeeeee;
    border-radius: 14px;
    padding: 18px 20px;
}

.details-title {
    font-size: 16px;
    font-weight: 700;
    margin-bottom: 14px;
}

.details-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 12px 30px;
}

.detail-item {
    font-size: 14px;
    color: #555;
}

.detail-item strong {
    color: #222;
}


/* =========================================================
   ORDER SUMMARY
   ========================================================= */

.order-summary {
    border-top: 1px solid #eeeeee;
    padding: 20px 24px 24px;
    background: #fcfcfc;
}

.summary-row {
    display: flex;
    justify-content: space-between;
    padding: 6px 0;
    color: #555;
    font-size: 14px;
}

.summary-row.total {
    margin-top: 10px;
    padding-top: 15px;
    border-top: 1px dashed #ddd;
    color: #111;
    font-size: 20px;
    font-weight: 700;
}


/* =========================================================
   CANCEL BUTTON
   ========================================================= */

.cancel-area {
    margin-top: 18px;
    display: flex;
    justify-content: flex-end;
}

.cancel-btn {
    border: none;
    background: #e53935;
    color: #ffffff;

    padding: 11px 22px;
    border-radius: 8px;

    font-size: 14px;
    font-weight: 700;

    cursor: pointer;
    transition: 0.2s;
}

.cancel-btn:hover {
    background: #c62828;
}


/* =========================================================
   RESPONSIVE
   ========================================================= */

@media (max-width: 900px) {

    .product-row {
        grid-template-columns:
            80px
            1fr
            100px;

        gap: 15px;
    }

    .product-price,
    .product-qty,
    .item-total {
        text-align: left;
    }

    .item-total {
        grid-column: 2 / 4;
    }
}


@media (max-width: 600px) {

    .container {
        width: 94%;
        margin-top: 20px;
    }

    .header {
        padding: 18px 5%;
    }

    .header h1 {
        font-size: 25px;
    }

    .order-top {
        flex-direction: column;
        align-items: flex-start;
    }

    .product-row {
        grid-template-columns: 75px 1fr;
        gap: 15px;
        padding: 12px;
        margin: 10px 12px;
    }

    .product-image-box {
        width: 75px;
        height: 75px;
    }

    .product-price,
    .product-qty,
    .item-total {
        text-align: left;
        grid-column: 2;
    }

    .details-box {
        margin: 15px 12px;
    }

    .details-grid {
        grid-template-columns: 1fr;
    }

    .order-summary {
        padding: 18px 15px;
    }
}

</style>

</head>


<body>


<!-- =====================================================
     PAGE HEADER
     ===================================================== -->

<div class="header">

    <h1>My Orders</h1>

    <p>
        View your complete grocery order history
    </p>

</div>


<!-- =====================================================
     MAIN
     ===================================================== -->

<div class="container">


    <!-- USER INFORMATION -->

    <div class="user-box">

        <h2>
            Welcome,
            <?php echo htmlspecialchars($userName); ?>
            👋
        </h2>

        <p>
            Email:
            <?php echo htmlspecialchars($userEmail); ?>
        </p>

    </div>


    <!-- MESSAGE -->

    <?php if ($message !== ""): ?>

        <div class="message <?php
            echo $messageType === 'success'
                ? 'success'
                : 'error';
        ?>">

            <?php echo htmlspecialchars($message); ?>

        </div>

    <?php endif; ?>


    <?php if ($orderResult->num_rows === 0): ?>


        <!-- NO ORDERS -->

        <div class="no-orders">

            <div class="emoji">
                📦
            </div>

            <h2>
                No Orders Yet
            </h2>

            <p>
                You haven't placed any orders yet.
            </p>

        </div>


    <?php else: ?>


        <!-- =================================================
             ORDER LOOP
             ================================================= -->

        <?php while ($order = $orderResult->fetch_assoc()): ?>


            <?php

            $orderId = (int) $order['id'];

            $status =
                trim($order['status'] ?? 'Pending');

            $statusClass =
                strtolower($status);

            ?>


            <div class="order-card">


                <!-- ORDER HEADER -->

                <div class="order-top">

                    <div>

                        <div class="order-number">

                            Order #<?php
                                echo $orderId;
                            ?>

                        </div>

                        <div class="order-date">

                            <?php

                            echo htmlspecialchars(
                                $order['created_at']
                            );

                            ?>

                        </div>

                    </div>


                    <div>

                        <span class="status <?php
                            echo htmlspecialchars(
                                $statusClass
                            );
                        ?>">

                            <?php
                            echo htmlspecialchars($status);
                            ?>

                        </span>

                    </div>

                </div>


                <!-- PRODUCTS HEADING -->

                <div class="products-heading">

                    Ordered Products

                </div>


                <!-- =================================================
                     GET ORDER ITEMS
                     ================================================= -->

                <?php

                $itemStmt = $conn->prepare("
                    SELECT
                        oi.id AS item_id,
                        oi.product_id,
                        oi.product_name,
                        oi.price,
                        oi.quantity,
                        oi.total,
                        p.product_image
                    FROM order_items oi
                    LEFT JOIN products p
                        ON p.id = oi.product_id
                    WHERE oi.order_id = ?
                    ORDER BY oi.id ASC
                ");

                $itemStmt->bind_param(
                    "i",
                    $orderId
                );

                $itemStmt->execute();

                $itemResult =
                    $itemStmt->get_result();

                ?>


                <!-- =================================================
                     ORDER ITEMS
                     ================================================= -->

                <?php while (
                    $item = $itemResult->fetch_assoc()
                ): ?>


                    <?php

                    $productName =
                        $item['product_name']
                        ?? 'Product';

                    $price =
                        (float) ($item['price'] ?? 0);

                    $quantity =
                        $item['quantity'] ?? 0;

                    $itemTotal =
                        (float) ($item['total'] ?? 0);

                    $productImage =
                        trim(
                            $item['product_image']
                            ?? ''
                        );

                    ?>


                    <div class="product-row">


                        <!-- IMAGE -->

                        <div class="product-image-box">

                            <?php if (
                                $productImage !== ""
                            ): ?>

                                <img
                                    src="<?php
                                        echo htmlspecialchars(
                                            $productImage
                                        );
                                    ?>"
                                    alt="<?php
                                        echo htmlspecialchars(
                                            $productName
                                        );
                                    ?>"
                                    onerror="this.style.display='none'; this.parentElement.innerHTML='<span class=\'no-image\'>No Image</span>';"
                                >

                            <?php else: ?>

                                <span class="no-image">
                                    No Image
                                </span>

                            <?php endif; ?>

                        </div>


                        <!-- PRODUCT NAME -->

                        <div class="product-name">

                            <h3>

                                <?php

                                echo htmlspecialchars(
                                    $productName
                                );

                                ?>

                            </h3>

                            <p>

                                Product ID:

                                <?php

                                echo (int)
                                    $item['product_id'];

                                ?>

                            </p>

                        </div>


                        <!-- PRICE -->

                        <div class="product-price">

                            <span>
                                Price
                            </span>

                            <strong>

                                ₹<?php

                                echo number_format(
                                    $price,
                                    2
                                );

                                ?>

                            </strong>

                        </div>


                        <!-- QUANTITY -->

                        <div class="product-qty">

                            <span>
                                Quantity
                            </span>

                            <strong>

                                <?php

                                echo htmlspecialchars(
                                    (string) $quantity
                                );

                                ?>

                            </strong>

                        </div>


                        <!-- ITEM TOTAL -->

                        <div class="item-total">

                            <span>
                                Item Total
                            </span>

                            <strong>

                                ₹<?php

                                echo number_format(
                                    $itemTotal,
                                    2
                                );

                                ?>

                            </strong>

                        </div>


                    </div>


                <?php endwhile; ?>


                <?php $itemStmt->close(); ?>


                <!-- =================================================
                     CUSTOMER DETAILS
                     ================================================= -->

                <div class="details-box">

                    <div class="details-title">

                        Customer & Payment Details

                    </div>


                    <div class="details-grid">


                        <div class="detail-item">

                            <strong>
                                Name:
                            </strong>

                            <?php

                            echo htmlspecialchars(
                                $order['first_name']
                            );

                            ?>

                        </div>


                        <div class="detail-item">

                            <strong>
                                Email:
                            </strong>

                            <?php

                            echo htmlspecialchars(
                                $order['email']
                            );

                            ?>

                        </div>


                        <div class="detail-item">

                            <strong>
                                Phone:
                            </strong>

                            <?php

                            echo htmlspecialchars(
                                $order['phone']
                            );

                            ?>

                        </div>


                        <div class="detail-item">

                            <strong>
                                Payment:
                            </strong>

                            <?php

                            echo htmlspecialchars(
                                $order['payment_method']
                            );

                            ?>

                        </div>


                        <div class="detail-item">

                            <strong>
                                Address:
                            </strong>

                            <?php

                            echo htmlspecialchars(
                                $order['address']
                            );

                            ?>

                        </div>


                    </div>

                </div>


                <!-- =================================================
                     ORDER SUMMARY
                     ================================================= -->

                <div class="order-summary">


                    <div class="summary-row">

                        <span>
                            Subtotal
                        </span>

                        <span>

                            ₹<?php

                            echo number_format(
                                (float)
                                $order['subtotal'],
                                2
                            );

                            ?>

                        </span>

                    </div>


                    <div class="summary-row">

                        <span>
                            GST
                        </span>

                        <span>

                            ₹<?php

                            echo number_format(
                                (float)
                                $order['gst'],
                                2
                            );

                            ?>

                        </span>

                    </div>


                    <div class="summary-row total">

                        <span>
                            Grand Total
                        </span>

                        <span>

                            ₹<?php

                            echo number_format(
                                (float)
                                $order['grand_total'],
                                2
                            );

                            ?>

                        </span>

                    </div>


                    <!-- CANCEL BUTTON -->

                    <?php if (
                        strtolower($status) === 'pending'
                    ): ?>

                        <div class="cancel-area">

                            <form
                                method="POST"
                                onsubmit="return confirmCancel();"
                            >

                                <input
                                    type="hidden"
                                    name="order_id"
                                    value="<?php
                                        echo $orderId;
                                    ?>"
                                >

                                <button
                                    type="submit"
                                    name="cancel_order"
                                    class="cancel-btn"
                                >
                                    Cancel Order
                                </button>

                            </form>

                        </div>

                    <?php endif; ?>


                </div>


            </div>


        <?php endwhile; ?>


    <?php endif; ?>


</div>


<script>

function confirmCancel() {

    return confirm(
        "Are you sure you want to cancel this order?"
    );

}

</script>


</body>

</html>


<?php

$orderStmt->close();

$conn->close();

include("footer1.php");

?>