import os

def clean_files():
    base = r"c:\xampp\htdocs\VitalDrop\VitalDrop"
    how_bg = os.path.join(base, "public", "howitworks_backup.php")
    cont_bg = os.path.join(base, "public", "contact_backup.php")

    with open(how_bg, 'r', encoding='utf-8') as f:
        how_lines = f.readlines()
    
    # Extract CSS
    css_lines = how_lines[7:431] # lines 8-431
    # Add light-mode CSS
    css_lines.append("""
/* --- LIGHT MODE OVERRIDES --- */
body.light-mode .how-it-works {
    background: linear-gradient(to bottom, #f9f9f9, #e0e0e0);
}
body.light-mode .how-it-works::before {
    background: radial-gradient(circle, rgba(200, 0, 0, 0.05) 0%, transparent 70%);
}
body.light-mode .section-title {
    color: #800000;
}
body.light-mode .section-title::after {
    background: #800000;
    box-shadow: 0 0 10px rgba(128, 0, 0, 0.5);
}
body.light-mode .step {
    background: rgba(255, 255, 255, 0.95);
    border: 1px solid rgba(128, 0, 0, 0.1);
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
}
body.light-mode .step:hover {
    box-shadow: 0 15px 40px rgba(128, 0, 0, 0.2);
    background: rgba(250, 250, 250, 0.95);
}
body.light-mode .step.active {
    background: rgba(128, 0, 0, 0.05);
    border-color: #800000;
    box-shadow: 0 0 30px rgba(128, 0, 0, 0.2);
}
body.light-mode .step-icon {
    border: 4px solid #800000;
    background: #ffffff;
}
body.light-mode .step h3 {
    color: #800000;
}
body.light-mode .step p {
    color: #333333;
}
body.light-mode .detail-panel {
    background: linear-gradient(135deg, rgba(255, 255, 255, 0.95), rgba(240, 240, 240, 0.98));
    border: 1px solid rgba(128, 0, 0, 0.2);
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1);
}
body.light-mode .detail-text h4 {
    color: #800000;
}
body.light-mode .detail-text h4::before {
    color: #a00000;
}
body.light-mode .detail-text .subtitle {
    color: #a00000;
}
body.light-mode .detail-text .description {
    color: #444444;
}
body.light-mode .feature-list li {
    color: #333333;
}
body.light-mode .feature-list li::before {
    color: #800000;
}
body.light-mode .close-detail {
    background: rgba(128, 0, 0, 0.1);
    border: 1px solid rgba(128, 0, 0, 0.3);
    color: #a00000;
}
body.light-mode .close-detail:hover {
    background: #800000;
    color: white;
}
body.light-mode .detail-panel::before {
    background: linear-gradient(to bottom, #800000, transparent);
}
body.light-mode .step-dot {
    background: rgba(128, 0, 0, 0.3);
}
body.light-mode .step-dot:hover, body.light-mode .step-dot.active {
    background: #800000;
    box-shadow: 0 0 10px rgba(128, 0, 0, 0.5);
}
""")
    with open(os.path.join(base, "css", "howitworks.css"), "w", encoding="utf-8") as f:
        f.writelines(css_lines)

    # Clean howitworks.php (Lines 435 to 613) Add id="howitworks"
    html_lines = how_lines[434:613]
    html_lines[0] = html_lines[0].replace('class="how-it-works"', 'id="howitworks" class="how-it-works"')
    with open(os.path.join(base, "public", "howitworks.php"), "w", encoding="utf-8") as f:
        f.writelines(html_lines)

    # Clean contact.php
    with open(cont_bg, 'r', encoding='utf-8') as f:
        cont_lines = f.readlines()
    
    # We want lines 19 to 199. And we change <main class="contact-main"> to <main id="contact" class="contact-main">
    cont_html = cont_lines[18:199]
    cont_html[0] = cont_html[0].replace('class="contact-main"', 'id="contact" class="contact-main"')
    with open(os.path.join(base, "public", "contact.php"), "w", encoding="utf-8") as f:
        f.writelines(cont_html)

if __name__ == "__main__":
    clean_files()
