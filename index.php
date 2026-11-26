<?php
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

include("header.php");
include("body.php");
include("product.php");
include("fecture.php");
include("about.php");
include("choose.php");
include("conatct.php");
include("footer1.php");
?>