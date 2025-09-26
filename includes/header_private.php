<?php
if (!isset($_SESSION['user_id'])) {
    header('Location: ../public/login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CCP Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css">
    <style>
        /* Reset default styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        /* Global styles */
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background-color: #f5f7fa;
            color: #333;
            line-height: 1.6;
        }

        /* Header styles */
        header {
            background-color: #ffffff;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .nav-container {
            max-width: 80rem;
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1rem 1.5rem;
        }

        .logo img {
            height: 2.5rem;
            transition: transform 0.2s ease;
        }

        .logo img:hover {
            transform: scale(1.05);
        }

        .nav-links {
            display: flex;
            align-items: center;
            gap: 2rem;
        }

        .nav-links a {
            font-size: 0.9rem;
            font-weight: 500;
            color: #4b5563;
            transition: color 0.2s ease-in-out;
        }

        .nav-links a:hover {
            color: a47bb9;
        }

        .logout-btn {
            background: linear-gradient(to right, a47bb9, #7d5a9b);
            padding: 0.5rem 1.5rem;
            border-radius: 9999px;
            color: #ffffff;
            font-weight: 500;
            font-size: 0.9rem;
            transition: background 0.3s ease;
        }

        .logout-btn:hover {
            background: linear-gradient(to right, #f7c8d9, a47bb9);
        }

        .mobile-menu {
            display: none;
            cursor: pointer;
        }

        /* Main content */
        main {
            max-width: 80rem;
            margin: 0 auto;
            padding: 2rem 1.5rem;
        }

        /* Responsive design */
        @media (max-width: 768px) {
            .nav-links {
                display: none;
                flex-direction: column;
                position: absolute;
                top: 4rem;
                left: 0;
                right: 0;
                background: #ffffff;
                box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
                padding: 1.5rem;
                z-index: 50;
            }

            .nav-links.active {
                display: flex;
                gap: 1rem;
            }

            .mobile-menu {
                display: block;
            }

            .mobile-menu i {
                font-size: 1.5rem;
                color: #4b5563;
            }

            main {
                padding: 1.5rem 1rem;
            }
        }
    </style>
</head>
<body>
    <header>
        <div class="nav-container">
            <div class="logo">
                <a href="dashboard.php" title="CCP Ride Sharing">
                    <img src="../assets/images/Car_logo.png" alt="CCP Logo" class="h-12">
                </a>
            </div>
            <nav class="nav-links" id="navLinks">
                <a href="dashboard.php" class="font-medium">Dashboard</a>
                <a href="post_ride.php" class="font-medium">Post Ride</a>
                <a href="search_ride.php" class="font-medium">Search Rides</a>
                <a href="drivers.php" class="font-medium">Drivers</a>  
                <a href="../public/logout.php" class="logout-btn">Logout</a>
            </nav>
            <button class="mobile-menu md:hidden" id="mobileMenuBtn">
                <i class="fas fa-bars"></i>
            </button>
        </div>
    </header>
    <main>
        <!-- Main content will be included here -->
    </main>
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
    <script>
        document.getElementById('mobileMenuBtn').addEventListener('click', () => {
            const navLinks = document.getElementById('navLinks');
            navLinks.classList.toggle('active');
        });
    </script>
</body>
</html>