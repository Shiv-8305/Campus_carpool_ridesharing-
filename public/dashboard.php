<?php
require_once '../includes/auth_check.php';
require_once '../includes/db_connection.php';
include '../includes/header_private.php';

$user_id = $_SESSION['user_id'];

try {
    // Fetch ride requests grouped by pickup, drop, date, and time
    $stmt_requests = $pdo->prepare("
        SELECT rr.id, rr.pickup_location, rr.drop_location, rr.ride_datetime, 
               rr.status, d.name as driver_name, d.id as driver_id,
               DATE(rr.ride_datetime) as ride_date,
               TIME(rr.ride_datetime) as ride_time
        FROM ride_requests rr
        LEFT JOIN drivers d ON rr.driver_id = d.id
        WHERE rr.user_id = ?
        ORDER BY rr.ride_datetime DESC, rr.pickup_location, rr.drop_location
    ");
    $stmt_requests->execute([$user_id]);
    $ride_requests = $stmt_requests->fetchAll();

    // Group requests by pickup, drop, date, and time
    $grouped_requests = [];
    foreach ($ride_requests as $request) {
        $key = $request['pickup_location'] . '|' . $request['drop_location'] . '|' . $request['ride_date'] . '|' . $request['ride_time'];
        if (!isset($grouped_requests[$key])) {
            $grouped_requests[$key] = [
                'pickup_location' => $request['pickup_location'],
                'drop_location' => $request['drop_location'],
                'ride_date' => $request['ride_date'],
                'ride_time' => $request['ride_time'],
                'ride_datetime' => $request['ride_datetime'],
                'drivers' => []
            ];
        }
        $grouped_requests[$key]['drivers'][] = [
            'id' => $request['id'],
            'driver_id' => $request['driver_id'],
            'driver_name' => $request['driver_name'],
            'status' => $request['status']
        ];
    }

    // Fetch bookings with post_ride = 'yes'
    $stmt_bookings = $pdo->prepare("
        SELECT rr.id, rr.pickup_location, rr.drop_location, rr.ride_datetime, 
               rr.status, d.name as driver_name, d.id as driver_id, rr.post_ride,
               DATE(rr.ride_datetime) as ride_date,
               TIME(rr.ride_datetime) as ride_time
        FROM ride_requests rr
        LEFT JOIN drivers d ON rr.driver_id = d.id
        WHERE rr.user_id = ? AND rr.post_ride = 'yes'
        ORDER BY rr.ride_datetime DESC
    ");
    $stmt_bookings->execute([$user_id]);
    $bookings = $stmt_bookings->fetchAll();
} catch (Exception $e) {
    $error = "Unable to load your ride requests. Error: " . $e->getMessage();
    error_log("Dashboard error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>User Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet" />
    <style>
        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
        }
        body {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
        #page-content {
            flex: 1 0 auto;
        }
        footer {
            flex-shrink: 0;
        }
        .status-confirmed { background-color: #28a745; color: white; }
        .status-waiting { background-color: #ffc107; color: #312600; }
        .status-rejected { background-color: #dc3545; color: white; }
        .driver-option {
            padding: 6px 10px;
            margin: 2px 0;
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.3s ease;
            border: 1px solid transparent;
            font-size: 0.875rem;
        }
        .driver-option:hover { background-color: #f8f9fa; }
        .driver-option.selected {
            border-color: #007bff;
            background-color: #e7f3ff;
        }
        .driver-confirmed { color: #28a745; font-weight: 600; }
        .driver-waiting { color: #ffc107; }
        .driver-rejected { color: #dc3545; }
        .table-responsive { overflow-x: auto; }
        .compact-table { font-size: 0.875rem; }
        .compact-table th,
        .compact-table td { padding: 0.5rem 0.75rem; }
        .status-badge { font-size: 0.75rem; padding: 2px 6px; }
        .driver-toggle { max-width: 200px; }
        #modalAlert {
            display: none;
        }
        #modalAlert:not(.hidden) {
            display: flex !important;
        }
        .booking-card {
            border-left: 4px solid #10b981;
        }
    </style>
</head>
<body class="bg-gray-50 font-sans">
  <div id="page-content" class="container mx-auto px-2 py-6 max-w-7xl">
    <!-- Header Section -->
    <div class="text-center mb-8">
      <h1 class="text-2xl font-bold text-gray-800">Welcome, <?= htmlspecialchars($_SESSION['user_name']) ?>!</h1>
      <p class="text-gray-600 mt-1 text-sm">Manage your ride requests and bookings</p>
    </div>

    <!-- Action Button -->
    <div class="flex justify-end mb-6">
      <a href="post_ride.php" class="bg-purple-600 text-white font-semibold py-2 px-4 rounded hover:bg-purple-700 transition duration-200 flex items-center text-sm">
        <i class="fas fa-plus mr-1"></i> Post New Ride
      </a>
    </div>

    <!-- Error Alert -->
    <?php if (isset($error)): ?>
      <div class="bg-red-100 border border-red-400 text-red-700 px-3 py-2 rounded mb-6 text-center text-sm">
        <?= htmlspecialchars($error) ?>
      </div>
    <?php endif; ?>
    
    <!-- In-Page Centered Modal Alert -->
    <div id="modalAlert" class="fixed inset-0 flex items-center justify-center bg-gray-800 bg-opacity-40 z-50 hidden">
      <div class="bg-white rounded-lg shadow-lg px-8 py-7 text-center w-full max-w-sm mx-auto">
        <div id="modalAlertText" class="text-lg font-semibold mb-5 text-gray-800"></div>
        <div class="flex justify-center gap-6">
          <button id="modalAlertOk" class="px-5 py-2 rounded bg-green-600 text-white font-bold hover:bg-green-700">OK</button>
          <button id="modalAlertCancel" class="px-5 py-2 rounded bg-gray-300 text-gray-700 font-bold hover:bg-gray-400">Cancel</button>
        </div>
      </div>
    </div>

    <!-- Bookings Section -->
    <section class="mb-8">
      <h2 class="text-xl font-bold text-gray-800 mb-4">Your Bookings</h2>
      <?php if (empty($bookings)): ?>
        <div class="bg-white rounded shadow p-6 text-center">
          <i class="fas fa-calendar-check text-3xl text-gray-400 mb-3"></i>
          <p class="text-gray-600 text-sm">No confirmed bookings yet.</p>
          <p class="text-gray-500 text-xs mt-1">Your posted rides will appear here once confirmed.</p>
        </div>
      <?php else: ?>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
          <?php foreach ($bookings as $booking): 
            $date = date('d M Y', strtotime($booking['ride_date']));
            $time = date('h:i A', strtotime($booking['ride_time']));
          ?>
            <div class="booking-card bg-white rounded-lg shadow p-4">
              <div class="flex justify-between items-start mb-3">
                <div>
                  <h3 class="font-semibold text-gray-800"><?= htmlspecialchars($booking['pickup_location']) ?> to <?= htmlspecialchars($booking['drop_location']) ?></h3>
                  <p class="text-sm text-gray-600"><?= htmlspecialchars($date) ?> at <?= htmlspecialchars($time) ?></p>
                </div>
                <span class="bg-green-100 text-green-800 text-xs font-medium px-2.5 py-0.5 rounded">Confirmed</span>
              </div>
              
              <div class="flex items-center mb-3">
                <i class="fas fa-user text-blue-500 mr-2"></i>
                <span class="text-sm text-gray-700">Driver: <?= htmlspecialchars($booking['driver_name'] ?: 'Unknown') ?></span>
              </div>
              
              <div class="flex justify-between items-center">
                <span class="text-xs text-gray-500">Booking ID: #<?= htmlspecialchars($booking['id']) ?></span>
                <button class="text-xs bg-blue-100 text-blue-700 hover:bg-blue-200 px-2 py-1 rounded transition duration-200">
                  View Details
                </button>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </section>

    <!-- Ride Requests Table -->
    <section class="mb-8">
      <h2 class="text-xl font-bold text-gray-800 mb-4">Your Ride Requests</h2>
      <?php if (empty($grouped_requests)): ?>
        <div class="bg-white rounded shadow p-6 text-center">
          <i class="fas fa-car text-3xl text-gray-400 mb-3"></i>
          <p class="text-gray-600 text-sm">No ride requests yet.</p>
          <a href="post_ride.php" class="inline-block mt-3 bg-purple-600 text-white font-semibold py-1 px-4 rounded hover:bg-purple-700 transition duration-200 text-sm">
            Post Your First Ride
          </a>
        </div>
      <?php else: ?>
        <div class="table-responsive">
          <table class="min-w-full compact-table bg-white rounded shadow">
            <thead class="bg-gray-50">
              <tr>
                <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pickup</th>
                <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Drop</th>
                <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Time</th>
                <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Drivers</th>
                <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
              <?php foreach ($grouped_requests as $key => $group): 
                $date = date('d M Y', strtotime($group['ride_date']));
                $time = date('h:i A', strtotime($group['ride_time']));
                $hasConfirmedDriver = false;
  
                foreach ($group['drivers'] as $driver) {
                  if ($driver['status'] === 'accepted') {
                    $hasConfirmedDriver = true;
                    break;
                  }
                }
              ?>
              <tr class="ride-group hover:bg-gray-50" data-key="<?= htmlspecialchars($key) ?>">
                <td class="whitespace-nowrap">
                  <div class="flex items-center text-sm text-gray-600">
                    <i class="fas fa-map-marker-alt text-red-500 mr-1 text-xs"></i>
                    <span class="truncate max-w-[120px]"><?= htmlspecialchars($group['pickup_location']) ?></span>
                  </div>
                </td>
                <td class="whitespace-nowrap">
                  <div class="flex items-center text-sm text-gray-600">
                    <i class="fas fa-flag-checkered text-green-500 mr-1 text-xs"></i>
                    <span class="truncate max-w-[120px]"><?= htmlspecialchars($group['drop_location']) ?></span>
                  </div>
                </td>
                <td class="whitespace-nowrap text-sm text-gray-600"><?= htmlspecialchars($date) ?></td>
                <td class="whitespace-nowrap text-sm text-gray-600"><?= htmlspecialchars($time) ?></td>
                <td class="py-2">
                  <div class="driver-toggle space-y-1">
                    <?php foreach ($group['drivers'] as $index => $driver): 
                      $status = strtolower($driver['status']);
                      $status_class = '';
                      if ($status === 'accepted') {
                        $status_class = 'driver-confirmed';
                      } elseif ($status === 'waiting') {
                        $status_class = 'driver-waiting';
                      } elseif ($status === 'rejected') {
                        $status_class = 'driver-rejected';
                      }
                      $isClickable = $status === 'accepted';
                    ?>
                    <div class="driver-option <?= $status_class ?> <?= $isClickable ? 'clickable' : '' ?> <?= $index === 0 && $isClickable ? 'selected' : '' ?>"
                      data-request-id="<?= $driver['id'] ?>"
                      data-driver-id="<?= $driver['driver_id'] ?>"
                      data-driver-name="<?= htmlspecialchars($driver['driver_name'] ?: 'Unknown Driver') ?>"
                      data-status="<?= $status ?>"
                      onclick="<?= $isClickable ? "selectDriver(this, '" . htmlspecialchars($key) . "')" : "void(0)" ?>">
                      <div class="flex items-center justify-between">
                        <span class="truncate max-w-[100px]">
                          <i class="fas fa-user mr-1 text-xs"></i>
                          <?= htmlspecialchars($driver['driver_name'] ?: 'No driver') ?>
                        </span>
                        <span class="status-badge rounded-full text-xs px-1">
                          <?= ucfirst($status) ?>
                        </span>
                      </div>
                    </div>
                    <?php endforeach; ?>
                  </div>
                </td>
                <td class="whitespace-nowrap">
                  <?php if ($hasConfirmedDriver): ?>
                    <button class="post-ride-btn bg-green-600 text-white font-semibold py-1 px-3 rounded hover:bg-green-700 transition duration-200 flex items-center text-xs"
                      onclick="postRide('<?= htmlspecialchars($key) ?>')">
                      <i class="fas fa-check mr-1"></i> Post
                    </button>
                  <?php else: ?>
                    <span class="text-gray-400 text-xs italic">Waiting...</span>
                  <?php endif; ?>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </section>
  </div>

<script>
const selectedDrivers = {};

function selectDriver(element, groupKey) {
  if (element.getAttribute('data-status') !== 'accepted') return;

  const groupRow = document.querySelector(`.ride-group[data-key="${groupKey}"]`);
  groupRow.querySelectorAll('.driver-option').forEach(opt => {
    opt.classList.remove('selected');
  });

  element.classList.add('selected');

  selectedDrivers[groupKey] = {
    requestId: element.getAttribute('data-request-id'),
    driverId: element.getAttribute('data-driver-id'),
    driverName: element.getAttribute('data-driver-name')
  };
}

function showModalAlert(message, okCallback, cancelCallback) {
  const modal = document.getElementById('modalAlert');
  const text = document.getElementById('modalAlertText');
  const okBtn = document.getElementById('modalAlertOk');
  const cancelBtn = document.getElementById('modalAlertCancel');
  text.textContent = message;
  modal.classList.remove('hidden');
  okBtn.onclick = () => { modal.classList.add('hidden'); okCallback && okCallback(); };
  cancelBtn.onclick = () => { modal.classList.add('hidden'); cancelCallback && cancelCallback(); };
}

function postRide(groupKey) {
  const selection = selectedDrivers[groupKey];
  if (!selection) {
    showModalAlert('Please select a driver first!');
    return;
  }
  showModalAlert(`Confirm posting ride with driver: ${selection.driverName}?`, () => {
    window.location.href = `post_ride.php?request_id=${selection.requestId}&driver_id=${selection.driverId}`;
  });
}

</script>

</body>
</html>

<?php include '../includes/footer_private.php'; ?>