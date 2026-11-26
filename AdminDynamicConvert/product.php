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


// ================= DELETE PRODUCT =================

if (isset($_POST["delete_product"])) {

    $product_id = (int)$_POST["product_id"];

    $stmt = $conn->prepare(
        "DELETE FROM products WHERE id = ?"
    );

    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $stmt->close();

    header("Location: product.php");
    exit();
}


// ================= UPDATE PRODUCT =================

if (isset($_POST["update_product"])) {

    $product_id = (int)$_POST["product_id"];

    $product_name = $_POST["product_name"];
    $price = $_POST["price"];
    $quantity = $_POST["quantity"];
    $category_id = $_POST["category_id"];

    // Old image
    $product_image = $_POST["old_product_image"];

    // New image
    if (
        isset($_FILES["product_image"]) &&
        $_FILES["product_image"]["error"] == 0
    ) {

        $image_name = $_FILES["product_image"]["name"];
        $tmp_name = $_FILES["product_image"]["tmp_name"];

        $upload_folder = "../dist/images/";

        if (!is_dir($upload_folder)) {
            mkdir($upload_folder, 0777, true);
        }

        $product_image = $image_name;

        move_uploaded_file(
            $tmp_name,
            $upload_folder . $image_name
        );
    }


    // Update Product

    $stmt = $conn->prepare(
        "UPDATE products SET
            product_name = ?,
            price = ?,
            product_image = ?,
            quantity = ?,
            category_id = ?
         WHERE id = ?"
    );

    $stmt->bind_param(
        "sdssii",
        $product_name,
        $price,
        $product_image,
        $quantity,
        $category_id,
        $product_id
    );

    $stmt->execute();

    $stmt->close();

    header("Location: product.php");
    exit();
}


// ================= ADD PRODUCT =================

if (isset($_POST["add_product"])) {

    $product_name = $_POST["product_name"];
    $price = $_POST["price"];
    $quantity = $_POST["quantity"];
    $category_id = $_POST["category_id"];

    // Image
    $product_image = "";

    if (
        isset($_FILES["product_image"]) &&
        $_FILES["product_image"]["error"] == 0
    ) {

        $image_name = $_FILES["product_image"]["name"];
        $tmp_name = $_FILES["product_image"]["tmp_name"];

        $upload_folder = "../dist/images/";

        if (!is_dir($upload_folder)) {
            mkdir($upload_folder, 0777, true);
        }

        $product_image = $image_name;

        move_uploaded_file(
            $tmp_name,
            $upload_folder . $image_name
        );
    }


    // Insert Product

    $stmt = $conn->prepare(
        "INSERT INTO products
        (
            product_name,
            price,
            product_image,
            quantity,
            category_id,
            status
        )
        VALUES (?, ?, ?, ?, ?, 1)"
    );

    $stmt->bind_param(
        "sdssi",
        $product_name,
        $price,
        $product_image,
        $quantity,
        $category_id
    );

    $stmt->execute();

    $stmt->close();

    header("Location: product.php");
    exit();
}


// ================= GET CATEGORIES =================

$categories = [];

$category_result = $conn->query(
    "SELECT
        id,
        category_name
     FROM categories
     ORDER BY id ASC"
);

if ($category_result) {

    while ($row = $category_result->fetch_assoc()) {

        $categories[] = $row;
    }
}


// ================= GET PRODUCTS =================

$products = [];

$product_result = $conn->query(
    "SELECT
        id,
        product_name,
        price,
        product_image,
        quantity,
        category_id
     FROM products
     ORDER BY id ASC"
);

if ($product_result) {

    while ($row = $product_result->fetch_assoc()) {

        $products[] = $row;
    }
}


$total_products = count($products);

?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>

<title>Products Management</title>


<link
    rel="stylesheet"
    href="admin.css"
>


<link
    rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"
>


<style>


/* ================= MAIN ================= */

.main-content {

    padding: 25px;

    margin-left: 250px;

}


/* ================= HEADER ================= */

.page-header {

    display: flex;

    justify-content: space-between;

    align-items: center;

    margin-bottom: 25px;

}


.page-header h1 {

    margin: 0;

    color: #1b5e20;

}


.page-header p {

    color: #777;

    margin-top: 6px;

}


/* ================= ADD BUTTON ================= */

.add-btn {

    background: #1b5e20;

    color: white;

    border: none;

    padding: 12px 20px;

    border-radius: 8px;

    cursor: pointer;

    font-size: 15px;

    font-weight: 600;

}


.add-btn:hover {

    background: #144d19;

}


/* ================= FORM ================= */

.product-form {

    display: none;

    background: white;

    padding: 25px;

    border-radius: 12px;

    box-shadow: 0 5px 20px rgba(0,0,0,0.08);

    margin-bottom: 25px;

    max-width: 750px;

}


.product-form.show {

    display: block;

}


.product-form h2 {

    color: #1b5e20;

    margin-bottom: 20px;

}


/* ================= FORM GRID ================= */

.form-grid {

    display: grid;

    grid-template-columns: 1fr 1fr;

    gap: 18px;

}


/* ================= INPUT ================= */

.input-box label {

    display: block;

    margin-bottom: 7px;

    font-weight: 600;

}


.input-box input,
.input-box select {

    width: 100%;

    box-sizing: border-box;

    padding: 12px;

    border: 1px solid #ddd;

    border-radius: 7px;

    font-size: 15px;

}


.input-box input:focus,
.input-box select:focus {

    outline: none;

    border-color: #1b5e20;

}


/* ================= FORM BUTTONS ================= */

.form-buttons {

    margin-top: 22px;

}


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


.close-btn {

    background: #777;

    color: white;

    border: none;

    padding: 12px 20px;

    border-radius: 7px;

    cursor: pointer;

    margin-left: 8px;

}


/* ================= TABLE ================= */

.table-box {

    background: white;

    padding: 22px;

    border-radius: 12px;

    box-shadow: 0 5px 20px rgba(0,0,0,0.08);

}


.table-header {

    display: flex;

    justify-content: space-between;

    align-items: center;

    margin-bottom: 20px;

}


.table-header h2 {

    color: #1b5e20;

    margin: 0;

}


.total-products {

    background: #e8f5e9;

    color: #1b5e20;

    padding: 8px 14px;

    border-radius: 20px;

    font-weight: 600;

}


.table-container {

    overflow-x: auto;

}


table {

    width: 100%;

    border-collapse: collapse;

}


table th {

    background: #1b5e20;

    color: white;

    padding: 14px;

    text-align: left;

}


table td {

    padding: 13px;

    border-bottom: 1px solid #eee;

    vertical-align: middle;

}


table tr:hover {

    background: #f8f8f8;

}


/* ================= IMAGE ================= */

.product-img {

    width: 60px;

    height: 60px;

    object-fit: contain;

    border-radius: 8px;

    background: #f5f5f5;

    padding: 5px;

}


/* ================= ACTION ================= */

.action-buttons {

    white-space: nowrap;

}


.edit-btn {

    background: #ffc107;

    color: #222;

    border: none;

    padding: 8px 11px;

    border-radius: 6px;

    cursor: pointer;

}


.delete-btn {

    background: #dc3545;

    color: white;

    border: none;

    padding: 8px 11px;

    border-radius: 6px;

    cursor: pointer;

}


.edit-btn:hover {

    background: #e0a800;

}


.delete-btn:hover {

    background: #bb2d3b;

}


/* ================= EDIT IMAGE ================= */

.edit-image {

    width: 70px;

    height: 70px;

    object-fit: contain;

    border-radius: 8px;

    background: #f5f5f5;

    padding: 5px;

    margin-top: 8px;

}


/* ================= RESPONSIVE ================= */

@media (max-width: 900px) {

    .main-content {

        margin-left: 0;

        padding: 15px;

    }


    .form-grid {

        grid-template-columns: 1fr;

    }


    .page-header {

        flex-direction: column;

        align-items: flex-start;

        gap: 15px;

    }

}

</style>

</head>


<body>


<!-- ================= SIDEBAR ================= -->

<?php include 'sidebar.php'; ?>


<!-- ================= MAIN ================= -->

<div class="main-content">


    <!-- ================= PAGE HEADER ================= -->

    <div class="page-header">


        <div>

            <h1>

                Products Management

            </h1>


            <p>

                Manage Grocery Store Products

            </p>

        </div>


        <!-- ADD PRODUCT BUTTON -->

        <button

            type="button"

            class="add-btn"

            onclick="openProductForm()"

        >

            <i class="fas fa-plus-square"></i>

            Add Product

        </button>


    </div>



    <!-- ================= ADD / EDIT FORM ================= -->

    <div

        id="productForm"

        class="product-form"

    >


        <h2 id="formTitle">

            <i class="fas fa-box"></i>

            Add New Product

        </h2>



        <form

            method="POST"

            enctype="multipart/form-data"

            id="productFormData"

        >


            <input
                type="hidden"
                name="product_id"
                id="product_id"
            >


            <input
                type="hidden"
                name="old_product_image"
                id="old_product_image"
            >


            <div class="form-grid">


                <!-- PRODUCT NAME -->

                <div class="input-box">


                    <label>

                        Product Name

                    </label>


                    <input

                        type="text"

                        name="product_name"

                        id="product_name"

                        placeholder="Enter Product Name"

                        required

                    >


                </div>



                <!-- PRICE -->

                <div class="input-box">


                    <label>

                        Price

                    </label>


                    <input

                        type="number"

                        name="price"

                        id="price"

                        placeholder="Enter Price"

                        required

                    >


                </div>



                <!-- PRODUCT IMAGE -->

                <div class="input-box">


                    <label>

                        Product Image

                    </label>


                    <input

                        type="file"

                        name="product_image"

                        id="product_image"

                        accept="image/*"

                    >


                    <img
                        id="editImage"
                        class="edit-image"
                        src=""
                        alt="Product Image"
                        style="display:none;"
                    >


                </div>



                <!-- QUANTITY -->

                <div class="input-box">


                    <label>

                        Quantity

                    </label>


                    <input

                        type="text"

                        name="quantity"

                        id="quantity"

                        placeholder="Example: 100 GM"

                        required

                    >


                </div>



                <!-- CATEGORY -->

                <div class="input-box">


                    <label>

                        Category

                    </label>


                    <select

                        name="category_id"

                        id="category_id"

                        required

                    >


                        <option value="">

                            Select Category

                        </option>


                        <?php foreach ($categories as $category): ?>


                            <option

                                value="<?php echo $category["id"]; ?>"

                            >


                                <?php

                                echo htmlspecialchars(

                                    $category["category_name"]

                                );

                                ?>


                            </option>


                        <?php endforeach; ?>


                    </select>


                </div>


            </div>



            <!-- FORM BUTTONS -->

            <div class="form-buttons">


                <button

                    type="submit"

                    name="add_product"

                    id="submitButton"

                    class="submit-btn"

                >

                    <i class="fas fa-plus"></i>

                    Add Product

                </button>



                <button

                    type="button"

                    class="close-btn"

                    onclick="closeProductForm()"

                >

                    Close

                </button>


            </div>


        </form>


    </div>



    <!-- ================= ALL PRODUCTS ================= -->

    <div class="table-box">


        <div class="table-header">


            <h2>

                All Products

            </h2>


            <span class="total-products">

                Total Products:

                <?php echo $total_products; ?>

            </span>


        </div>



        <div class="table-container">


            <table>


                <thead>


                    <tr>


                        <th>ID</th>

                        <th>Product Name</th>

                        <th>Price</th>

                        <th>Product Image</th>

                        <th>Quantity</th>

                        <th>Category ID</th>

                        <th>Action</th>


                    </tr>


                </thead>



                <tbody>


                    <?php if (count($products) > 0): ?>


                        <?php foreach ($products as $product): ?>


                            <tr>


                                <!-- ID -->

                                <td>

                                    <?php

                                    echo $product["id"];

                                    ?>

                                </td>



                                <!-- PRODUCT NAME -->

                                <td>


                                    <strong>


                                        <?php

                                        echo htmlspecialchars(

                                            $product["product_name"]

                                        );

                                        ?>


                                    </strong>


                                </td>



                                <!-- PRICE -->

                                <td>


                                    ₹<?php

                                    echo htmlspecialchars(

                                        $product["price"]

                                    );

                                    ?>


                                </td>



                                <!-- IMAGE -->

                                <td>


                                    <?php if (!empty($product["product_image"])): ?>


                                        <img

                                            src="<?php

                                            echo (

                                                strpos(

                                                    $product["product_image"],

                                                    "dist/images/"

                                                ) === 0

                                            )

                                            ? "../" .

                                                htmlspecialchars(

                                                    $product["product_image"]

                                                )

                                            : "../dist/images/" .

                                                htmlspecialchars(

                                                    $product["product_image"]

                                                );

                                            ?>"

                                            class="product-img"

                                            alt="Product"

                                        >


                                    <?php else: ?>


                                        No Image


                                    <?php endif; ?>


                                </td>



                                <!-- QUANTITY -->

                                <td>


                                    <?php

                                    echo htmlspecialchars(

                                        $product["quantity"]

                                    );

                                    ?>


                                </td>



                                <!-- CATEGORY -->

                                <td>


                                    <?php

                                    echo htmlspecialchars(

                                        $product["category_id"]

                                    );

                                    ?>


                                </td>



                                <!-- ACTION -->

                                <td class="action-buttons">


                                    <!-- EDIT BUTTON -->

                                    <button

                                        type="button"

                                        class="edit-btn"

                                        onclick='editProduct(

                                            <?php echo json_encode($product); ?>

                                        )'

                                    >

                                        <i class="fas fa-edit"></i>

                                    </button>



                                    <!-- DELETE BUTTON -->

                                    <form

                                        method="POST"

                                        style="display:inline;"

                                    >


                                        <input

                                            type="hidden"

                                            name="product_id"

                                            value="<?php

                                            echo $product["id"];

                                            ?>"

                                        >


                                        <button

                                            type="submit"

                                            name="delete_product"

                                            class="delete-btn"

                                            onclick="return confirm('Are you sure you want to delete this product?');"

                                        >

                                            <i class="fas fa-trash"></i>

                                        </button>


                                    </form>


                                </td>


                            </tr>


                        <?php endforeach; ?>


                    <?php else: ?>


                        <tr>


                            <td

                                colspan="7"

                                style="text-align:center;"

                            >

                                No Products Found

                            </td>


                        </tr>


                    <?php endif; ?>


                </tbody>


            </table>


        </div>


    </div>


</div>



<!-- ================= JAVASCRIPT ================= -->

<script>


// ================= OPEN ADD FORM =================

function openProductForm()
{

    document
        .getElementById("productForm")
        .classList.add("show");


    document
        .getElementById("formTitle")
        .innerHTML =
        '<i class="fas fa-box"></i> Add New Product';


    document
        .getElementById("submitButton")
        .name = "add_product";


    document
        .getElementById("submitButton")
        .innerHTML =
        '<i class="fas fa-plus"></i> Add Product';


    document
        .getElementById("productFormData")
        .reset();


    document
        .getElementById("product_id")
        .value = "";


    document
        .getElementById("old_product_image")
        .value = "";


    document
        .getElementById("editImage")
        .style.display = "none";

}


// ================= CLOSE FORM =================

function closeProductForm()
{

    document
        .getElementById("productForm")
        .classList.remove("show");

}


// ================= EDIT PRODUCT =================

function editProduct(product)
{

    document
        .getElementById("productForm")
        .classList.add("show");


    document
        .getElementById("formTitle")
        .innerHTML =
        '<i class="fas fa-edit"></i> Edit Product';


    document
        .getElementById("submitButton")
        .name = "update_product";


    document
        .getElementById("submitButton")
        .innerHTML =
        '<i class="fas fa-save"></i> Update Product';


    document
        .getElementById("product_id")
        .value = product.id;


    document
        .getElementById("product_name")
        .value = product.product_name;


    document
        .getElementById("price")
        .value = product.price;


    document
        .getElementById("quantity")
        .value = product.quantity;


    document
        .getElementById("category_id")
        .value = product.category_id;


    document
        .getElementById("old_product_image")
        .value = product.product_image;


    // Existing Image

    if (product.product_image != "")
    {

        let imagePath;


        if (
            product.product_image.indexOf(
                "dist/images/"
            ) === 0
        )
        {

            imagePath =
                "../" +
                product.product_image;

        }
        else
        {

            imagePath =
                "../dist/images/" +
                product.product_image;

        }


        document
            .getElementById("editImage")
            .src = imagePath;


        document
            .getElementById("editImage")
            .style.display = "block";

    }
    else
    {

        document
            .getElementById("editImage")
            .style.display = "none";

    }

}

</script>


</body>

</html>