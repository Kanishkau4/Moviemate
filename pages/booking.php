<!DOCTYPE html>
<html>
<head>
    <style>
        .card-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 40px;
            padding: 40px;
            max-width: 1300px;
            perspective: 2500px;
            background: linear-gradient(135deg, #f5f7fa, #e4e9f2);
        }

        .card {
            background: linear-gradient(145deg, #ffffff, #f0f2f5);
            border-radius: 20px;
            padding: 40px;
            text-align: center;
            cursor: pointer;
            position: relative;
            transition: all 0.6s cubic-bezier(0.23, 1, 0.32, 1);
            overflow: hidden;
            border: 2px solid rgba(66, 153, 225, 0.2);
            box-shadow: 0 0 20px rgba(66, 153, 225, 0.1);
        }

        .card::before {
            content: '';
            position: absolute;
            inset: -100px;
            background: radial-gradient(circle at center, 
                rgba(66, 153, 225, 0.2) 0%, 
                transparent 70%);
            opacity: 0;
            transition: all 0.6s ease;
            transform: rotate(45deg);
        }

        .card:hover::before {
            opacity: 1;
            transform: rotate(0deg) scale(1.5);
        }

        .card:hover {
            transform: translateY(-20px) rotateX(10deg);
            box-shadow: 
                0 30px 60px rgba(0, 0, 0, 0.1),
                0 0 40px rgba(66, 153, 225, 0.2),
                inset 0 0 20px rgba(66, 153, 225, 0.1);
        }

        .icon-container {
            width: 120px;
            height: 120px;
            margin: 0 auto 30px;
            position: relative;
            border-radius: 50%;
            padding: 20px;
            background: linear-gradient(45deg, #4299e1, #63b3ed);
            transition: all 0.6s ease;
            transform-style: preserve-3d;
        }

        .card:hover .icon-container {
            transform: rotateY(360deg) scale(1.1);
            box-shadow: 0 0 30px rgba(66, 153, 225, 0.4);
        }

        .icon {
            width: 100%;
            height: 100%;
            color: white;
            position: absolute;
            padding: 20px;
            transition: all 0.6s ease;
        }

        /* Holographic effect */
        .holo-effect {
            position: absolute;
            width: 200%;
            height: 200%;
            top: -50%;
            left: -50%;
            background: linear-gradient(
                45deg,
                transparent,
                rgba(255, 255, 255, 0.3),
                transparent
            );
            opacity: 0;
            transform: rotate(45deg);
        }

        .card:hover .holo-effect {
            opacity: 0.5;
            animation: holoScan 2s infinite linear;
        }

        @keyframes holoScan {
            0% { transform: translateY(-100%) rotate(45deg); }
            100% { transform: translateY(100%) rotate(45deg); }
        }

        /* Ticket stub effect */
        .ticket-stub {
            position: absolute;
            width: 40px;
            height: 100%;
            left: 0;
            top: 0;
            background: repeating-linear-gradient(
                45deg,
                rgba(66, 153, 225, 0.2),
                rgba(66, 153, 225, 0.2) 10px,
                transparent 10px,
                transparent 20px
            );
            opacity: 0.3;
            transition: all 0.3s ease;
        }

        .card:hover .ticket-stub {
            opacity: 0.6;
            width: 50px;
        }

        .card-title {
            font-size: 2rem;
            font-weight: 800;
            color: #2d3748;
            margin-bottom: 20px;
            text-transform: uppercase;
            letter-spacing: 2px;
            position: relative;
            text-shadow: 0 0 5px rgba(66, 153, 225, 0.3);
        }

        .card:hover .card-title {
            animation: lightFlicker 1.5s infinite alternate;
        }

        @keyframes lightFlicker {
            0% { text-shadow: 0 0 5px rgba(66, 153, 225, 0.3); }
            50% { text-shadow: 0 0 15px rgba(66, 153, 225, 0.6), 0 0 25px rgba(66, 153, 225, 0.3); }
            100% { text-shadow: 0 0 5px rgba(66, 153, 225, 0.3); }
        }

        .card-description {
            color: #4a5568;
            font-size: 1.1rem;
            line-height: 1.8;
            padding: 0 20px;
            transition: all 0.5s ease;
            position: relative;
        }

        .card:hover .card-description {
            color: #2d3748;
            transform: translateY(-5px);
            text-shadow: 0 0 10px rgba(66, 153, 225, 0.2);
        }
    </style>
</head>
<body>
<div class="card-container">
    <div class="card" onclick="window.location.href='view-bookings.php'">
        <div class="icon-container">
            <div class="holo-effect"></div>
            <svg class="icon" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm-1-13h2v6h-2zm0 8h2v2h-2z" fill="currentColor"/>
            </svg>
        </div>
        <div class="ticket-stub"></div>
        <h3 class="card-title">View Bookings</h3>
        <p class="card-description">Access and manage your cinematic reservations in style</p>
    </div>
</div>
</body>
</html>