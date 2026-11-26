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
   HELPER FUNCTION
   ========================================================= */

function getCount($conn, $table)
{
    $check = $conn->query("SHOW TABLES LIKE '$table'");

    if (!$check || $check->num_rows == 0) {
        return 0;
    }

    $result = $conn->query(
        "SELECT COUNT(*) AS total FROM `$table`"
    );

    if ($result) {
        $row = $result->fetch_assoc();
        return (int)($row["total"] ?? 0);
    }

    return 0;
}


/* =========================================================
   DASHBOARD STATS
   ========================================================= */

$total_products = getCount($conn, "products");

$total_categories = getCount($conn, "categories");

$total_orders = getCount($conn, "orders");


/* =========================================================
   TOTAL CUSTOMERS
   ========================================================= */

$total_customers = 0;

$result_customers = $conn->query("
    SELECT COUNT(*) AS total
    FROM user_register
");

if ($result_customers) {

    $row_customers = $result_customers->fetch_assoc();

    $total_customers =
        (int)($row_customers['total'] ?? 0);
}


/* =========================================================
   TOTAL SALES
   ========================================================= */

$total_sales = 0;
$check_orders = $conn->query("SHOW TABLES LIKE 'orders'");

if ($check_orders && $check_orders->num_rows > 0) {
    $result_sales = $conn->query("SELECT COALESCE(SUM(grand_total), 0) AS total FROM orders");
    if ($result_sales) {
        $row_sales = $result_sales->fetch_assoc();
        $total_sales = $row_sales["total"] ?? 0;
    }
}


/* =========================================================
   STATS ARRAY
   ========================================================= */

$stats = [

    "total_products" =>
        $total_products,

    "total_categories" =>
        $total_categories,

    "total_orders" =>
        $total_orders,

    "total_customers" =>
        $total_customers,

    "total_sales" =>
        $total_sales
];


/* =========================================================
   RECENT ORDERS
   ========================================================= */

$dashboard_orders = [];


/* =========================================================
   CHECK TABLES
   ========================================================= */

$orders_exists =
    $conn->query(
        "SHOW TABLES LIKE 'orders'"
    );

$order_items_exists =
    $conn->query(
        "SHOW TABLES LIKE 'order_items'"
    );

$products_exists =
    $conn->query(
        "SHOW TABLES LIKE 'products'"
    );


/* =========================================================
   RECENT ORDERS DATA
   ========================================================= */

if (
    $orders_exists &&
    $orders_exists->num_rows > 0 &&
    $order_items_exists &&
    $order_items_exists->num_rows > 0 &&
    $products_exists &&
    $products_exists->num_rows > 0
) {


    /* =====================================================
       PRODUCT COLUMN
       ===================================================== */

    $product_column = "product_name";

    $check_product = $conn->query(
        "SHOW COLUMNS FROM products LIKE 'product_name'"
    );

    if (
        !$check_product ||
        $check_product->num_rows == 0
    ) {

        $check_product = $conn->query(
            "SHOW COLUMNS FROM products LIKE 'name'"
        );

        if (
            $check_product &&
            $check_product->num_rows > 0
        ) {

            $product_column = "name";
        }
    }


    /* =====================================================
       ORDER STATUS
       ===================================================== */

    $status_select =
        "'Pending' AS status";

    $check_status = $conn->query(
        "SHOW COLUMNS FROM orders LIKE 'status'"
    );

    if (
        $check_status &&
        $check_status->num_rows > 0
    ) {

        $status_select =
            "o.status AS status";
    }


    /* =====================================================
       CUSTOMER
       ===================================================== */

    $customer_select =
        "'Customer' AS customer";

    $customer_join = "";


    $check_user_id = $conn->query(
        "SHOW COLUMNS FROM orders LIKE 'user_id'"
    );

    if (
        $check_user_id &&
        $check_user_id->num_rows > 0
    ) {

        $customer_select =
            "u.user_name AS customer";

        $customer_join = "

            LEFT JOIN users u

            ON o.user_id = u.user_id

        ";
    }

    else {

        $check_customer_id = $conn->query(
            "SHOW COLUMNS FROM orders LIKE 'customer_id'"
        );

        if (
            $check_customer_id &&
            $check_customer_id->num_rows > 0
        ) {

            $customer_select =
                "u.user_name AS customer";

            $customer_join = "

                LEFT JOIN users u

                ON o.customer_id = u.user_id

            ";
        }
    }


    /* =====================================================
       ORDER QUERY
       ===================================================== */

    $order_query = "

        SELECT

            o.id,

            $status_select,

            $customer_select,

            p.`$product_column` AS product,

            oi.quantity,

            oi.price

        FROM orders o

        LEFT JOIN order_items oi

            ON o.id = oi.order_id

        LEFT JOIN products p

            ON oi.product_id = p.id

        $customer_join

        ORDER BY o.id DESC

        LIMIT 4

    ";


    $order_result =
        $conn->query($order_query);


    if ($order_result) {

        while (
            $order =
            $order_result->fetch_assoc()
        ) {

            $quantity =
                (int)(
                    $order["quantity"] ?? 0
                );

            $price =
                (float)(
                    $order["price"] ?? 0
                );

            $amount =
                $quantity * $price;


            $status =
                $order["status"]
                ?? "Pending";


            $status_lower =
                strtolower(
                    trim($status)
                );


            if (
                $status_lower ==
                "delivered"
            ) {

                $class =
                    "delivered";

            } elseif (
                $status_lower ==
                "processing"
            ) {

                $class =
                    "processing";

            } else {

                $class =
                    "pending";
            }


            $dashboard_orders[] = [

                "id" =>
                    "#" .
                    ($order["id"] ?? ""),

                "customer" =>
                    $order["customer"]
                    ?? "Customer",

                "product" =>
                    $order["product"]
                    ?? "Product",

                "amount" =>
                    "₹" .
                    number_format(
                        $amount,
                        0
                    ),

                "status" =>
                    $status,

                "class" =>
                    $class
            ];
        }
    }
}

?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
    content="width=device-width, initial-scale=1.0">

<title>Grocery Admin Dashboard</title>


<link
    rel="stylesheet"
    href="admin.css"
>


<link
    rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"
>


<!-- =========================================================
     PROJECT PROFILE CSS
     ========================================================= -->

<style>

.project-profile {
    margin-left: auto;
}

.project-profile a {
    display: flex;
    align-items: center;
    gap: 8px;

    color: #555;
    text-decoration: none;

    font-size: 15px;
    font-weight: 500;

    padding: 8px 12px;

    border-radius: 6px;

    transition: 0.3s ease;
}

.project-profile a i {
    font-size: 16px;
}

.project-profile a:hover {
    color: #2e7d32;
    background: #f1f8f2;
}

</style>

</head>


<body>


<div class="container">


    <!-- =================================================
         SIDEBAR
         ================================================= -->

    <?php include 'sidebar.php'; ?>


    <!-- =================================================
         MAIN
         ================================================= -->

    <main class="main">


        <!-- =================================================
             TOP HEADER
             ================================================= -->

        <header class="topbar">


            <div>

                <h2>
                    Dashboard
                </h2>

                <p>
                    Welcome to Grocery Store Management System
                </p>

            </div>


            <!-- =================================================
                 PROJECT PROFILE LINK
                 ================================================= -->

            <div class="project-profile">

                <a href="project_profile.php">

                    <i class="fa-solid fa-file-lines"></i>

                    <span>
                        Project Profile
                    </span>

                </a>

            </div>


        </header>



        <!-- =================================================
             WELCOME
             ================================================= -->

        <section class="welcome">

            <div>

                <h1>
                    🌿 Welcome Back, Admin!
                </h1>

                <p>
                    Manage Products, Categories, Orders and
                    Customers from one dashboard.
                </p>

            </div>

        </section>



        <!-- =================================================
             DASHBOARD CARDS
             ================================================= -->

        <section class="cards">


            <!-- PRODUCTS -->

            <div
                class="card"
                onclick="window.location.href='product.php'"
            >

                <i class="fa-solid fa-box"></i>

                <h3>
                    Total Products
                </h3>

                <h1>

                    <?php

                    echo htmlspecialchars(
                        $stats["total_products"]
                    );

                    ?>

                </h1>

            </div>



            <!-- CATEGORIES -->

            <div
                class="card"
                onclick="window.location.href='categories.php'"
            >

                <i class="fa-solid fa-layer-group"></i>

                <h3>
                    Categories
                </h3>

                <h1>

                    <?php

                    echo htmlspecialchars(
                        $stats["total_categories"]
                    );

                    ?>

                </h1>

            </div>



            <!-- ORDERS -->

            <div
                class="card"
                onclick="window.location.href='orders.php'"
            >

                <i class="fa-solid fa-cart-shopping"></i>

                <h3>
                    Total Orders
                </h3>

                <h1>

                    <?php

                    echo htmlspecialchars(
                        $stats["total_orders"]
                    );

                    ?>

                </h1>

            </div>



            <!-- CUSTOMERS -->

            <div
                class="card"
                onclick="window.location.href='customer.php'"
            >

                <i class="fa-solid fa-users"></i>

                <h3>
                    Customers
                </h3>

                <h1>

                    <?php

                    echo htmlspecialchars(
                        $stats["total_customers"]
                    );

                    ?>

                </h1>

            </div>



            <!-- SALES -->

            <div
                class="card"
                onclick="window.location.href='orders.php'"
            >

                <i class="fa-solid fa-indian-rupee-sign"></i>

                <h3>
                    Total Sales
                </h3>

                <h1>

                    ₹<?php

                    echo htmlspecialchars(
                        number_format(
                            (float)$stats["total_sales"],
                            0
                        )
                    );

                    ?>

                </h1>

            </div>


        </section>



        <!-- =================================================
             QUICK ACTIONS
             ================================================= -->

        <section class="quick-actions">


            <button
                onclick="window.location.href='product.php'"
            >

                <i class="fa-solid fa-plus"></i>

                Add Product

            </button>



            <button
                onclick="window.location.href='categories.php'"
            >

                <i class="fa-solid fa-folder-plus"></i>

                Add Category

            </button>



            <button
                onclick="window.location.href='orders.php'"
            >

                <i class="fa-solid fa-cart-shopping"></i>

                View Orders

            </button>



            <button
                onclick="window.location.href='customer.php'"
            >

                <i class="fa-solid fa-users"></i>

                View Customers

            </button>


        </section>



        <!-- =================================================
             RECENT ORDERS
             ================================================= -->

        <section class="table-section">

            <div class="table-box">


                <div class="table-title">

                    <h2>
                        Recent Orders
                    </h2>

                    <button
                        onclick="window.location.href='orders.php'"
                    >

                        View All

                    </button>

                </div>



                <table>

                    <tr>

                        <th>
                            Order ID
                        </th>

                        <th>
                            Customer
                        </th>

                        <th>
                            Product
                        </th>

                        <th>
                            Amount
                        </th>

                        <th>
                            Status
                        </th>

                    </tr>



                    <?php if (!empty($dashboard_orders)): ?>


                        <?php foreach (
                            $dashboard_orders
                            as $order
                        ): ?>


                        <tr>

                            <td>

                                <?php

                                echo htmlspecialchars(
                                    $order["id"]
                                );

                                ?>

                            </td>


                            <td>

                                <?php

                                echo htmlspecialchars(
                                    $order["customer"]
                                );

                                ?>

                            </td>


                            <td>

                                <?php

                                echo htmlspecialchars(
                                    $order["product"]
                                );

                                ?>

                            </td>


                            <td>

                                <?php

                                echo htmlspecialchars(
                                    $order["amount"]
                                );

                                ?>

                            </td>


                            <td>

                                <span
                                    class="<?php

                                    echo htmlspecialchars(
                                        $order["class"]
                                    );

                                    ?>"
                                >

                                    <?php

                                    echo htmlspecialchars(
                                        $order["status"]
                                    );

                                    ?>

                                </span>

                            </td>

                        </tr>


                        <?php endforeach; ?>


                    <?php else: ?>


                        <tr>

                            <td
                                colspan="5"
                                style="text-align:center;"
                            >

                                No Orders Found

                            </td>

                        </tr>


                    <?php endif; ?>


                </table>


            </div>

        </section>



        <!-- =================================================
             FOOTER
             ================================================= -->

        <footer class="footer">

            <p>

                © 2026 Shreenath Grocery Store |

                Admin Dashboard |

                Designed by

                <strong>
                    Khushi Amraniya
                </strong>

            </p>

        </footer>


    </main>

</div>



<script src="admin.js"></script>


</body>

</html>


<?php

$conn->close();

?>