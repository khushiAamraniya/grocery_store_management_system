<?php

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
   CONTACT DETAILS FROM ADMIN SETTINGS
   ========================================================= */

$sql = "SELECT contact_number, email, address
        FROM admin_settings
        WHERE id = 1
        LIMIT 1";

$result = $conn->query($sql);

$contact_number = "+91 9876543210";
$email = "info@shreenathgrocery.com";
$address = "Upleta, Gujarat";

if ($result && $result->num_rows > 0) {

    $row = $result->fetch_assoc();

    if (!empty($row['contact_number'])) {
        $contact_number = $row['contact_number'];
    }

    if (!empty($row['email'])) {
        $email = $row['email'];
    }

    if (!empty($row['address'])) {
        $address = $row['address'];
    }
}


/* =========================================================
   CONTACT FORM SUBMIT
   ========================================================= */

$message_status = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $name = trim($_POST['name']);
    $user_email = trim($_POST['email']);
    $subject = trim($_POST['subject']);
    $message = trim($_POST['message']);

    if (!empty($name) && !empty($user_email) && !empty($message)) {

        $stmt = $conn->prepare(
            "INSERT INTO contact_messages
            (name, email, subject, message)
            VALUES (?, ?, ?, ?)"
        );

        $stmt->bind_param(
            "ssss",
            $name,
            $user_email,
            $subject,
            $message
        );

        if ($stmt->execute()) {
            $message_status = "Message sent successfully!";
        } else {
            $message_status = "Message could not be sent.";
        }

        $stmt->close();

    } else {

        $message_status = "Please fill all required fields.";

    }
}

?>

<!-- =========================================================
     CONTACT
     ========================================================= -->

<section class="contact" id="contact">

    <div class="contact-info">

        <h2>Contact Us</h2>

        <p>
            <i class="fa-solid fa-location-dot"></i>
            <?php echo htmlspecialchars($address); ?>
        </p>

        <p>
            <i class="fa-solid fa-phone"></i>
            <?php echo htmlspecialchars($contact_number); ?>
        </p>

        <p>
            <i class="fa-solid fa-envelope"></i>
            <?php echo htmlspecialchars($email); ?>
        </p>

    </div>


    <div class="contact-form">

        <?php if (!empty($message_status)) { ?>

            <p class="contact-message">
                <?php echo htmlspecialchars($message_status); ?>
            </p>

        <?php } ?>


        <form method="POST">

            <input
                type="text"
                name="name"
                placeholder="Your Name"
                required
            >

            <input
                type="email"
                name="email"
                placeholder="Your Email"
                required
            >

            <input
                type="text"
                name="subject"
                placeholder="Subject"
            >

            <textarea
                name="message"
                rows="5"
                placeholder="Message"
                required
            ></textarea>

            <button type="submit">
                Send Message
            </button>

        </form>

    </div>

</section>

<script src="script.js"></script>