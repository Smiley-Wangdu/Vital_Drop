    <section id="howitworks" class="how-it-works">
        <h2 class="section-title">HOW DOES IT WORK?</h2>
        
        <div class="steps">
            <div class="step" data-step="0" onclick="openDetail(0)">
                <img src="../images/signup.png" alt="Sign up" class="step-icon">
                <h3>Sign-up</h3>
                <p>Join campaigns and make a change</p>
            </div>
            
            <div class="step" data-step="1" onclick="openDetail(1)">
                <img src="../images/donor.png" alt="Find donors" class="step-icon">
                <h3>Find donors</h3>
                <p>Find donors near you</p>
            </div>
            
            <div class="step" data-step="2" onclick="openDetail(2)">
                <img src="../images/join.png" alt="Join campaigns" class="step-icon">
                <h3>Join campaigns</h3>
                <p>Join campaigns and make a change</p>
            </div>
            
            <div class="step" data-step="3" onclick="openDetail(3)">
                <img src="../images/request.png" alt="Request blood" class="step-icon">
                <h3>Request blood</h3>
                <p>Request when in need</p>
            </div>
        </div>

        <!-- Detail Panel -->
        <div class="detail-container">
            <div class="detail-panel" id="detailPanel">
                <button class="close-detail" onclick="closeDetail()">×</button>
                <div class="detail-content" id="detailContent">
                    <!-- Content injected via JS -->
                </div>
                <div class="step-indicators" id="stepIndicators">
                    <!-- Indicators injected via JS -->
                </div>
            </div>
        </div>
    </section>

    <script>
        // Detailed content for each step
        const stepDetails = [
            {
                title: "Sign-Up Process",
                subtitle: "Start your journey to save lives",
                image: "../images/signup.png",
                description: "Creating your account is the first step towards making a significant impact in your community. Our streamlined registration process takes less than 2 minutes and connects you to a network of lifesavers.",
                features: [
                    "Quick 2-minute registration with email verification",
                    "Complete your donor profile with blood type and health history",
                    "Upload medical certificates and identification securely",
                    "Set your availability and preferred donation centers",
                    "Receive instant notifications for emergencies in your area"
                ]
            },
            {
                title: "Find Blood Donors",
                subtitle: "Locate compatible donors nearby",
                image: "../images/donor.png",
                description: "Our advanced geolocation system helps you find verified blood donors within your vicinity. Whether you're a hospital administrator or an individual in need, access our real-time database of available donors instantly.",
                features: [
                    "Real-time map showing available donors within 5km radius",
                    "Filter by blood type, Rh factor, and donation history",
                    "Direct messaging system with privacy protection",
                    "View donor verification badges and health status",
                    "Emergency SOS feature for urgent requirements"
                ]
            },
            {
                title: "Join Campaigns",
                subtitle: "Be part of organized donation drives",
                image: "../images/join.png",
                description: "Participate in community blood drives and corporate campaigns. These organized events ensure a steady supply of blood for hospitals while creating awareness about the importance of regular donation.",
                features: [
                    "Browse upcoming campaigns in your city or workplace",
                    "Register for donation slots with one click",
                    "Invite friends and family via social media integration",
                    "Track campaign progress and impact statistics",
                    "Earn badges and certificates for participation"
                ]
            },
            {
                title: "Request Blood",
                subtitle: "Emergency blood request system",
                image: "../images/request.png",
                description: "When emergencies strike, every second counts. Our urgent request system immediately notifies compatible donors in your area and provides you with a list of potential donors ready to help.",
                features: [
                    "Emergency request broadcasts to all matching donors",
                    "AI-powered matching with 99.9% blood compatibility",
                    "Track request status in real-time dashboard",
                    "Connect with blood banks and hospitals directly",
                    "24/7 support hotline for critical situations"
                ]
            }
        ];

        let currentStep = -1;

        function openDetail(index) {
            const panel = document.getElementById('detailPanel');
            const content = document.getElementById('detailContent');
            const indicators = document.getElementById('stepIndicators');
            const steps = document.querySelectorAll('.step');
            
            // Remove active class from all steps
            steps.forEach(step => step.classList.remove('active'));
            
            // Add active class to clicked step
            steps[index].classList.add('active');
            
            // Update current step
            currentStep = index;
            const data = stepDetails[index];
            
            // Generate content HTML
            content.innerHTML = `
                <img src="${data.image}" alt="${data.title}" class="detail-image">
                <div class="detail-text">
                    <h4>${data.title}</h4>
                    <div class="subtitle">${data.subtitle}</div>
                    <div class="description">${data.description}</div>
                    <ul class="feature-list">
                        ${data.features.map(feature => `<li>${feature}</li>`).join('')}
                    </ul>
                </div>
            `;
            
            // Generate indicators
            indicators.innerHTML = stepDetails.map((_, i) => 
                `<div class="step-dot ${i === index ? 'active' : ''}" onclick="openDetail(${i}); event.stopPropagation();"></div>`
            ).join('');
            
            // Show panel
            panel.classList.add('active');
            
            // Smooth scroll to panel on mobile
            if (window.innerWidth <= 768) {
                panel.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            }
        }

        function closeDetail() {
            const panel = document.getElementById('detailPanel');
            const steps = document.querySelectorAll('.step');
            
            panel.classList.remove('active');
            steps.forEach(step => step.classList.remove('active'));
            currentStep = -1;
        }

        // Close panel when clicking outside
        document.addEventListener('click', function(e) {
            const panel = document.getElementById('detailPanel');
            const steps = document.querySelector('.steps');
            const isClickInsideSteps = steps.contains(e.target);
            const isClickInsidePanel = panel.contains(e.target);
            
            if (!isClickInsideSteps && !isClickInsidePanel && currentStep !== -1) {
                closeDetail();
            }
        });

        // Keyboard navigation
        document.addEventListener('keydown', function(e) {
            if (currentStep === -1) return;
            
            if (e.key === 'Escape') {
                closeDetail();
            } else if (e.key === 'ArrowRight' && currentStep < 3) {
                openDetail(currentStep + 1);
            } else if (e.key === 'ArrowLeft' && currentStep > 0) {
                openDetail(currentStep - 1);
            }
        });
    </script>
