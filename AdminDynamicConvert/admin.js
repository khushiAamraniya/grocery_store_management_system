// =====================================================
// USER FORM
// =====================================================

function openForm() {

    const form = document.getElementById("userForm");

    if (form) {
        form.style.display = "block";
    }

}


function closeForm() {

    const form = document.getElementById("userForm");

    if (form) {
        form.style.display = "none";
    }

}



// =====================================================
// CATEGORY FORM
// =====================================================

function openCategoryForm() {

    const form =
        document.getElementById("categoryForm");

    if (form) {

        form.style.display = "block";

    }

}


function closeCategoryForm() {

    const form =
        document.getElementById("categoryForm");

    if (form) {

        form.style.display = "none";

    }

}



// =====================================================
// PRODUCT FORM
// =====================================================

function openProductForm() {

    const form =
        document.getElementById("productForm");

    if (form) {

        form.style.display = "block";

    }

}


function closeProductForm() {

    const form =
        document.getElementById("productForm");

    if (form) {

        form.style.display = "none";

    }

}



// =====================================================
// CATEGORY DELETE
// =====================================================

document.addEventListener(
    "click",
    function(event) {

        const button =
            event.target.closest(".delete-btn");

        if (!button) {
            return;
        }


        const row =
            button.closest("tr");

        if (!row) {
            return;
        }


        const confirmDelete =
            confirm(
                "Are you sure you want to delete this category?"
            );


        if (confirmDelete) {

            row.remove();

            alert(
                "Category Deleted Successfully!"
            );

        }

    }
);



// =====================================================
// CATEGORY EDIT
// =====================================================

document.addEventListener(
    "click",
    function(event) {

        const button =
            event.target.closest(".edit-btn");

        if (!button) {
            return;
        }


        const row =
            button.closest("tr");

        if (!row) {
            return;
        }


        const name =
            row.children[1].innerText.trim();


        const newName =
            prompt(
                "Enter Category Name:",
                name
            );


        if (newName === null) {
            return;
        }


        if (newName.trim() === "") {

            alert(
                "Category Name Cannot Be Empty!"
            );

            return;

        }


        row.children[1].innerText =
            newName.trim();


        alert(
            "Category Updated Successfully!"
        );

    }
);



// =====================================================
// USER DELETE
// =====================================================

document.addEventListener(
    "click",
    function(event) {

        const button =
            event.target.closest(".user-delete-btn");

        if (!button) {
            return;
        }


        const row =
            button.closest("tr");


        if (!row) {
            return;
        }


        if (
            confirm(
                "Are you sure you want to delete this user?"
            )
        ) {

            row.remove();

            alert(
                "User Deleted Successfully!"
            );

        }

    }
);



// =====================================================
// PRODUCT DELETE
// =====================================================

function deleteProduct(id) {

    const rows =
        document.querySelectorAll("tbody tr");


    let row = null;


    rows.forEach(function(tr) {

        if (
            tr.children[0] &&
            tr.children[0].innerText.trim() == id
        ) {

            row = tr;

        }

    });


    if (!row) {

        alert(
            "Product Not Found!"
        );

        return;

    }


    const productName =
        row.children[2]
        ? row.children[2].innerText.trim()
        : "Product";


    if (
        confirm(
            "Are you sure you want to delete " +
            productName +
            "?"
        )
    ) {

        row.remove();

        alert(
            "Product Deleted Successfully!"
        );

    }

}



// =====================================================
// PRODUCT EDIT
// =====================================================

function editProduct(id) {

    const rows =
        document.querySelectorAll("tbody tr");


    let row = null;


    rows.forEach(function(tr) {

        if (
            tr.children[0] &&
            tr.children[0].innerText.trim() == id
        ) {

            row = tr;

        }

    });


    if (!row) {

        alert(
            "Product Not Found!"
        );

        return;

    }


    const name =
        row.children[2].innerText.trim();


    const newName =
        prompt(
            "Enter Product Name:",
            name
        );


    if (newName === null) {
        return;
    }


    if (newName.trim() === "") {

        alert(
            "Product Name Cannot Be Empty!"
        );

        return;

    }


    row.children[2].innerText =
        newName.trim();


    alert(
        "Product Updated Successfully!"
    );

}



// =====================================================
// ACTIVE / INACTIVE
// =====================================================

document.addEventListener(
    "click",
    function(event) {

        const status =
            event.target.closest(".status");


        if (!status) {
            return;
        }


        if (
            status.innerText.trim() ===
            "Active"
        ) {

            status.innerText =
                "Inactive";


            status.classList.remove(
                "active"
            );


            status.classList.add(
                "inactive"
            );

        }

        else {

            status.innerText =
                "Active";


            status.classList.remove(
                "inactive"
            );


            status.classList.add(
                "active"
            );

        }

    }
);



// =====================================================
// ORDER DELETE
// =====================================================

function deleteOrder(button) {

    const confirmDelete =
        confirm(
            "Are you sure you want to delete this order?"
        );


    if (confirmDelete) {

        const row =
            button.closest("tr");


        if (row) {

            row.remove();

        }


        alert(
            "Order Deleted Successfully!"
        );

    }

}



// =====================================================
// ORDER MODAL
// =====================================================

function viewOrder(orderId) {

    const modal =
        document.getElementById(
            "orderModal"
        );


    const modalBody =
        document.getElementById(
            "modalBody"
        );


    if (!modal || !modalBody) {
        return;
    }


    modalBody.innerHTML = `

        <div class="order-detail">

            <div class="detail-box">
                <span>Order ID</span>
                <strong>#${orderId}</strong>
            </div>

            <div class="detail-box">
                <span>Customer</span>
                <strong>Khushi</strong>
            </div>

            <div class="detail-box">
                <span>Total Items</span>
                <strong>5 Items</strong>
            </div>

            <div class="detail-box">
                <span>Payment Method</span>
                <strong>Cash / Online</strong>
            </div>

            <div class="detail-box">
                <span>Total Amount</span>
                <strong>₹850</strong>
            </div>

            <div class="detail-box">
                <span>Order Status</span>
                <strong>Delivered</strong>
            </div>

        </div>

    `;


    modal.classList.add("show");

}



// =====================================================
// CLOSE MODAL
// =====================================================

function closeModal() {

    const modal =
        document.getElementById(
            "orderModal"
        );


    if (modal) {

        modal.classList.remove(
            "show"
        );

    }

}



// =====================================================
// ORDER STATUS
// =====================================================

document.addEventListener(
    "change",
    function(event) {

        if (
            !event.target.classList.contains(
                "status-select"
            )
        ) {

            return;

        }


        const select =
            event.target;


        const status =
            select.value;


        select.className =
            "status-select";


        if (
            status === "Delivered"
        ) {

            select.classList.add(
                "delivered-status"
            );

        }

        else if (
            status === "Pending"
        ) {

            select.classList.add(
                "pending-status"
            );

        }

        else if (
            status === "Processing"
        ) {

            select.classList.add(
                "processing-status"
            );

        }

        else if (
            status === "Shipped"
        ) {

            select.classList.add(
                "shipped-status"
            );

        }

        else if (
            status === "Cancelled"
        ) {

            select.classList.add(
                "cancelled-status"
            );

        }

    }
);



// =====================================================
// SETTINGS
// =====================================================

document.addEventListener(
    "DOMContentLoaded",
    function() {

        const buttons =
            document.querySelectorAll(
                ".setting-card button"
            );


        if (buttons[0]) {

            buttons[0].addEventListener(
                "click",
                function() {

                    alert(
                        "Profile Updated Successfully"
                    );

                }
            );

        }


        if (buttons[1]) {

            buttons[1].addEventListener(
                "click",
                function() {

                    alert(
                        "Password Changed Successfully"
                    );

                }
            );

        }


        if (buttons[2]) {

            buttons[2].addEventListener(
                "click",
                function() {

                    alert(
                        "Store Information Saved Successfully"
                    );

                }
            );

        }

    }
);