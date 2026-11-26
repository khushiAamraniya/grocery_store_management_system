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

$success = "";
$error = "";


/* =========================================================
   GET ADMIN SETTINGS
   ========================================================= */

$result = $conn->query("
    SELECT
        id,
        admin_name,
        email,
        password,
        store_name,
        contact_number,
        address,
        updated_at
    FROM admin_settings
    ORDER BY id ASC
    LIMIT 1
");

if (!$result) {
    die("Settings Query Error: " . $conn->error);
}

if ($result->num_rows == 0) {
    die("No admin settings found in database.");
}

$settings = $result->fetch_assoc();

$settings_id = (int)$settings['id'];


/* =========================================================
   SAVE PROFILE
   ========================================================= */

if (isset($_POST['save_profile'])) {

    $admin_name = trim($_POST['admin_name'] ?? "");
    $email = trim($_POST['email'] ?? "");

    if ($admin_name === "") {

        $error = "Admin name is required.";

    } elseif ($email === "") {

        $error = "Email is required.";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $error = "Please enter a valid email.";

    } else {

        $stmt = $conn->prepare("
            UPDATE admin_settings
            SET
                admin_name = ?,
                email = ?,
                updated_at = NOW()
            WHERE id = ?
        ");

        if (!$stmt) {

            $error = "Profile query error: " . $conn->error;

        } else {

            $stmt->bind_param(
                "ssi",
                $admin_name,
                $email,
                $settings_id
            );

            if ($stmt->execute()) {

                $success = "Profile updated successfully.";

            } else {

                $error = "Profile update failed: " . $stmt->error;

            }

            $stmt->close();
        }
    }
}


/* =========================================================
   CHANGE PASSWORD
   ========================================================= */

if (isset($_POST['update_password'])) {

    $old_password = trim($_POST['old_password'] ?? "");
    $new_password = trim($_POST['new_password'] ?? "");
    $confirm_password = trim($_POST['confirm_password'] ?? "");


    if (
        $old_password === "" ||
        $new_password === "" ||
        $confirm_password === ""
    ) {

        $error = "Please fill all password fields.";

    } elseif ($new_password !== $confirm_password) {

        $error = "New password and confirm password do not match.";

    } elseif (strlen($new_password) < 6) {

        $error = "Password must be at least 6 characters.";

    } else {

        /* ================================================
           GET CURRENT PASSWORD
           ================================================ */

        $stmt = $conn->prepare("
            SELECT password
            FROM admin_settings
            WHERE id = ?
            LIMIT 1
        ");

        if (!$stmt) {

            $error = "Password query error: " . $conn->error;

        } else {

            $stmt->bind_param(
                "i",
                $settings_id
            );

            $stmt->execute();

            $password_result = $stmt->get_result();

            if ($password_result->num_rows == 0) {

                $error = "Admin settings not found.";

            } else {

                $row = $password_result->fetch_assoc();

                $current_password = $row['password'];


                /* ========================================
                   SUPPORT BOTH:
                   1. HASHED PASSWORD
                   2. PLAIN PASSWORD
                   ======================================== */

                $password_correct = false;


                /* Hashed password */

                if (
                    password_verify(
                        $old_password,
                        $current_password
                    )
                ) {

                    $password_correct = true;

                }


                /* Plain text password */

                elseif (
                    $old_password === $current_password
                ) {

                    $password_correct = true;

                }


                /* ========================================
                   PASSWORD RESULT
                   ======================================== */

                if (!$password_correct) {

                    $error = "Old password is incorrect.";

                } else {

                    /* ====================================
                       SAVE NEW PASSWORD
                       ==================================== */

                    $update = $conn->prepare("
                        UPDATE admin_settings
                        SET
                            password = ?,
                            updated_at = NOW()
                        WHERE id = ?
                    ");

                    if (!$update) {

                        $error =
                            "Password update query error: "
                            . $conn->error;

                    } else {

                        /*
                         * New password HASHED રાખીએ છીએ
                         * જેથી old + new બંને secure રહે.
                         */

                        $hashed_password =
                            password_hash(
                                $new_password,
                                PASSWORD_DEFAULT
                            );

                        $update->bind_param(
                            "si",
                            $hashed_password,
                            $settings_id
                        );

                        if ($update->execute()) {

                            $success =
                                "Password changed successfully.";

                        } else {

                            $error =
                                "Password update failed: "
                                . $update->error;

                        }

                        $update->close();
                    }
                }
            }

            $stmt->close();
        }
    }
}


/* =========================================================
   SAVE STORE INFORMATION
   ========================================================= */

if (isset($_POST['save_store'])) {

    $store_name =
        trim($_POST['store_name'] ?? "");

    $contact_number =
        trim($_POST['contact_number'] ?? "");

    $address =
        trim($_POST['address'] ?? "");


    if ($store_name === "") {

        $error = "Store name is required.";

    } elseif ($contact_number === "") {

        $error = "Contact number is required.";

    } elseif ($address === "") {

        $error = "Address is required.";

    } else {

        $stmt = $conn->prepare("
            UPDATE admin_settings
            SET
                store_name = ?,
                contact_number = ?,
                address = ?,
                updated_at = NOW()
            WHERE id = ?
        ");

        if (!$stmt) {

            $error =
                "Store query error: "
                . $conn->error;

        } else {

            $stmt->bind_param(
                "sssi",
                $store_name,
                $contact_number,
                $address,
                $settings_id
            );

            if ($stmt->execute()) {

                $success =
                    "Store information updated successfully.";

            } else {

                $error =
                    "Store information update failed: "
                    . $stmt->error;

            }

            $stmt->close();
        }
    }
}


/* =========================================================
   RELOAD SETTINGS
   ========================================================= */

$result = $conn->query("
    SELECT
        id,
        admin_name,
        email,
        password,
        store_name,
        contact_number,
        address,
        updated_at
    FROM admin_settings
    WHERE id = $settings_id
    LIMIT 1
");

if ($result && $result->num_rows > 0) {

    $settings = $result->fetch_assoc();

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

<title>Admin Settings</title>


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
   SETTINGS CONTAINER
   ========================================================= */

.settings-container {

    display: grid;

    grid-template-columns:
        repeat(2, minmax(300px, 1fr));

    gap: 25px;

    margin-top: 25px;

}


/* =========================================================
   SETTING CARD
   ========================================================= */

.setting-card {

    background: #ffffff;

    padding: 28px;

    border-radius: 16px;

    box-shadow:
        0 6px 25px rgba(0, 0, 0, 0.07);

}


.setting-card h3 {

    margin: 0 0 22px;

    color: #176b35;

    font-size: 20px;

}


.setting-card h3 i {

    margin-right: 8px;

}


/* =========================================================
   LABEL
   ========================================================= */

.setting-card label {

    display: block;

    margin-top: 15px;

    margin-bottom: 7px;

    color: #333;

    font-size: 14px;

    font-weight: 600;

}


/* =========================================================
   INPUT
   ========================================================= */

.setting-card input,
.setting-card textarea {

    width: 100%;

    padding: 12px 14px;

    border: 1px solid #dddddd;

    border-radius: 8px;

    font-size: 14px;

    outline: none;

    background: #ffffff;

    transition: 0.2s;

    box-sizing: border-box;

}


.setting-card input:focus,
.setting-card textarea:focus {

    border-color: #176b35;

    box-shadow:
        0 0 0 3px
        rgba(23, 107, 53, 0.08);

}


.setting-card textarea {

    min-height: 110px;

    resize: vertical;

}


/* =========================================================
   BUTTON
   ========================================================= */

.setting-card button {

    width: 100%;

    margin-top: 22px;

    padding: 13px 18px;

    border: none;

    border-radius: 8px;

    background: #176b35;

    color: #ffffff;

    font-size: 14px;

    font-weight: 600;

    cursor: pointer;

    transition: 0.2s;

}


.setting-card button:hover {

    background: #0f5428;

    transform: translateY(-1px);

}


.setting-card button:active {

    transform: translateY(0);

}


/* =========================================================
   MESSAGE
   ========================================================= */

.settings-message {

    padding: 13px 16px;

    border-radius: 9px;

    margin-top: 20px;

    font-size: 14px;

    font-weight: 600;

}


.settings-success {

    background: #e8f5e9;

    color: #2e7d32;

    border: 1px solid #c8e6c9;

}


.settings-error {

    background: #ffebee;

    color: #c62828;

    border: 1px solid #ffcdd2;

}


/* =========================================================
   RESPONSIVE
   ========================================================= */

@media (max-width: 900px) {

    .settings-container {

        grid-template-columns: 1fr;

    }

}


@media (max-width: 600px) {

    .setting-card {

        padding: 20px;

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

<main class="main-content">


<!-- =====================================================
     HEADER
     ===================================================== -->

<header class="top-header">


    <div>

        <h2>

            <i class="fa-solid fa-gear"></i>

            Settings

        </h2>


        <p>

            Manage admin and store settings

        </p>

    </div>


    <div class="admin-profile">

        <i class="fa-solid fa-user-circle"></i>

        <div>

            <strong>

                <?php

                echo htmlspecialchars(
                    $settings['admin_name']
                );

                ?>

            </strong>

            <small>

                Administrator

            </small>

        </div>

    </div>


</header>


<!-- =====================================================
     SUCCESS MESSAGE
     ===================================================== -->

<?php if ($success !== "") { ?>

    <div class="settings-message settings-success">

        <i class="fa-solid fa-circle-check"></i>

        <?php

        echo htmlspecialchars($success);

        ?>

    </div>

<?php } ?>


<!-- =====================================================
     ERROR MESSAGE
     ===================================================== -->

<?php if ($error !== "") { ?>

    <div class="settings-message settings-error">

        <i class="fa-solid fa-circle-exclamation"></i>

        <?php

        echo htmlspecialchars($error);

        ?>

    </div>

<?php } ?>


<!-- =====================================================
     SETTINGS CARDS
     ===================================================== -->

<div class="settings-container">


<!-- =====================================================
     PROFILE SETTINGS
     ===================================================== -->

<div class="setting-card">


<h3>

    <i class="fa-solid fa-user"></i>

    Profile Settings

</h3>


<form method="POST" action="">


<label>

    Admin Name

</label>


<input
    type="text"
    name="admin_name"
    value="<?php

        echo htmlspecialchars(
            $settings['admin_name']
        );

    ?>"
    required
>


<label>

    Email

</label>


<input
    type="email"
    name="email"
    value="<?php

        echo htmlspecialchars(
            $settings['email']
        );

    ?>"
    required
>


<button
    type="submit"
    name="save_profile"
>

    <i class="fa-solid fa-floppy-disk"></i>

    Save Profile

</button>


</form>


</div>


<!-- =====================================================
     CHANGE PASSWORD
     ===================================================== -->

<div class="setting-card">


<h3>

    <i class="fa-solid fa-lock"></i>

    Change Password

</h3>


<form method="POST" action="">


<label>

    Old Password

</label>


<input
    type="password"
    name="old_password"
    placeholder="Enter Old Password"
    required
>


<label>

    New Password

</label>


<input
    type="password"
    name="new_password"
    placeholder="Enter New Password"
    required
>


<label>

    Confirm Password

</label>


<input
    type="password"
    name="confirm_password"
    placeholder="Confirm Password"
    required
>


<button
    type="submit"
    name="update_password"
>

    <i class="fa-solid fa-key"></i>

    Update Password

</button>


</form>


</div>


<!-- =====================================================
     STORE INFORMATION
     ===================================================== -->

<div class="setting-card">


<h3>

    <i class="fa-solid fa-store"></i>

    Store Information

</h3>


<form method="POST" action="">


<label>

    Store Name

</label>


<input
    type="text"
    name="store_name"
    value="<?php

        echo htmlspecialchars(
            $settings['store_name']
        );

    ?>"
    required
>


<label>

    Contact Number

</label>


<input
    type="text"
    name="contact_number"
    value="<?php

        echo htmlspecialchars(
            $settings['contact_number']
        );

    ?>"
    required
>


<label>

    Address

</label>


<textarea
    name="address"
    required
><?php

echo htmlspecialchars(
    $settings['address']
);

?></textarea>


<button
    type="submit"
    name="save_store"
>

    <i class="fa-solid fa-floppy-disk"></i>

    Save Changes

</button>


</form>


</div>


</div>


</main>


</div>


</body>

</html>


<?php

$conn->close();

?>