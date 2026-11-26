<?php

session_start();


// ================= DATABASE CONNECTION =================

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


$error = "";


// ================= LOGIN =================

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $username = trim($_POST["username"] ?? "");
    $password = trim($_POST["password"] ?? "");


    // ================= VALIDATION =================

    if ($username == "" || $password == "") {

        $error = "Please enter Email and Password.";

    } else {


        // ================= CHECK USER =================

        $sql = "SELECT id, full_name, email, password
                FROM user_register
                WHERE email = ?
                LIMIT 1";

        $stmt = $conn->prepare($sql);


        if (!$stmt) {

            $error = "Database Error: " . $conn->error;

        } else {

            $stmt->bind_param("s", $username);

            $stmt->execute();

            $result = $stmt->get_result();


            // ================= USER FOUND =================

            if ($result->num_rows == 1) {

                $user = $result->fetch_assoc();


                // ================= PASSWORD CHECK =================

                if (password_verify($password, $user["password"])) {


                    // ================= SESSION =================

                    $_SESSION["user_id"] = $user["id"];

                    $_SESSION["username"] = $user["full_name"];

                    $_SESSION["user_email"] = $user["email"];


                    // =================================================
                    // ADD TO CART AFTER SUCCESSFUL LOGIN
                    // =================================================

                    if (isset($_SESSION["pending_product_id"])) {

                        $product_id =
                            (int)$_SESSION["pending_product_id"];

                        // Pending product ID remove કરો
                        unset($_SESSION["pending_product_id"]);


                        // Product ID ફરીથી session માં રાખો
                        $_SESSION["add_after_login"] = $product_id;


                        // Add To Cart પર મોકલો
                        header(
                            "Location: add_to_cart.php"
                        );

                        exit();

                    }


                    // ================= NORMAL LOGIN =================

                    header("Location: index.php");

                    exit();


                } else {

                    $error = "Incorrect password.";

                }

            } else {

                $error = "Email not registered.";

            }


            $stmt->close();

        }

    }

}

?>


<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Login - Grocery Store</title>

<link rel="stylesheet" href="login.css">

<link
    rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"
>

</head>


<body>


<div class="container">

    <div class="login-box">

        <h2>🛒 Grocery Store Login</h2>


        <!-- ERROR MESSAGE -->

        <?php if ($error != "") { ?>

            <p style="
                color:red;
                text-align:center;
                font-weight:bold;
                margin-bottom:15px;
            ">

                <?php echo htmlspecialchars($error); ?>

            </p>

        <?php } ?>


        <!-- LOGIN FORM -->

        <form method="POST" action="" autocomplete="off">


            <!-- EMAIL -->

            <div class="input-box">

                <label>Email</label>

               <input
    type="email"
    name="username"
    id="username"
    placeholder="Enter Email"
    value=""
    autocomplete="off"
    required
>

            </div>


            <!-- PASSWORD -->

            <div class="input-box">

                <label>Password</label>

                <div class="password-box">

                   <input
    type="password"
    name="password"
    id="password"
    placeholder="Enter Password"
    value=""
    autocomplete="new-password"
    required
>

                    <i
                        class="fa-solid fa-eye"
                        id="eye"
                    ></i>

                </div>

            </div>


            <!-- LOGIN BUTTON -->

            <button
                type="submit"
                name="login"
            >

                Login

            </button>


            <!-- REGISTER -->

            <p>

                Don't have an account?

                <a href="register.php">
                    Register
                </a>

            </p>


        </form>

    </div>

</div>


<script>

const eye = document.getElementById("eye");

const password = document.getElementById("password");


eye.onclick = function () {

    if (password.type === "password") {

        password.type = "text";

        eye.classList.remove("fa-eye");
        eye.classList.add("fa-eye-slash");

    } else {

        password.type = "password";

        eye.classList.remove("fa-eye-slash");
        eye.classList.add("fa-eye");

    }

};

</script>


</body>

</html>


<?php

$conn->close();

?>