MovieMate - Movie Ticket Booking Platform
Overview
MovieMate is a comprehensive movie ticket booking platform that allows users to browse and book movie tickets, order food and beverages, and purchase movie-themed merchandise. The platform features a modern, responsive design with engaging animations and an AI-powered assistant to enhance user experience.
Features

Movie Ticket Booking: Browse now-showing and upcoming movies, check showtimes, and book tickets securely.
Food & Beverage Ordering: Order snacks and drinks to enjoy during the movie.
Merchandise Store: Purchase exclusive movie-themed merchandise.
AI Assistant: An integrated chatbot powered by Google's Gemini API to assist users with movie recommendations, ticket booking, and general inquiries.
User Authentication: Role-based access for customers, staff, and admins with profile management and secure login.
Responsive Design: Optimized for both desktop and mobile devices with a mobile-friendly navigation menu.
Dynamic Animations: Includes loading animations, slideshow transitions, scroll-based animations, and a scroll progress bar.
Promotions Banner: Highlights exclusive deals with a parallax zoom effect.
Database Integration: Fetches movie data from a MySQL database for dynamic content display.

Technologies Used

Frontend:
HTML5, CSS3, JavaScript
Font Awesome for icons
Lottie for loading animation


Backend:
PHP for server-side logic
MySQL for database management


APIs:
Google Gemini API for the AI assistant


Libraries:
Lottie Web for animations
CDN-hosted Font Awesome



File Structure
moviemate/
├── Images/                     # Static image assets
│   ├── hero-1.jpeg             # Hero section background images
│   ├── hero-2.jpeg
│   ├── hero-3.jpeg
│   ├── ca-1.png                # Category images
│   ├── ca-2.png
│   ├── ca-3.png
│   ├── 50.png                  # Promotions banner background
│   ├── default-avatar.jpg      # Default user profile picture
├── auth_check.php              # Authentication logic
├── index.php                   # Main homepage
├── movie.php                   # Movie listing page
├── items.php                   # Merchandise page
├── food.php                    # Food and beverage page
├── about.php                   # About us page
├── admin.php                   # Admin dashboard
├── edit-movie.php              # Movie management page
├── update-items.php            # Merchandise management page
├── update-food.php             # Food and beverage management page
├── staff_scanner.php           # Staff ticket scanner page
├── customer.php                # Customer profile page
├── movie-booking.php           # Movie booking page
├── Login.php                   # Login page
├── logout.php                  # Logout logic
└── README.md                   # This file

Setup Instructions

Prerequisites:

PHP 7.4 or higher
MySQL 5.7 or higher
Web server (e.g., Apache, Nginx)
Internet connection for CDN-hosted libraries and API calls


Database Setup:

Create a MySQL database named moviemate.
Import the following table structure for movies:CREATE TABLE movies (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    thumbnail VARCHAR(255) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    release_date DATE NOT NULL,
    status ENUM('now_showing', 'upcoming') NOT NULL
);


Configure database credentials in index.php:$servername = "localhost";
$username = "root";
$password = "";
$dbname = "moviemate";




API Configuration:

Obtain a Google Gemini API key from the Google Cloud Console.
Update the API_KEY in the JavaScript code:const API_KEY = "YOUR_API_KEY_HERE";




File Configuration:

Place all files in your web server's root directory (e.g., htdocs for Apache).
Ensure the Images/ directory contains all required image assets.
Verify that auth_check.php is properly configured for user authentication.


Run the Application:

Start your web server and MySQL server.
Access the application via http://localhost/moviemate/index.php.



Usage

Home Page: Browse featured movies, categories, and promotions. Use the AI assistant for quick help.
User Roles:
Customers: Book tickets, order food, and purchase merchandise.
Staff: Access ticket scanner functionality.
Admins: Manage movies, merchandise, and food items via the admin dashboard.


AI Assistant: Click the chat button to interact with the assistant for movie recommendations, booking assistance, or general inquiries.
Mobile Navigation: Toggle the mobile menu for easy access on smaller screens.

Known Issues

The AI assistant may encounter rate limits with the Google Gemini API during heavy usage.
Image paths in the Images/ directory must be correctly set to avoid broken links.
The loading animation may not display correctly if the Lottie file URL is inaccessible.

Future Improvements

Implement caching for API responses to improve performance.
Add support for multiple languages.
Enhance the AI assistant with more advanced natural language processing capabilities.
Integrate a payment gateway for secure transactions.

Contributing
Contributions are welcome! Please submit a pull request or open an issue for bug reports or feature requests.
License
This project is licensed under the MIT License. See the LICENSE file for details.
Contact
For support, contact the MovieMate team at:

Email: info@moviemate.lk
Phone: +94 41 222 3333

