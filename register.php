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

$message = "";
$message_color = "red";


/* =========================================================
   REGISTER
   ========================================================= */

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $name = trim($_POST["name"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $mobile = trim($_POST["mobile"] ?? "");
    $password = trim($_POST["password"] ?? "");
    $confirm_password = trim($_POST["confirm_password"] ?? "");


    /* =====================================================
       VALIDATION
       ===================================================== */

    if (
        $name == "" ||
        $email == "" ||
        $mobile == "" ||
        $password == "" ||
        $confirm_password == ""
    ) {

        $message = "Please fill all fields.";

    }

    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $message = "Please enter a valid email.";

    }

    elseif (!preg_match("/^[0-9]{10}$/", $mobile)) {

        $message = "Please enter valid 10 digit mobile number.";

    }

    elseif (strlen($password) < 6) {

        $message = "Password must be at least 6 characters.";

    }

    elseif ($password != $confirm_password) {

        $message = "Password and Confirm Password do not match.";

    }

    else {


        /* =================================================
           CHECK EMAIL IN user_register
           ================================================= */

        $check = $conn->prepare("
            SELECT id
            FROM user_register
            WHERE email = ?
            LIMIT 1
        ");


        if (!$check) {

            $message =
                "Database Error: " .
                $conn->error;

        }

        else {

            $check->bind_param(
                "s",
                $email
            );

            $check->execute();

            $result = $check->get_result();


            /* =================================================
               EMAIL ALREADY EXISTS
               ================================================= */

            if ($result->num_rows > 0) {

                $message =
                    "Email already registered.";

            }

            else {


                /* =================================================
                   PASSWORD HASH
                   ================================================= */

                $hashed_password =
                    password_hash(
                        $password,
                        PASSWORD_DEFAULT
                    );


                /* =================================================
                   CREATE USERNAME FROM EMAIL
                   ================================================= */

                $username = strtolower(
                    preg_replace(
                        "/[^a-zA-Z0-9]/",
                        "",
                        explode("@", $email)[0]
                    )
                );


                if ($username == "") {

                    $username = "customer";

                }


                /* =================================================
                   CHECK USERNAME
                   ================================================= */

                $username_check = $conn->prepare("
                    SELECT user_id
                    FROM users
                    WHERE username = ?
                    LIMIT 1
                ");


                if ($username_check) {

                    $username_check->bind_param(
                        "s",
                        $username
                    );

                    $username_check->execute();

                    $username_result =
                        $username_check->get_result();


                    if (
                        $username_result->num_rows > 0
                    ) {

                        $username =
                            $username .
                            rand(100, 999);

                    }


                    $username_check->close();

                }


                /* =================================================
                   START TRANSACTION
                   ================================================= */

                $conn->begin_transaction();


                try {


                    /* =================================================
                       1. INSERT INTO user_register
                       ================================================= */

                    $register = $conn->prepare("
                        INSERT INTO user_register
                        (
                            full_name,
                            email,
                            mobile_number,
                            password,
                            created_at
                        )
                        VALUES
                        (
                            ?,
                            ?,
                            ?,
                            ?,
                            NOW()
                        )
                    ");


                    if (!$register) {

                        throw new Exception(
                            "user_register Error: " .
                            $conn->error
                        );

                    }


                    $register->bind_param(
                        "ssss",
                        $name,
                        $email,
                        $mobile,
                        $hashed_password
                    );


                    if (!$register->execute()) {

                        throw new Exception(
                            "Registration Failed: " .
                            $register->error
                        );

                    }


                    $register->close();


                    /* =================================================
                       2. INSERT INTO customer

                       YOUR ACTUAL CUSTOMER TABLE:

                       id
                       name
                       email
                       phone
                       status
                       created_at
                       ================================================= */

                    $customer = $conn->prepare("
                        INSERT INTO customers
                        (
                            name,
                            email,
                            phone,
                            status,
                            created_at
                        )
                        VALUES
                        (
                            ?,
                            ?,
                            ?,
                            'Active',
                            NOW()
                        )
                    ");


                    if (!$customer) {

                        throw new Exception(
                            "Customer Table Error: " .
                            $conn->error
                        );

                    }


                    $customer->bind_param(
                        "sss",
                        $name,
                        $email,
                        $mobile
                    );


                    if (!$customer->execute()) {

                        throw new Exception(
                            "Customer Insert Failed: " .
                            $customer->error
                        );

                    }


                    /* =================================================
                       GET CUSTOMER ID
                       ================================================= */

                    $customer_id =
                        $conn->insert_id;


                    $customer->close();


                    /* =================================================
                       3. INSERT INTO users

                       user_type = c
                       customer_id = customer table ID
                       active = 1
                       ================================================= */

                    $user_insert = $conn->prepare("
                        INSERT INTO users
                        (
                            username,
                            user_name,
                            user_password,
                            user_email,
                            user_contact,
                            user_type,
                            customer_id,
                            active,
                            created_at
                        )
                        VALUES
                        (
                            ?,
                            ?,
                            ?,
                            ?,
                            ?,
                            'c',
                            ?,
                            1,
                            NOW()
                        )
                    ");


                    if (!$user_insert) {

                        throw new Exception(
                            "Users Table Error: " .
                            $conn->error
                        );

                    }


                    $user_insert->bind_param(
                        "sssssi",
                        $username,
                        $name,
                        $hashed_password,
                        $email,
                        $mobile,
                        $customer_id
                    );


                    if (!$user_insert->execute()) {

                        throw new Exception(
                            "Users Insert Failed: " .
                            $user_insert->error
                        );

                    }


                    $user_insert->close();


                    /* =================================================
                       EVERYTHING SUCCESS
                       ================================================= */

                    $conn->commit();


                    $message =
                        "Registration Successful! ✅";

                    $message_color = "green";


                }

                catch (Exception $e) {


                    /* =================================================
                       ROLLBACK
                       ================================================= */

                    $conn->rollback();


                    $message =
                        $e->getMessage();

                    $message_color = "red";

                }

            }


            $check->close();

        }

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

<title>Register - Grocery Store</title>


<link
    rel="stylesheet"
    href="register.css"
>

</head>


<body>


<div class="container">


<div class="register-box">


<h2>
    🛒 Grocery Store Register
</h2>


<!-- =====================================================
     MESSAGE
     ===================================================== -->

<?php if ($message != "") { ?>

<p
    style="
        color: <?php echo htmlspecialchars($message_color); ?>;
        text-align: center;
        font-weight: bold;
        margin-bottom: 15px;
    "
>

<?php

echo htmlspecialchars($message);

?>

</p>

<?php } ?>


<!-- =====================================================
     FORM
     ===================================================== -->

<form
    method="POST"
    action=""
>


<!-- =====================================================
     FULL NAME
     ===================================================== -->

<div class="input-box">

<label>
    Full Name
</label>

<input
    type="text"
    name="name"
    placeholder="Enter Full Name"
    value="<?php
        echo htmlspecialchars(
            $_POST["name"] ?? ""
        );
    ?>"
    required
>

</div>


<!-- =====================================================
     EMAIL
     ===================================================== -->

<div class="input-box">

<label>
    Email
</label>

<input
    type="email"
    name="email"
    placeholder="Enter Email"
    value="<?php
        echo htmlspecialchars(
            $_POST["email"] ?? ""
        );
    ?>"
    required
>

</div>


<!-- =====================================================
     MOBILE
     ===================================================== -->

<div class="input-box">

<label>
    Mobile Number
</label>

<input
    type="text"
    name="mobile"
    placeholder="Enter Mobile Number"
    maxlength="10"
    value="<?php
        echo htmlspecialchars(
            $_POST["mobile"] ?? ""
        );
    ?>"
    required
>

</div>


<!-- =====================================================
     PASSWORD
     ===================================================== -->

<div class="input-box">

<label>
    Password
</label>

<input
    type="password"
    name="password"
    placeholder="Enter Password"
    required
>

</div>


<!-- =====================================================
     CONFIRM PASSWORD
     ===================================================== -->

<div class="input-box">

<label>
    Confirm Password
</label>

<input
    type="password"
    name="confirm_password"
    placeholder="Confirm Password"
    required
>

</div>


<!-- =====================================================
     REGISTER BUTTON
     ===================================================== -->

<button
    type="submit"
    name="register"
>

Register

</button>


<!-- =====================================================
     LOGIN
     ===================================================== -->

<p>

Already have an account?

<a href="login.php">
    Login
</a>

</p>


</form>


</div>

</div>


</body>

</html>


<?php

$conn->close();

?>