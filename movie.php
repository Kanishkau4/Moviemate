<?php
// config.php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "moviemate";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle search query
$search_query = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';

// Fetch movies with search filter
$sql = "SELECT * FROM movies WHERE 
        title LIKE '%$search_query%' OR 
        description LIKE '%$search_query%' OR 
        language LIKE '%$search_query%'
        ORDER BY release_date DESC";
$result = $conn->query($sql);

// Function to format runtime
function formatRuntime($minutes) {
    $hours = floor($minutes / 60);
    $mins = $minutes % 60;
    return $hours . "h " . $mins . "m";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MovieMate - Find and Book Movie Tickets</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        :root {
            --primary: #6d28d9;
            --primary-dark: #5b21b6;
            --secondary: #10b981;
            --dark: #1e293b;
            --light: #f8fafc;
            --gray: #64748b;
            --light-gray: #e2e8f0;
            --badge: #fbbf24;
            --star: #fbbf24;
        }

        body {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            color: var(--light);
            min-height: 100vh;
        }

        .header {
            background-color: rgba(15, 23, 42, 0.8);
            backdrop-filter: blur(10px);
            position: sticky;
            top: 0;
            z-index: 100;
            padding: 1.25rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .nav-container {
            max-width: 1280px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-size: 1.5rem;
            font-weight: 700;
            color: white;
        }

        .logo i {
            color: var(--primary);
        }

        .container {
            max-width: 1280px;
            margin: 3rem auto;
            padding: 0 1.5rem;
        }

        .section-header {
            margin-bottom: 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .section-title {
            font-size: 2rem;
            font-weight: 700;
            position: relative;
            padding-bottom: 0.75rem;
        }

        .section-title::after {
            content: '';
            position: absolute;
            left: 0;
            bottom: 0;
            width: 60px;
            height: 4px;
            background: var(--primary);
            border-radius: 2px;
        }

        .movies-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 1.5rem;
        }

        .movie-card {
            background: rgba(30, 41, 59, 0.7);
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
            transition: all 0.3s ease;
            position: relative;
            border: 1px solid rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(10px);
        }

        .movie-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.3);
            border-color: rgba(109, 40, 217, 0.3);
        }

        .movie-image {
            position: relative;
            padding-top: 130%;
            overflow: hidden;
        }

        .movie-image img {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }

        .movie-card:hover .movie-image img {
            transform: scale(1.05);
        }

        .upcoming-badge {
            position: absolute;
            top: 1rem;
            right: 1rem;
            background: var(--badge);
            color: var(--dark);
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .trailer-btn {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: rgba(0, 0, 0, 0.6);
            color: white;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            opacity: 0;
            transition: opacity 0.3s ease;
            cursor: pointer;
            border: 2px solid white;
        }

        .movie-card:hover .trailer-btn {
            opacity: 1;
        }

        .movie-content {
            padding: 1rem;
        }

        .movie-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.5rem;
        }

        .movie-meta-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.75rem;
            color: var(--gray);
        }

        .rating {
            display: flex;
            align-items: center;
            gap: 0.25rem;
        }

        .star {
            color: var(--star);
        }

        .movie-title {
            font-size: 1.1rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            color: white;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .movie-info {
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem;
            margin-bottom: 0.75rem;
        }

        .movie-info-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.75rem;
            color: var(--light-gray);
        }

        .movie-info-item i {
            color: var(--primary);
            font-size: 0.875rem;
        }

        .movie-description {
            color: var(--light-gray);
            font-size: 0.75rem;
            margin-bottom: 1rem;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            line-height: 1.5;
        }

        .movie-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .price-container {
            display: flex;
            flex-direction: column;
        }

        .price {
            font-size: 1rem;
            font-weight: 700;
            color: white;
        }

        .discount {
            color: var(--secondary);
            font-size: 0.75rem;
            font-weight: 600;
        }

        .book-button {
            background: var(--primary);
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.875rem;
        }

        .book-button:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(109, 40, 217, 0.3);
        }

        .search-bar {
            margin-bottom: 2.5rem;
            position: relative;
        }

        .search-input {
            width: 100%;
            background: rgba(30, 41, 59, 0.7);
            border: 1px solid rgba(255, 255, 255, 0.1);
            padding: 1rem 1.5rem;
            padding-left: 3rem;
            border-radius: 12px;
            color: white;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .search-input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(109, 40, 217, 0.2);
        }

        .search-icon {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--gray);
        }

        @media (max-width: 768px) {
            .section-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }
            
            .movies-grid {
                grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
                gap: 1rem;
            }
            
            .movie-content {
                padding: 0.75rem;
            }
        }
    </style>
    <script>
        function handleSearch(event) {
            if (event.key === 'Enter') {
                const searchValue = event.target.value.trim();
                window.location.href = '?search=' + encodeURIComponent(searchValue);
            }
        }

        window.onload = function() {
            const urlParams = new URLSearchParams(window.location.search);
            const searchInput = document.querySelector('.search-input');
            if (urlParams.has('search')) {
                searchInput.value = urlParams.get('search');
            }
        }
    </script>
</head>
<body>
    <header class="header">
        <div class="nav-container">
            <div class="logo">
                <i class="fas fa-film"></i>
                <span>MovieMate</span>
            </div>
        </div>
    </header>

    <main class="container">
        <div class="search-bar">
            <i class="fas fa-search search-icon"></i>
            <input type="text" 
                   class="search-input" 
                   placeholder="Search for movies, theaters, actors..." 
                   onkeypress="handleSearch(event)">
        </div>

        <div class="section-header">
            <h1 class="section-title">
                <?php echo $search_query ? "Search Results for '" . htmlspecialchars($search_query) . "'" : "Available Movies"; ?>
            </h1>
        </div>

        <div class="movies-grid">
            <?php
            if ($result->num_rows > 0) {
                while($movie = $result->fetch_assoc()) {
                    $releaseDate = new DateTime($movie['release_date']);
                    $formattedDate = $releaseDate->format('M d, Y');
                    $formattedRuntime = formatRuntime($movie['runtime']);
            ?>
                <div class="movie-card">
                    <div class="movie-image">
                        <img src="<?php echo htmlspecialchars($movie['thumbnail']); ?>" 
                             alt="<?php echo htmlspecialchars($movie['title']); ?>">
                        <?php if ($movie['status'] == 'upcoming'): ?>
                            <div class="upcoming-badge">Coming Soon</div>
                        <?php endif; ?>
                        <?php if (!empty($movie['trailer_url'])): ?>
                            <a href="<?php echo htmlspecialchars($movie['trailer_url']); ?>" target="_blank" class="trailer-btn">
                                <i class="fas fa-play"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                    <div class="movie-content">
                        <div class="movie-meta">
                            <div class="rating">
                                <i class="fas fa-star star"></i>
                                <span><?php echo number_format($movie['ratings'], 1); ?></span>
                            </div>
                            <div class="movie-meta-item">
                                <i class="far fa-calendar-alt"></i>
                                <span><?php echo $formattedDate; ?></span>
                            </div>
                        </div>
                        
                        <h2 class="movie-title"><?php echo htmlspecialchars($movie['title']); ?></h2>
                        
                        <div class="movie-info">
                            <div class="movie-info-item">
                                <i class="fas fa-clock"></i>
                                <span><?php echo $formattedRuntime; ?></span>
                            </div>
                            <div class="movie-info-item">
                                <i class="fas fa-globe"></i>
                                <span><?php echo htmlspecialchars($movie['language']); ?></span>
                            </div>
                        </div>
                        
                        <p class="movie-description"><?php echo htmlspecialchars($movie['description']); ?></p>
                        
                        <div class="movie-footer">
                            <div class="price-container">
                                <div class="price">Rs:<?php echo number_format($movie['price'], 2); ?></div>
                                <?php if ($movie['discount'] > 0): ?>
                                    <div class="discount">Save <?php echo $movie['discount']; ?>%</div>
                                <?php endif; ?>
                            </div>
                            <button class="book-button" onclick="window.location.href='movie-booking.php?id=<?php echo $movie['id']; ?>'">
                                <i class="fas fa-ticket-alt"></i>
                                <?php echo $movie['status'] == 'upcoming' ? 'Pre-book' : 'Book Now'; ?>
                            </button>
                        </div>
                    </div>
                </div>
            <?php
                }
            } else {
                echo "<p>No movies found" . ($search_query ? " matching '" . htmlspecialchars($search_query) . "'" : " at the moment") . ".</p>";
            }
            ?>
        </div>
    </main>

    <?php $conn->close(); ?>
</body>
</html>