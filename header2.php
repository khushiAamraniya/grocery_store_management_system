<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Shreenath Grocery Store</title>

<link rel="stylesheet" href="style.css">

<link
    rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"
>

<style>

/* ================= CHECKOUT HEADER ================= */

.checkout-header {

    width: 100%;
    height: 100px;

    background: #176b35;

    display: flex;
    align-items: center;
    justify-content: space-between;

    padding: 0 6%;

    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.10);

}


/* ================= LOGO ================= */

.checkout-header .logo {

    margin: 0;

}

.checkout-header .logo a {

    display: flex;
    align-items: center;
    gap: 10px;

    color: #ffffff;

    text-decoration: none;

    font-size: 30px;

    font-weight: 700;

}

.checkout-header .logo i {

    font-size: 32px;

}


/* ================= HEADER BUTTONS ================= */

.header-buttons {

    display: flex;

    align-items: center;

    gap: 20px;

}


/* ================= CART BUTTON ================= */

.checkout-header .cart-link {

    color: #ffffff;

    text-decoration: none;

    font-size: 19px;

    font-weight: 500;

    display: flex;

    align-items: center;

    gap: 8px;

    transition: 0.2s;

}


.checkout-header .cart-link:hover {

    opacity: 0.8;

}


/* ================= CONTINUE SHOPPING ================= */

.checkout-header .back-cart {

    color: #ffffff;

    text-decoration: none;

    font-size: 19px;

    font-weight: 500;

    transition: 0.2s;

}


.checkout-header .back-cart:hover {

    opacity: 0.8;

}


/* ================= MOBILE ================= */

@media (max-width: 650px) {

    .checkout-header {

        height: 80px;

        padding: 0 4%;

    }


    .checkout-header .logo a {

        font-size: 20px;

    }


    .checkout-header .logo i {

        font-size: 23px;

    }


    .header-buttons {

        gap: 10px;

    }


    .checkout-header .cart-link {

        font-size: 14px;

    }


    .checkout-header .back-cart {

        font-size: 14px;

    }

}

</style>

</head>


<body>


<!-- ================= CHECKOUT HEADER ================= -->

<header class="checkout-header">


    <!-- ================= LOGO ================= -->

    <h2 class="logo">

        <a href="index.php">

            <i class="fa-solid fa-cart-shopping"></i>

            Shreenath Grocery Store

        </a>

    </h2>


    <!-- ================= HEADER BUTTONS ================= -->

    <div class="header-buttons">


        <!-- ================= CART ================= -->

        <a
            href="cart.php"
            class="cart-link"
        >

            <i class="fa-solid fa-cart-shopping"></i>

            Cart

        </a>


        <!-- ================= CONTINUE SHOPPING ================= -->

        <a
            href="product.php"
            class="back-cart"
        >

            ⬅ Continue Shopping

        </a>


    </div>


</header>


</body>

</html>