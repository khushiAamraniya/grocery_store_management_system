<?php

session_start();

/* =====================================================
   DATABASE CONNECTION
===================================================== */

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


/* =====================================================
   IMAGE PATH
===================================================== */

$projectRoot = dirname(__DIR__);

$imageFolder = $projectRoot . "/dist/images/";

$imageWebFolder = "../dist/images/";


if (!is_dir($imageFolder)) {

    mkdir(
        $imageFolder,
        0777,
        true
    );

}


/* =====================================================
   ADD CATEGORY
===================================================== */

if (
    $_SERVER["REQUEST_METHOD"] === "POST"
    &&
    isset($_POST["add_category"])
) {

    $categoryName = trim(
        $_POST["category_name"] ?? ""
    );


    if ($categoryName === "") {

        die("Category Name is required.");

    }


    /* =================================================
       IMAGE
    ================================================= */

    $imageName = "";


    if (
        isset($_FILES["category_image"])
        &&
        $_FILES["category_image"]["error"]
        === UPLOAD_ERR_OK
    ) {

        $extension = strtolower(
            pathinfo(
                $_FILES["category_image"]["name"],
                PATHINFO_EXTENSION
            )
        );


        $allowedExtensions = [
            "jpg",
            "jpeg",
            "png",
            "gif",
            "webp"
        ];


        if (
            !in_array(
                $extension,
                $allowedExtensions
            )
        ) {

            die(
                "Only JPG, JPEG, PNG, GIF and WEBP images are allowed."
            );

        }


        $fileName =
            time()
            . "_"
            . uniqid()
            . "."
            . $extension;


        $uploadPath =
            $imageFolder
            . $fileName;


        if (
            !move_uploaded_file(
                $_FILES["category_image"]["tmp_name"],
                $uploadPath
            )
        ) {

            die("Image Upload Failed.");

        }


        /* Save only filename in database */

        $imageName = $fileName;

    }


    /* =================================================
       INSERT CATEGORY
    ================================================= */

    $stmt = $conn->prepare(
        "INSERT INTO categories
        (
            category_name,
            category_image
        )
        VALUES
        (
            ?,
            ?
        )"
    );


    if (!$stmt) {

        die(
            "Prepare Error: "
            . $conn->error
        );

    }


    $stmt->bind_param(
        "ss",
        $categoryName,
        $imageName
    );


    if (!$stmt->execute()) {

        die(
            "Database Insert Error: "
            . $stmt->error
        );

    }


    $stmt->close();


    header(
        "Location: categories.php?success=1"
    );

    exit();

}


/* =====================================================
   GET ALL CATEGORIES
===================================================== */

$categories = [];


$sql = "
    SELECT
        id,
        category_name,
        category_image
    FROM categories
    ORDER BY id ASC
";


$result = $conn->query($sql);


if (!$result) {

    die(
        "Database Fetch Error: "
        . $conn->error
    );

}


while (
    $row = $result->fetch_assoc()
) {

    $categories[] = $row;

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


<title>
    Categories Management
</title>


<!-- =================================================
     ADMIN CSS
================================================= -->

<link
    rel="stylesheet"
    href="admin.css"
>


<!-- =================================================
     FONT AWESOME
================================================= -->

<link
    rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"
>


<style>

/* =====================================================
   CATEGORY IMAGE
===================================================== */

.category-img {

    width: 70px;

    height: 70px;

    object-fit: cover;

    border-radius: 8px;

    border: 1px solid #ddd;

    display: block;

}


/* =====================================================
   SUCCESS MESSAGE
===================================================== */

.success-message {

    background: #d4edda;

    color: #155724;

    padding: 12px 15px;

    border-radius: 6px;

    margin: 15px 0;

}


/* =====================================================
   TABLE BOX
===================================================== */

.table-box {

    background: #ffffff;

    padding: 25px;

    border-radius: 12px;

    box-shadow: 0 5px 20px rgba(0,0,0,0.08);

    overflow-x: auto;

}


/* =====================================================
   TABLE TITLE
===================================================== */

.table-box h2 {

    margin-bottom: 20px;

    color: #1b5e20;

}


/* =====================================================
   TABLE
===================================================== */

.table-box table {

    width: 100%;

    border-collapse: collapse;

    table-layout: fixed;

}


/* =====================================================
   TABLE HEADER
===================================================== */

.table-box th {

    background: #1b5e20;

    color: white;

    padding: 14px 18px;

    text-align: left;

    font-size: 15px;

}


/* =====================================================
   TABLE DATA
===================================================== */

.table-box td {

    padding: 14px 18px;

    border-bottom: 1px solid #eeeeee;

    vertical-align: middle;

    font-size: 14px;

}


/* =====================================================
   ID COLUMN
===================================================== */

.table-box th:nth-child(1),
.table-box td:nth-child(1) {

    width: 80px;

    text-align: center;

}


/* =====================================================
   CATEGORY NAME COLUMN
===================================================== */

.table-box th:nth-child(2),
.table-box td:nth-child(2) {

    width: 45%;

}


/* =====================================================
   IMAGE COLUMN
===================================================== */

.table-box th:nth-child(3),
.table-box td:nth-child(3) {

    width: 200px;

}


/* =====================================================
   ROW HOVER
===================================================== */

.table-box tbody tr:hover {

    background: #f7faf7;

}


/* =====================================================
   NO IMAGE
===================================================== */

.no-image {

    color: #888;

    font-size: 13px;

}


/* =====================================================
   FORM
===================================================== */

#categoryForm {

    display: none;

}


/* =====================================================
   FORM BOX
===================================================== */

.form-box {

    margin-bottom: 25px;

}

</style>


</head>


<body>


<div class="container">


<!-- =================================================
     SIDEBAR
================================================= -->

<?php include "sidebar.php"; ?>


<!-- =================================================
     MAIN
================================================= -->

<main class="main">


<!-- =================================================
     TOP BAR
================================================= -->

<header class="topbar">


<div>


<h2>
    Categories Management
</h2>


<p>
    Manage Grocery Store Categories
</p>


</div>





</header>



<!-- =================================================
     SUCCESS MESSAGE
================================================= -->

<?php

if (
    isset($_GET["success"])
    &&
    $_GET["success"] == "1"
) {

?>


<div
    class="success-message"
>


<i
    class="fa-solid fa-circle-check"
></i>


Category Added Successfully!


</div>


<?php

}

?>



<!-- =================================================
     ADD CATEGORY BUTTON
================================================= -->

<button
    class="add-btn"
    type="button"
    onclick="openCategoryForm()"
>


<i
    class="fa-solid fa-folder-plus"
></i>


Add Category


</button>



<!-- =================================================
     ADD CATEGORY FORM
================================================= -->

<div
    class="form-box"
    id="categoryForm"
>


<h2>
    Add New Category
</h2>



<form
    method="POST"
    action=""
    enctype="multipart/form-data"
>


<!-- =================================================
     CATEGORY NAME
================================================= -->

<div
    class="input-box"
>


<label>
    Category Name
</label>


<input
    type="text"
    name="category_name"
    placeholder="Enter Category Name"
    required
>


</div>



<!-- =================================================
     CATEGORY IMAGE
================================================= -->

<div
    class="input-box"
>


<label>
    Category Image
</label>


<input
    type="file"
    name="category_image"
    accept="image/*"
    required
>


</div>



<!-- =================================================
     ADD BUTTON
================================================= -->

<button
    type="submit"
    name="add_category"
    class="submit-btn"
>


<i
    class="fa-solid fa-plus"
></i>


Add Category


</button>



<!-- =================================================
     CLOSE BUTTON
================================================= -->

<button
    type="button"
    class="close-btn"
    onclick="closeCategoryForm()"
>


Close


</button>


</form>


</div>



<!-- =================================================
     ALL CATEGORIES
================================================= -->

<div
    class="table-box"
>


<h2>
    All Categories
</h2>



<table>


<!-- =================================================
     TABLE HEADER
================================================= -->

<thead>


<tr>


<th>
    ID
</th>


<th>
    Category Name
</th>


<th>
    Category Image
</th>


</tr>


</thead>



<!-- =================================================
     TABLE BODY
================================================= -->

<tbody>


<?php

if (
    count($categories) > 0
) {


    foreach (
        $categories
        as $cat
    ) {


?>


<tr>


<!-- =================================================
     ID
================================================= -->

<td>

<?php

echo htmlspecialchars(
    $cat["id"]
);

?>

</td>



<!-- =================================================
     CATEGORY NAME
================================================= -->

<td>

<?php

echo htmlspecialchars(
    $cat["category_name"]
);

?>

</td>



<!-- =================================================
     CATEGORY IMAGE
================================================= -->

<td>


<?php

if (
    !empty(
        $cat["category_image"]
    )
) {


    $imagePath =
        $imageWebFolder
        . $cat["category_image"];

?>


<img

    src="<?php

        echo htmlspecialchars(
            $imagePath
        );

    ?>"

    class="category-img"

    alt="<?php

        echo htmlspecialchars(
            $cat["category_name"]
        );

    ?>"

>


<?php

} else {

?>

<span class="no-image">
    No Image
</span>

<?php

}

?>


</td>


</tr>


<?php


    }


} else {


?>


<tr>


<td
    colspan="3"
    style="text-align:center;"
>


No Categories Found


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



<!-- =================================================
     JAVASCRIPT
================================================= -->

<script>


function openCategoryForm()
{

    document.getElementById(
        "categoryForm"
    ).style.display = "block";

}


function closeCategoryForm()
{

    document.getElementById(
        "categoryForm"
    ).style.display = "none";

}

</script>



<!-- =================================================
     ADMIN JS
================================================= -->

<script
    src="admin.js"
></script>


</body>


</html>


<?php

$conn->close();

?>