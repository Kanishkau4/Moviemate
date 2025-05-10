<!DOCTYPE html>
<html>
<head>
    <style>
        /* New theme for messaging and feedback cards */
        .card-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 30px;
            padding: 30px;
            max-width: 1200px;
        }

        .message-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 16px;
            padding: 40px 30px;
            text-align: center;
            cursor: pointer;
            position: relative;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            overflow: hidden;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }

        .message-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(45deg, 
                rgba(100, 115, 255, 0.1), 
                rgba(200, 100, 255, 0.1));
            opacity: 0;
            transition: opacity 0.5s;
        }

        .message-card:hover::before {
            opacity: 1;
            animation: gradientMove 3s infinite;
        }

        @keyframes gradientMove {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        .message-card:hover {
            transform: translateY(-10px) scale(1.03);
            box-shadow: 
                0 20px 40px rgba(0, 0, 0, 0.2),
                0 0 20px rgba(100, 115, 255, 0.3),
                inset 0 0 15px rgba(255, 255, 255, 0.5);
        }

        .message-icon-container {
            width: 90px;
            height: 90px;
            margin: 0 auto 25px;
            position: relative;
            background: linear-gradient(135deg, #6469ff, #b84dff);
            border-radius: 50%;
            padding: 18px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
            transition: all 0.5s ease;
        }

        .message-card:hover .message-icon-container {
            transform: rotate(360deg);
            background: linear-gradient(135deg, #b84dff, #6469ff);
        }

        .message-icon {
            width: 100%;
            height: 100%;
            color: white;
            transition: transform 0.5s ease;
        }

        .message-title {
            font-size: 1.6rem;
            font-weight: 700;
            color: #1F2937;
            margin-bottom: 15px;
            position: relative;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .message-card:hover .message-title {
            animation: textGlow 1.5s ease infinite alternate;
        }

        @keyframes textGlow {
            from { text-shadow: 0 0 5px rgba(100, 115, 255, 0); }
            to { text-shadow: 0 0 15px rgba(100, 115, 255, 0.5); }
        }

        .message-description {
            color: #6B7280;
            font-size: 1rem;
            line-height: 1.6;
            opacity: 0.9;
            transition: all 0.5s ease;
        }

        .message-card:hover .message-description {
            opacity: 1;
            transform: scale(1.05);
        }

        /* Chat bubble animation */
        .chat-bubble {
            position: absolute;
            width: 20px;
            height: 20px;
            background: rgba(100, 115, 255, 0.3);
            border-radius: 50%;
            opacity: 0;
            transition: opacity 0.3s;
        }

        .chat-bubble-1 {
            top: 10%;
            left: 20%;
            animation: float 4s infinite ease-in-out;
        }

        .chat-bubble-2 {
            top: 30%;
            left: 70%;
            animation: float 5s infinite ease-in-out;
        }

        .chat-bubble-3 {
            top: 60%;
            left: 40%;
            animation: float 6s infinite ease-in-out;
        }

        .message-card:hover .chat-bubble {
            opacity: 1;
        }

        @keyframes float {
            0% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
            100% { transform: translateY(0); }
        }
    </style>
</head>
<body>
<div class="card-container">
    <!-- Reply to Feedback Card -->
    <div class="message-card" onclick="window.location.href='reply-feedback.php'">
        <div class="message-icon-container">
            <svg class="message-icon" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M20 2H4c-1.1 0-1.99.9-1.99 2L2 22l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm-3 12H7v-2h10v2zm0-3H7V9h10v2zm0-3H7V6h10v2z" fill="currentColor"/>
            </svg>
        </div>
        <h3 class="message-title">Reply to Feedback</h3>
        <p class="message-description">Respond to user feedback and queries</p>
        <div class="chat-bubble chat-bubble-1"></div>
        <div class="chat-bubble chat-bubble-2"></div>
        <div class="chat-bubble chat-bubble-3"></div>
    </div>

    <!-- Message All Users Card -->
    <div class="message-card" onclick="window.location.href='message-all-users.php'">
        <div class="message-icon-container">
            <svg class="message-icon" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M20 4H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 14H4V8l8 5 8-5v10zm-8-7L4 6h16l-8 5z" fill="currentColor"/>
            </svg>
        </div>
        <h3 class="message-title">Message All Users</h3>
        <p class="message-description">Send announcements to all users</p>
        <div class="chat-bubble chat-bubble-1"></div>
        <div class="chat-bubble chat-bubble-2"></div>
        <div class="chat-bubble chat-bubble-3"></div>
    </div>
</div>
</body>
</html>