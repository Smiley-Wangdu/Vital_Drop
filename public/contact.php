    <main id="contact" class="contact-main">
        <header class="contact-header">
            <h1 class="contact-title">CONTACT US <span class="contact-subtitle">connect with us, save lives together</span></h1>
            <p class="contact-desc">
                Whether you want to donate blood, request assistance, or join our life-saving campaigns, we're here to help you make a difference.
            </p>
        </header>

        <section class="contact-tabs-container">
            <div class="tabs-nav">
                <button class="tab-button active" data-target="find-donors">Find Donors</button>
                <button class="tab-button" data-target="request-blood">Request Blood</button>
                <button class="tab-button" data-target="join-campaigns">Join Campaigns</button>
                <button class="tab-button" data-target="general-inquiry">General Inquiry</button>
            </div>

            <div class="tabs-content-wrapper">
                <!-- FIND DONORS TAB -->
                <div id="find-donors" class="tab-content active">
                    <div class="tab-header">
                        <div class="icon-wrapper red">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/></svg>
                        </div>
                        <div>
                            <h2>Find Donors</h2>
                            <p>Search for blood donors in your area</p>
                        </div>
                    </div>

                    <form class="contact-form" action="find_donors_action.php" method="POST">
                        <div class="form-group">
                            <label>Full Name</label>
                            <input type="text" name="full_name" placeholder="Enter your full name" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Email Address</label>
                            <input type="email" name="email" placeholder="your.email@example.com" required>
                        </div>

                        <div class="form-group">
                            <label>Phone Number</label>
                            <input type="tel" name="phone" placeholder="9800000000" pattern="[0-9]{10}" maxlength="10" minlength="10" title="Please enter a valid 10-digit phone number" oninput="this.value = this.value.replace(/[^0-9]/g, '');" required>
                        </div>

                        <div class="form-group blood-type-group">
                            <label>Blood Type Needed</label>
                            <div class="blood-type-grid">
                                <label class="blood-radio"><input type="radio" name="blood_group" value="A+" required><span>A+</span></label>
                                <label class="blood-radio"><input type="radio" name="blood_group" value="A-"><span>A-</span></label>
                                <label class="blood-radio"><input type="radio" name="blood_group" value="B+"><span>B+</span></label>
                                <label class="blood-radio"><input type="radio" name="blood_group" value="B-"><span>B-</span></label>
                                <label class="blood-radio"><input type="radio" name="blood_group" value="O+"><span>O+</span></label>
                                <label class="blood-radio"><input type="radio" name="blood_group" value="O-"><span>O-</span></label>
                                <label class="blood-radio"><input type="radio" name="blood_group" value="AB+"><span>AB+</span></label>
                                <label class="blood-radio"><input type="radio" name="blood_group" value="AB-"><span>AB-</span></label>
                            </div>
                        </div>

                        <button type="submit" class="submit-btn">Search Donors</button>
                    </form>
                </div>

                <!-- REQUEST BLOOD TAB -->
                <div id="request-blood" class="tab-content">
                    <div class="tab-header">
                        <div class="icon-wrapper red">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
                        </div>
                        <div>
                            <h2>Request Blood</h2>
                            <p>Submit urgent blood requirement details</p>
                        </div>
                    </div>

                    <form class="contact-form" action="request_blood_action.php" method="POST">
                        <div class="form-group blood-type-group">
                            <label>Required Blood Group</label>
                            <div class="blood-type-grid">
                                <label class="blood-radio"><input type="radio" name="blood_group" value="A+" required><span>A+</span></label>
                                <label class="blood-radio"><input type="radio" name="blood_group" value="A-"><span>A-</span></label>
                                <label class="blood-radio"><input type="radio" name="blood_group" value="B+"><span>B+</span></label>
                                <label class="blood-radio"><input type="radio" name="blood_group" value="B-"><span>B-</span></label>
                                <label class="blood-radio"><input type="radio" name="blood_group" value="O+"><span>O+</span></label>
                                <label class="blood-radio"><input type="radio" name="blood_group" value="O-"><span>O-</span></label>
                                <label class="blood-radio"><input type="radio" name="blood_group" value="AB+"><span>AB+</span></label>
                                <label class="blood-radio"><input type="radio" name="blood_group" value="AB-"><span>AB-</span></label>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Hospital Name</label>
                            <input type="text" name="hospital_name" placeholder="Enter hospital name" required>
                        </div>

                        <div class="form-group">
                            <label>Location</label>
                            <input type="text" name="location" placeholder="City, State or Full Address" required>
                        </div>

                        <div class="form-group">
                            <label>Urgency Level</label>
                            <div class="urgency-grid">
                                <label class="urgency-radio">
                                    <input type="radio" name="urgency" value="Normal" checked>
                                    <span class="urgency-btn">Normal</span>
                                </label>
                                <label class="urgency-radio">
                                    <input type="radio" name="urgency" value="Emergency">
                                    <span class="urgency-btn outline">Emergency</span>
                                </label>
                            </div>
                        </div>

                        <div class="form-row-group" style="display: flex; gap: 1rem;">
                            <div class="form-group" style="flex: 2;">
                                <label>Contact Number</label>
                                <input type="tel" name="contact_number" placeholder="9800000000" pattern="[0-9]{10}" maxlength="10" minlength="10" title="Please enter a valid 10-digit phone number" oninput="this.value = this.value.replace(/[^0-9]/g, '');" required>
                            </div>
                            <div class="form-group" style="flex: 1;">
                                <label>Number of Units</label>
                                <input type="number" name="units_required" min="1" value="1" placeholder="1" required>
                            </div>
                        </div>

                        <button type="submit" class="submit-btn red-btn">Submit Blood Request</button>
                    </form>
                </div>

                <!-- JOIN CAMPAIGNS TAB -->
                <div id="join-campaigns" class="tab-content">
                    <div class="tab-header">
                        <h2>Join Campaigns</h2>
                    </div>
                    <p style="color: #bbb;">Campaign features coming soon.</p>
                </div>

                <!-- GENERAL INQUIRY TAB -->
                <div id="general-inquiry" class="tab-content">
                    <div class="tab-header">
                        <h2>General Inquiry</h2>
                    </div>
                    <form class="contact-form">
                        <div class="form-group">
                            <input type="text" placeholder="Full Name" required>
                        </div>
                        <div class="form-group">
                            <input type="email" placeholder="Email Address" required>
                        </div>
                        <div class="form-group">
                            <textarea placeholder="Your Message" rows="5" required style="width: 100%; border-radius: 8px; border: 1px solid #333; background: transparent; color: #fff; padding: 1rem;"></textarea>
                        </div>
                        <button type="submit" class="submit-btn">Send Message</button>
                    </form>
                </div>
            </div>
        </section>

        <footer class="contact-footer">
            &copy; 2026 Vital Drop. All rights reserved.
        </footer>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var findDonorsForm = document.querySelector('form[action="find_donors_action.php"]');
            var isLoggedIn = <?php echo isset($_SESSION['user_id']) ? 'true' : 'false'; ?>;
            
            if (findDonorsForm) {
                findDonorsForm.addEventListener('submit', function(e) {
                    if (!isLoggedIn) {
                        e.preventDefault();
                        alert("Please Login first");
                        window.location.href = "../auth/login.php";
                    }
                });
            }
        });
    </script>
    <script src="../js/contact.js"></script>
