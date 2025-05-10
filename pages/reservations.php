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
            border-radius: 20px;
            padding: 40px 30px;
            text-align: center;
            cursor: pointer;
            position: relative;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            overflow: hidden;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            text-decoration: none; /* Remove underline */
        }

        .card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(45deg, 
                rgba(255, 99, 71, 0.1), 
                rgba(255, 165, 0, 0.1));
            opacity: 0;
            transition: opacity 0.5s;
        }

        .card:hover::before {
            opacity: 1;
            animation: steamEffect 3s infinite;
        }

        @keyframes steamEffect {
            0% { transform: translateY(0) scale(1); opacity: 0; }
            50% { opacity: 0.5; }
            100% { transform: translateY(-20px) scale(1.1); opacity: 0; }
        }

        .card:hover {
            transform: translateY(-15px) scale(1.02);
            box-shadow: 
                0 20px 40px rgba(0, 0, 0, 0.1),
                0 0 20px rgba(255, 99, 71, 0.2),
                inset 0 0 15px rgba(255, 255, 255, 0.5);
        }

        .icon-container {
            width: 90px;
            height: 90px;
            margin: 0 auto 25px;
            position: relative;
            background: #FF6347;
            border-radius: 50%;
            padding: 18px;
            box-shadow: 0 5px 15px rgba(255, 99, 71, 0.3);
            transition: all 0.5s ease;
        }

        .steam {
            position: absolute;
            width: 8px;
            height: 20px;
            background: rgba(255, 255, 255, 0.4);
            border-radius: 10px;
            top: -20px;
            opacity: 0;
        }

        .card:hover .steam {
            animation: rise 2s infinite;
        }

        @keyframes rise {
            0% { transform: translateY(0) scale(1); opacity: 0; }
            50% { opacity: 0.7; }
            100% { transform: translateY(-20px) scale(1.5); opacity: 0; }
        }

        .icon {
            width: 100%;
            height: 100%;
            color: white;
            transition: transform 0.5s ease;
            position: relative;
        }

        .card:hover .icon {
            animation: bounce 0.5s ease infinite alternate;
        }

        @keyframes bounce {
            from { transform: translateY(0); }
            to { transform: translateY(-5px); }
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
            animation: textWave 2s ease infinite;
            background: linear-gradient(45deg, #FF6347, #FFA500);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        @keyframes textWave {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-5px); }
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

        .plate-decoration {
            position: absolute;
            width: 150px;
            height: 150px;
            border: 2px dashed rgba(255, 99, 71, 0.2);
            border-radius: 50%;
            bottom: -75px;
            right: -75px;
            transition: all 0.5s ease;
        }

        .card:hover .plate-decoration {
            transform: scale(1.2) rotate(45deg);
            border-color: rgba(255, 99, 71, 0.4);
        }

        .spice-dots {
            position: absolute;
            width: 4px;
            height: 4px;
            background: rgba(255, 99, 71, 0.3);
            border-radius: 50%;
        }
    </style>
</head>
<body>
    <div class="card-container">
        <!-- Add Food Card -->
        <a href="add-food.php" class="card">
            <div class="icon-container">
                <div class="steam" style="left: 30%;"></div>
                <div class="steam" style="left: 50%;"></div>
                <div class="steam" style="left: 70%;"></div>
                <svg class="icon" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm5 11h-4v4h-2v-4H7v-2h4V7h2v4h4v2z" fill="currentColor"/>
                    <path d="M15 5c-.83 0-1.5.67-1.5 1.5S14.17 8 15 8s1.5-.67 1.5-1.5S15.83 5 15 5zm-6 0c-.83 0-1.5.67-1.5 1.5S8.17 8 9 8s1.5-.67 1.5-1.5S9.83 5 9 5z" fill="currentColor"/>
                </svg>
            </div>
            <h3 class="card-title">Add Food Item</h3>
            <p class="card-description">Add new dishes to the menu</p>
            <div class="plate-decoration"></div>
            <div class="spice-dots" style="top: 20%; left: 10%;"></div>
            <div class="spice-dots" style="top: 30%; right: 15%;"></div>
            <div class="spice-dots" style="bottom: 25%; left: 20%;"></div>
        </a>

        <!-- Update Food Card -->
        <a href="update-food.php" class="card">
            <div class="icon-container">
                <div class="steam" style="left: 30%;"></div>
                <div class="steam" style="left: 50%;"></div>
                <div class="steam" style="left: 70%;"></div>
                <svg class="icon" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-2 10h-4v4h-2v-4H7v-2h4V7h2v4h4v2z" fill="currentColor"/>
                    <path d="M15 8c-.83 0-1.5.67-1.5 1.5S14.17 11 15 11s1.5-.67 1.5-1.5S15.83 8 15 8zm-6 0c-.83 0-1.5.67-1.5 1.5S8.17 11 9 11s1.5-.67 1.5-1.5S9.83 8 9 8z" fill="currentColor"/>
                </svg>
            </div>
            <h3 class="card-title">Update Food</h3>
            <p class="card-description">Modify existing menu items</p>
            <div class="plate-decoration"></div>
            <div class="spice-dots" style="top: 25%; right: 20%;"></div>
            <div class="spice-dots" style="top: 40%; left: 15%;"></div>
            <div class="spice-dots" style="bottom: 30%; right: 25%;"></div>
        </a>
    </div>
</body>
</html>
