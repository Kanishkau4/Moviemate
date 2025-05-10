<!DOCTYPE html>
<html>
<head>
    <style>
        .card-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 24px;
            padding: 24px;
            max-width: 1200px;
            perspective: 1000px;
        }

        .card {
            background: white;
            border-radius: 12px;
            padding: 32px;
            text-align: center;
            cursor: pointer;
            position: relative;
            transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transform-style: preserve-3d;
            text-decoration: none;
            color: inherit;
        }

        .card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(45deg, transparent, rgba(79, 70, 229, 0.1), transparent);
            transform: translateX(-100%);
            transition: transform 0.6s;
        }

        .card:hover::before {
            transform: translateX(100%);
        }

        .card:hover {
            transform: translateZ(20px) rotateX(5deg);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1),
                       0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }

        .icon {
            width: 64px;
            height: 64px;
            margin: 0 auto 20px;
            transition: transform 0.5s ease;
            position: relative;
        }

        .card:hover .icon {
            transform: scale(1.1) translateY(-5px);
        }

        .add-icon {
            color: #4F46E5;
        }

        .update-icon {
            color: #059669;
        }

        .card-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: #1F2937;
            margin-bottom: 12px;
            position: relative;
            transition: transform 0.3s ease;
        }

        .card:hover .card-title {
            transform: scale(1.05);
        }

        .card-description {
            color: #6B7280;
            font-size: 0.95rem;
            line-height: 1.6;
            opacity: 0.9;
            transition: opacity 0.3s ease;
        }

        .card:hover .card-description {
            opacity: 1;
        }

        .add-icon svg, .update-icon svg {
            width: 100%;
            height: 100%;
            transition: transform 0.5s ease;
        }

        .card:hover .add-icon svg {
            animation: pulse 1.5s infinite;
        }

        .card:hover .update-icon svg {
            animation: rotate 3s infinite linear;
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); }
        }

        @keyframes rotate {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Glow effect on hover */
        .card::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            border-radius: 12px;
            background: radial-gradient(circle at center, 
                                    rgba(79, 70, 229, 0.1) 0%,
                                    transparent 70%);
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .card:hover::after {
            opacity: 1;
        }
    </style>
</head>
<body>
    <div class="card-container">
        <!-- Add Card -->
        <a href="add-item.php" class="card">
            <div class="icon add-icon">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
            </div>
            <h3 class="card-title">Add New</h3>
            <p class="card-description">Create a new entry in the system</p>
        </a>

        <!-- Update Card -->
        <a href="update-items.php" class="card">
            <div class="icon update-icon">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
            </div>
            <h3 class="card-title">Update</h3>
            <p class="card-description">Modify existing entries</p>
        </a>
    </div>
</body>
</html>
