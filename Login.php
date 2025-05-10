<?php
session_start();
$servername = "localhost"; // Change if necessary
$username = "root"; // Your database username
$password = ""; // Your database password
$dbname = "moviemate"; // Your database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$error_message = ""; // Initialize error message variable

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Check for default admin credentials
    if ($username == 'Admin' && $password == '123') {
        // Set session variables for the default admin
        $_SESSION['user_id'] = 'admin';
        $_SESSION['user_type'] = 'admin';

        // Redirect to the admin dashboard
        header("Location: admin.php");
        exit();
    }

    // Check for default staff credentials
    if ($username == 'staff' && $password == '123') {
        // Set session variables for the staff user
        $_SESSION['user_id'] = 'staff';
        $_SESSION['user_type'] = 'staff';

        // Redirect to the staff dashboard
        header("Location: staff_scanner.php");
        exit();
    }

    // Prepare and execute the SQL statement for other users
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        // Verify the password
        if (password_verify($password, $user['password'])) {
            // Store user data in session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['profile_picture'] = $user['profile_picture'];

            // Redirect to customer page
            header("Location: customer.php");
            exit();
        } else {
            $error_message = "Invalid password.";
        }
    } else {
        $error_message = "No user found with that username.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MovieMate - Login</title>
    <style>
        /* Add the new design styles here */
        /* From Uiverse.io by ilkhoeri */
        .card {
            --p: 32px;
            --h-form: auto;
            --w-form: 380px;
            --input-px: 0.75rem;
            --input-py: 0.65rem;
            --submit-h: 38px;
            --blind-w: 64px;
            --space-y: 0.5rem;
            width: var(--w-form);
            height: var(--h-form);
            max-width: 100%;
            border-radius: 16px;
            background: white;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: space-evenly;
            flex-direction: column;
            overflow-y: auto;
            padding: var(--p);
            scrollbar-width: none;
            -webkit-overflow-scrolling: touch;
            -webkit-font-smoothing: antialiased;
            -webkit-user-select: none;
            user-select: none;
            font-family: "Trebuchet MS", "Lucida Sans Unicode", "Lucida Grande",
                "Lucida Sans", Arial, sans-serif;
        }

        .avatar {
            --sz-avatar: 166px;
            order: 0;
            width: var(--sz-avatar);
            min-width: var(--sz-avatar);
            max-width: var(--sz-avatar);
            height: var(--sz-avatar);
            min-height: var(--sz-avatar);
            max-height: var(--sz-avatar);
            border: 1px solid #707070;
            border-radius: 9999px;
            overflow: hidden;
            cursor: pointer;
            z-index: 2;
            perspective: 80px;
            position: relative;
            margin: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            --sz-svg: calc(var(--sz-avatar) - 10px);
        }
        .avatar svg {
            position: absolute;
            transition:
                transform 0.2s ease-in,
                opacity 0.1s;
            transform-origin: 50% 100%;
            height: var(--sz-svg);
            width: var(--sz-svg);
            pointer-events: none;
        }
        .avatar svg#monkey {
            z-index: 1;
        }
        .avatar svg#monkey-hands {
            z-index: 2;
            transform-style: preserve-3d;
            transform: translateY(calc(var(--sz-avatar) / 1.25)) rotateX(-21deg);
        }

        .avatar::before {
            content: "";
            border-radius: 45%;
            width: calc(var(--sz-svg) / 3.889);
            height: calc(var(--sz-svg) / 5.833);
            border: 0;
            border-bottom: calc(var(--sz-svg) * (4 / 100)) solid #3c302a;
            bottom: 20%;
            position: absolute;
            transition: all 0.2s ease;
            z-index: 3;
        }
        .blind-check:checked ~ .avatar::before {
            width: calc(var(--sz-svg) * (9 / 100));
            height: 0;
            border-radius: 50%;
            border-bottom: calc(var(--sz-svg) * (10 / 100)) solid #3c302a;
        }
        .avatar svg#monkey .monkey-eye-r,
        .avatar svg#monkey .monkey-eye-l {
            animation: blink 10s 1s infinite;
            transition: all 0.2s ease;
        }
        @keyframes blink {
            0%,
            2%,
            4%,
            26%,
            28%,
            71%,
            73%,
            100% {
                ry: 4.5;
                cy: 31.7;
            }
            1%,
            3%,
            27%,
            72% {
                ry: 0.5;
                cy: 30;
            }
        }
        .blind-check:checked ~ .avatar svg#monkey .monkey-eye-r,
        .blind-check:checked ~ .avatar svg#monkey .monkey-eye-l {
            ry: 0.5;
            cy: 30;
        }
        .blind-check:checked ~ .avatar svg#monkey-hands {
            transform: translate3d(0, 0, 0) rotateX(0deg);
        }
        .avatar svg#monkey,
        .avatar::before,
        .avatar svg#monkey .monkey-eye-nose,
        .avatar svg#monkey .monkey-eye-r,
        .avatar svg#monkey .monkey-eye-l {
            transition: all 0.2s ease;
        }
        .blind-check:checked ~ .form:focus-within ~ .avatar svg#monkey,
        .blind-check:checked ~ .form:focus-within ~ .avatar::before,
        .blind-check:checked ~ .form:focus-within ~ .avatar svg#monkey .monkey-eye-nose,
        .blind-check:checked ~ .form:focus-within ~ .avatar svg#monkey .monkey-eye-r,
        .blind-check:checked ~ .form:focus-within ~ .avatar svg#monkey .monkey-eye-l {
            animation: none;
        }
        .form:focus-within ~ .avatar svg#monkey {
            animation: slick 3s ease infinite 1s;
            --center: rotateY(0deg);
            --left: rotateY(-4deg);
            --right: rotateY(4deg);
        }
        .form:focus-within ~ .avatar::before,
        .form:focus-within ~ .avatar svg#monkey .monkey-eye-nose,
        .blind-check:not(:checked)
            ~ .form:focus-within
            ~ .avatar
            svg#monkey
            .monkey-eye-r,
        .blind-check:not(:checked)
            ~ .form:focus-within
            ~ .avatar
            svg#monkey
            .monkey-eye-l {
            ry: 3;
            cy: 35;
            animation: slick 3s ease infinite 1s;
            --center: translateX(0);
            --left: translateX(-0.5px);
            --right: translateX(0.5px);
        }
        @keyframes slick {
            0%,
            100% {
                transform: var(--center);
            }
            25% {
                transform: var(--left);
            }
            75% {
                transform: var(--right);
            }
        }

        .card label.blind_input {
            -webkit-user-select: none;
            user-select: none;
            cursor: pointer;
            z-index: 4;
            position: absolute;
            border: none;
            right: calc(var(--p) + (var(--input-px) / 2));
            bottom: calc(
                var(--p) + var(--submit-h) + var(--space-y) + (var(--input-py) / 1) + 3px
            );
            padding: 4px 0;
            width: var(--blind-w);
            border-radius: 4px;
            background-color: #fff;
            color: #4d4d4d;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        .card label.blind_input:before {
            content: "";
            position: absolute;
            left: calc((var(--input-px) / 2) * -1);
            top: 0;
            height: 100%;
            width: 1px;
            background: #8f8f8f;
        }
        .card label.blind_input:hover {
            color: #262626;
            background-color: #f2f2f2;
        }
        .blind-check ~ label.blind_input span.show,
        .blind-check:checked ~ label.blind_input span.hide {
            display: none;
        }
        .blind-check ~ label.blind_input span.hide,
        .blind-check:checked ~ label.blind_input span.show {
            display: block;
        }

        .form {
            order: 1;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: space-evenly;
            flex-direction: column;
            width: 100%;
        }

        .form .title {
            width: 100%;
            font-size: 1.5rem;
            font-weight: 600;
            margin-top: 0;
            margin-bottom: 1rem;
            padding-top: 0;
            padding-bottom: 1rem;
            color: rgba(0, 0, 0, 0.7);
            border-bottom: 2px solid rgba(0, 0, 0, 0.3);
        }

        .form .label_input {
            white-space: nowrap;
            font-size: 1rem;
            margin-top: calc(var(--space-y) / 2);
            color: rgba(0, 0, 0, 0.9);
            font-weight: 600;
            display: inline;
            text-align: left;
            margin-right: auto;
            position: relative;
            z-index: 99;
            -webkit-user-select: none;
            user-select: none;
        }

        .form .input {
            resize: vertical;
            background: white;
            border: 1px solid #8f8f8f;
            border-radius: 6px;
            outline: none;
            padding: var(--input-py) var(--input-px);
            font-size: 18px;
            width: 100%;
            color: #000000b3;
            margin: var(--space-y) 0;
            transition: all 0.25s ease;
        }
        .form .input#password-input {
            padding-right: calc(var(--blind-w) + var(--input-px) + 4px);
        }
        .form .input:focus {
            border: 1px solid #0969da;
            outline: 0;
            box-shadow: 0 0 0 2px #0969da;
        }
        .form .frg_pss {
            width: 100%;
            display: inline-flex;
            align-items: center;
        }
        .form .frg_pss a {
            background-color: transparent;
            cursor: pointer;
            text-decoration: underline;
            transition: color 0.25s ease;
            color: #000000b3;
            font-weight: 500;
            float: right;
        }
        .form .frg_pss a:hover {
            color: #000;
        }

        .form .submit {
            height: var(--submit-h);
            width: 100%;
            outline: none;
            cursor: pointer;
            background-color: #000; /* Set button color to black */
            background-image: linear-gradient(
                -180deg,
                rgba(255, 255, 255, 0.09) 0%,
                rgba(17, 17, 17, 0.04) 100%
            );
            border: 1px solid rgba(22, 22, 22, 0.2);
            font-weight: 500;
            letter-spacing: 0.25px;
            color: #fff; /* Set text color to white */
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            font-size: 1rem;
            text-align: center;
            text-decoration: none;
            padding: 0.5rem 1rem;
            border-radius: 0.25rem;
            -webkit-appearance: button;
            appearance: button;
            margin: var(--space-y) 0 0;
        }

        .form .submit:hover {
            background-image: linear-gradient(
                -180deg,
                rgba(255, 255, 255, 0.18) 0%,
                rgba(17, 17, 17, 0.08) 100%
            );
            border: 1px solid rgba(22, 22, 22, 0.2);
            color: #111;
        }

        .blind-check:checked ~ .form .input[type="text"] {
            -webkit-text-security: disc;
        }

        /* Existing styles */
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
            width: 100%;
        }

        .logo {
            font-size: 1.5rem;
            font-style: italic;
            font-weight: bold;
        }

        nav {
            flex-grow: 1;
            display: flex;
            justify-content: center;
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

        /* Main Content Styles */
        main {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 1rem;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 4rem;
        }

        .form-section {
            flex: 1;
        }

        h2 {
            margin-bottom: 1.5rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        label {
            display: block;
            margin-bottom: 0.5rem;
        }

        input[type="text"],
        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: 4px;
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

        .sign-up-link {
            margin-top: 1rem;
            text-align: center;
        }

        .sign-up-link a {
            color: #ff4400;
            text-decoration: none;
            font-weight: bold;
        }

        .sign-up-link a:hover {
            text-decoration: underline;
        }

        /* Popup Styles */
        .error-popup {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) scale(0.7);
            background: linear-gradient(135deg, #ff4444, #ff8787);
            color: white;
            padding: 20px 30px;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            z-index: 1000;
            max-width: 400px;
            width: 90%;
            opacity: 0;
            animation: popupIn 0.5s ease-out forwards;
        }

        .error-popup.show {
            display: block;
        }

        .error-popup::before {
            content: '⚠';
            font-size: 24px;
            margin-right: 10px;
            vertical-align: middle;
        }

        .error-popup-content {
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            font-size: 16px;
            line-height: 1.5;
        }

        @keyframes popupIn {
            0% {
                opacity: 0;
                transform: translate(-50%, -50%) scale(0.7) rotate(5deg);
            }
            60% {
                opacity: 1;
                transform: translate(-50%, -50%) scale(1.05) rotate(-2deg);
            }
            100% {
                opacity: 1;
                transform: translate(-50%, -50%) scale(1) rotate(0deg);
            }
        }

        @keyframes popupOut {
            0% {
                opacity: 1;
                transform: translate(-50%, -50%) scale(1);
            }
            100% {
                opacity: 0;
                transform: translate(-50%, -50%) scale(0.7) rotate(-5deg);
            }
        }

        /* Overlay */
        .popup-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.4);
            z-index: 999;
        }

        .popup-overlay.show {
            display: block;
        }

        /* Footer Styles */
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
    </style>
</head>
<body>
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

    <main>
        <section class="form-section">
            <h2>Login</h2>
            <?php if (!empty($error_message)): ?>
                <script>
                    window.errorMessage = <?php echo json_encode($error_message); ?>;
                </script>
            <?php endif; ?>
            <div class="card">
                <input
                    value=""
                    class="blind-check"
                    type="checkbox"
                    id="blind-input"
                    name="blindcheck"
                    hidden=""
                />

                <label for="blind-input" class="blind_input">
                    <span class="hide">Hide</span>
                    <span class="show">Show</span>
                </label>

                <form class="form" action="Login.php" method="POST">
                    <div class="title"></div>

                    <label class="label_input" for="email-input">Username or email address *</label>
                    <input
                        spellcheck="false"
                        class="input"
                        type="text"
                        name="username"
                        id="email-input"
                        required
                    />

                    <div class="frg_pss">
                        <label class="label_input" for="password-input">Password *</label>
                        <a href="">Forgot password?</a>
                    </div>
                    <input
                        spellcheck="false"
                        class="input"
                        type="text"
                        name="password"
                        id="password-input"
                        required
                    />
                    <button class="submit" type="submit">Submit</button>
                </form>

                <label for="blind-input" class="avatar">
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        width="35"
                        height="35"
                        viewBox="0 0 64 64"
                        id="monkey"
                    >
                        <ellipse cx="53.7" cy="33" rx="8.3" ry="8.2" fill="#89664c"></ellipse>
                        <ellipse cx="53.7" cy="33" rx="5.4" ry="5.4" fill="#ffc5d3"></ellipse>
                        <ellipse cx="10.2" cy="33" rx="8.2" ry="8.2" fill="#89664c"></ellipse>
                        <ellipse cx="10.2" cy="33" rx="5.4" ry="5.4" fill="#ffc5d3"></ellipse>
                        <g fill="#89664c">
                            <path
                                d="m43.4 10.8c1.1-.6 1.9-.9 1.9-.9-3.2-1.1-6-1.8-8.5-2.1 1.3-1 2.1-1.3 2.1-1.3-20.4-2.9-30.1 9-30.1 19.5h46.4c-.7-7.4-4.8-12.4-11.8-15.2"
                            ></path>
                            <path
                                d="m55.3 27.6c0-9.7-10.4-17.6-23.3-17.6s-23.3 7.9-23.3 17.6c0 2.3.6 4.4 1.6 6.4-1 2-1.6 4.2-1.6 6.4 0 9.7 10.4 17.6 23.3 17.6s23.3-7.9 23.3-17.6c0-2.3-.6-4.4-1.6-6.4 1-2 1.6-4.2 1.6-6.4"
                            ></path>
                        </g>
                        <path
                            d="m52 28.2c0-16.9-20-6.1-20-6.1s-20-10.8-20 6.1c0 4.7 2.9 9 7.5 11.7-1.3 1.7-2.1 3.6-2.1 5.7 0 6.1 6.6 11 14.7 11s14.7-4.9 14.7-11c0-2.1-.8-4-2.1-5.7 4.4-2.7 7.3-7 7.3-11.7"
                            fill="#e0ac7e"
                        ></path>
                        <g fill="#3b302a" class="monkey-eye-nose">
                            <path
                                d="m35.1 38.7c0 1.1-.4 2.1-1 2.1-.6 0-1-.9-1-2.1 0-1.1.4-2.1 1-2.1.6.1 1 1 1 2.1"
                            ></path>
                            <path
                                d="m30.9 38.7c0 1.1-.4 2.1-1 2.1-.6 0-1-.9-1-2.1 0-1.1.4-2.1 1-2.1.5.1 1 1 1 2.1"
                            ></path>
                            <ellipse
                                cx="40.7"
                                cy="31.7"
                                rx="3.5"
                                ry="4.5"
                                class="monkey-eye-r"
                            ></ellipse>
                            <ellipse
                                cx="23.3"
                                cy="31.7"
                                rx="3.5"
                                ry="4.5"
                                class="monkey-eye-l"
                            ></ellipse>
                        </g>
                    </svg>
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        width="35"
                        height="35"
                        viewBox="0 0 64 64"
                        id="monkey-hands"
                    >
                        <path
                            fill="#89664C"
                            d="M9.4,32.5L2.1,61.9H14c-1.6-7.7,4-21,4-21L9.4,32.5z"
                        ></path>
                        <path
                            fill="#FFD6BB"
                            d="M15.8,24.8c0,0,4.9-4.5,9.5-3.9c2.3,0.3-7.1,7.6-7.1,7.6s9.7-8.2,11.7-5.6c1.8,2.3-8.9,9.8-8.9,9.8
    s10-8.1,9.6-4.6c-0.3,3.8-7.9,12.8-12.5,13.8C11.5,43.2,6.3,39,9.8,24.4C11.6,17,13.3,25.2,15.8,24.8"
                        ></path>
                        <path
                            fill="#89664C"
                            d="M54.8,32.5l7.3,29.4H50.2c1.6-7.7-4-21-4-21L54.8,32.5z"
                        ></path>
                        <path
                            fill="#FFD6BB"
                            d="M48.4,24.8c0,0-4.9-4.5-9.5-3.9c-2.3,0.3,7.1,7.6,7.1,7.6s-9.7-8.2-11.7-5.6c-1.8,2.3,8.9,9.8,8.9,9.8
    s-10-8.1-9.7-4.6c0.4,3.8,8,12.8,12.6,13.8c6.6,1.3,11.8-2.9,8.3-17.5C52.6,17,50.9,25.2,48.4,24.8"
                        ></path>
                    </svg>
                </label>
            </div>
            <div class="sign-up-link">
                <p>New to MovieMate? <a href="register.php">Create an account</a></p>
            </div>
        </section>
    </main>

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
        <div class="footer-bottom">
            <p>© 2025 MovieMate. All rights reserved.</p>
        </div>
    </footer>

    <div class="popup-overlay" id="popupOverlay"></div>
    <div class="error-popup" id="errorPopup">
        <div class="error-popup-content" id="errorPopupContent"></div>
    </div>

    <script>
        // Newsletter form submission animation
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

        // Error popup handling
        function showErrorPopup(message) {
            const popup = document.getElementById('errorPopup');
            const popupContent = document.getElementById('errorPopupContent');
            const overlay = document.getElementById('popupOverlay');
            
            popupContent.textContent = message;
            popup.classList.add('show');
            overlay.classList.add('show');
            
            setTimeout(() => {
                hideErrorPopup();
            }, 4000);
        }

        function hideErrorPopup() {
            const popup = document.getElementById('errorPopup');
            const overlay = document.getElementById('popupOverlay');
            
            popup.style.animation = 'popupOut 0.4s ease-out forwards';
            setTimeout(() => {
                popup.classList.remove('show');
                overlay.classList.remove('show');
                popup.style.animation = 'popupIn 0.5s ease-out forwards';
            }, 400);
        }

        document.addEventListener('DOMContentLoaded', () => {
            if (window.errorMessage) {
                showErrorPopup(window.errorMessage);
            }
        });

        document.getElementById('popupOverlay').addEventListener('click', () => {
            hideErrorPopup();
        });
    </script>
</body>
</html>