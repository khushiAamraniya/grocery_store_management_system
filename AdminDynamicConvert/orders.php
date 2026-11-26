<?php

session_start();

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
   DELETE ORDER
   ========================================================= */

if (
    isset($_GET['action']) &&
    $_GET['action'] === 'delete' &&
    isset($_GET['id']) &&
    is_numeric($_GET['id'])
) {

    $order_id = (int) $_GET['id'];

    // Delete order items first
    $stmt = $conn->prepare("
        DELETE FROM order_items
        WHERE order_id = ?
    ");

    if ($stmt) {
        $stmt->bind_param("i", $order_id);
        $stmt->execute();
        $stmt->close();
    }

    // Delete order
    $stmt = $conn->prepare("
        DELETE FROM orders
        WHERE id = ?
    ");

    if ($stmt) {
        $stmt->bind_param("i", $order_id);
        $stmt->execute();
        $stmt->close();
    }

    header("Location: orders.php");
    exit();
}


/* =========================================================
   UPDATE ORDER
   ========================================================= */

if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['update_order'])
) {

    $order_id = (int) ($_POST['order_id'] ?? 0);

    $first_name = trim(
        $_POST['first_name'] ?? ''
    );

    $email = trim(
        $_POST['email'] ?? ''
    );

    $phone = trim(
        $_POST['phone'] ?? ''
    );

    $address = trim(
        $_POST['address'] ?? ''
    );

    $payment_method = trim(
        $_POST['payment_method'] ?? ''
    );

    $subtotal = (float) (
        $_POST['subtotal'] ?? 0
    );

    $gst = (float) (
        $_POST['gst'] ?? 0
    );

    $grand_total = (float) (
        $_POST['grand_total'] ?? 0
    );

    $status = trim(
        $_POST['status'] ?? 'Pending'
    );


    if ($order_id > 0) {

        $stmt = $conn->prepare("
            UPDATE orders
            SET
                first_name = ?,
                email = ?,
                phone = ?,
                address = ?,
                payment_method = ?,
                subtotal = ?,
                gst = ?,
                grand_total = ?,
                status = ?
            WHERE id = ?
        ");


        if (!$stmt) {
            die(
                "Update Query Error: " .
                $conn->error
            );
        }


        $stmt->bind_param(
            "sssssdddsi",
            $first_name,
            $email,
            $phone,
            $address,
            $payment_method,
            $subtotal,
            $gst,
            $grand_total,
            $status,
            $order_id
        );


        $stmt->execute();

        $stmt->close();
    }


    header("Location: orders.php");
    exit();
}


/* =========================================================
   EDIT ORDER
   ========================================================= */

$editOrder = null;


if (
    isset($_GET['action']) &&
    $_GET['action'] === 'edit' &&
    isset($_GET['id']) &&
    is_numeric($_GET['id'])
) {

    $edit_id = (int) $_GET['id'];


    $stmt = $conn->prepare("
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
        WHERE id = ?
        LIMIT 1
    ");


    if (!$stmt) {
        die(
            "Edit Query Error: " .
            $conn->error
        );
    }


    $stmt->bind_param(
        "i",
        $edit_id
    );


    $stmt->execute();


    $result =
        $stmt->get_result();


    if (
        $result &&
        $result->num_rows > 0
    ) {

        $editOrder =
            $result->fetch_assoc();
    }


    $stmt->close();
}


/* =========================================================
   GET ALL ORDERS
   ========================================================= */

$orders = [];


$result = $conn->query("
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
    ORDER BY id DESC
");


if (!$result) {
    die(
        "Orders Query Error: " .
        $conn->error
    );
}


while (
    $row = $result->fetch_assoc()
) {

    $orders[] = $row;
}


/* =========================================================
   ORDER COUNTS
   ========================================================= */

$total_orders =
    count($orders);

$pending_orders = 0;

$delivered_orders = 0;

$cancelled_orders = 0;


foreach ($orders as $order) {

    $status =
        strtolower(
            trim(
                $order['status'] ?? ''
            )
        );


    if ($status === 'pending') {
        $pending_orders++;
    }


    if ($status === 'delivered') {
        $delivered_orders++;
    }


    if ($status === 'cancelled') {
        $cancelled_orders++;
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>

<title>Orders Management</title>


<link
    rel="stylesheet"
    href="admin.css"
>


<link
    rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"
>


<style>

/* =========================================================
   GENERAL
   ========================================================= */

.orders-section {

    background: #fff;

    border-radius: 15px;

    padding: 25px;

    margin-top: 25px;

    box-shadow: 0 5px 20px rgba(0,0,0,.06);
}


.order-stats {

    display: grid;

    grid-template-columns:
        repeat(4, 1fr);

    gap: 20px;

    margin-top: 25px;
}


.stat-card {

    background: #fff;

    border-radius: 15px;

    padding: 22px;

    display: flex;

    align-items: center;

    gap: 18px;

    box-shadow: 0 5px 20px rgba(0,0,0,.06);
}


.stat-icon {

    width: 55px;

    height: 55px;

    border-radius: 12px;

    display: flex;

    align-items: center;

    justify-content: center;

    background: #e8f5e9;

    color: #176b35;

    font-size: 22px;
}


.stat-icon.pending {

    background: #fff4df;

    color: #e69200;
}


.stat-icon.delivered {

    background: #e5f8ec;

    color: #1d8a45;
}


.stat-icon.cancelled {

    background: #ffe8e8;

    color: #d93636;
}


.stat-card h3 {

    margin: 0;

    font-size: 25px;
}


.stat-card p {

    margin: 5px 0 0;

    color: #777;
}


/* =========================================================
   SECTION HEADER
   ========================================================= */

.section-header {

    display: flex;

    justify-content: space-between;

    align-items: center;

    margin-bottom: 20px;
}


.section-header h2 {

    margin: 0;
}


.section-header p {

    margin: 5px 0 0;

    color: #777;
}


/* =========================================================
   SEARCH
   ========================================================= */

.order-tools {

    margin-bottom: 20px;
}


.search-box {

    width: 350px;

    max-width: 100%;

    height: 45px;

    border: 1px solid #ddd;

    border-radius: 8px;

    display: flex;

    align-items: center;

    padding: 0 14px;

    background: #fff;
}


.search-box i {

    color: #888;

    margin-right: 10px;
}


.search-box input {

    width: 100%;

    border: none;

    outline: none;

    font-size: 14px;
}


/* =========================================================
   TABLE
   ========================================================= */

.table-container {

    width: 100%;

    overflow-x: auto;
}


table {

    width: 100%;

    border-collapse: collapse;

    min-width: 1100px;
}


table th {

    background: #f7f8fa;

    padding: 15px;

    text-align: left;

    font-size: 13px;

    color: #555;

    white-space: nowrap;
}


table td {

    padding: 15px;

    border-bottom: 1px solid #eee;

    vertical-align: middle;

    font-size: 14px;
}


.customer {

    display: flex;

    align-items: center;

    gap: 10px;
}


.customer-icon {

    width: 38px;

    height: 38px;

    border-radius: 50%;

    background: #eaf6ed;

    color: #176b35;

    display: flex;

    align-items: center;

    justify-content: center;
}


.customer strong {

    display: block;
}


.customer small {

    display: block;

    color: #888;

    margin-top: 3px;
}


/* =========================================================
   STATUS
   ========================================================= */

.status-badge {

    display: inline-block;

    padding: 7px 12px;

    border-radius: 20px;

    font-size: 12px;

    font-weight: 600;

    white-space: nowrap;
}


.status-pending {

    background: #fff4df;

    color: #d88600;
}


.status-delivered {

    background: #e5f8ec;

    color: #238b49;
}


.status-cancelled {

    background: #ffe8e8;

    color: #d93636;
}


/* =========================================================
   PAYMENT
   ========================================================= */

.payment-badge {

    display: inline-block;

    padding: 7px 11px;

    border-radius: 20px;

    background: #eef5ff;

    color: #2866b1;

    font-size: 12px;

    font-weight: 600;

    white-space: nowrap;
}


/* =========================================================
   ACTION BUTTONS
   ========================================================= */

.action-buttons {

    display: flex;

    justify-content: center;

    align-items: center;

    gap: 8px;
}


.action-btn {

    display: inline-flex;

    align-items: center;

    justify-content: center;

    gap: 6px;

    padding: 8px 12px;

    border-radius: 7px;

    text-decoration: none;

    font-size: 13px;

    font-weight: 600;

    transition: .2s;

    white-space: nowrap;
}


.edit-btn {

    background: #eaf4ff;

    color: #1976d2;
}


.edit-btn:hover {

    background: #1976d2;

    color: #fff;
}


.delete-btn {

    background: #fff0f0;

    color: #d32f2f;
}


.delete-btn:hover {

    background: #d32f2f;

    color: #fff;
}


/* =========================================================
   EDIT BOX
   ========================================================= */

.edit-box {

    background: #fff;

    border-radius: 15px;

    padding: 25px;

    margin-top: 25px;

    box-shadow: 0 5px 20px rgba(0,0,0,.06);
}


.edit-box h2 {

    margin-top: 0;

    margin-bottom: 22px;

    color: #176b35;
}


.edit-form {

    display: grid;

    grid-template-columns:
        repeat(2, 1fr);

    gap: 18px;
}


.form-group {

    display: flex;

    flex-direction: column;

    gap: 7px;
}


.form-group.full {

    grid-column: 1 / -1;
}


.form-group label {

    font-size: 14px;

    font-weight: 600;

    color: #444;
}


.form-group input,
.form-group textarea,
.form-group select {

    width: 100%;

    padding: 11px 12px;

    border: 1px solid #ddd;

    border-radius: 7px;

    outline: none;

    font-family: inherit;

    font-size: 14px;
}


.form-group input:focus,
.form-group textarea:focus,
.form-group select:focus {

    border-color: #176b35;
}


.edit-actions {

    grid-column: 1 / -1;

    display: flex;

    gap: 10px;

    margin-top: 5px;
}


.save-btn {

    border: none;

    background: #176b35;

    color: #fff;

    padding: 11px 20px;

    border-radius: 7px;

    font-weight: 600;

    cursor: pointer;
}


.save-btn:hover {

    background: #0f5228;
}


.cancel-btn {

    display: inline-flex;

    align-items: center;

    padding: 11px 20px;

    border-radius: 7px;

    background: #eee;

    color: #444;

    text-decoration: none;

    font-weight: 600;
}


/* =========================================================
   RESPONSIVE
   ========================================================= */

@media (max-width: 1100px) {

    .order-stats {

        grid-template-columns:
            repeat(2, 1fr);
    }
}


@media (max-width: 700px) {

    .order-stats {

        grid-template-columns: 1fr;
    }


    .edit-form {

        grid-template-columns: 1fr;
    }


    .form-group.full {

        grid-column: auto;
    }


    .edit-actions {

        grid-column: auto;
    }
}

</style>

</head>


<body>


<div class="container">


<?php include 'sidebar.php'; ?>


<main class="main-content">


<!-- =========================================================
     TOP HEADER
     ========================================================= -->

<header class="top-header">

    <div>

        <h2>
            Orders Management
        </h2>

        <p>
            Manage and track customer orders
        </p>

    </div>

</header>


<!-- =========================================================
     EDIT ORDER
     ========================================================= -->

<?php if ($editOrder !== null): ?>


<section class="edit-box">


<h2>

    <i class="fa-solid fa-pen-to-square"></i>

    Edit Order #

    <?php
    echo (int)$editOrder['id'];
    ?>

</h2>


<form
    method="POST"
    class="edit-form"
>


<input
    type="hidden"
    name="order_id"
    value="<?php
    echo (int)$editOrder['id'];
    ?>"
>


<div class="form-group">

    <label>
        Customer Name
    </label>

    <input
        type="text"
        name="first_name"
        required
        value="<?php

        echo htmlspecialchars(
            $editOrder['first_name'] ?? '',
            ENT_QUOTES,
            'UTF-8'
        );

        ?>"
    >

</div>


<div class="form-group">

    <label>
        Email
    </label>

    <input
        type="email"
        name="email"
        required
        value="<?php

        echo htmlspecialchars(
            $editOrder['email'] ?? '',
            ENT_QUOTES,
            'UTF-8'
        );

        ?>"
    >

</div>


<div class="form-group">

    <label>
        Phone
    </label>

    <input
        type="text"
        name="phone"
        value="<?php

        echo htmlspecialchars(
            $editOrder['phone'] ?? '',
            ENT_QUOTES,
            'UTF-8'
        );

        ?>"
    >

</div>


<div class="form-group">

    <label>
        Payment Method
    </label>

    <select name="payment_method">

        <option
            value="Cash On Delivery"

            <?php

            echo (
                ($editOrder['payment_method'] ?? '')
                === 'Cash On Delivery'
            )
            ? 'selected'
            : '';

            ?>
        >

            Cash On Delivery

        </option>


        <option
            value="Online Payment"

            <?php

            echo (
                ($editOrder['payment_method'] ?? '')
                === 'Online Payment'
            )
            ? 'selected'
            : '';

            ?>
        >

            Online Payment

        </option>

    </select>

</div>


<div class="form-group full">

    <label>
        Address
    </label>

    <textarea
        name="address"
        rows="3"
        required
    ><?php

    echo htmlspecialchars(
        $editOrder['address'] ?? '',
        ENT_QUOTES,
        'UTF-8'
    );

    ?></textarea>

</div>


<div class="form-group">

    <label>
        Subtotal
    </label>

    <input
        type="number"
        step="0.01"
        name="subtotal"
        value="<?php

        echo htmlspecialchars(
            $editOrder['subtotal'] ?? 0,
            ENT_QUOTES,
            'UTF-8'
        );

        ?>"
    >

</div>


<div class="form-group">

    <label>
        GST
    </label>

    <input
        type="number"
        step="0.01"
        name="gst"
        value="<?php

        echo htmlspecialchars(
            $editOrder['gst'] ?? 0,
            ENT_QUOTES,
            'UTF-8'
        );

        ?>"
    >

</div>


<div class="form-group">

    <label>
        Grand Total
    </label>

    <input
        type="number"
        step="0.01"
        name="grand_total"
        value="<?php

        echo htmlspecialchars(
            $editOrder['grand_total'] ?? 0,
            ENT_QUOTES,
            'UTF-8'
        );

        ?>"
    >

</div>


<div class="form-group">

    <label>
        Status
    </label>

    <select name="status">

        <option
            value="Pending"

            <?php

            echo (
                ($editOrder['status'] ?? '')
                === 'Pending'
            )
            ? 'selected'
            : '';

            ?>
        >

            Pending

        </option>


        <option
            value="Delivered"

            <?php

            echo (
                ($editOrder['status'] ?? '')
                === 'Delivered'
            )
            ? 'selected'
            : '';

            ?>
        >

            Delivered

        </option>


        <option
            value="Cancelled"

            <?php

            echo (
                ($editOrder['status'] ?? '')
                === 'Cancelled'
            )
            ? 'selected'
            : '';

            ?>
        >

            Cancelled

        </option>

    </select>

</div>


<div class="edit-actions">


<button
    type="submit"
    name="update_order"
    class="save-btn"
>

    <i class="fa-solid fa-floppy-disk"></i>

    Save Changes

</button>


<a
    href="orders.php"
    class="cancel-btn"
>

    Cancel

</a>


</div>


</form>


</section>


<?php endif; ?>


<!-- =========================================================
     STATISTICS
     ========================================================= -->

<section class="order-stats">


<div class="stat-card">

    <div class="stat-icon">

        <i class="fa-solid fa-cart-shopping"></i>

    </div>

    <div>

        <h3>
            <?php
            echo $total_orders;
            ?>
        </h3>

        <p>
            Total Orders
        </p>

    </div>

</div>


<div class="stat-card">

    <div class="stat-icon pending">

        <i class="fa-solid fa-clock"></i>

    </div>

    <div>

        <h3>
            <?php
            echo $pending_orders;
            ?>
        </h3>

        <p>
            Pending Orders
        </p>

    </div>

</div>


<div class="stat-card">

    <div class="stat-icon delivered">

        <i class="fa-solid fa-circle-check"></i>

    </div>

    <div>

        <h3>
            <?php
            echo $delivered_orders;
            ?>
        </h3>

        <p>
            Delivered
        </p>

    </div>

</div>


<div class="stat-card">

    <div class="stat-icon cancelled">

        <i class="fa-solid fa-circle-xmark"></i>

    </div>

    <div>

        <h3>
            <?php
            echo $cancelled_orders;
            ?>
        </h3>

        <p>
            Cancelled
        </p>

    </div>

</div>


</section>


<!-- =========================================================
     ORDERS
     ========================================================= -->

<section class="orders-section">


<div class="section-header">

    <div>

        <h2>
            All Orders
        </h2>

        <p>
            View and manage customer orders
        </p>

    </div>

</div>


<!-- SEARCH -->

<div class="order-tools">

    <div class="search-box">

        <i class="fa-solid fa-magnifying-glass"></i>

        <input
            type="text"
            id="searchOrder"
            placeholder="Search order, customer, email or phone..."
        >

    </div>

</div>


<!-- TABLE -->

<div class="table-container">


<table>


<thead>

<tr>

    <th>
        Order ID
    </th>

    <th>
        Customer
    </th>

    <th>
        Phone
    </th>

    <th>
        Date
    </th>

    <th>
        Subtotal
    </th>

    <th>
        GST
    </th>

    <th>
        Grand Total
    </th>

    <th>
        Payment
    </th>

    <th>
        Status
    </th>

    <th>
        Action
    </th>

</tr>

</thead>


<tbody id="orderTable">


<?php if (!empty($orders)): ?>


<?php foreach ($orders as $order): ?>


<?php

$status =
    trim(
        $order['status'] ?? 'Pending'
    );


$statusClass =
    strtolower(
        $status
    );


?>


<tr>


<!-- ORDER ID -->

<td>

    <strong>

        #<?php
        echo (int)$order['id'];
        ?>

    </strong>

</td>


<!-- CUSTOMER -->

<td>

<div class="customer">


<div class="customer-icon">

    <i class="fa-solid fa-user"></i>

</div>


<div>

    <strong>

        <?php

        echo htmlspecialchars(
            $order['first_name'] ?? '',
            ENT_QUOTES,
            'UTF-8'
        );

        ?>

    </strong>


    <small>

        <?php

        echo htmlspecialchars(
            $order['email'] ?? '',
            ENT_QUOTES,
            'UTF-8'
        );

        ?>

    </small>

</div>


</div>

</td>


<!-- PHONE -->

<td>

<?php

echo !empty($order['phone'])
    ? htmlspecialchars(
        $order['phone'],
        ENT_QUOTES,
        'UTF-8'
    )
    : '-';

?>

</td>


<!-- DATE -->

<td>

<?php

if (!empty($order['created_at'])) {

    echo date(
        'd M Y',
        strtotime(
            $order['created_at']
        )
    );

} else {

    echo '-';
}

?>

</td>


<!-- SUBTOTAL -->

<td>

₹<?php

echo number_format(
    (float)$order['subtotal'],
    2
);

?>

</td>


<!-- GST -->

<td>

₹<?php

echo number_format(
    (float)$order['gst'],
    2
);

?>

</td>


<!-- GRAND TOTAL -->

<td>

<strong>

₹<?php

echo number_format(
    (float)$order['grand_total'],
    2
);

?>

</strong>

</td>


<!-- PAYMENT -->

<td>

<span class="payment-badge">

    <?php

    echo htmlspecialchars(
        $order['payment_method'] ?? '-',
        ENT_QUOTES,
        'UTF-8'
    );

    ?>

</span>

</td>


<!-- STATUS -->

<td>

<?php

if ($statusClass === 'delivered') {

    $statusClassName =
        'status-delivered';

} elseif (
    $statusClass === 'cancelled'
) {

    $statusClassName =
        'status-cancelled';

} else {

    $statusClassName =
        'status-pending';
}

?>


<span
    class="status-badge <?php
    echo $statusClassName;
    ?>"
>

    <?php

    echo htmlspecialchars(
        $status,
        ENT_QUOTES,
        'UTF-8'
    );

    ?>

</span>

</td>


<!-- ACTION -->

<td>


<div class="action-buttons">


<a
    href="orders.php?action=edit&id=<?php
    echo (int)$order['id'];
    ?>"
    class="action-btn edit-btn"
>

    <i class="fa-solid fa-pen-to-square"></i>

    Edit

</a>


<a
    href="orders.php?action=delete&id=<?php
    echo (int)$order['id'];
    ?>"
    class="action-btn delete-btn"

    onclick="return confirm(
        'Are you sure you want to delete Order #<?php
        echo (int)$order['id'];
        ?>?'
    );"
>

    <i class="fa-solid fa-trash"></i>

    Delete

</a>


</div>


</td>


</tr>


<?php endforeach; ?>


<?php else: ?>


<tr>

<td
    colspan="10"
    style="
        text-align:center;
        padding:40px;
        color:#777;
    "
>

    <i
        class="fa-solid fa-box-open"
        style="
            font-size:35px;
            margin-bottom:10px;
            display:block;
        "
    ></i>

    No Orders Found

</td>

</tr>


<?php endif; ?>


</tbody>


</table>


</div>


</section>


</main>


</div>


<script>

/* =========================================================
   SEARCH ORDERS
   ========================================================= */

const searchInput =
    document.getElementById(
        "searchOrder"
    );


if (searchInput) {

    searchInput.addEventListener(
        "keyup",
        function () {

            const search =
                this.value
                .toLowerCase()
                .trim();


            const rows =
                document.querySelectorAll(
                    "#orderTable tr"
                );


            rows.forEach(
                function(row) {

                    const text =
                        row.innerText
                        .toLowerCase();


                    if (
                        text.includes(search)
                    ) {

                        row.style.display =
                            "";

                    } else {

                        row.style.display =
                            "none";
                    }

                }
            );

        }
    );

}

</script>


</body>

</html>