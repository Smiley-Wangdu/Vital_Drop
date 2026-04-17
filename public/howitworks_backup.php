<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>How It Works - Interactive</title>
    <style>
        /* Reset */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background-color: #0d0d0d;
        }

        /* HOW IT WORKS SECTION - DARK MODE */
        .how-it-works {
            text-align: center;
            padding: 60px 60px 100px 60px;
            background: linear-gradient(to bottom, #1a1a1a, #0d0d0d);
            position: relative;
            overflow: hidden;
        }

        /* Background glow effect */
        .how-it-works::before {
            content: "";
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255, 51, 51, 0.03) 0%, transparent 70%);
            pointer-events: none;
        }

        /* Section Title */
        .section-title {
            color: #ff3333;
            font-size: 36px;
            margin-bottom: 100px;
            letter-spacing: 3px;
            font-weight: 700;
            font-family: Arial, sans-serif;
            position: relative;
            display: inline-block;
            text-transform: uppercase;
        }

        /* Title Underline Effect */
        .section-title::after {
            content: "";
            position: absolute;
            bottom: -15px;
            left: 50%;
            transform: translateX(-50%);
            width: 60px;
            height: 3px;
            background: #ff3333;
            border-radius: 2px;
            box-shadow: 0 0 10px rgba(255, 51, 51, 0.5);
        }

        /* Steps Container */
        .steps {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 30px;
            justify-items: center;
            align-items: center;
            max-width: 1200px;
            margin: 0 auto;
            position: relative;
        }

        /* Connecting Lines */
        .steps::before {
            content: "";
            position: absolute;
            top: 40%;
            left: 10%;
            right: 10%;
            height: 2px;
            background: linear-gradient(90deg, transparent, #ff3333, #ff6666, #ff3333, transparent);
            z-index: 0;
            opacity: 0.3;
        }

        /* Individual Step - Dark Card */
        .step {
            width: 220px;
            text-align: center;
            position: relative;
            z-index: 2;
            background: rgba(30, 30, 30, 0.95);
            border-radius: 20px;
            padding: 25px 15px;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.3);
            cursor: pointer;
            border: 1px solid rgba(255, 51, 51, 0.1);
        }

        /* Click hint */
        .step::after {
            content: "Click for details";
            position: absolute;
            bottom: -25px;
            left: 50%;
            transform: translateX(-50%);
            font-size: 11px;
            color: #ff6666;
            opacity: 0;
            transition: all 0.3s ease;
            white-space: nowrap;
            letter-spacing: 1px;
            text-transform: uppercase;
        }

        .step:hover::after {
            opacity: 0.7;
            bottom: -30px;
        }

        /* Hover Effect for Step Card */
        .step:hover {
            transform: translateY(-10px) scale(1.02);
            box-shadow: 0 15px 40px rgba(255, 51, 51, 0.2);
            background: rgba(40, 40, 40, 0.95);
            border-color: rgba(255, 51, 51, 0.3);
        }

        .step.active {
            background: rgba(255, 51, 51, 0.1);
            border-color: #ff3333;
            box-shadow: 0 0 30px rgba(255, 51, 51, 0.3);
        }

        /* ZIG-ZAG POSITIONING */
        .step:nth-child(1) { transform: translateY(-40px); }
        .step:nth-child(2) { transform: translateY(40px); }
        .step:nth-child(3) { transform: translateY(-40px); }
        .step:nth-child(4) { transform: translateY(40px); }

        /* Hover override for zig-zag */
        .step:nth-child(1):hover, .step:nth-child(3):hover { transform: translateY(-50px) scale(1.02); }
        .step:nth-child(2):hover, .step:nth-child(4):hover { transform: translateY(30px) scale(1.02); }
        
        /* Active state override */
        .step:nth-child(1).active, .step:nth-child(3).active { transform: translateY(-40px) scale(1.05); }
        .step:nth-child(2).active, .step:nth-child(4).active { transform: translateY(40px) scale(1.05); }

        /* Circle Images - Dark Mode */
        .step-icon {
            width: 130px;
            height: 130px;
            border: 4px solid #ff3333;
            border-radius: 50%;
            padding: 12px;
            background: #1a1a1a;
            object-fit: cover;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
            pointer-events: none; /* Let click pass through to parent */
        }

        /* Image Hover Effect */
        .step:hover .step-icon {
            transform: scale(1.08);
            border-color: #ff6666;
            box-shadow: 0 8px 25px rgba(255, 51, 51, 0.3);
        }

        .step.active .step-icon {
            border-color: #ff3333;
            box-shadow: 0 0 20px rgba(255, 51, 51, 0.5);
            animation: none;
        }

        /* Step Heading - Dark Mode */
        .step h3 {
            color: #ff5555;
            font-size: 20px;
            font-weight: 700;
            margin: 20px 0 10px 0;
            font-family: Arial, sans-serif;
            pointer-events: none;
        }

        /* Step Paragraph - Dark Mode */
        .step p {
            color: #cccccc;
            font-size: 14px;
            line-height: 1.5;
            font-family: Arial, sans-serif;
            margin: 0;
            pointer-events: none;
        }

        /* Pulse Animation - Dark Mode */
        @keyframes pulse {
            0% { box-shadow: 0 0 0 0 rgba(255, 51, 51, 0.4); }
            70% { box-shadow: 0 0 0 15px rgba(255, 51, 51, 0); }
            100% { box-shadow: 0 0 0 0 rgba(255, 51, 51, 0); }
        }

        .step-icon {
            animation: pulse 3s infinite;
        }

        .step:nth-child(2) .step-icon { animation-delay: 0.5s; }
        .step:nth-child(3) .step-icon { animation-delay: 1s; }
        .step:nth-child(4) .step-icon { animation-delay: 1.5s; }
        .step:hover .step-icon, .step.active .step-icon { animation: none; }

        /* DETAIL PANEL - The Information Box */
        .detail-container {
            max-width: 1200px;
            margin: 80px auto 0;
            position: relative;
            z-index: 10;
        }

        .detail-panel {
            background: linear-gradient(135deg, rgba(30, 30, 30, 0.95), rgba(20, 20, 20, 0.98));
            border: 1px solid rgba(255, 51, 51, 0.2);
            border-radius: 25px;
            padding: 0;
            max-height: 0;
            overflow: hidden;
            opacity: 0;
            transform: translateY(-20px);
            transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5), inset 0 1px 0 rgba(255, 255, 255, 0.05);
        }

        .detail-panel.active {
            max-height: 600px;
            opacity: 1;
            transform: translateY(0);
            padding: 40px;
        }

        .detail-content {
            display: grid;
            grid-template-columns: 200px 1fr;
            gap: 40px;
            align-items: start;
            text-align: left;
        }

        .detail-image {
            width: 200px;
            height: 200px;
            border-radius: 50%;
            border: 4px solid #ff3333;
            object-fit: cover;
            box-shadow: 0 10px 30px rgba(255, 51, 51, 0.3);
            animation: float 6s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }

        .detail-text h4 {
            color: #ff3333;
            font-size: 28px;
            margin-bottom: 15px;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .detail-text h4::before {
            content: "→";
            color: #ff6666;
            font-size: 24px;
        }

        .detail-text .subtitle {
            color: #ff8888;
            font-size: 16px;
            margin-bottom: 20px;
            font-style: italic;
        }

        .detail-text .description {
            color: #e0e0e0;
            font-size: 16px;
            line-height: 1.8;
            margin-bottom: 25px;
        }

        .feature-list {
            list-style: none;
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 12px;
        }

        .feature-list li {
            color: #ccc;
            font-size: 14px;
            padding-left: 25px;
            position: relative;
            line-height: 1.6;
        }

        .feature-list li::before {
            content: "✓";
            position: absolute;
            left: 0;
            color: #ff3333;
            font-weight: bold;
            font-size: 16px;
        }

        .close-detail {
            position: absolute;
            top: 20px;
            right: 20px;
            background: rgba(255, 51, 51, 0.1);
            border: 1px solid rgba(255, 51, 51, 0.3);
            color: #ff6666;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            cursor: pointer;
            font-size: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
        }

        .close-detail:hover {
            background: #ff3333;
            color: white;
            transform: rotate(90deg);
        }

        /* Connecting line from step to detail */
        .detail-panel::before {
            content: "";
            position: absolute;
            top: -40px;
            left: 50%;
            transform: translateX(-50%);
            width: 2px;
            height: 40px;
            background: linear-gradient(to bottom, #ff3333, transparent);
            opacity: 0.5;
        }

        /* Step indicator dots */
        .step-indicators {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-top: 30px;
        }

        .step-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: rgba(255, 51, 51, 0.3);
            cursor: pointer;
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }

        .step-dot:hover, .step-dot.active {
            background: #ff3333;
            transform: scale(1.2);
            box-shadow: 0 0 10px rgba(255, 51, 51, 0.5);
        }

        /* Responsive */
        @media (max-width: 1200px) {
            .steps { gap: 20px; }
            .step { width: 200px; }
            .step-icon { width: 110px; height: 110px; }
            .step::after { display: none; }
            .steps::before { display: none; }
        }

        @media (max-width: 992px) {
            .steps {
                grid-template-columns: repeat(2, 1fr);
                gap: 40px;
            }
            .step {
                width: 250px;
                margin: 0 auto;
                transform: translateY(0) !important;
            }
            .step:hover { transform: translateY(-10px) scale(1.02) !important; }
            .detail-content {
                grid-template-columns: 1fr;
                text-align: center;
            }
            .detail-image {
                margin: 0 auto;
                width: 150px;
                height: 150px;
            }
            .feature-list {
                grid-template-columns: 1fr;
                text-align: left;
                max-width: 400px;
                margin: 0 auto;
            }
        }

        @media (max-width: 768px) {
            .how-it-works { padding: 40px 20px 60px; }
            .section-title { font-size: 28px; margin-bottom: 50px; }
            .steps { grid-template-columns: 1fr; gap: 30px; }
            .step { width: 280px; }
            .detail-panel.active { padding: 30px 20px; }
            .detail-text h4 { font-size: 22px; }
        }
    </style>
</head>
<body>

    <section class="how-it-works">
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

</body>
</html>