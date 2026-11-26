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

$conn->set_charset("utf8mb4");


/* =========================================================
   DELETE CUSTOMER
   ========================================================= */

if (isset($_GET['delete'])) {

    $delete_id = (int)$_GET['delete'];

    if ($delete_id > 0) {

        /* =====================================================
           GET CUSTOMER EMAIL BEFORE DELETE
           ===================================================== */

        $get_customer = $conn->prepare("
            SELECT email
            FROM user_register
            WHERE id = ?
            LIMIT 1
        ");

        if ($get_customer) {

            $get_customer->bind_param(
                "i",
                $delete_id
            );

            $get_customer->execute();

            $customer_result = $get_customer->get_result();

            if ($customer_result->num_rows > 0) {

                $customer_data = $customer_result->fetch_assoc();

                $customer_email = $customer_data['email'];

                $get_customer->close();


                /* =================================================
                   START TRANSACTION
                   ================================================= */

                $conn->begin_transaction();

                try {

                    /* =================================================
                       1. DELETE FROM users
                       ONLY CUSTOMER ACCOUNT
                       ================================================= */

                    $delete_user = $conn->prepare("
                        DELETE FROM users
                        WHERE user_email = ?
                        AND user_type = 'c'
                    ");

                    if (!$delete_user) {
                        throw new Exception(
                            "Users Delete Error: " . $conn->error
                        );
                    }

                    $delete_user->bind_param(
                        "s",
                        $customer_email
                    );

                    if (!$delete_user->execute()) {
                        throw new Exception(
                            "Users Delete Failed: " .
                            $delete_user->error
                        );
                    }

                    $delete_user->close();


                    /* =================================================
                       2. DELETE FROM customers TABLE
                       IF TABLE EXISTS
                       ================================================= */

                    $table_check = $conn->query("
                        SHOW TABLES LIKE 'customers'
                    ");

                    if (
                        $table_check &&
                        $table_check->num_rows > 0
                    ) {

                        $delete_customer = $conn->prepare("
                            DELETE FROM customers
                            WHERE email = ?
                        ");

                        if ($delete_customer) {

                            $delete_customer->bind_param(
                                "s",
                                $customer_email
                            );

                            $delete_customer->execute();

                            $delete_customer->close();

                        }

                    }


                    /* =================================================
                       3. DELETE FROM user_register
                       ================================================= */

                    $delete_register = $conn->prepare("
                        DELETE FROM user_register
                        WHERE id = ?
                    ");

                    if (!$delete_register) {
                        throw new Exception(
                            "Registration Delete Error: " .
                            $conn->error
                        );
                    }

                    $delete_register->bind_param(
                        "i",
                        $delete_id
                    );

                    if (!$delete_register->execute()) {
                        throw new Exception(
                            "Registration Delete Failed: " .
                            $delete_register->error
                        );
                    }

                    $delete_register->close();


                    /* =================================================
                       COMMIT
                       ================================================= */

                    $conn->commit();


                    /* =================================================
                       REFRESH PAGE
                       ================================================= */

                    header(
                        "Location: customer.php?deleted=1"
                    );

                    exit;

                }

                catch (Exception $e) {

                    $conn->rollback();

                    die(
                        "Delete Error: " .
                        htmlspecialchars($e->getMessage())
                    );
                }

            }

            else {

                $get_customer->close();

                header(
                    "Location: customer.php"
                );

                exit;
            }

        }

    }

}


/* =========================================================
   GET REGISTERED CUSTOMERS
   FROM user_register TABLE
   ========================================================= */

$customers = [];

$sql = "
    SELECT
        id,
        full_name,
        email,
        mobile_number,
        created_at
    FROM user_register
    ORDER BY id ASC
";

$result = $conn->query($sql);

if (!$result) {
    die("Database Error: " . $conn->error);
}

while ($row = $result->fetch_assoc()) {

    $customers[] = $row;

}

$total_customers = count($customers);

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>

<title>Customer Management</title>

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
   CUSTOMER PAGE
   ========================================================= */

.customer-page {
    width: 100%;
}


/* =========================================================
   HEADER
   ========================================================= */

.customer-header {

    display: flex;

    justify-content: space-between;

    align-items: center;

    margin-bottom: 25px;

}

.customer-header h1 {

    margin: 0;

    color: #176b35;

    font-size: 28px;

}

.customer-header p {

    margin: 7px 0 0;

    color: #777;

}


/* =========================================================
   CUSTOMER COUNT
   ========================================================= */

.customer-count {

    background: #e8f5e9;

    color: #176b35;

    padding: 12px 18px;

    border-radius: 10px;

    font-weight: 700;

}


/* =========================================================
   TABLE BOX
   ========================================================= */

.customer-table-box {

    background: #ffffff;

    border-radius: 15px;

    box-shadow: 0 6px 25px rgba(0,0,0,0.07);

    overflow: hidden;

}

.table-wrapper {

    width: 100%;

    overflow-x: auto;

}


/* =========================================================
   TABLE
   ========================================================= */

.customer-table {

    width: 100%;

    border-collapse: collapse;

}

.customer-table th {

    background: #176b35;

    color: white;

    padding: 16px;

    text-align: left;

    font-size: 14px;

}

.customer-table td {

    padding: 16px;

    border-bottom: 1px solid #eeeeee;

    color: #444;

    font-size: 14px;

}

.customer-table tbody tr:hover {

    background: #f7fbf8;

}


/* =========================================================
   ID
   ========================================================= */

.customer-id {

    font-weight: 700;

    color: #176b35;

}


/* =========================================================
   NAME
   ========================================================= */

.customer-name {

    display: flex;

    align-items: center;

    gap: 10px;

}

.customer-icon {

    width: 38px;

    height: 38px;

    min-width: 38px;

    border-radius: 50%;

    background: #e8f5e9;

    color: #176b35;

    display: flex;

    align-items: center;

    justify-content: center;

}

.customer-name strong {

    color: #333;

}


/* =========================================================
   EMAIL
   ========================================================= */

.customer-email {

    color: #555;

}


/* =========================================================
   PHONE
   ========================================================= */

.customer-phone {

    white-space: nowrap;

}


/* =========================================================
   STATUS
   ========================================================= */

.status {

    display: inline-flex;

    align-items: center;

    gap: 6px;

    padding: 7px 12px;

    border-radius: 20px;

    font-size: 12px;

    font-weight: 700;

}

.status.active {

    background: #e8f5e9;

    color: #2e7d32;

}


/* =========================================================
   DELETE BUTTON
   ========================================================= */

.delete-btn {

    display: inline-flex;

    align-items: center;

    gap: 7px;

    padding: 8px 13px;

    border: none;

    border-radius: 7px;

    background: #ffebee;

    color: #c62828;

    font-size: 13px;

    font-weight: 700;

    text-decoration: none;

    cursor: pointer;

    transition: 0.2s;

}

.delete-btn:hover {

    background: #c62828;

    color: white;

}


/* =========================================================
   EMPTY
   ========================================================= */

.no-customers {

    text-align: center;

    padding: 50px !important;

    color: #777;

}


/* =========================================================
   RESPONSIVE
   ========================================================= */

@media(max-width: 900px) {

    .customer-header {

        flex-direction: column;

        align-items: flex-start;

        gap: 15px;

    }

    .customer-table {

        min-width: 850px;

    }

}

</style>

</head>


<body>


<div class="container">


<!-- =====================================================
     SIDEBAR
     ===================================================== -->

<?php include 'sidebar.php'; ?>


<!-- =====================================================
     MAIN
     ===================================================== -->

<main class="main">


<div class="customer-page">


<!-- =====================================================
     HEADER
     ===================================================== -->

<div class="customer-header">

    <div>

        <h1>

            <i class="fa-solid fa-users"></i>

            Customer Management

        </h1>

        <p>

            Manage and view all registered customers

        </p>

    </div>


    <div class="customer-count">

        <i class="fa-solid fa-user-group"></i>

        <?php echo $total_customers; ?>

        Customers

    </div>

</div>


<!-- =====================================================
     CUSTOMER TABLE
     ===================================================== -->

<div class="customer-table-box">

<div class="table-wrapper">

<table class="customer-table">


<thead>

<tr>

    <th>
        Customer ID
    </th>

    <th>
        Name
    </th>

    <th>
        Email
    </th>

    <th>
        Mobile
    </th>

    <th>
        Status
    </th>

    <th>
        Action
    </th>

</tr>

</thead>


<tbody>


<?php if (!empty($customers)): ?>


<?php foreach ($customers as $customer): ?>


<tr>


<!-- =================================================
     CUSTOMER ID
     ================================================= -->

<td>

    <span class="customer-id">

        #

        <?php

        echo (int)$customer['id'];

        ?>

    </span>

</td>


<!-- =================================================
     NAME
     ================================================= -->

<td>

<div class="customer-name">

    <div class="customer-icon">

        <i class="fa-solid fa-user"></i>

    </div>

    <strong>

        <?php

        echo htmlspecialchars(
            $customer['full_name'],
            ENT_QUOTES,
            'UTF-8'
        );

        ?>

    </strong>

</div>

</td>


<!-- =================================================
     EMAIL
     ================================================= -->

<td>

<span class="customer-email">

    <?php

    echo htmlspecialchars(
        $customer['email'],
        ENT_QUOTES,
        'UTF-8'
    );

    ?>

</span>

</td>


<!-- =================================================
     MOBILE
     ================================================= -->

<td>

<span class="customer-phone">

    <?php

    echo htmlspecialchars(
        $customer['mobile_number'],
        ENT_QUOTES,
        'UTF-8'
    );

    ?>

</span>

</td>


<!-- =================================================
     STATUS
     ================================================= -->

<td>

<span class="status active">

    <i class="fa-solid fa-circle-check"></i>

    Active

</span>

</td>


<!-- =================================================
     ACTION
     ================================================= -->

<td>

<a
    href="customer.php?delete=<?php echo (int)$customer['id']; ?>"
    class="delete-btn"
    onclick="return confirm(
        'Are you sure you want to delete this customer?'
    );"
>

    <i class="fa-solid fa-trash"></i>

    Delete

</a>

</td>


</tr>


<?php endforeach; ?>


<?php else: ?>


<tr>

<td
    colspan="6"
    class="no-customers"
>

    <i
        class="fa-solid fa-users"
        style="font-size:35px;"
    ></i>

    <br><br>

    No Customers Found

    <br><br>

    <small>

        Registered customers will appear here.

    </small>

</td>

</tr>


<?php endif; ?>


</tbody>

</table>

</div>

</div>


</div>

</main>

</div>


</body>

</html>


<?php

$conn->close();

?>