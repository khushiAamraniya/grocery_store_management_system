// ===============================
// CART ARRAY
// ===============================

let cart = JSON.parse(localStorage.getItem("cart")) || [];


// ===============================
// ADD TO CART
// ===============================

function addToCart(id, name, price, image, category, description) {

    let existingProduct = cart.find(item => item.id == id);

    if (existingProduct) {

        existingProduct.quantity += 1;

    } else {

        cart.push({
            id: id,
            name: name,
            price: price,
            image: image,
            category: category,
            description: description,
            quantity: 1
        });

    }

    localStorage.setItem("cart", JSON.stringify(cart));

    updateCartCount();

    alert(name + " Added To Cart Successfully!");
}


// ===============================
// UPDATE CART COUNT
// ===============================

function updateCartCount() {

    let cart = JSON.parse(localStorage.getItem("cart")) || [];

    let total = 0;

    cart.forEach(function(item) {

        total += Number(item.quantity) || 0;

    });

    let badge = document.querySelector(".cart-count");

    if (badge) {
        badge.innerHTML = total;
    }
}


// ===============================
// PAGE LOAD
// ===============================

document.addEventListener("DOMContentLoaded", function() {

    updateCartCount();


    // ===============================
    // CONTACT FORM
    // ===============================

    let contactForm = document.getElementById("contactForm");

    // Form exists હોય ત્યારે જ code ચલાવવો
    if (contactForm) {

        contactForm.addEventListener("submit", function(e) {

            e.preventDefault();

            let name = document.getElementById("name").value.trim();
            let email = document.getElementById("email").value.trim();
            let subject = document.getElementById("subject").value.trim();
            let message = document.getElementById("message").value.trim();

            let valid = true;


            // Clear errors

            let nameError = document.getElementById("nameError");
            let emailError = document.getElementById("emailError");
            let subjectError = document.getElementById("subjectError");
            let messageError = document.getElementById("messageError");

            if (nameError) nameError.innerHTML = "";
            if (emailError) emailError.innerHTML = "";
            if (subjectError) subjectError.innerHTML = "";
            if (messageError) messageError.innerHTML = "";


            // ===============================
            // NAME VALIDATION
            // ===============================

            if (name === "") {

                if (nameError) {
                    nameError.innerHTML = "Name is required";
                }

                valid = false;
            }


            // ===============================
            // EMAIL VALIDATION
            // ===============================

            let emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

            if (email === "") {

                if (emailError) {
                    emailError.innerHTML = "Email is required";
                }

                valid = false;

            } else if (!emailPattern.test(email)) {

                if (emailError) {
                    emailError.innerHTML = "Enter valid email";
                }

                valid = false;
            }


            // ===============================
            // SUBJECT VALIDATION
            // ===============================

            if (subject === "") {

                if (subjectError) {
                    subjectError.innerHTML = "Subject is required";
                }

                valid = false;
            }


            // ===============================
            // MESSAGE VALIDATION
            // ===============================

            if (message === "") {

                if (messageError) {
                    messageError.innerHTML = "Message is required";
                }

                valid = false;

            } else if (message.length < 10) {

                if (messageError) {
                    messageError.innerHTML =
                        "Message minimum 10 characters";
                }

                valid = false;
            }


            // ===============================
            // SUCCESS
            // ===============================

            if (valid) {

                alert("Message Sent Successfully!");

                contactForm.reset();
            }

        });

    }

});


// ===============================
// ADD AND OPEN CART
// ===============================

function addAndOpen(name) {

    alert("✅ " + name + " Added To Cart Successfully!");

}


// ===============================
// CHECK LOGIN
// ===============================

function checkLogin() {

    let isLoggedIn = localStorage.getItem("isLoggedIn");

    if (isLoggedIn !== "true") {

        alert("Please login first!");

        window.location.href = "login.php";

        return false;
    }

    return true;
}

