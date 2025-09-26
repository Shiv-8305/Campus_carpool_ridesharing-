<?php
require_once '../includes/auth_check.php';
require_once '../includes/db_connection.php';
include '../includes/header_private.php';

// Inputs
$pickup = trim($_GET['pickup'] ?? '');
$drop = trim($_GET['drop'] ?? '');
$ride_type = $_GET['ride_type'] ?? '';
$required_seats = max(1, intval($_GET['seats'] ?? 1));

$limit = 6;
$page = max(1, intval($_GET['page'] ?? 1));
$offset = ($page - 1) * $limit;

$where = ["r.status = 'accepted'", "r.available_seats >= ?"];
$params = [$required_seats];

if ($pickup !== '') { $where[] = "r.pickup_location LIKE ?"; $params[] = "%$pickup%"; }
if ($drop !== '') { $where[] = "r.drop_location LIKE ?"; $params[] = "%$drop%"; }
if (in_array($ride_type, ['drop', 'pick', 'roundtrip'])) { $where[] = "r.ride_type = ?"; $params[] = $ride_type; }
$where_sql = count($where) ? 'WHERE ' . implode(' AND ', $where) : '';

$stmt_count = $pdo->prepare("SELECT COUNT(*) FROM ride_requests r $where_sql");
$stmt_count->execute($params);
$total_rides = $stmt_count->fetchColumn();
$total_pages = ceil($total_rides / $limit);

$stmt = $pdo->prepare("
  SELECT r.*, d.name AS driver_name, d.phone AS driver_phone
  FROM ride_requests r
  JOIN drivers d ON r.driver_id = d.id
  $where_sql
  ORDER BY r.ride_datetime ASC 
  LIMIT $limit OFFSET $offset
");
$stmt->execute($params);
$rides = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Search Rides</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet" />
    <style>
        body {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            background: #f6f2fa;
            color: #534779;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .page-content {
            flex: 1;
            max-width: 950px;
            margin: 2.5rem auto 0 auto;
            width: 100%;
            padding: 0 1.1rem;
            box-sizing: border-box;
        }
        h2 {
            font-size: 2rem;
            font-weight: 700;
            color: #754dac;
            margin-bottom: 1.25rem;
            letter-spacing: 0.02em;
        }
        form.simple-search-form {
            display: flex;
            flex-wrap: wrap;
            gap: 1.1rem 2.4rem;
            align-items: flex-end;
            margin-bottom: 1.5rem;
        }
        form.simple-search-form > div { flex: 1; min-width: 180px; }
        form.simple-search-form label {
            font-weight: 600;
            margin-bottom: 0.3rem;
            display: block;
            color: #6e529a;
        }
        form.simple-search-form input, form.simple-search-form select {
            width: 100%;
            padding: 9px 11px;
            font-size: 1rem;
            border-radius: 7px;
            border: 1px solid #c6b3e3;
            background: #fff;
            color: #5a3e85;
        }
        form.simple-search-form input:focus, form.simple-search-form select:focus {
            outline: 2px solid #b595e2;
        }
        button.search-btn {
            padding: 10px 22px;
            background: #7248bb;
            border: none;
            color: white;
            font-weight: 600;
            font-size: 1rem;
            border-radius: 7px;
            cursor: pointer;
        }
        button.search-btn:hover { background: #523078; }

        .rides-table-block {
            background: #f8f5fc;
            border-radius: 12px;
            box-shadow: 0 3px 14px #c2b1e627;
            margin-bottom: 1.7rem;
            overflow-x: auto;
        }
        .ride-table {
            width: 100%;
            border-collapse: collapse;
            background: transparent;
        }
        .ride-table th, .ride-table td {
            padding: 9px 14px;
            border-bottom: 1px solid #d6cbe7;
            font-size: 1rem;
            color: #563682;
        }
        .ride-table th {
            background: #efe6fa;
            font-weight: 700;
        }
        .ride-table tr:last-child td { border-bottom: none; }
        .ride-table tr:hover { background: #eae4f3; }
        .ride-table a { color: #784abe; text-decoration: underline; }
        
        .page-control {
            display: flex; justify-content: space-between;
            color: #786697; font-size:0.97rem;
            margin-top: 10px;
        }
        .page-control a { color: #784abe; font-weight:600; text-decoration:none;}
        .page-control a:hover { text-decoration:underline; }

        @media (max-width: 700px) {
            .page-content {padding: 0 0.3rem;}
            form.simple-search-form {flex-direction: column;}
            .ride-table th, .ride-table td {font-size: 0.92rem; white-space: normal; padding: 7px 7px;}
        }
        .ride-table th, .ride-table td {
            vertical-align: middle;
        }
        
        /* Button Styles */
        .action-btn {
            display: inline-block;
            min-width: 110px;
            padding: 8px 0;
            border-radius: 6px;
            color: #fff;
            font-weight: 600;
            text-align: center;
            text-decoration: none;
            border: none;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            margin: 2px;
        }
        .view-driver-btn {
            background: #754dac;
        }
        .view-driver-btn:hover {
            background: #563682;
        }
        .book-ride-btn {
            background: #10B981;
        }
        .book-ride-btn:hover {
            background: #0D9F6E;
        }
        .ride-table th:last-child, .ride-table td:last-child {
            width: 240px;
            text-align: center;
        }
        
        /* Modal Styles */
        #bookingModal {
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
        
        #bookingModal.active {
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
        
        .modal-btn {
            padding: 10px 20px;
            border-radius: 5px;
            font-weight: 600;
            cursor: pointer;
            border: none;
            transition: all 0.3s ease;
        }
        
        .modal-ok-btn {
            background: #10B981;
            color: white;
        }
        
        .modal-ok-btn:hover {
            background: #0D9F6E;
        }
    </style>
</head>
<body>
    <!-- Booking Confirmation Modal -->
    <div id="bookingModal">
        <div class="modal-content">
            <div class="success-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <h2 class="text-2xl font-bold text-gray-800 mb-2">Ride Booked!</h2>
            <p class="text-gray-600 mb-4">Your ride has been successfully booked.</p>
            <div class="modal-buttons">
                <button id="modalOkBtn" class="modal-btn modal-ok-btn">OK</button>
            </div>
        </div>
    </div>

    <div class="page-content">
        <h2>Search for Relevant Rides</h2>
        <form method="get" class="simple-search-form" autocomplete="off">
            <div>
                <label for="pickup">Pickup</label>
                <input id="pickup" name="pickup" type="text" placeholder="Enter pickup" value="<?= htmlspecialchars($pickup) ?>">
            </div>
            <div>
                <label for="drop">Drop</label>
                <input id="drop" name="drop" type="text" placeholder="Enter drop" value="<?= htmlspecialchars($drop) ?>">
            </div>
            <div>
                <label for="seats">Seats Needed</label>
                <input id="seats" name="seats" type="number" min="1" max="10" value="<?= $required_seats ?>" required>
            </div>
            <div>
                <label for="ride_type">Ride Type</label>
                <select id="ride_type" name="ride_type">
                    <option value="" <?= $ride_type === '' ? 'selected' : '' ?>>All</option>
                    <option value="drop" <?= $ride_type === 'drop' ? 'selected' : '' ?>>Drop</option>
                    <option value="pick" <?= $ride_type === 'pick' ? 'selected' : '' ?>>Pick</option>
                    <option value="roundtrip" <?= $ride_type === 'roundtrip' ? 'selected' : '' ?>>Roundtrip</option>
                </select>
            </div>
            <button type="submit" class="search-btn">Search</button>
        </form>

        <div class="rides-table-block">
            <?php if ($total_rides > 0): ?>
                <table class="ride-table">
                    <thead>
                        <tr>
                            <th>Date/Time</th>
                            <th>Pickup</th>
                            <th>Drop</th>
                            <th>Type</th>
                            <th>Seats</th>
                            <th>Driver</th>
                            <th>Phone</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rides as $ride): ?>
                            <tr>
                                <td><?= date('d M Y, h:i A', strtotime($ride['ride_datetime'])) ?></td>
                                <td><?= htmlspecialchars($ride['pickup_location']) ?></td>
                                <td><?= htmlspecialchars($ride['drop_location']) ?></td>
                                <td><?= ucfirst($ride['ride_type']) ?></td>
                                <td><?= intval($ride['available_seats']) ?></td>
                                <td><?= htmlspecialchars($ride['driver_name']) ?></td>
                                <td><a href="tel:<?= htmlspecialchars($ride['driver_phone']) ?>"><?= htmlspecialchars($ride['driver_phone']) ?></a></td>
                                <td style="text-align:center;">
                                    <a style="color: white" href="driver_profile.php?driver_id=<?= (int)$ride['driver_id'] ?>" class="action-btn view-driver-btn">View Driver</a>
                                    <button class="action-btn book-ride-btn" onclick="bookRide(<?= (int)$ride['id'] ?>, '<?= htmlspecialchars($ride['pickup_location']) ?>', '<?= htmlspecialchars($ride['drop_location']) ?>', '<?= htmlspecialchars($ride['driver_name']) ?>')">Book Ride</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <div class="page-control">
                    <?php if ($page > 1): ?>
                        <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>">&larr; Prev</a>
                    <?php else: ?>
                        <span></span>
                    <?php endif; ?>
                    <span>Page <?= $page ?> of <?= $total_pages ?></span>
                    <?php if ($page < $total_pages): ?>
                        <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>">Next &rarr;</a>
                    <?php else: ?>
                        <span></span>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div style="padding: 1.8rem; text-align:center; color:#a17bb3;">No rides found. Try other pickup/drop or seats criteria.</div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Book Ride Function
        function bookRide(rideId, pickup, drop, driverName) {
            // Show the booking confirmation modal
            const modal = document.getElementById('bookingModal');
            modal.classList.add('active');
            
            // Set up the OK button to redirect to dashboard
            document.getElementById('modalOkBtn').onclick = function() {
                window.location.href = 'dashboard.php';
            };
        }
        
        // Make sure the footer is always at the bottom
        document.body.style.display = 'flex';
        document.body.style.flexDirection = 'column';
        document.body.style.minHeight = '100vh';
    </script>

    <?php include '../includes/footer_private.php'; ?>
</body>
</html>