<?php
require_once '../includes/auth_check.php';
require_once '../includes/db_connection.php';
include '../includes/header_private.php';

$user_id = $_SESSION['user_id'];

// Check if we're coming from a successful ride posting
$success = isset($_GET['success']) && $_GET['success'] === 'true';
$ride_id = isset($_GET['ride_id']) ? intval($_GET['ride_id']) : 0;

// Fetch latest accepted ride request for autofill including post_ride and available_seats
$selected_ride_id = intval($_GET['request_id'] ?? 0); // comes from dashboard modal if set
$ride_request = null;
try {
    if ($selected_ride_id) {
        $stmt_request = $pdo->prepare("
            SELECT rr.id as ride_id, rr.pickup_location, rr.drop_location, rr.ride_datetime, rr.ride_type,
                   d.id as driver_id, d.name as driver_name, d.car_capacity, rr.post_ride, rr.available_seats
            FROM ride_requests rr
            LEFT JOIN drivers d ON rr.driver_id = d.id
            WHERE rr.user_id = ? AND rr.status = 'accepted' AND rr.id = ?
            LIMIT 1
        ");
        $stmt_request->execute([$user_id, $selected_ride_id]);
    } else {
        // fallback: latest
        $stmt_request = $pdo->prepare("
            SELECT rr.id as ride_id, rr.pickup_location, rr.drop_location, rr.ride_datetime, rr.ride_type,
                   d.id as driver_id, d.name as driver_name, d.car_capacity, rr.post_ride, rr.available_seats
            FROM ride_requests rr
            LEFT JOIN drivers d ON rr.driver_id = d.id
            WHERE rr.user_id = ? AND rr.status = 'accepted'
            ORDER BY rr.ride_datetime DESC
            LIMIT 1
        ");
        $stmt_request->execute([$user_id]);
    }
    $ride_request = $stmt_request->fetch();
} catch (Exception $e) {
    $error = "Unable to load ride details. Please try again.";
}


// Fetch accepted drivers for this user to populate dropdown
$drivers = [];
try {
    $stmt = $pdo->prepare("
        SELECT d.id, d.name, d.car_model, d.car_number, d.car_capacity
        FROM drivers d
        JOIN ride_requests rr ON d.id = rr.driver_id
        WHERE rr.status = 'accepted' AND rr.user_id = ?
        GROUP BY d.id, d.name, d.car_model, d.car_number, d.car_capacity
        ORDER BY d.name ASC
    ");
    $stmt->execute([$user_id]);
    $drivers = $stmt->fetchAll();
} catch (Exception $e) {
    $drivers = [];
    $error = "Could not load drivers list. Please try later.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Post a Ride</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.9.2/dist/confetti.browser.min.js"></script>
    <style>
        .form-container {
            background-color: #ffffff;
            border-radius: 0.5rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .form-row {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1rem;
        }
        @media (min-width: 768px) {
            .form-row {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        .friend-row input,
        .friend-row select {
            min-width: 0;
        }
        .alert-error {
            border-radius: 0.375rem;
            padding: 0.75rem 1rem;
            margin-bottom: 1.5rem;
            font-size: 0.875rem;
        }
        .remove-friend-btn {
            flex-shrink: 0;
        }
        
        /* Success Modal Styles */
        #successModal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.7);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }
        
        #successModal.active {
            display: flex;
        }
        
        .modal-content {
            background-color: white;
            padding: 2rem;
            border-radius: 10px;
            text-align: center;
            max-width: 500px;
            width: 90%;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
            animation: modalAppear 0.5s ease-out;
        }
        
        @keyframes modalAppear {
            0% { transform: scale(0.8); opacity: 0; }
            100% { transform: scale(1); opacity: 1; }
        }
        
        .success-icon {
            font-size: 4rem;
            color: #10B981;
            margin-bottom: 1rem;
        }
        
        .modal-buttons {
            margin-top: 1.5rem;
        }
        
        .confetti-canvas {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: 999;
        }
    </style>
</head>
<body class="bg-gray-50 font-sans">
    <!-- Confetti Canvas -->
    <canvas id="confettiCanvas" class="confetti-canvas"></canvas>
    
    <!-- Success Modal -->
    <div id="successModal">
        <div class="modal-content">
            <div class="success-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <h2 class="text-2xl font-bold text-gray-800 mb-2">Ride Confirmed!</h2>
            <p class="text-gray-600 mb-4">Your ride has been successfully posted and confirmed.</p>
            <div class="modal-buttons">
                <button id="goToDashboard" class="bg-green-600 text-white font-semibold py-2 px-6 rounded hover:bg-green-700 transition duration-200">
                    Go to Dashboard
                </button>
            </div>
        </div>
    </div>

    <section class="form-container max-w-3xl mx-auto px-4 py-8 bg-white shadow-lg rounded-lg mt-8 mb-8">
        <h2 class="text-2xl font-bold text-gray-800 mb-6">Post a New Ride</h2>

        <?php if (!empty($error)): ?>
            <div class="alert alert-error bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6 text-sm">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form action="post_ride_process.php" method="POST" class="post-ride-form space-y-6" id="rideForm">
            <input type="hidden" id="ride_id" name="ride_id" value="<?= $ride_request ? htmlspecialchars($ride_request['ride_id']) : '' ?>">
            <!-- Driver selection -->
            <fieldset class="border-t border-gray-200 pt-6">
                <legend class="text-lg font-semibold text-gray-700 mb-4">Select Driver</legend>
                <div class="form-group mb-4">
                    <label for="driver_id" class="block text-sm font-medium text-gray-600 mb-1">Driver</label>
                    <select id="driver_id" name="driver_id" required class="w-full p-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-600 focus:border-transparent" onchange="updateSeatOptions()">
                        <option value="" selected disabled>-- Select a driver --</option>
                        <?php foreach ($drivers as $driver): ?>
                            <option value="<?= intval($driver['id']) ?>" data-capacity="<?= intval($driver['car_capacity'] ?? 4) ?>" <?= $ride_request && $ride_request['driver_id'] == $driver['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($driver['name']) ?> - <?= htmlspecialchars($driver['car_model'] . ' (' . $driver['car_number'] . ')') ?> (Capacity: <?= intval($driver['car_capacity'] ?? 4) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </fieldset>

            <!-- Ride Details -->
            <fieldset class="border-t border-gray-200 pt-6">
                <legend class="text-lg font-semibold text-gray-700 mb-4">Ride Details</legend>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div class="form-group">
                        <label for="pickup_location" class="block text-sm font-medium text-gray-600 mb-1">Pickup Location</label>
                        <input type="text" id="pickup_location" name="pickup_location" placeholder="Enter pickup location" required class="w-full p-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-600 focus:border-transparent" value="<?= $ride_request ? htmlspecialchars($ride_request['pickup_location']) : '' ?>" />
                    </div>
                    <div class="form-group">
                        <label for="pickup_time" class="block text-sm font-medium text-gray-600 mb-1">Pickup Time</label>
                        <input type="datetime-local" id="pickup_time" name="pickup_time" required class="w-full p-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-600 focus:border-transparent" value="<?= $ride_request ? date('Y-m-d\TH:i', strtotime($ride_request['ride_datetime'])) : '' ?>" />
                    </div>
                    <div class="form-group">
                        <label for="destination" class="block text-sm font-medium text-gray-600 mb-1">Destination</label>
                        <input type="text" id="destination" name="destination" placeholder="Enter the destination" required class="w-full p-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-600 focus:border-transparent" value="<?= $ride_request ? htmlspecialchars($ride_request['drop_location']) : '' ?>" />
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="form-group">
                        <label for="ride_type" class="block text-sm font-medium text-gray-600 mb-1">Ride Type</label>
                        <select id="ride_type" name="ride_type" required class="w-full p-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-600 focus:border-transparent">
                            <option value="drop" <?= !$ride_request || $ride_request['ride_type'] === 'drop' ? 'selected' : '' ?>>Drop</option>
                            <option value="pick" <?= $ride_request && $ride_request['ride_type'] === 'pick' ? 'selected' : '' ?>>Pick</option>
                            <option value="roundtrip" <?= $ride_request && $ride_request['ride_type'] === 'roundtrip' ? 'selected' : '' ?>>Roundtrip</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="available_seats" class="block text-sm font-medium text-gray-600 mb-1">Number of Seats to Offer</label>
                        <select id="available_seats" name="available_seats" required class="w-full p-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-600 focus:border-transparent">
                            <option value="" selected disabled>-- Select seats --</option>
                            <?php
                            // If available_seats already set in ride_request, prefill options up to that number
                            if ($ride_request && intval($ride_request['available_seats']) > 0) {
                                $availableSeats = intval($ride_request['available_seats']);
                                for ($i = 1; $i <= $availableSeats; $i++) {
                                    $selected = ($ride_request['available_seats'] == $i) ? 'selected' : '';
                                    echo "<option value='$i' $selected>$i</option>";
                                }
                            }
                            ?>
                        </select>
                        <p id="remainingSeatsInfo" class="text-xs text-gray-500 mt-1"></p>
                    </div>
                </div>
            </fieldset>

            <!-- Co-travelling friends -->
            <fieldset class="border-t border-gray-200 pt-6">
                <legend class="text-lg font-semibold text-gray-700 mb-4">Co-travelling Friends (Optional)</legend>

                <p class="text-sm text-gray-600 mb-4">Add co-travelling friends' details (Name, Admission No, Gender):</p>

                <div id="friends-container" class="space-y-4">
                    <div class="friend-row flex space-x-4 items-center">
                        <input type="text" name="friends_name[]" placeholder="Friend's Name" class="flex-1 p-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-600 focus:border-transparent" />
                        <input type="text" name="friends_adm_no[]" placeholder="Admission No" class="flex-1 p-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-600 focus:border-transparent" />
                        <select name="friends_gender[]" class="flex-1 p-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-600 focus:border-transparent">
                            <option value="" selected disabled>Gender</option>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                            <option value="Other">Other</option>
                        </select>
                        <button type="button" class="remove-friend-btn ml-2 bg-red-500 text-white font-semibold py-1 px-2 rounded hover:bg-red-600 transition duration-200 text-sm" onclick="removeFriendRow(this)">Remove</button>
                    </div>
                </div>

                <button type="button" id="add-friend-btn" class="mt-4 bg-gray-200 text-gray-800 font-semibold py-2 px-4 rounded hover:bg-gray-300 transition duration-200 text-sm">+ Add Another Friend</button>
            </fieldset>

            <button type="submit" class="mt-6 bg-purple-600 text-white font-semibold py-3 px-6 rounded hover:bg-purple-700 transition duration-200 text-sm w-full md:w-auto">Post Ride</button>
        </form>
    </section>

    <script>
        // Check if we should show the success modal
        <?php if ($success): ?>
        document.addEventListener('DOMContentLoaded', function() {
            showSuccessModal();
        });
        <?php endif; ?>

        // Form handling
        document.getElementById('add-friend-btn').addEventListener('click', function() {
            const container = document.getElementById('friends-container');
            const newFriendRow = document.createElement('div');
            newFriendRow.classList.add('friend-row', 'flex', 'space-x-4', 'items-center');
            newFriendRow.innerHTML = `
                <input type="text" name="friends_name[]" placeholder="Friend's Name" class="flex-1 p-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-600 focus:border-transparent" />
                <input type="text" name="friends_adm_no[]" placeholder="Admission No" class="flex-1 p-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-600 focus:border-transparent" />
                <select name="friends_gender[]" class="flex-1 p-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-600 focus:border-transparent">
                    <option value="" selected disabled>Gender</option>
                    <option value="Male">Male</option>
                    <option value="Female">Female</option>
                    <option value="Other">Other</option>
                </select>
                <button type="button" class="remove-friend-btn ml-2 bg-red-500 text-white font-semibold py-1 px-2 rounded hover:bg-red-600 transition duration-200 text-sm" onclick="removeFriendRow(this)">Remove</button>
            `;
            container.appendChild(newFriendRow);
            updateSeatOptions();
        });

        function removeFriendRow(button) {
            const row = button.closest('.friend-row');
            row.remove();
            updateSeatOptions();
        }

        function updateSeatOptions() {
            const driverSelect = document.getElementById('driver_id');
            const seatSelect = document.getElementById('available_seats');
            const remainingSeatsInfo = document.getElementById('remainingSeatsInfo');
            const form = document.getElementById('rideForm');

            if (!driverSelect.value) {
                seatSelect.innerHTML = '<option value="" selected disabled>-- Select seats --</option>';
                remainingSeatsInfo.textContent = '';
                return;
            }

            const selectedOption = driverSelect.options[driverSelect.selectedIndex];
            const carCapacity = parseInt(selectedOption.getAttribute('data-capacity')) || 4;
            const friendRows = form.querySelectorAll('.friend-row');
            const filledFriends = Array.from(friendRows).filter(row => {
                const name = row.querySelector('input[name="friends_name[]"]').value.trim();
                return name !== '';
            }).length;
            const occupiedSeats = 1 + filledFriends; // 1 for the user + number of friends
            const remainingSeats = Math.max(0, carCapacity - occupiedSeats);

            seatSelect.innerHTML = '<option value="" selected disabled>-- Select seats --</option>';
            for (let i = 1; i <= remainingSeats; i++) {
                const option = document.createElement('option');
                option.value = i;
                option.textContent = i;
                seatSelect.appendChild(option);
            }

            remainingSeatsInfo.textContent = `Remaining seats: ${remainingSeats} (Capacity: ${carCapacity}, Occupied: ${occupiedSeats})`;
        }

        // Success modal and confetti functions
        function showSuccessModal() {
            const modal = document.getElementById('successModal');
            modal.classList.add('active');
            
            // Trigger confetti
            triggerConfetti();
            
            // Set up the button to redirect to dashboard
            document.getElementById('goToDashboard').addEventListener('click', function() {
                window.location.href = 'dashboard.php';
            });
        }

        function triggerConfetti() {
            const canvas = document.getElementById('confettiCanvas');
            const myConfetti = confetti.create(canvas, {
                resize: true,
                useWorker: true
            });
            
            // Confetti configuration
            const confettiConfig = {
                particleCount: 150,
                spread: 70,
                origin: { y: 0.6 },
                colors: ['#10B981', '#3B82F6', '#8B5CF6', '#F59E0B', '#EF4444']
            };
            
            // Multiple bursts of confetti
            myConfetti(confettiConfig);
            
            setTimeout(() => {
                myConfetti({
                    ...confettiConfig,
                    angle: 60,
                    origin: { x: 0, y: 0.6 }
                });
            }, 250);
            
            setTimeout(() => {
                myConfetti({
                    ...confettiConfig,
                    angle: 120,
                    origin: { x: 1, y: 0.6 }
                });
            }, 500);
        }

        document.addEventListener('DOMContentLoaded', function() {
            updateSeatOptions();
            document.querySelectorAll('input[name="friends_name[]"], input[name="friends_adm_no[]"], select[name="friends_gender[]"]').forEach(input => {
                input.addEventListener('input', updateSeatOptions);
            });
        });
    </script>
</body>
</html>

<?php
include '../includes/footer_private.php';
?>