<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Charts</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --primary-color: #4361ee;
            --secondary-color: #3f37c9;
            --text-color: #2b2d42;
            --background-color: #ffffff;
            --card-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }

        body {
            margin: 0;
            padding: 20px;
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            background-color: var(--background-color);
            color: var(--text-color);
            line-height: 1.6;
        }

        .dashboard-container {
            margin-left: 260px;
            padding: 20px;
            max-width: 1440px;
            margin: 0 auto;
        }

        .chart-card {
            background: white;
            border-radius: 16px;
            box-shadow: var(--card-shadow);
            padding: 24px;
            margin-bottom: 24px;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .chart-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 12px -2px rgba(0, 0, 0, 0.15);
        }

        .chart-card h2 {
            margin: 0 0 24px 0;
            color: var(--text-color);
            font-size: 20px;
            font-weight: 600;
            display: flex;
            align-items: center;
            padding-bottom: 12px;
            border-bottom: 2px solid #e9ecef;
        }

        .chart-container {
            position: relative;
            height: 350px;
            width: 100%;
            padding: 12px;
        }

        .chart-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(450px, 1fr));
            gap: 24px;
            padding: 12px;
        }

        canvas {
            border-radius: 8px;
            background-color: white;
        }

        @media (max-width: 768px) {
            .dashboard-container {
                margin-left: 0;
                padding: 12px;
            }

            .chart-grid {
                grid-template-columns: 1fr;
                gap: 16px;
            }

            .chart-card {
                padding: 16px;
            }

            .chart-container {
                height: 300px;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <div class="chart-grid">
            <div class="chart-card">
                <h2>Movie Bookings</h2>
                <div class="chart-container">
                    <canvas id="moviesChart"></canvas>
                </div>
            </div>

            <div class="chart-card">
                <h2>Food Orders</h2>
                <div class="chart-container">
                    <canvas id="foodOrdersChart"></canvas>
                </div>
            </div>

            <div class="chart-card">
                <h2>Item Orders</h2>
                <div class="chart-container">
                    <canvas id="itemsOrdersChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <script>
        async function fetchDataAndRenderCharts() {
            const response = await fetch('database_config.php');
            const data = await response.json();

            // Vibrant color palette
            const vibrantColors = {
                backgrounds: [
                    'rgba(255, 99, 132, 0.6)',   // Red
                    'rgba(54, 162, 235, 0.6)',   // Blue
                    'rgba(255, 206, 86, 0.6)',   // Yellow
                    'rgba(75, 192, 192, 0.6)',  // Teal
                    'rgba(153, 102, 255, 0.6)',  // Purple
                    'rgba(255, 159, 64, 0.6)'   // Orange
                ],
                borders: [
                    'rgba(255, 99, 132, 1)',     // Red
                    'rgba(54, 162, 235, 1)',     // Blue
                    'rgba(255, 206, 86, 1)',     // Yellow
                    'rgba(75, 192, 192, 1)',     // Teal
                    'rgba(153, 102, 255, 1)',    // Purple
                    'rgba(255, 159, 64, 1)'      // Orange
                ]
            };

            // Movie Bookings Chart (Doughnut Chart)
            const moviesChartCtx = document.getElementById('moviesChart').getContext('2d');
            new Chart(moviesChartCtx, {
                type: 'doughnut',
                data: {
                    labels: data.movieBookings.map(booking => booking.movie_name),
                    datasets: [{
                        label: 'Movie Bookings Count',
                        data: data.movieBookings.map(booking => booking.booked_count),
                        backgroundColor: vibrantColors.backgrounds,
                        borderColor: vibrantColors.borders,
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: {
                        duration: 1500,
                        easing: 'easeInOutCirc'
                    },
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });

            // Food Orders Chart (Bar Chart with Food Names)
            const foodOrdersChartCtx = document.getElementById('foodOrdersChart').getContext('2d');
            new Chart(foodOrdersChartCtx, {
                type: 'bar',
                data: {
                    labels: data.foodOrders.map(order => order.food_name),
                    datasets: [{
                        label: 'Food Orders Count',
                        data: data.foodOrders.map(order => order.quantity),
                        backgroundColor: vibrantColors.backgrounds,
                        borderColor: vibrantColors.borders,
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Order Count'
                            }
                        },
                        x: {
                            title: {
                                display: true,
                                text: 'Food Items'
                            }
                        }
                    },
                    animation: {
                        duration: 1200,
                        easing: 'easeOutQuart'
                    }
                }
            });

            // Items Orders Chart (Line Chart)
            const itemsOrdersChartCtx = document.getElementById('itemsOrdersChart').getContext('2d');
            new Chart(itemsOrdersChartCtx, {
                type: 'line',
                data: {
                    labels: data.itemsOrders.map(order => order.item_name),
                    datasets: [{
                        label: 'Item Orders Quantity',
                        data: data.itemsOrders.map(order => order.quantity),
                        borderColor: vibrantColors.borders[1],
                        backgroundColor: vibrantColors.backgrounds[1],
                        borderWidth: 3,
                        tension: 0.4,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        x: {
                            title: {
                                display: true,
                                text: 'Items'
                            }
                        },
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Quantity'
                            }
                        }
                    },
                    animation: {
                        duration: 2000,
                        easing: 'easeInOutExpo'
                    }
                }
            });
        }

        fetchDataAndRenderCharts();
    </script>
</body>
</html>