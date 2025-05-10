<!DOCTYPE html>
<html>
<head>
    <style>
        .card-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 30px;
            padding: 30px;
            max-width: 1200px;
            perspective: 2000px;
        }

        .card {
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
        }

        .card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(45deg, 
                rgba(229, 70, 70, 0.1), 
                rgba(79, 70, 229, 0.1));
            opacity: 0;
            transition: opacity 0.5s;
        }

        .card:hover::before {
            opacity: 1;
            animation: gradientMove 3s infinite;
        }

        @keyframes gradientMove {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        .card:hover {
            transform: translateY(-15px) scale(1.02);
            box-shadow: 
                0 20px 40px rgba(0, 0, 0, 0.2),
                0 0 20px rgba(79, 70, 229, 0.2),
                inset 0 0 15px rgba(255, 255, 255, 0.5);
        }

        .icon-container {
            width: 90px;
            height: 90px;
            margin: 0 auto 25px;
            position: relative;
            background: #1F2937;
            border-radius: 15px;
            padding: 18px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
            transition: all 0.5s ease;
            transform-style: preserve-3d;
        }

        .card:hover .icon-container {
            transform: rotateY(180deg);
            background: #4F46E5;
        }

        .icon {
            width: 100%;
            height: 100%;
            color: white;
            transition: transform 0.5s ease;
            backface-visibility: hidden;
            position: absolute;
            top: 0;
            left: 0;
            padding: 18px;
        }

        .icon-back {
            transform: rotateY(180deg);
        }

        /* Film reel animation */
        .reel {
            position: absolute;
            width: 100%;
            height: 100%;
            border-radius: 50%;
            border: 2px dashed rgba(255, 255, 255, 0.3);
            top: 0;
            left: 0;
            opacity: 0;
            transition: opacity 0.3s;
        }

        .card:hover .reel {
            opacity: 1;
            animation: spin 4s linear infinite;
        }

        @keyframes spin {
            100% { transform: rotate(360deg); }
        }

        .card-title {
            font-size: 1.6rem;
            font-weight: 700;
            color: #1F2937;
            margin-bottom: 15px;
            position: relative;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .card:hover .card-title {
            animation: textGlow 1.5s ease infinite alternate;
        }

        @keyframes textGlow {
            from { text-shadow: 0 0 5px rgba(79, 70, 229, 0); }
            to { text-shadow: 0 0 15px rgba(79, 70, 229, 0.5); }
        }

        .card-description {
            color: #6B7280;
            font-size: 1rem;
            line-height: 1.6;
            opacity: 0.9;
            transition: all 0.5s ease;
        }

        .card:hover .card-description {
            opacity: 1;
            transform: scale(1.05);
        }

        /* Film strip decoration */
        .film-strip {
            position: absolute;
            width: 30px;
            height: 100%;
            right: 20px;
            top: 0;
            opacity: 0.1;
            transition: opacity 0.3s;
        }

        .film-hole {
            width: 12px;
            height: 12px;
            background: #1F2937;
            margin: 15px auto;
            border-radius: 50%;
        }

        .card:hover .film-strip {
            opacity: 0.2;
            animation: stripMove 2s infinite;
        }

        @keyframes stripMove {
            0% { transform: translateY(0); }
            100% { transform: translateY(-20px); }
        }
    </style>
</head>
<body>
<div class="card-container">
    <!-- Add Movie Card -->
    <div class="card" onclick="window.location.href='add-movie.php'">
        <div class="icon-container">
            <div class="reel"></div>
            <svg class="icon" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M18 4l2 4h-3l-2-4h-2l2 4h-3l-2-4H8l2 4H7L5 4H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V4h-4zm-6.75 11.25L10 18l-1.25-2.75L6 14l2.75-1.25L10 10l1.25 2.75L14 14l-2.75 1.25zm5.69-3.31L16 14l-.94-2.06L13 11l2.06-.94L16 8l.94 2.06L19 11l-2.06.94z" fill="currentColor"/>
            </svg>
            <svg class="icon icon-back" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z" fill="currentColor"/>
            </svg>
        </div>
        <h3 class="card-title">Add Movie</h3>
        <p class="card-description">Add new movie to the database</p>
        <div class="film-strip">
            <div class="film-hole"></div>
            <div class="film-hole"></div>
            <div class="film-hole"></div>
            <div class="film-hole"></div>
            <div class="film-hole"></div>
        </div>
    </div>

    <!-- Update Movie Card -->
    <div class="card" onclick="window.location.href='edit-movie.php'">
        <div class="icon-container">
            <div class="reel"></div>
            <svg class="icon" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M18 4l2 4h-3l-2-4h-2l2 4h-3l-2-4H8l2 4H7L5 4H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V4h-4zM4 18V6h16v12H4z M12 7v5.5l3.5 2.1-0.7 1.2L10 13V7h2z" fill="currentColor"/>
            </svg>
            <svg class="icon icon-back" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M21 10.12h-6.78l2.74-2.82c-2.73-2.7-7.15-2.8-9.88-.1-2.73 2.71-2.73 7.08 0 9.79s7.15 2.71 9.88 0C18.32 15.65 19 14.08 19 12.1h2c0 1.98-.88 4.55-2.64 6.29-3.51 3.48-9.21 3.48-12.72 0-3.5-3.47-3.53-9.11-.02-12.58s9.14-3.47 12.65 0L21 3v7.12z" fill="currentColor"/>
            </svg>
        </div>
        <h3 class="card-title">Update Movie</h3>
        <p class="card-description">Modify existing movie details</p>
        <div class="film-strip">
            <div class="film-hole"></div>
            <div class="film-hole"></div>
            <div class="film-hole"></div>
            <div class="film-hole"></div>
            <div class="film-hole"></div>
        </div>
    </div>
</div>


</body>
</html>