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
   CHECK LOGIN
   ========================================================= */

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}


/* =========================================================
   BUY NOW vs NORMAL CART CHECKOUT HANDLER
   ========================================================= */

$is_buy_now = false;
$buy_now_id = 0;

if (isset($_POST['buy_now_id']) && (int)$_POST['buy_now_id'] > 0) {
    $is_buy_now = true;
    $buy_now_id = (int)$_POST['buy_now_id'];
    $cart = [['product_id' => $buy_now_id, 'quantity' => 1]];
} elseif (isset($_GET['id']) && is_numeric($_GET['id']) && (int)$_GET['id'] > 0) {
    $is_buy_now = true;
    $buy_now_id = (int)$_GET['id'];
    $cart = [['product_id' => $buy_now_id, 'quantity' => 1]];
} else {
    $cart = $_SESSION['cart'] ?? [];
}


/* =========================================================
   TOTAL VARIABLES
   ========================================================= */

$subtotal   = 0;
$delivery   = 40;
$gst        = 0;
$grandTotal = 0;

$orderMessage = "";
$orderError   = "";
$orderId      = 0;


/* =========================================================
   CURRENT USER
   ========================================================= */

$userId = (int) $_SESSION['user_id'];

$userStmt = $conn->prepare("
    SELECT
        id,
        full_name,
        email
    FROM user_register
    WHERE id = ?
    LIMIT 1
");

if ($userStmt) {
    $userStmt->bind_param("i", $userId);
    $userStmt->execute();
    $userResult = $userStmt->get_result();
    $userData   = $userResult->fetch_assoc();
    $userStmt->close();

    if ($userData) {
        $_SESSION['username']   = $userData['full_name'];
        $_SESSION['user_email'] = $userData['email'];
    }
}


/* =========================================================
   FUNCTION : GET CART ITEM DATA
   ========================================================= */

function getCartItemData($cartItem)
{
    if (is_array($cartItem)) {
        $product_id = (int)($cartItem['product_id'] ?? 0);
        $quantity   = (int)($cartItem['quantity'] ?? 1);
    } else {
        $product_id = (int)$cartItem;
        $quantity   = 1;
    }

    if ($quantity < 1) {
        $quantity = 1;
    }

    return [
        'product_id' => $product_id,
        'quantity'   => $quantity
    ];
}


/* =========================================================
   PLACE ORDER
   ========================================================= */

if (isset($_POST['place_order'])) {

    $customerName = trim($_SESSION['username'] ?? '');
    $email        = trim($_SESSION['user_email'] ?? '');
    $phone        = trim($_POST['phone'] ?? '');
    $address      = trim($_POST['address'] ?? '');
    $payment      = trim($_POST['payment'] ?? '');

    if ($customerName === '') {
        $orderError = "Customer name not found. Please login again.";
    } elseif ($email === '') {
        $orderError = "Customer email not found. Please login again.";
    } elseif ($phone === '') {
        $orderError = "Please enter phone number.";
    } elseif ($address === '') {
        $orderError = "Please enter delivery address.";
    } elseif ($payment === '') {
        $orderError = "Please select payment method.";
    } elseif ($payment !== "Cash On Delivery") {
        $orderError = "Invalid payment method.";
    } elseif (empty($cart)) {
        $orderError = "Your cart is empty.";
    } else {

        $subtotal = 0;
        $validCartItems = [];

        foreach ($cart as $cartItem) {

            $itemData     = getCartItemData($cartItem);
            $product_id   = $itemData['product_id'];
            $cartQuantity = $itemData['quantity'];

            if ($product_id <= 0) {
                continue;
            }

            $stmt = $conn->prepare("
                SELECT id, product_name, price, quantity
                FROM products
                WHERE id = ?
                LIMIT 1
            ");

            if (!$stmt) {
                $orderError = "Database error: " . $conn->error;
                break;
            }

            $stmt->bind_param("i", $product_id);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 0) {
                $stmt->close();
                continue;
            }

            $product = $result->fetch_assoc();
            $stmt->close();

            $price = (float)$product['price'];

            // Check stock
            $stockValue = $product['quantity'];
            if (is_numeric($stockValue)) {
                $availableStock = (int)$stockValue;
                if ($cartQuantity > $availableStock) {
                    $orderError = "Not enough stock for " . $product['product_name'] . ". Available stock: " . $availableStock;
                    break;
                }
            }

            $itemTotal = $price * $cartQuantity;
            $subtotal += $itemTotal;

            $validCartItems[] = [
                'product_id'   => $product_id,
                'product_name' => $product['product_name'],
                'price'        => $price,
                'quantity'     => $cartQuantity,
                'item_total'   => $itemTotal
            ];
        }

        if ($orderError === '' && empty($validCartItems)) {
            $orderError = "No valid products found in cart.";
        }

        if ($orderError === '') {

            // GST = 5% of (Subtotal + Delivery)
            $gst = ($subtotal + $delivery) * 0.05;

            // GRAND TOTAL = Subtotal + Delivery + GST (FIXED)
            $grandTotal = $subtotal + $delivery + $gst;

            $conn->begin_transaction();

            try {
                $status = "Pending";

                $orderStmt = $conn->prepare("
                    INSERT INTO orders
                    (first_name, email, phone, address, payment_method, subtotal, gst, grand_total, status)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");

                if (!$orderStmt) {
                    throw new Exception("Orders table error: " . $conn->error);
                }

                $orderStmt->bind_param(
                    "sssssddds",
                    $customerName,
                    $email,
                    $phone,
                    $address,
                    $payment,
                    $subtotal,
                    $gst,
                    $grandTotal,
                    $status
                );

                if (!$orderStmt->execute()) {
                    throw new Exception("Order insert failed: " . $orderStmt->error);
                }

                $orderId = $conn->insert_id;
                $orderStmt->close();

                // Order items insert & Stock reduction
                foreach ($validCartItems as $item) {
                    $itemStmt = $conn->prepare("
                        INSERT INTO order_items
                        (order_id, product_id, product_name, price, quantity, total)
                        VALUES (?, ?, ?, ?, ?, ?)
                    ");

                    if ($itemStmt) {
                        $itemStmt->bind_param(
                            "iisdid",
                            $orderId,
                            $item['product_id'],
                            $item['product_name'],
                            $item['price'],
                            $item['quantity'],
                            $item['item_total']
                        );
                        $itemStmt->execute();
                        $itemStmt->close();
                    }

                    // Update stock
                    $stockStmt = $conn->prepare("SELECT quantity FROM products WHERE id = ? LIMIT 1");
                    if ($stockStmt) {
                        $stockStmt->bind_param("i", $item['product_id']);
                        $stockStmt->execute();
                        $stockRes = $stockStmt->get_result();
                        $stockData = $stockRes->fetch_assoc();
                        $stockStmt->close();

                        if ($stockData && is_numeric($stockData['quantity'])) {
                            $newStock = (int)$stockData['quantity'] - $item['quantity'];
                            if ($newStock < 0) { $newStock = 0; }

                            $updStock = $conn->prepare("UPDATE products SET quantity = ? WHERE id = ?");
                            if ($updStock) {
                                $updStock->bind_param("ii", $newStock, $item['product_id']);
                                $updStock->execute();
                                $updStock->close();
                            }
                        }
                    }
                }

                $conn->commit();

                // Clear cart only if NOT buy now
                if (!$is_buy_now) {
                    $_SESSION['cart'] = [];
                }
                $cart = [];

                $orderMessage = "Order placed successfully! Order ID: #" . $orderId;

            } catch (Exception $e) {
                $conn->rollback();
                $orderId = 0;
                $orderError = $e->getMessage();
            }
        }
    }
}


/* =========================================================
   DISPLAY CART TOTAL FOR RENDERING
   ========================================================= */

if (!empty($cart)) {

    $subtotal = 0;

    foreach ($cart as $cartItem) {
        $itemData     = getCartItemData($cartItem);
        $product_id   = $itemData['product_id'];
        $cartQuantity = $itemData['quantity'];

        if ($product_id <= 0) {
            continue;
        }

        $stmt = $conn->prepare("
            SELECT id, product_name, price, product_image, quantity
            FROM products
            WHERE id = ?
            LIMIT 1
        ");

        if (!$stmt) {
            continue;
        }

        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            $stmt->close();
            continue;
        }

        $product = $result->fetch_assoc();
        $price = (float)$product['price'];
        $itemTotal = $price * $cartQuantity;
        $subtotal += $itemTotal;

        $stmt->close();
    }

    $gst        = ($subtotal + $delivery) * 0.05;
    $grandTotal = $subtotal + $delivery + $gst; // FIXED
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Checkout - Shreenath Grocery Store</title>

<link rel="stylesheet" href="checkout.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>
* { box-sizing: border-box; }
body { margin: 0; background: #f7f8fa; font-family: Arial, sans-serif; }
.checkout-container { width: 94%; max-width: 1450px; margin: 35px auto 70px; display: flex; gap: 30px; align-items: flex-start; }
.left-box { flex: 2; background: #fff; padding: 35px; border-radius: 18px; box-shadow: 0 8px 25px rgba(0,0,0,.08); }
.right-box { flex: 1; min-width: 400px; background: #fff; padding: 35px; border-radius: 18px; box-shadow: 0 8px 25px rgba(0,0,0,.08); height: fit-content; }
.left-box h2, .right-box h2 { color: #176b35; margin-bottom: 25px; font-size: 24px; }
.form-group { margin-bottom: 20px; }
.form-group label { display: block; margin-bottom: 8px; font-weight: bold; color: #333; }
.form-group input, .form-group textarea, .form-group select { width: 100%; padding: 14px; border: 1px solid #ddd; border-radius: 10px; font-size: 16px; outline: none; }
.form-group input:focus, .form-group textarea:focus, .form-group select:focus { border-color: #176b35; }
.place-order { width: 100%; padding: 18px; background: #ff6500; color: white; border: none; border-radius: 12px; font-size: 20px; font-weight: bold; cursor: pointer; margin-top: 15px; transition: .2s; }
.place-order:hover { background: #e85600; }
.order-product { display: flex; align-items: center; gap: 15px; margin-bottom: 20px; padding-bottom: 18px; border-bottom: 1px solid #eee; }
.order-product img { width: 80px; height: 80px; min-width: 80px; border-radius: 10px; object-fit: cover; background: #f1f1f1; }
.order-product-info { flex: 1; }
.order-product h4 { color: #176b35; font-size: 18px; margin: 0 0 5px; }
.summary-row { display: flex; justify-content: space-between; margin: 15px 0; font-size: 18px; }
.grand { color: #176b35; font-weight: bold; font-size: 22px; }
.empty-checkout { width: 94%; max-width: 800px; margin: 50px auto; background: white; padding: 60px; text-align: center; border-radius: 18px; box-shadow: 0 8px 25px rgba(0,0,0,.08); }
.empty-checkout h2 { color: #176b35; }
.empty-checkout a { display: inline-block; padding: 12px 25px; background: #176b35; color: white; text-decoration: none; border-radius: 8px; margin-top: 15px; }
.success-message { background: #e8f5e9; color: #176b35; padding: 15px; border-radius: 8px; margin-bottom: 20px; font-weight: bold; text-align: center; }
.error-message { background: #ffebee; color: #c62828; padding: 15px; border-radius: 8px; margin-bottom: 20px; font-weight: bold; text-align: center; }
@media(max-width: 900px) { .checkout-container { flex-direction: column; } .right-box { width: 100%; min-width: 100%; } }
</style>
</head>

<body>

<?php include 'header.php'; ?>

<?php if (empty($cart)) { ?>

<div class="empty-checkout">
    <?php if ($orderMessage !== '') { ?>
        <div class="success-message">
            <?php echo htmlspecialchars($orderMessage); ?>
        </div>
        <p>Your order has been placed successfully. Thank you for shopping with us!</p>
        <a href="orders.php">View Your Orders</a>
    <?php } elseif ($orderError !== '') { ?>
        <div class="error-message">
            <?php echo htmlspecialchars($orderError); ?>
        </div>
        <a href="productListing.php">Back To Shopping</a>
    <?php } else { ?>
        <h2>Your cart is empty 😔</h2>
        <p>Please add some products before checkout.</p>
        <a href="productListing.php">Continue Shopping</a>
    <?php } ?>
</div>

<?php } else { ?>

<div class="checkout-container">

    <!-- LEFT BOX : DELIVERY FORM -->
    <div class="left-box">
        <h2><i class="fa-solid fa-location-dot"></i> Delivery Information</h2>

        <?php if ($orderMessage !== '') { ?>
            <div class="success-message"><?php echo htmlspecialchars($orderMessage); ?></div>
        <?php } ?>

        <?php if ($orderError !== '') { ?>
            <div class="error-message"><?php echo htmlspecialchars($orderError); ?></div>
        <?php } ?>

        <form method="POST" action="">

            <?php if ($is_buy_now && $buy_now_id > 0) { ?>
                <input type="hidden" name="buy_now_id" value="<?php echo $buy_now_id; ?>">
            <?php } ?>

            <div class="form-group">
                <label>Full Name</label>
                <input type="text" value="<?php echo htmlspecialchars($_SESSION['username'] ?? ''); ?>" readonly style="background:#f0f0f0;">
            </div>

            <div class="form-group">
                <label>Email Address</label>
                <input type="email" value="<?php echo htmlspecialchars($_SESSION['user_email'] ?? ''); ?>" readonly style="background:#f0f0f0;">
            </div>

            <div class="form-group">
                <label>Mobile Number *</label>
                <input type="text" name="phone" placeholder="Enter 10 Digit Mobile Number" required maxlength="10">
            </div>

            <div class="form-group">
                <label>Delivery Address *</label>
                <textarea name="address" rows="4" placeholder="Enter Full Delivery Address" required></textarea>
            </div>

            <div class="form-group">
                <label>Payment Method</label>
                <select name="payment" required>
                    <option value="Cash On Delivery">Cash On Delivery (COD)</option>
                </select>
            </div>

            <button type="submit" name="place_order" class="place-order">
                <i class="fa-solid fa-check-circle"></i> Place Order
            </button>

        </form>
    </div>

    <!-- RIGHT BOX : ORDER SUMMARY -->
    <div class="right-box">
        <h2><i class="fa-solid fa-receipt"></i> Order Summary</h2>

        <div id="order-items">
            <?php
            foreach ($cart as $cartItem) {
                $itemData     = getCartItemData($cartItem);
                $product_id   = $itemData['product_id'];
                $cartQuantity = $itemData['quantity'];

                if ($product_id <= 0) continue;

                $stmt = $conn->prepare("SELECT product_name, price, product_image FROM products WHERE id = ? LIMIT 1");
                if (!$stmt) continue;
                $stmt->bind_param("i", $product_id);
                $stmt->execute();
                $res = $stmt->get_result();
                if ($res->num_rows == 0) { $stmt->close(); continue; }
                $prod = $res->fetch_assoc();
                $stmt->close();

                $pName  = $prod['product_name'];
                $pPrice = (float)$prod['price'];
                $pImage = $prod['product_image'];
                $pTotal = $pPrice * $cartQuantity;
            ?>

            <div class="order-product">
                <?php if (!empty($pImage)) { ?>
                    <img src="<?php echo htmlspecialchars($pImage); ?>" alt="<?php echo htmlspecialchars($pName); ?>">
                <?php } else { ?>
                    <div style="width:80px;height:80px;background:#eee;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:12px;">No Image</div>
                <?php } ?>

                <div class="order-product-info">
                    <h4><?php echo htmlspecialchars($pName); ?></h4>
                    <p style="margin:4px 0;color:#666;">Qty: <b><?php echo $cartQuantity; ?></b> × ₹<?php echo number_format($pPrice, 2); ?></p>
                    <p style="margin:0;font-weight:bold;color:#176b35;">Total: ₹<?php echo number_format($pTotal, 2); ?></p>
                </div>
            </div>

            <?php } ?>
        </div>

        <div class="summary-row">
            <span>Subtotal</span>
            <strong>₹<?php echo number_format($subtotal, 2); ?></strong>
        </div>

        <div class="summary-row">
            <span>Delivery Charge</span>
            <strong>₹<?php echo number_format($delivery, 2); ?></strong>
        </div>

        <div class="summary-row">
            <span>GST (5%)</span>
            <strong>₹<?php echo number_format($gst, 2); ?></strong>
        </div>

        <hr style="margin:15px 0;border:0;border-top:1px solid #eee;">

        <div class="summary-row grand">
            <span>Grand Total</span>
            <strong>₹<?php echo number_format($grandTotal, 2); ?></strong>
        </div>
    </div>

</div>

<?php } ?>

<?php
$conn->close();
include 'footer1.php';
?>