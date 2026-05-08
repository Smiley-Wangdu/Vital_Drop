document.addEventListener('DOMContentLoaded', () => {
    const tabButtons = document.querySelectorAll('.tab-button');
    const tabContents = document.querySelectorAll('.tab-content');

    tabButtons.forEach(button => {
        button.addEventListener('click', () => {
            // Remove active class from all buttons and contents
            tabButtons.forEach(btn => btn.classList.remove('active'));
            tabContents.forEach(content => content.classList.remove('active'));

            // Add active class to clicked button
            button.classList.add('active');

            // Add active class to corresponding content
            const targetId = button.getAttribute('data-target');
            document.getElementById(targetId).classList.add('active');
        });
    });
});

// contact.php's:

// TOGGLE FORM
function toggleJoinForm(id) {
    const form = document.getElementById("join-form-" + id);
    form.style.display = (form.style.display === "block") ? "none" : "block";
}

// CLOSE FORM
function closeJoinForm(id) {
    const form = document.getElementById("join-form-" + id);

    form.style.display = "none";

    const errorBox = form.querySelector(".error-msg");
    const successBox = form.querySelector(".success-box");

    if (errorBox) {
        errorBox.style.display = "none";
        errorBox.innerText = "";
    }

    if (successBox) {
        successBox.style.display = "none";
        successBox.innerHTML = "";
    }
}

// FORM SUBMIT HANDLER (FIXED SINGLE VERSION)
document.querySelectorAll(".join-form").forEach(form => {

    form.addEventListener("submit", function (e) {
        e.preventDefault();

        const id = this.dataset.id;

        const idInput = this.querySelector("input[name='campaign_id']");
        const campaignId = idInput ? idInput.value : "";

        const campaignName = this.querySelector("input[name='campaign_name']").value.trim();
        const location = this.querySelector("input[name='location']").value.trim();

        const errorBox = this.querySelector(".error-msg");
        const successBox = this.querySelector(".success-box");
        console.log("JOIN FORM DATA:", {
            campaignId,
            campaignName,
            location
        });
        errorBox.style.display = "none";
        successBox.style.display = "none";

        // VALIDATION
        if (campaignName === "" || location === "") {
            errorBox.style.display = "block";
            errorBox.innerText = "All fields are required.";
            return;
        }

        // SEND REQUEST
        fetch("../public/join_campaign_action.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/x-www-form-urlencoded"
            },
            body: new URLSearchParams({
                campaign_id: campaignId,
                campaign_name: campaignName,
                location: location
            })
        })
            .then(res => res.text())
            .then(response => {

                successBox.style.display = "block";

                /* SUCCESS */
                if (response === "success") {
                    successBox.innerHTML = `
                    <p style="color:#2ecc71;">You have successfully joined the campaign.</p>
                    <button type="button" class="submit-btn"
                        onclick="closeJoinForm(${id})">
                        OK
                    </button>
                `;
                }

                /* ALREADY JOINED */
                else if (response === "already_joined") {
                    successBox.innerHTML = `
                    <p style="color:#f1c40f;">You have already joined this campaign.</p>
                    <button type="button" class="submit-btn"
                        onclick="closeJoinForm(${id})">
                        OK
                    </button>
                `;
                }

                /* REQUIRED FIELDS */
                else if (response === "required") {
                    successBox.style.display = "none";
                    errorBox.style.display = "block";
                    errorBox.innerText = "All fields are required.";
                }

                /* ERROR */
                else {
                    successBox.style.display = "none";
                    errorBox.style.display = "block";
                    errorBox.innerText = "Something went wrong. Please try again.";
                }

            })
            .catch(() => {
                successBox.style.display = "none";
                errorBox.style.display = "block";
                errorBox.innerText = "Server error. Please try again.";
            });

    });

});
