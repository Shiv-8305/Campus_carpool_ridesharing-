<?php
ob_start(); // Prevent unwanted output
require_once '../includes/auth_check.php';
require_once '../includes/db_connection.php';
include '../includes/header_private.php';

$userId = $_SESSION['user_id'] ?? null;
$driver_id = filter_input(INPUT_GET, 'driver_id', FILTER_VALIDATE_INT) ?? 0;

if ($driver_id <= 0) {
    echo '<div class="max-w-4xl mx-auto p-4"><div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg">Invalid Driver ID.</div></div>';
    include '../includes/footer_private.php';
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT * FROM drivers WHERE id = ?");
    $stmt->execute([$driver_id]);
    $driver = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$driver) {
        echo '<div class="max-w-4xl mx-auto p-4"><div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg">Driver not found.</div></div>';
        include '../includes/footer_private.php';
        exit;
    }
} catch (Exception $ex) {
    echo '<div class="max-w-4xl mx-auto p-4"><div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg">Unable to load driver info.</div></div>';
    include '../includes/footer_private.php';
    exit;
}

$photo_path = !empty($driver['photo_path']) && file_exists("../assets/images/{$driver['photo_path']}")
    ? "../assets/images/{$driver['photo_path']}"
    : "../assets/images/driver.png";
$outstation_status = ($driver['outstation'] == 'Yes' || $driver['outstation'] == 1) ? "Yes" : "No";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['book_ride'])) {
    ob_clean(); // Clear output buffer to avoid corrupting JSON response
    $ride_date = filter_input(INPUT_POST, 'ride_date', FILTER_SANITIZE_STRING) ?? '';
    $ride_time = filter_input(INPUT_POST, 'ride_time', FILTER_SANITIZE_STRING) ?? '';
    $ride_type_ui = filter_input(INPUT_POST, 'ride_type', FILTER_SANITIZE_STRING) ?? '';
    $pickup_location = trim(filter_input(INPUT_POST, 'pickup_location', FILTER_SANITIZE_STRING) ?? '');
    $drop_location = trim(filter_input(INPUT_POST, 'drop_location', FILTER_SANITIZE_STRING) ?? '');

    $ride_datetime = $ride_date && $ride_time ? $ride_date . " " . $ride_time . ":00" : "";

    $ride_type = match ($ride_type_ui) {
        'one_way' => 'drop',
        'round_trip' => 'roundtrip',
        default => null,
    };

    if (!$userId || !$ride_datetime || !$ride_type || !$pickup_location || !$drop_location) {
        $response = [
            'status' => 'error',
            'message' => 'Please fill out all required fields and select valid locations.'
        ];
    } else {
        try {
            $pdo->beginTransaction();
            $stmt = $pdo->prepare(
                "INSERT INTO ride_sharing_db.ride_requests (user_id, driver_id, ride_datetime, ride_type, pickup_location, drop_location, status, notification_sent, request_time) 
                VALUES (?, ?, ?, ?, ?, ?, 'waiting', 1, NOW())"
            );
            $success = $stmt->execute([
                $userId, $driver_id, $ride_datetime, $ride_type, $pickup_location, $drop_location
            ]);

            if ($success) {
                $request_id = $pdo->lastInsertId();
                $verify_stmt = $pdo->prepare("SELECT notification_sent FROM ride_sharing_db.ride_requests WHERE id = ?");
                $verify_stmt->execute([$request_id]);
                $result = $verify_stmt->fetch(PDO::FETCH_ASSOC);

                if ($result && $result['notification_sent'] == 1) {
                    $pdo->commit();
                    $response = [
                        'status' => 'success',
                        'message' => 'Request sent, you can view your status on dashboard'
                    ];
                } else {
                    $pdo->rollBack();
                    $response = [
                        'status' => 'error',
                        'message' => 'Failed to confirm ride request notification. Please try again.'
                    ];
                }
            } else {
                $pdo->rollBack();
                $response = [
                    'status' => 'error',
                    'message' => 'Failed to send ride request. Please try again.'
                ];
            }
        } catch (Exception $ex) {
            $pdo->rollBack();
            $response = [
                'status' => 'error',
                'message' => 'An error occurred while processing your request. Please try again.'
            ];
        }
    }

    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Driver Profile - CCP Ride Sharing</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet" />
    <style>
        /* Add your CSS styles here (same as before) */
        /* Example styles (repeat from previous code) */
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: #f7f7f7;
        }
        .osm-suggestions {
            position: absolute;
            z-index: 50;
            width: 100%;
            max-height: 200px;
            overflow-y: auto;
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 0.5rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
        .osm-suggestions li {
            padding: 0.75rem 1rem;
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: #6a4981;
        }
        .osm-suggestions li:hover {
            background: #f7c8d9;
            color: #6a4981;
        }
        .osm-suggestions .loc-meta {
            font-size: 0.875rem;
            color: #7d5a9b;
        }
        .btn-primary {
            background-color: #7d5a9b;
            color: #f7c8d9;
            transition: background-color 0.3s ease, transform 0.2s ease;
        }
        .btn-primary:hover {
            background-color: #6a4981;
            transform: translateY(-1px);
        }
        .btn-primary:disabled {
            background-color: #a3a3a3;
            cursor: not-allowed;
        }
        .profile-card {
            background-color: white;
            border: 1px solid #e5e7eb;
            border-radius: 1rem;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }
        .form-section {
            background-color: white;
            border: 1px solid #e5e7eb;
            border-radius: 1rem;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }
        .alert-box {
            transition: opacity 0.3s ease;
        }
    </style>
</head>
<body>
<div class="container mx-auto px-4 py-8 max-w-5xl">
    <!-- Driver Profile -->
    <div class="profile-card p-6 md:flex gap-8">
        <div class="flex-shrink-0">
            <img src="<?= htmlspecialchars($photo_path) ?>" alt="Photo of <?= htmlspecialchars($driver['name']) ?>"
                 class="w-48 h-48 md:w-64 md:h-64 rounded-full object-cover border-4 border-[#f7c8d9] shadow-lg" />
        </div>
        <div class="flex-1 mt-6 md:mt-0">
            <h2 class="text-3xl font-bold text-[#6a4981] mb-6"><?= htmlspecialchars($driver['name']) ?></h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-3 text-[#6a4981]">
                    <p><span class="font-semibold">Phone:</span> <a href="tel:<?= htmlspecialchars($driver['phone']) ?>"
                        class="text-[#7d5a9b] hover:underline"><?= htmlspecialchars($driver['phone']) ?></a></p>
                    <p><span class="font-semibold">Aadhar:</span> <?= htmlspecialchars($driver['aadhar']) ?></p>
                    <p><span class="font-semibold">License:</span> <?= htmlspecialchars($driver['license']) ?></p>
                    <p><span class="font-semibold">Languages:</span> <?= htmlspecialchars($driver['languages']) ?></p>
                </div>
                <div class="space-y-3 text-[#6a4981]">
                    <p><span class="font-semibold">Car Model:</span> <?= htmlspecialchars($driver['car_model']) ?></p>
                    <p><span class="font-semibold">Seating:</span> <?= intval($driver['car_capacity']) ?></p>
                    <p><span class="font-semibold">Car Number:</span> <?= htmlspecialchars($driver['car_number']) ?></p>
                    <p><span class="font-semibold">Outstation:</span> <?= $outstation_status ?></p>
                    <p><span class="font-semibold">Price / KM:</span> ₹<?= number_format($driver['price_per_km'], 2) ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Booking Form -->
    <section id="sendRequestForm" class="form-section mt-12 p-8">
        <h3 class="text-2xl font-bold text-[#6a4981] text-center mb-8">Book Your Ride</h3>
        <form method="POST" id="bookingForm" class="space-y-6 max-w-md mx-auto" autocomplete="off">
            <div>
                <label for="ride_date" class="block text-sm font-medium text-[#6a4981]">Date</label>
                <input type="date" name="ride_date" id="ride_date" required
                       min="<?= date('Y-m-d') ?>"
                       class="mt-1 block w-full rounded-md border-[#e5e7eb] shadow-sm focus:border-[#7d5a9b] focus:ring-[#7d5a9b] text-[#6a4981]" />
            </div>
            <div>
                <label for="ride_time" class="block text-sm font-medium text-[#6a4981]">Time</label>
                <input type="time" name="ride_time" id="ride_time" required
                       class="mt-1 block w-full rounded-md border-[#e5e7eb] shadow-sm focus:border-[#7d5a9b] focus:ring-[#7d5a9b] text-[#6a4981]" />
            </div>
            <div>
                <label for="ride_type" class="block text-sm font-medium text-[#6a4981]">Ride Type</label>
                <select name="ride_type" id="ride_type" required
                        class="mt-1 block w-full rounded-md border-[#e5e7eb] shadow-sm focus:border-[#7d5a9b] focus:ring-[#7d5a9b] text-[#6a4981]">
                    <option value="one_way">One Way</option>
                    <option value="round_trip">Round Trip</option>
                </select>
            </div>
            <div class="relative">
                <label for="pickup_location" class="block text-sm font-medium text-[#6a4981]">Pickup Location</label>
                <input type="text" id="pickup_location" name="pickup_location" placeholder="Enter pickup location"
                       autocomplete="off" required
                       class="mt-1 block w-full rounded-md border-[#e5e7eb] shadow-sm focus:border-[#7d5a9b] focus:ring-[#7d5a9b] text-[#6a4981]" />
                <ul id="pickup_suggestions" class="osm-suggestions hidden"></ul>
            </div>
            <div class="relative">
                <label for="drop_location" class="block text-sm font-medium text-[#6a4981]">Drop Location</label>
                <input type="text" id="drop_location" name="drop_location" placeholder="Enter drop location"
                       autocomplete="off" required
                       class="mt-1 block w-full rounded-md border-[#e5e7eb] shadow-sm focus:border-[#7d5a9b] focus:ring-[#7d5a9b] text-[#6a4981]" />
                <ul id="drop_suggestions" class="osm-suggestions hidden"></ul>
            </div>
            <button type="submit" name="book_ride" id="bookRideBtn"
                    class="w-full btn-primary font-semibold py-3 rounded-md disabled:opacity-50 disabled:cursor-not-allowed">
                <i class="fas fa-paper-plane mr-2"></i> Send Request
            </button>
            <div id="booking-alert" class="alert-box hidden text-center p-4 rounded-lg"></div>
        </form>
    </section>
</div>

<script>
function setupAutocomplete(inputId, suggId) {
    const input = document.getElementById(inputId);
    const suggBox = document.getElementById(suggId);
    let selectedCoords = null;

    input.addEventListener('input', async () => {
        selectedCoords = null;
        const val = input.value.trim();
        suggBox.classList.add('hidden');
        if (val.length < 3) return;

        try {
            const res = await fetch(`https://photon.komoot.io/api/?q=${encodeURIComponent(val)}&limit=7`);
            const data = await res.json();
            suggBox.innerHTML = '';
            suggBox.classList.remove('hidden');

            if (!data.features) return;
            data.features.forEach(f => {
                const li = document.createElement('li');
                let meta = [];
                if (f.properties.city) meta.push(f.properties.city);
                if (f.properties.state) meta.push(f.properties.state);
                if (f.properties.country) meta.push(f.properties.country);
                li.innerHTML = `<span>${f.properties.label || f.properties.name || ""}</span><span class="loc-meta">${meta.length ? meta.join(", ") : ""}</span>`;
                li.classList.add('text-[#6a4981]', 'hover:bg-[#f7c8d9]');
                li.tabIndex = 0;
                li.addEventListener('mousedown', () => {
                    input.value = (f.properties.label || f.properties.name || "") + (meta.length ? ` (${meta.join(", ")})` : "");
                    selectedCoords = f.geometry.coordinates.reverse();
                    suggBox.classList.add('hidden');
                });
                suggBox.appendChild(li);
            });
        } catch (error) {
            console.error('Error fetching suggestions:', error);
        }
    });

    input.addEventListener('blur', () => setTimeout(() => suggBox.classList.add('hidden'), 200));
    return () => selectedCoords;
}

const pickupCoordsGetter = setupAutocomplete('pickup_location', 'pickup_suggestions');
const dropCoordsGetter = setupAutocomplete('drop_location', 'drop_suggestions');

document.getElementById('bookingForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const alertBox = document.getElementById('booking-alert');
    const submitBtn = document.getElementById('bookRideBtn');
    alertBox.classList.add('hidden');
    submitBtn.disabled = true;
    submitBtn.textContent = 'Sending...';

    if (!pickupCoordsGetter() || !dropCoordsGetter()) {
        alertBox.textContent = 'Please select valid locations from the suggestions.';
        alertBox.className = 'alert-box bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg';
        alertBox.classList.remove('hidden');
        submitBtn.disabled = false;
        submitBtn.textContent = 'Send Request';
        return;
    }

    try {
        const formData = new FormData(this);
        formData.append('book_ride', '1');
        const res = await fetch('', { method: 'POST', body: formData });
        const raw = await res.text();
        console.log('Raw response:', raw);
        const data = JSON.parse(raw);

        alertBox.textContent = data.message;
        alertBox.classList.remove('hidden');

        if (data.status === 'success') {
            alertBox.className = 'alert-box bg-[#f7c8d9] border border-[#7d5a9b] text-[#6a4981] px-4 py-3 rounded-lg';
            this.reset();
        } else {
            alertBox.className = 'alert-box bg-red-100 border border-red-400 text-green-700 px-4 py-3 rounded-lg';
        }
    } catch (error) {
        alertBox.textContent = 'An error occurred. Please try again.';
        alertBox.className = 'alert-box bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg';
        alertBox.classList.remove('hidden');
    } finally {
        submitBtn.disabled = false;
        submitBtn.textContent = 'Send Request';
    }
});
</script>

<?php include '../includes/footer_private.php'; ?>
</body>
</html>
