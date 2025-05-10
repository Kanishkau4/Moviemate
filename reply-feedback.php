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
        
        .feedback-card {
            border: 1px solid #e5e7eb;
            padding: 15px;
            margin-bottom: 15px;
            border-radius: 8px;
            background: #f9fafb;
        }
        
        .feedback-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }
        
        .rating {
            color: #4338ca;
            font-weight: bold;
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

        .response-text {
            margin-top: 10px;
            padding: 10px;
            background-color: #e5e7eb;
            border-radius: 4px;
        }
    </style>
</head>
<body>
<div class="container">
    <!-- Existing Back Icon and Feedback Section -->
    <div class="back-icon" onclick="goBack()">
        <i class="fas fa-arrow-left"></i> Back
    </div>
    <h2>User Feedback</h2>
    <div id="feedback-list">
        <!-- Feedback items will be inserted here -->
    </div>

    <!-- New Section for Staff Messages -->
    <h2>Staff Messages</h2>
    <div id="staff-messages-list">
        <!-- Staff messages will be inserted here -->
    </div>
</div>

    <script>
        async function fetchFeedback() {
            try {
                const response = await fetch('admin_actions.php?action=getFeedback');
                const data = await response.json();
                displayFeedback(data);
            } catch (error) {
                console.error('Error fetching feedback:', error);
                alert('Error loading feedback data');
            }
        }

        function displayFeedback(feedbackData) {
            const feedbackList = document.getElementById('feedback-list');
            feedbackList.innerHTML = '';

            feedbackData.forEach(feedback => {
                const responseHtml = feedback.admin_response ? 
                    `<div class="response-text">
                        <strong>Admin Response:</strong><br>
                        ${feedback.admin_response}
                        <br>
                        <small>Responded on: ${new Date(feedback.response_date).toLocaleDateString()}</small>
                    </div>` : '';

                const feedbackHTML = 
                    `<div class="feedback-card">
                        <div class="feedback-header">
                            <span>User: ${feedback.username}</span>
                            <span class="rating">Rating: ${feedback.rating}/5</span>
                        </div>
                        <p>${feedback.feedback_text}</p>
                        <small>Submitted on: ${new Date(feedback.created_at).toLocaleDateString()}</small>
                        ${responseHtml}
                        <div class="form-group">
                            <label>Response:</label>
                            <textarea class="form-control" rows="2" ${feedback.admin_response ? 'disabled' : ''}></textarea>
                        </div>
                        ${!feedback.admin_response ? 
                            `<button class="btn" onclick="respondToFeedback(${feedback.id})">Send Response</button>` : 
                            ''}
                    </div>`;
                feedbackList.innerHTML += feedbackHTML;
            });
        }

        async function respondToFeedback(feedbackId) {
            const feedbackCard = event.target.parentElement;
            const response = feedbackCard.querySelector('textarea').value;
            
            if (response.trim() === '') {
                alert('Please enter a response');
                return;
            }

            try {
                const formData = new FormData();
                formData.append('action', 'saveResponse');
                formData.append('feedbackId', feedbackId);
                formData.append('response', response);

                const response = await fetch('admin_actions.php', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();
                if (result.success) {
                    alert('Response sent successfully');
                    fetchFeedback();
                } else {
                    alert('Error sending response');
                }
            } catch (error) {
                console.error('Error sending response:', error);
                alert('Error sending response');
            }
        }

        function goBack() {
            window.history.back();
        }

        fetchFeedback();

        async function fetchStaffMessages() {
    try {
        const response = await fetch('staff_messages.php?action=getStaffMessages');
        console.log('Staff Messages Response:', response);

        if (!response.ok) {
            throw new Error('Network response was not ok');
        }

        const data = await response.json();
        console.log('Staff Messages Data:', data);
        displayStaffMessages(data);
    } catch (error) {
        console.error('Error fetching staff messages:', error);
        alert('Error loading staff messages');
    }
}

function displayStaffMessages(messages) {
    const messagesList = document.getElementById('staff-messages-list');
    messagesList.innerHTML = ''; // Clear existing content

    messages.forEach(message => {
        const replyForm = message.admin_reply ? 
            `<div class="response-text">
                <strong>Admin Reply:</strong><br>
                ${message.admin_reply}
            </div>` :
            `<div class="form-group">
                <label>Reply:</label>
                <textarea class="form-control reply-textarea" rows="2" placeholder="Type your reply..."></textarea>
                <button class="btn reply-btn" data-message-id="${message.id}">Send Reply</button>
            </div>`;

        const messageHTML = `
            <div class="feedback-card">
                <div class="feedback-header">
                    <span>${message.sender_type}: ${message.sender_id}</span>
                </div>
                <p>${message.message}</p>
                <small>Sent on: ${new Date(message.timestamp).toLocaleString()}</small>
                ${replyForm}
            </div>
        `;
        messagesList.innerHTML += messageHTML;
    });

    // Add event listeners to reply buttons
    document.querySelectorAll('.reply-btn').forEach(button => {
        button.addEventListener('click', sendAdminReply);
    });
}

async function sendAdminReply(event) {
    const messageId = event.target.getAttribute('data-message-id');
    const replyTextarea = event.target.previousElementSibling;
    const replyText = replyTextarea.value.trim();

    if (!replyText) {
        alert('Please enter a reply.');
        return;
    }

    try {
        const formData = new FormData();
        formData.append('action', 'sendAdminReply');
        formData.append('messageId', messageId);
        formData.append('replyText', replyText);

        const response = await fetch('staff_messages.php', {
            method: 'POST',
            body: formData
        });

        const result = await response.json();
        if (result.success) {
            alert('Reply sent successfully!');
            fetchStaffMessages(); // Refresh the messages
        } else {
            alert('Error sending reply.');
        }
    } catch (error) {
        console.error('Error sending reply:', error);
        alert('Error sending reply.');
    }
}

// Call the function to fetch staff messages when the page loads
fetchStaffMessages();
    </script>
</body>
</html>