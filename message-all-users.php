<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .container {
            padding: 20px;
            max-width: 800px;
            margin: 0 auto;
        }
        
        .back-icon {
            font-size: 24px;
            margin-bottom: 20px;
            cursor: pointer;
            color: #4338ca;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
        }
        
        .form-control {
            width: 100%;
            padding: 8px;
            border: 1px solid #e5e7eb;
            border-radius: 4px;
        }
        
        .btn {
            background-color: #4338ca;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        
        .btn:hover {
            background-color: #3730a3;
        }

        .messages-list {
            margin-top: 30px;
        }

        .message-item {
            border: 1px solid #e5e7eb;
            padding: 15px;
            margin-bottom: 10px;
            border-radius: 4px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .message-item .delete-btn {
            background-color: #ef4444;
            color: white;
            padding: 5px 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .message-item .delete-btn:hover {
            background-color: #dc2626;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="back-icon" onclick="goBack()">
            <i class="fas fa-arrow-left"></i> Back
        </div>
        <h2>Send Message to All Users</h2>
        <form id="message-form">
            <div class="form-group">
                <label for="subject">Subject</label>
                <input type="text" id="subject" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="message">Message</label>
                <textarea id="message" class="form-control" rows="4" required></textarea>
            </div>
            <button type="submit" class="btn">Send to All Users</button>
        </form>

        <div class="messages-list">
            <h3>Sent Messages</h3>
            <div id="messages-container"></div>
        </div>
    </div>

    <script>
        document.getElementById('message-form').addEventListener('submit', async function(e) {
            e.preventDefault();
            const subject = document.getElementById('subject').value;
            const message = document.getElementById('message').value;

            try {
                const formData = new FormData();
                formData.append('action', 'sendMessage');
                formData.append('subject', subject);
                formData.append('message', message);

                const response = await fetch('admin_actions.php', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();
                if (result.success) {
                    alert('Message sent to all users successfully');
                    this.reset();
                    fetchSentMessages(); // Refresh the list of sent messages
                } else {
                    alert('Error sending message');
                }
            } catch (error) {
                console.error('Error sending message:', error);
                alert('Error sending message');
            }
        });

        async function fetchSentMessages() {
            try {
                const response = await fetch('admin_actions.php?action=getSentMessages');
                const messages = await response.json();
                const messagesContainer = document.getElementById('messages-container');
                messagesContainer.innerHTML = '';

                messages.forEach(msg => {
                    const messageItem = document.createElement('div');
                    messageItem.className = 'message-item';
                    messageItem.innerHTML = `
                        <div>
                            <strong>${msg.subject}</strong><br>
                            ${msg.message}<br>
                            <small>Sent on: ${new Date(msg.sent_date).toLocaleString()}</small>
                        </div>
                        <button class="delete-btn" onclick="deleteMessage(${msg.id})">Delete</button>
                    `;
                    messagesContainer.appendChild(messageItem);
                });
            } catch (error) {
                console.error('Error fetching sent messages:', error);
            }
        }

        async function deleteMessage(messageId) {
            if (confirm('Are you sure you want to delete this message?')) {
                try {
                    const formData = new FormData();
                    formData.append('action', 'deleteMessage');
                    formData.append('messageId', messageId);

                    const response = await fetch('admin_actions.php', {
                        method: 'POST',
                        body: formData
                    });

                    const result = await response.json();
                    if (result.success) {
                        alert('Message deleted successfully');
                        fetchSentMessages(); // Refresh the list of sent messages
                    } else {
                        alert('Error deleting message');
                    }
                } catch (error) {
                    console.error('Error deleting message:', error);
                    alert('Error deleting message');
                }
            }
        }

        function goBack() {
            window.history.back();
        }

        // Fetch sent messages when the page loads
        fetchSentMessages();
    </script>
</body>
</html>