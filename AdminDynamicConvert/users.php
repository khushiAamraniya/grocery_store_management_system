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
   MESSAGE
   ========================================================= */

$error = "";


/* =========================================================
   ADD ADMIN USER
   ========================================================= */

if (isset($_POST['add_user'])) {

    $username = trim($_POST['username'] ?? "");
    $user_name = trim($_POST['user_name'] ?? "");
    $user_password = trim($_POST['user_password'] ?? "");
    $user_email = trim($_POST['user_email'] ?? "");
    $user_contact = trim($_POST['user_contact'] ?? "");


    /* =====================================================
       VALIDATION
       ===================================================== */

    if (
        $username === "" ||
        $user_name === "" ||
        $user_password === "" ||
        $user_email === "" ||
        $user_contact === ""
    ) {

        $error = "Please fill all fields.";

    } elseif (!filter_var($user_email, FILTER_VALIDATE_EMAIL)) {

        $error = "Please enter a valid email address.";

    } elseif (!preg_match("/^[6-9][0-9]{9}$/", $user_contact)) {

        $error = "Please enter a valid 10 digit mobile number.";

    } elseif (
        preg_match(
            "/^([0-9])\1{9}$/",
            $user_contact
        )
    ) {

        $error = "Invalid mobile number.";

    } elseif (strlen($user_password) < 6) {

        $error = "Password must be at least 6 characters.";

    } else {


        /* =================================================
           HASH PASSWORD
           ================================================= */

        $hashed_password = password_hash(
            $user_password,
            PASSWORD_DEFAULT
        );


        /* =================================================
           ADMIN USER
           
           a = Admin
           c = Customer
           ================================================= */

        $user_type = "a";

        $active = 1;


        /* =================================================
           INSERT USER
           ================================================= */

        $sql = "
            INSERT INTO users
            (
                username,
                user_name,
                user_password,
                user_email,
                user_contact,
                user_type,
                active,
                created_at
            )
            VALUES
            (?, ?, ?, ?, ?, ?, ?, NOW())
        ";


        $stmt = $conn->prepare($sql);


        if ($stmt) {

            $stmt->bind_param(
                "ssssssi",
                $username,
                $user_name,
                $hashed_password,
                $user_email,
                $user_contact,
                $user_type,
                $active
            );


            if ($stmt->execute()) {

                $stmt->close();

                header(
                    "Location: users.php?success=1"
                );

                exit();

            } else {

                if ($stmt->errno == 1062) {

                    $error = "Username already exists.";

                } else {

                    $error =
                        "User Add Failed: "
                        . $stmt->error;
                }
            }


            $stmt->close();

        } else {

            $error =
                "SQL Error: "
                . $conn->error;
        }
    }
}


/* =========================================================
   DELETE USER
   ========================================================= */

if (isset($_GET['delete'])) {

    $delete_id = (int)$_GET['delete'];


    if ($delete_id > 0) {

        $stmt = $conn->prepare(
            "DELETE FROM users WHERE user_id = ?"
        );


        if ($stmt) {

            $stmt->bind_param(
                "i",
                $delete_id
            );

            $stmt->execute();

            $stmt->close();


            header(
                "Location: users.php?deleted=1"
            );

            exit();
        }
    }
}


/* =========================================================
   GET ALL USERS
   ========================================================= */

$sql = "
    SELECT
        user_id,
        username,
        user_name,
        user_email,
        user_contact,
        user_type,
        active,
        created_at
    FROM users
    ORDER BY user_id ASC
";


$result = $conn->query($sql);


if (!$result) {

    die(
        "Users Query Error: "
        . $conn->error
    );
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

<title>Users Management</title>


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
   ADD BUTTON
   ========================================================= */

.add-btn {

    background: #1b5e20;

    color: white;

    border: none;

    padding: 12px 20px;

    border-radius: 8px;

    font-size: 15px;

    font-weight: 600;

    cursor: pointer;

    margin: 25px 0;

}


.add-btn:hover {

    background: #144d19;

}


/* =========================================================
   FORM BOX
   ========================================================= */

.form-box {

    display: none;

    background: white;

    padding: 25px;

    border-radius: 12px;

    box-shadow:
        0 5px 20px
        rgba(0,0,0,0.08);

    margin-bottom: 30px;

    max-width: 700px;

}


.form-box.show {

    display: block;

}


.form-box h2 {

    margin-bottom: 20px;

    color: #1b5e20;

}


/* =========================================================
   INPUT
   ========================================================= */

.input-box {

    margin-bottom: 15px;

}


.input-box label {

    display: block;

    margin-bottom: 7px;

    font-weight: 600;

}


.input-box input {

    width: 100%;

    padding: 12px;

    border: 1px solid #ddd;

    border-radius: 7px;

    font-size: 15px;

}


.input-box input:focus {

    outline: none;

    border-color: #1b5e20;

}


/* =========================================================
   SUBMIT BUTTON
   ========================================================= */

.submit-btn {

    background: #1b5e20;

    color: white;

    border: none;

    padding: 12px 20px;

    border-radius: 7px;

    cursor: pointer;

    font-weight: 600;

}


.submit-btn:hover {

    background: #144d19;

}


/* =========================================================
   CLOSE BUTTON
   ========================================================= */

.close-btn {

    background: #777;

    color: white;

    border: none;

    padding: 12px 20px;

    border-radius: 7px;

    cursor: pointer;

    margin-left: 8px;

}


/* =========================================================
   MESSAGE
   ========================================================= */

.success-message {

    background: #d1e7dd;

    color: #0f5132;

    padding: 12px 15px;

    border-radius: 7px;

    margin-bottom: 20px;

}


.error-message {

    background: #f8d7da;

    color: #842029;

    padding: 12px 15px;

    border-radius: 7px;

    margin-bottom: 20px;

}


/* =========================================================
   TABLE
   ========================================================= */

.table-box {

    background: white;

    padding: 20px;

    border-radius: 12px;

    box-shadow:
        0 5px 20px
        rgba(0,0,0,0.08);

    overflow-x: auto;

}


.table-title {

    margin-bottom: 20px;

}


.table-title h2 {

    color: #1b5e20;

}


table {

    width: 100%;

    border-collapse: collapse;

}


table th {

    background: #1b5e20;

    color: white;

    padding: 13px;

    text-align: left;

}


table td {

    padding: 13px;

    border-bottom: 1px solid #eee;

}


table tr:hover {

    background: #f7f7f7;

}


/* =========================================================
   ACTION BUTTON
   ========================================================= */

.delete-btn {

    background: #dc3545;

    color: white;

    border: none;

    padding: 8px 12px;

    border-radius: 6px;

    cursor: pointer;

    text-decoration: none;

}


.delete-btn:hover {

    background: #bb2d3b;

}


/* =========================================================
   STATUS
   ========================================================= */

.status-active {

    color: green;

    font-weight: bold;

}


.status-inactive {

    color: red;

    font-weight: bold;

}


/* =========================================================
   TYPE
   ========================================================= */

.type-admin {

    color: #176b35;

    font-weight: bold;

}


.type-customer {

    color: #555;

    font-weight: bold;

}


/* =========================================================
   RESPONSIVE
   ========================================================= */

@media (max-width: 700px) {

    .form-box {

        max-width: 100%;

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
     MAIN CONTENT
     ===================================================== -->

<main class="main">


<!-- =====================================================
     TOPBAR
     ===================================================== -->

<header class="topbar">

    <div>

        <h2>
            Users Management
        </h2>

        <p>
            Manage admin users of the Grocery Store.
        </p>

    </div>

</header>


<!-- =====================================================
     SUCCESS MESSAGE
     ===================================================== -->

<?php if (isset($_GET['success'])) { ?>

    <div class="success-message">

        <i class="fa-solid fa-circle-check"></i>

        Admin user added successfully!

    </div>

<?php } ?>


<!-- =====================================================
     DELETE MESSAGE
     ===================================================== -->

<?php if (isset($_GET['deleted'])) { ?>

    <div class="success-message">

        <i class="fa-solid fa-circle-check"></i>

        User deleted successfully!

    </div>

<?php } ?>


<!-- =====================================================
     ERROR MESSAGE
     ===================================================== -->

<?php if ($error !== "") { ?>

    <div class="error-message">

        <i class="fa-solid fa-circle-exclamation"></i>

        <?php
        echo htmlspecialchars($error);
        ?>

    </div>

<?php } ?>


<!-- =====================================================
     ADD USER BUTTON
     ===================================================== -->

<button
    type="button"
    class="add-btn"
    onclick="openForm()"
>

    <i class="fa-solid fa-user-plus"></i>

    Add Admin

</button>


<!-- =====================================================
     USER FORM
     ===================================================== -->

<div
    class="form-box"
    id="userForm"
>


    <h2>

        <i class="fa-solid fa-user-plus"></i>

        Create New Admin

    </h2>


    <form
        method="POST"
        action=""
    >


        <!-- USERNAME -->

        <div class="input-box">

            <label>
                Username
            </label>

            <input
                type="text"
                name="username"
                placeholder="Enter Username"
                required
            >

        </div>


        <!-- NAME -->

        <div class="input-box">

            <label>
                Full Name
            </label>

            <input
                type="text"
                name="user_name"
                placeholder="Enter Full Name"
                required
            >

        </div>


        <!-- MOBILE -->

        <div class="input-box">

            <label>
                Mobile Number
            </label>

            <input
                type="tel"
                name="user_contact"
                placeholder="Enter Mobile Number"
                maxlength="10"
                pattern="[6-9][0-9]{9}"
                required
            >

        </div>


        <!-- EMAIL -->

        <div class="input-box">

            <label>
                Email Address
            </label>

            <input
                type="email"
                name="user_email"
                placeholder="Enter Email Address"
                required
            >

        </div>


        <!-- PASSWORD -->

        <div class="input-box">

            <label>
                Password
            </label>

            <input
                type="password"
                name="user_password"
                placeholder="Enter Password"
                minlength="6"
                required
            >

        </div>


        <!-- SUBMIT -->

        <button
            type="submit"
            name="add_user"
            class="submit-btn"
        >

            <i class="fa-solid fa-user-plus"></i>

            Add Admin

        </button>


        <!-- CLOSE -->

        <button
            type="button"
            class="close-btn"
            onclick="closeForm()"
        >

            Close

        </button>


    </form>


</div>


<!-- =====================================================
     USER TABLE
     ===================================================== -->

<div class="table-box">


    <div class="table-title">

        <h2>

            All Admin Users

        </h2>

    </div>


    <table>


        <thead>

            <tr>

                <th>ID</th>

                <th>Username</th>

                <th>Name</th>

                <th>Mobile</th>

                <th>Email</th>

                <th>Type</th>

                <th>Status</th>

                <th>Action</th>

            </tr>

        </thead>


        <tbody>


        <?php

        if (
            $result &&
            $result->num_rows > 0
        ) {

            while (
                $user =
                $result->fetch_assoc()
            ) {

        ?>


            <tr>


                <!-- ID -->

                <td>

                    <?php
                    echo htmlspecialchars(
                        $user['user_id']
                    );
                    ?>

                </td>


                <!-- USERNAME -->

                <td>

                    <?php
                    echo htmlspecialchars(
                        $user['username']
                    );
                    ?>

                </td>


                <!-- NAME -->

                <td>

                    <?php
                    echo htmlspecialchars(
                        $user['user_name']
                    );
                    ?>

                </td>


                <!-- MOBILE -->

                <td>

                    <?php
                    echo htmlspecialchars(
                        $user['user_contact']
                    );
                    ?>

                </td>


                <!-- EMAIL -->

                <td>

                    <?php
                    echo htmlspecialchars(
                        $user['user_email']
                    );
                    ?>

                </td>


                <!-- TYPE -->

                <td>

                    <?php

                    if (
                        $user['user_type'] === 'a'
                    ) {

                    ?>

                        <span class="type-admin">

                            Admin

                        </span>

                    <?php

                    } else {

                    ?>

                        <span class="type-customer">

                            Customer

                        </span>

                    <?php

                    }

                    ?>

                </td>


                <!-- STATUS -->

                <td>

                    <?php

                    if (
                        $user['active'] == 1
                    ) {

                    ?>

                        <span class="status-active">

                            Active

                        </span>

                    <?php

                    } else {

                    ?>

                        <span class="status-inactive">

                            Inactive

                        </span>

                    <?php

                    }

                    ?>

                </td>


                <!-- DELETE -->

                <td>

                    <a
                        href="users.php?delete=<?php echo (int)$user['user_id']; ?>"
                        class="delete-btn"
                        onclick="return confirm('Are you sure you want to delete this user?');"
                    >

                        <i class="fa-solid fa-trash"></i>

                        Delete

                    </a>

                </td>


            </tr>


        <?php

            }

        } else {

        ?>


            <tr>

                <td
                    colspan="8"
                    style="text-align:center;"
                >

                    No Users Found

                </td>

            </tr>


        <?php

        }

        ?>


        </tbody>


    </table>


</div>


</main>


</div>


<script>

/* =========================================================
   OPEN FORM
   ========================================================= */

function openForm() {

    document
        .getElementById("userForm")
        .classList.add("show");

}


/* =========================================================
   CLOSE FORM
   ========================================================= */

function closeForm() {

    document
        .getElementById("userForm")
        .classList.remove("show");

}

</script>


</body>

</html>


<?php

/* =========================================================
   CLOSE DATABASE
   ========================================================= */

if (
    isset($conn) &&
    $conn instanceof mysqli &&
    $conn->thread_id
) {

    $conn->close();

}

?>