<!DOCTYPE html>
<html lang="en">
<head>
    <title>Staff Dashboard | QR Scanner</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html5-qrcode/2.3.8/html5-qrcode.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary: #3949ab;
            --primary-dark: #283593;
            --secondary: #5c6bc0;
            --success: #2e7d32;
            --error: #c62828;
            --background: #e8eaf6;
            --card: #ffffff;
            --text: #263238;
            --gradient-start: #3949ab;
            --gradient-end: #5c6bc0;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            background: var(--background);
            background-image: linear-gradient(135deg, #e8eaf6 0%, #c5cae9 100%);
            color: var(--text);
            line-height: 1.6;
            min-height: 100vh;
        }

        .navbar {
            background: linear-gradient(90deg, var(--gradient-start), var(--gradient-end));
            color: white;
            padding: 1rem 2rem;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .navbar h1 {
            font-size: 1.5rem;
            font-weight: 500;
            display: flex;
            align-items: center;
        }

        .navbar h1 i {
            margin-right: 0.5rem;
        }

        //* Add this to your existing CSS */
        /* Ensure the navbar buttons container aligns items horizontally */
        .navbar-buttons {
            display: flex;
            align-items: center;
            gap: 1rem; /* Adds space between the buttons */
        }

        /* Style for the View Bookings button */
        .view-bookings-btn {
            padding: 0.5rem 1rem;
            background-color: rgba(255, 255, 255, 0.2);
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 1rem;
            transition: all 0.2s;
            text-decoration: none;
            display: inline-flex; /* Ensures the button behaves as inline-flex */
            align-items: center;
        }

        .view-bookings-btn i {
            margin-right: 0.5rem;
        }

        .view-bookings-btn:hover {
            background-color: rgba(255, 255, 255, 0.3);
            transform: translateY(-2px);
        }

        /* Style for the Logout button */
        .logout-btn {
            padding: 0.5rem 1rem;
            background-color: rgba(255, 255, 255, 0.2);
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 1rem;
            transition: all 0.2s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
        }

        .logout-btn i {
            margin-right: 0.5rem;
        }

        .logout-btn:hover {
            background-color: rgba(255, 255, 255, 0.3);
            transform: translateY(-2px);
        }

        /* Style for the Home button */
        .home-btn {
            padding: 0.5rem 1rem;
            background-color: rgba(255, 255, 255, 0.2);
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 1rem;
            transition: all 0.2s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
        }

        .home-btn i {
            margin-right: 0.5rem;
        }

        .home-btn:hover {
            background-color: rgba(255, 255, 255, 0.3);
            transform: translateY(-2px);
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }

        .scanner-section {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
            margin-bottom: 2rem;
        }

        @media (max-width: 768px) {
            .scanner-section {
                grid-template-columns: 1fr;
            }
        }

        #reader {
            background: var(--card);
            padding: 1rem;
            border-radius: 12px;
            box-shadow: 0 10px 15px rgba(0,0,0,0.05);
            transition: transform 0.3s;
        }

        #reader:hover {
            transform: translateY(-5px);
        }

        #result {
            background: var(--card);
            padding: 1.5rem;
            border-radius: 12px;
            box-shadow: 0 10px 15px rgba(0,0,0,0.05);
            display: none;
            transition: all 0.3s;
        }

        .ticket-info {
            margin-bottom: 1.5rem;
        }

        .ticket-info h2 {
            color: var(--primary);
            margin-bottom: 1rem;
            font-size: 1.25rem;
            display: flex;
            align-items: center;
        }

        .ticket-info h2 i {
            margin-right: 0.5rem;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
        }

        .info-item {
            background: #f8f9fa;
            padding: 0.75rem;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            transition: transform 0.2s;
        }

        .info-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }

        .info-item strong {
            color: var(--secondary);
            display: block;
            font-size: 0.875rem;
            margin-bottom: 0.25rem;
        }

        .food-history {
            margin-top: 1.5rem;
            padding-top: 1.5rem;
            border-top: 1px solid #eee;
        }

        .food-history h3 {
            color: var(--primary);
            margin-bottom: 1rem;
            font-size: 1.125rem;
            display: flex;
            align-items: center;
        }

        .food-history h3 i {
            margin-right: 0.5rem;
        }

        .food-item {
            background: #f8f9fa;
            padding: 0.75rem;
            border-radius: 8px;
            margin-bottom: 0.5rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            transition: transform 0.2s;
        }

        .food-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }

        .confirm-btn {
            margin-top: 1.5rem;
            padding: 0.75rem 1.5rem;
            background-color: var(--success);
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 1rem;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

        .confirm-btn i {
            margin-right: 0.5rem;
        }

        .confirm-btn:hover:not(:disabled) {
            background-color: #1b5e20;
            transform: translateY(-2px);
            box-shadow: 0 6px 8px rgba(0,0,0,0.15);
        }

        .confirm-btn:disabled {
            background-color: #9e9e9e;
            cursor: not-allowed;
        }

        .success {
            background-color: #e8f5e9;
            color: var(--success);
            padding: 1rem;
            border-radius: 8px;
            margin-top: 1rem;
            display: flex;
            align-items: center;
        }

        .success i {
            margin-right: 0.5rem;
            font-size: 1.25rem;
        }

        .error {
            background-color: #ffebee;
            color: var(--error);
            padding: 1rem;
            border-radius: 8px;
            margin-top: 1rem;
            display: flex;
            align-items: center;
        }

        .error i {
            margin-right: 0.5rem;
            font-size: 1.25rem;
        }

        /* Chat Popup Styles */
        .chat-popup {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 1000;
        }

        .chat-btn-toggle {
            background: var(--primary);
            color: white;
            border: none;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            position: absolute;
            bottom: 0;
            right: 0;
            transition: all 0.3s;
        }

        .chat-btn-toggle:hover {
            background: var(--primary-dark);
            transform: translateY(-5px);
        }

        .chat-window {
            width: 350px;
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 10px 20px rgba(0,0,0,0.15);
            display: none;
            flex-direction: column;
            margin-bottom: 70px;
            transition: all 0.3s;
            transform: translateY(20px);
            opacity: 0;
        }

        .chat-window.active {
            display: flex;
            transform: translateY(0);
            opacity: 1;
        }

        .chat-header {
            background: linear-gradient(90deg, var(--gradient-start), var(--gradient-end));
            color: white;
            padding: 1rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .chat-header h3 {
            margin: 0;
            font-size: 1.1rem;
            display: flex;
            align-items: center;
        }

        .chat-header h3 i {
            margin-right: 0.5rem;
        }

        .chat-close {
            background: none;
            border: none;
            color: white;
            cursor: pointer;
            font-size: 1.2rem;
        }

        .chat-body {
            padding: 1rem;
            flex-grow: 1;
            max-height: 300px;
            overflow-y: auto;
        }

        .chat-messages {
            display: flex;
            flex-direction: column;
        }

        .chat-message {
            margin-bottom: 1rem;
            max-width: 80%;
            padding: 0.75rem;
            border-radius: 8px;
            position: relative;
        }

        .chat-message.outgoing {
            background: #e3f2fd;
            color: #0d47a1;
            align-self: flex-end;
            border-bottom-right-radius: 0;
        }

        .chat-message.incoming {
            background: #f5f5f5;
            color: #333;
            align-self: flex-start;
            border-bottom-left-radius: 0;
        }

        .chat-message .timestamp {
            font-size: 0.7rem;
            opacity: 0.7;
            margin-top: 0.25rem;
            text-align: right;
        }

        .chat-footer {
            padding: 1rem;
            border-top: 1px solid #eee;
        }

        .chat-input-container {
            display: flex;
            gap: 0.5rem;
        }

        .chat-input {
            flex-grow: 1;
            padding: 0.75rem;
            font-size: 1rem;
            border: 1px solid #ddd;
            border-radius: 8px;
            resize: none;
        }

        .chat-send {
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 8px;
            width: 50px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background-color 0.2s;
        }

        .chat-send:hover {
            background-color: var(--primary-dark);
        }

        /* Notification Badge */
        .notification-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background: #f44336;
            color: white;
            border-radius: 50%;
            width: 22px;
            height: 22px;
            font-size: 0.75rem;
            display: flex;
            align-items: center;
            justify-content: center;
            display: none;
        }

        .chat-message.outgoing {
            background: #e3f2fd;
            color: #0d47a1;
            align-self: flex-end;
            border-bottom-right-radius: 0;
        }

        .chat-message.incoming {
            background: #f5f5f5;
            color: #333;
            align-self: flex-start;
            border-bottom-left-radius: 0;
        }

        /* Animations */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .fade-in {
            animation: fadeIn 0.3s ease forwards;
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <h1><i class="fas fa-qrcode"></i> Staff Dashboard | QR Scanner</h1>
        <div class="navbar-buttons">
            <a href="index.php" class="home-btn">
                <i class="fas fa-home"></i> Home
            </a>
            <a href="view_bookings_forstaff.php" class="view-bookings-btn">
                <i class="fas fa-ticket-alt"></i> View Bookings
            </a>
            <button class="logout-btn" onclick="logout()">
                <i class="fas fa-sign-out-alt"></i> Logout
            </button>
        </div>
    </nav>

    <div class="container">
        <div class="scanner-section">
            <div id="reader"></div>
            <div id="result"></div>
        </div>
    </div>

    <!-- Chat Popup -->
    <div class="chat-popup">
        <button class="chat-btn-toggle" id="chatToggle">
            <i class="fas fa-comments"></i>
            <span class="notification-badge" id="notificationBadge">0</span>
        </button>
        <div class="chat-window" id="chatWindow">
            <div class="chat-header">
                <h3><i class="fas fa-headset"></i> Chat with Admin</h3>
                <button class="chat-close" id="chatClose"><i class="fas fa-times"></i></button>
            </div>
            <div class="chat-body">
                <div class="chat-messages" id="chatMessages">
                    <div class="chat-message incoming">
                        <div class="message-content">Welcome! How can we help you today?</div>
                        <div class="timestamp">Now</div>
                    </div>
                </div>
            </div>
            <div class="chat-footer">
                <div class="chat-input-container">
                    <textarea class="chat-input" id="chatInput" rows="2" placeholder="Type a message..."></textarea>
                    <button class="chat-send" id="chatSend"><i class="fas fa-paper-plane"></i></button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Initialize the QR Scanner
        function onScanSuccess(decodedText) {
            try {
                const ticketData = JSON.parse(decodedText);
                const resultDiv = document.getElementById('result');
                resultDiv.style.display = 'block';
                resultDiv.classList.add('fade-in');
                
                // Format the food history HTML
                let foodHistoryHtml = '<div class="food-history">';
                foodHistoryHtml += '<h3><i class="fas fa-utensils"></i> Food Order History</h3>';
                if (ticketData.user_food_history && ticketData.user_food_history.length > 0) {
                    ticketData.user_food_history.forEach(order => {
                        foodHistoryHtml += `
                            <div class="food-item">
                                <strong>${order.food_name}</strong> (${order.size}) x${order.quantity}
                                <div>Total: Rs:${order.total_price}</div>
                                <div style="font-size: 0.875rem; color: #666;">
                                    Ordered: ${new Date(order.order_date).toLocaleString()}
                                </div>
                            </div>`;
                    });
                } else {
                    foodHistoryHtml += '<p>No food order history available.</p>';
                }
                foodHistoryHtml += '</div>';

                // Display ticket information
                resultDiv.innerHTML = `
                    <div class="ticket-info">
                        <h2><i class="fas fa-ticket-alt"></i> Ticket Information</h2>
                        <div class="info-grid">
                            <div class="info-item">
                                <strong>Booking ID</strong>
                                ${ticketData.booking_id}
                            </div>
                            <div class="info-item">
                                <strong>Customer</strong>
                                ${ticketData.customer}
                            </div>
                            <div class="info-item">
                                <strong>Movie</strong>
                                ${ticketData.movie}
                            </div>
                            <div class="info-item">
                                <strong>Date & Time</strong>
                                ${ticketData.date} at ${ticketData.time}
                            </div>
                            <div class="info-item">
                                <strong>Seats</strong>
                                ${ticketData.seats}
                            </div>
                            <div class="info-item">
                                <strong>Tickets</strong>
                                Adult: ${ticketData.tickets.adult}, Child: ${ticketData.tickets.child}
                            </div>
                        </div>
                    </div>
                    ${foodHistoryHtml}
                    <button onclick="confirmTicket(${ticketData.booking_id}, ${ticketData.user_id})" class="confirm-btn">
                        <i class="fas fa-check-circle"></i> Confirm Ticket
                    </button>`;
            } catch (error) {
                document.getElementById('result').innerHTML = `
                    <div class="error">
                        <i class="fas fa-exclamation-circle"></i>
                        <p>Invalid QR Code format: ${error.message}</p>
                    </div>`;
            }
        }

        function onScanError(errorMessage) {
            console.error(errorMessage);
        }

        // Initialize QR Scanner
        let html5QrcodeScanner = new Html5QrcodeScanner(
            "reader",
            { fps: 10, qrbox: { width: 250, height: 250 } }
        );
        html5QrcodeScanner.render(onScanSuccess, onScanError);

        // Function to confirm ticket
        function confirmTicket(bookingId, userId) {
            fetch('confirm_ticket.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    booking_id: bookingId,
                    user_id: userId
                })
            })
            .then(response => response.json())
            .then(data => {
                const resultDiv = document.getElementById('result');
                if (data.success) {
                    resultDiv.innerHTML += `
                        <div class="success fade-in">
                            <i class="fas fa-check-circle"></i>
                            <p>${data.message}</p>
                        </div>`;
                    // Disable the confirm button
                    document.querySelector('.confirm-btn').disabled = true;
                } else {
                    resultDiv.innerHTML += `
                        <div class="error fade-in">
                            <i class="fas fa-exclamation-circle"></i>
                            <p>${data.message}</p>
                        </div>`;
                }
            })
            .catch(error => {
                document.getElementById('result').innerHTML += `
                    <div class="error fade-in">
                        <i class="fas fa-exclamation-circle"></i>
                        <p>Error confirming ticket: ${error.message}</p>
                    </div>`;
            });
        }

        // Logout function
        function logout() {
            // Redirect to the login page or perform logout operations
            window.location.href = 'logout.php'; 
        }

        // Chat popup functionality
        document.addEventListener('DOMContentLoaded', function() {
        const chatToggle = document.getElementById('chatToggle');
        const chatWindow = document.getElementById('chatWindow');
        const chatClose = document.getElementById('chatClose');
        const chatInput = document.getElementById('chatInput');
        const chatSend = document.getElementById('chatSend');
        const chatMessages = document.getElementById('chatMessages');
        const notificationBadge = document.getElementById('notificationBadge');
        
        let unreadCount = 0;
        let staffId = 1; // Replace with actual staff ID from your authentication system
        
        // Toggle chat window
        chatToggle.addEventListener('click', function() {
            chatWindow.classList.toggle('active');
            if (chatWindow.classList.contains('active')) {
                chatInput.focus();
                // Reset notification count when opening chat
                unreadCount = 0;
                notificationBadge.style.display = 'none';
                // Fetch messages when chat is opened
                fetchMessages();
            }
        });
        
        // Close chat window
        chatClose.addEventListener('click', function() {
            chatWindow.classList.remove('active');
        });
        
        // Send message on button click
        chatSend.addEventListener('click', sendChatMessage);
        
        // Send message on Enter key (but allow Shift+Enter for new line)
        chatInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                sendChatMessage();
            }
        });

        // Function to fetch messages
        function fetchMessages() {
            fetch('get_messages.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.messages) {
                        chatMessages.innerHTML = ''; // Clear existing messages
                        data.messages.forEach(message => {
                            addMessageToChat(message.type, message.content, message.timestamp);
                        });
                    }
                })
                .catch(error => {
                    console.error('Error fetching messages:', error);
                });
        }

        // Function to send chat message
        function sendChatMessage() {
            const message = chatInput.value.trim();
            if (message === '') return;

            // Get current timestamp
            const now = new Date();
            const timestamp = now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });

            // Add message to chat
            addMessageToChat('outgoing', message, timestamp);

            // Clear input
            chatInput.value = '';

            // Send message to server
            fetch('send_message_to_admin.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ 
                    message: message,
                    staff_id: staffId,
                    timestamp: now.toISOString()
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success && data.response) {
                    // Add admin response if available
                    setTimeout(() => {
                        addMessageToChat('incoming', data.response, timestamp);

                        // Show notification if chat is closed
                        if (!chatWindow.classList.contains('active')) {
                            unreadCount++;
                            notificationBadge.textContent = unreadCount;
                            notificationBadge.style.display = 'flex';
                        }
                    }, 1000); // Simulate delay for realistic chat feel
                }
            })
            .catch(error => {
                console.error('Error sending message:', error);
                // Add error message
                setTimeout(() => {
                    addMessageToChat('incoming', 'Sorry, there was an error sending your message. Please try again.', timestamp);
                }, 1000);
            });
        }

        // Function to add message to chat
        function addMessageToChat(type, content, timestamp) {
            const messageDiv = document.createElement('div');
            messageDiv.classList.add('chat-message', type, 'fade-in');

            messageDiv.innerHTML = `
                <div class="message-content">${content}</div>
                <div class="timestamp">${timestamp}</div>
            `;

            chatMessages.appendChild(messageDiv);

            // Scroll to bottom
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }
    });
    </script>
</body>
</html>