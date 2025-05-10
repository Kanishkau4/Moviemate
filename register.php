<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <title>MovieMate - Registration</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        /* Header Styles */
        header {
            padding: 1rem 2rem;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
            width: 100%; /* Ensure the header takes up the full width */
        }

        .logo {
            font-size: 1.5rem;
            font-style: italic;
            font-weight: bold;
        }

        nav {
            flex-grow: 1; /* Ensure nav takes the remaining space */
            display: flex;
            justify-content: center; /* Center the navigation links */
        }

        nav ul {
            display: flex;
            list-style: none;
            gap: 2rem;
        }

        nav a {
            text-decoration: none;
            color: #333;
            position: relative;
            padding-bottom: 2px;
            transition: color 0.3s ease;
        }

        /* New hover effect styles */
        nav a::after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            bottom: 0;
            left: 0;
            background-color: #333;
            transition: width 0.3s ease;
        }

        nav a:hover::after {
            width: 100%;
        }

        nav a:hover {
            color: #000;
        }


        /* New Registration Form Styles */
        .registration-container {
            max-width: 800px;
            margin: 2rem auto;
            padding: 2rem;
            background: #fff;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }

        .profile-upload {
            text-align: center;
            margin-bottom: 2rem;
        }

        .profile-preview {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            border: 3px solid #ddd;
            margin: 0 auto 1rem;
            overflow: hidden;
            position: relative;
            background: #f5f5f5;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .profile-preview img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: none;
        }

        .profile-preview .placeholder {
            font-size: 4rem;
            color: #ccc;
        }

        .upload-btn {
            display: inline-block;
            padding: 0.5rem 1.5rem;
            background: #000;
            color: white;
            border-radius: 4px;
            cursor: pointer;
            transition: background 0.3s;
        }

        .upload-btn:hover {
            background: #333;
        }

        #profile-input {
            display: none;
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group.full-width {
            grid-column: 1 / -1;
        }

        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
        }

        input[type="text"],
        input[type="email"],
        input[type="password"],
        input[type="tel"],
        textarea {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            transition: border-color 0.3s;
        }

        input:focus,
        textarea:focus {
            outline: none;
            border-color: #000;
        }

        textarea {
            height: 100px;
            resize: vertical;
        }

        .required {
            color: red;
        }

        /* Popup Messages */
        .message-popup {
            display: none;
            position: fixed;
            top: 20px;
            right: -300px; /* Start off-screen */
            padding: 15px 25px;
            border-radius: 5px;
            color: white;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
            z-index: 1000;
            min-width: 200px;
        }

        .success-message {
            background: linear-gradient(45deg, #4CAF50, #8BC34A);
        }

        .error-message {
            background: linear-gradient(45deg, #f44336, #ef5350);
        }

        /* Animation keyframes */
        @keyframes slideIn {
            from {
                right: -300px;
                opacity: 0;
            }
            to {
                right: 20px;
                opacity: 1;
            }
        }

        @keyframes slideOut {
            from {
                right: 20px;
                opacity: 1;
            }
            to {
                right: -300px;
                opacity: 0;
            }
        }

        .message-popup.show {
            display: block;
            animation: slideIn 0.5s ease-out forwards;
        }

        .message-popup.hide {
            animation: slideOut 0.5s ease-out forwards;
        }

        .submit-btn {
            background: #000;
            color: white;
            padding: 1rem 2rem;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            width: 100%;
            font-size: 1.1rem;
            transition: background 0.3s;
        }

        .submit-btn:hover {
            background: #333;
        }

        .password-requirements {
            font-size: 0.85rem;
            color: #666;
            margin-top: 0.5rem;
        }

        .password-wrapper {
            position: relative;
        }

        .toggle-password {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #333;
        }

        .toggle-password:hover {
            color: #000;
        }

        button {
            background: #000;
            color: white;
            padding: 0.75rem 2rem;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .success-message {
            display: none;
            position: fixed;
            top: 20px;
            right: 20px;
            background: linear-gradient(45deg, #4CAF50, #8BC34A);
            color: white;
            padding: 15px 25px;
            border-radius: 5px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
            z-index: 1000;
            animation: slideIn 0.5s forwards, slideOut 0.5s 3.5s forwards;
        }

        /* Footer Styles - Same as before */
        footer {
            background: #f9f9f9;
            padding: 4rem 2rem 2rem;
            margin-top: 4rem;
        }

        .footer-content {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 2rem;
        }

        .footer-section h3 {
            margin-bottom: 1rem;
        }

        .footer-section ul {
            list-style: none;
        }

        .footer-section li {
            margin-bottom: 0.5rem;
        }

        .footer-section a {
            text-decoration: none;
            color: #333;
        }

        .newsletter input[type="email"] {
            width: 100%;
            margin-bottom: 1rem;
        }

        .footer-bottom {
            border-top: 1px solid #333;
            padding-top: 2rem;
            text-align: center;
            color: #999;
        }


        @media (max-width: 768px) {
            .category-grid {
                grid-template-columns: 1fr;
            }

            .footer-grid {
                grid-template-columns: 1fr 1fr;
            }
        }

        /* Responsive Styles */
        @media (max-width: 768px) {
            .form-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div id="successPopup" class="message-popup success-message"></div>
    <div id="errorPopup" class="message-popup error-message"></div>
    <!-- Header Section -->
    <header>
        <div class="logo">MovieMate</div>
        <nav>
            <ul>
                <li><a href="#">Movie Tickets</a></li>
                <li><a href="#">Film Merchandise</a></li>
                <li><a href="#">Food and Beverage</a></li>
                <li><a href="#">Exclusive Offers</a></li>
            </ul>
        </nav>
    </header>

    <!-- Registration Form Section -->
    <div class="registration-container">
        <form action="process-registration.php" method="POST" enctype="multipart/form-data">
            <div class="profile-upload">
                <div class="profile-preview">
                    <span class="placeholder">👤</span>
                    <img id="preview-image" src="#" alt="Profile preview">
                </div>
                <input type="file" id="profile-input" name="profile_picture" accept="image/*">
                <label for="profile-input" class="upload-btn">Upload Profile Picture</label>
            </div>

            <div class="form-grid">
                <div class="form-group">
                    <label>Full Name <span class="required">*</span></label>
                    <input type="text" name="name" required>
                </div>

                <div class="form-group">
                    <label>Username (Email) <span class="required">*</span></label>
                    <input type="email" name="email" required>
                </div>

                <div class="form-group">
                    <label>Contact Number <span class="required">*</span></label>
                    <input type="tel" name="contact" pattern="[0-9]+" required>
                </div>

                <div class="form-group">
                    <label>Password <span class="required">*</span></label>
                    <div class="password-wrapper">
                        <input type="password" name="password" id="password" required>
                        <span class="toggle-password" onclick="togglePassword('password')">
                            <i class="fas fa-eye"></i>
                        </span>
                    </div>
                </div>

                <div class="form-group">
                    <label>Confirm Password <span class="required">*</span></label>
                    <div class="password-wrapper">
                        <input type="password" name="confirm_password" id="confirm_password" required>
                        <span class="toggle-password" onclick="togglePassword('confirm_password')">
                            <i class="fas fa-eye"></i>
                        </span>
                    </div>
                </div>

                <div class="form-group full-width">
                    <label>Address <span class="required">*</span></label>
                    <textarea name="address" required></textarea>
                </div>
            </div>

            <button type="submit" class="submit-btn">Create Account</button>
        </form>
    </div>

    <!-- Footer Section -->
    <footer>
        <div class="footer-content">
             <div class="footer-section">
                <h3>Explore</h3>
                <ul>
                    <li><a href="#">Latest Releases</a></li>
                    <li><a href="#">Classic Favorites</a></li>
                    <li><a href="#">Movie Tickets</a></li>
                </ul>
            </div>
            <div class="footer-section">
                <h3>Shop</h3>
                <ul>
                    <li><a href="#">Film Merchandise</a></li>
                    <li><a href="#">Food and Beverage</a></li>
                </ul>
            </div>
            <div class="footer-section">
                <h3>Offers</h3>
                <ul>
                    <li><a href="#">Exclusive Offers</a></li>
                </ul>
            </div>
            <div class="footer-section newsletter">
                <h3>Stay Updated</h3>
                <p>Subscribe to our newsletter for the latest movie releases and exclusive offers.</p>
                <form action="subscribe.php" method="POST" id="newsletter-form">
                    <input type="email" placeholder="Enter your email" required>
                    <button type="submit">Subscribe</button>
                </form>
            </div>
        </div>
        </div>
        <div class="footer-bottom">
            <p>© 2025 MovieMate. All rights reserved.</p>

        </div>
        </div>
    </footer>

    <script>
    // Preview uploaded profile picture
    document.getElementById('profile-input').addEventListener('change', function(e) {
        const file = e.target.files[0];
        const preview = document.getElementById('preview-image');
        const placeholder = document.querySelector('.placeholder');
        
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.src = e.target.result;
                preview.style.display = 'block';
                placeholder.style.display = 'none';
            }
            reader.readAsDataURL(file);
        }
    });

    // Form submission with popup messages
    document.querySelector('form').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const submitBtn = this.querySelector('.submit-btn');
        submitBtn.disabled = true;
        submitBtn.textContent = 'Processing...';

        fetch('process-registration.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            showPopup(data.success ? 'success' : 'error', data.message);
            
            if (data.success && data.redirect) {
                setTimeout(() => {
                    window.location.href = data.redirect;
                }, 2000);
            }
        })
        .catch(error => {
            showPopup('error', 'An error occurred: ' + error.message);
        })
        .finally(() => {
            submitBtn.disabled = false;
            submitBtn.textContent = 'Create Account';
        });
    });

    // Function to show popup messages
    function showPopup(type, message) {
        const popup = document.getElementById(type === 'success' ? 'successPopup' : 'errorPopup');
        popup.textContent = message;
        
        // Show popup
        popup.classList.remove('hide');
        popup.classList.add('show');
        
        // Hide popup after 3 seconds
        setTimeout(() => {
            popup.classList.remove('show');
            popup.classList.add('hide');
        }, 3000);
    }

    // Newsletter form submission animation (unchanged)
    document.getElementById('newsletter-form').addEventListener('submit', function(e) {
        e.preventDefault();
        const emailInput = this.querySelector('input[type="email"]');
        const submitBtn = this.querySelector('button');
        
        emailInput.disabled = true;
        submitBtn.disabled = true;
        
        submitBtn.innerHTML = '✓ Subscribed!';
        submitBtn.style.background = 'linear-gradient(90deg, #4CAF50, #8BC34A)';
        
        setTimeout(() => {
            this.reset();
            submitBtn.innerHTML = 'Subscribe';
            submitBtn.style.background = '#000';
            emailInput.disabled = false;
            submitBtn.disabled = false;
        }, 3000);
    });

    function togglePassword(fieldId) {
    const passwordField = document.getElementById(fieldId);
    const toggleIcon = passwordField.nextElementSibling.querySelector('i');

    if (passwordField.type === "password") {
        passwordField.type = "text";
        toggleIcon.classList.remove('fa-eye');
        toggleIcon.classList.add('fa-eye-slash');
    } else {
        passwordField.type = "password";
        toggleIcon.classList.remove('fa-eye-slash');
        toggleIcon.classList.add('fa-eye');
    }
}
</script>
</body>
</html>