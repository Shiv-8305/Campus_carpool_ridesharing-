<?php
require_once '../includes/auth_check_driver.php';
require_once '../includes/db_connection.php';

// Handle confirm/reject actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && isset($_POST['request_id'])) {
        $request_id = intval($_POST['request_id']);
        $action = $_POST['action'];
        $driver_id = $_SESSION['driver_id'];
        
        if ($action === 'confirm') {
            // Update ride request status to accepted
            $stmt = $pdo->prepare("UPDATE ride_requests SET status = 'accepted' WHERE id = ? AND driver_id = ? AND status = 'waiting'");
            $stmt->execute([$request_id, $driver_id]);
            
            if ($stmt->rowCount() > 0) {
                $_SESSION['success_message'] = "Ride request confirmed successfully!";
            } else {
                $_SESSION['error_message'] = "Unable to confirm ride request. It may have been taken by another driver.";
            }
            
        } elseif ($action === 'reject') {
            // Update ride request status to rejected
            $stmt = $pdo->prepare("UPDATE ride_requests SET status = 'rejected' WHERE id = ? AND driver_id = ?");
            $stmt->execute([$request_id, $driver_id]);
            
            if ($stmt->rowCount() > 0) {
                $_SESSION['success_message'] = "Ride request rejected successfully!";
            } else {
                $_SESSION['error_message'] = "Unable to reject ride request.";
            }
        }
        
        // Redirect to avoid form resubmission
        header('Location: driver_dashboard.php');
        exit;
    }
}

// Fetch ride requests for this driver
try {
    $driver_id = $_SESSION['driver_id'];
    
    // Get waiting ride requests assigned to this specific driver
    $waiting_requests_stmt = $pdo->prepare("
        SELECT rr.*, u.name as user_name 
        FROM ride_requests rr 
        JOIN users u ON rr.user_id = u.id 
        WHERE rr.driver_id = ? AND rr.status = 'waiting'
        ORDER BY rr.request_time DESC
    ");
    $waiting_requests_stmt->execute([$driver_id]);
    $waiting_requests = $waiting_requests_stmt->fetchAll();
    
    // Get accepted ride requests by this driver
    $accepted_requests_stmt = $pdo->prepare("
        SELECT rr.*, u.name as user_name
        FROM ride_requests rr 
        JOIN users u ON rr.user_id = u.id 
        WHERE rr.driver_id = ? AND rr.status = 'accepted' 
        ORDER BY rr.ride_datetime DESC
    ");
    $accepted_requests_stmt->execute([$driver_id]);
    $accepted_requests = $accepted_requests_stmt->fetchAll();
    
    // Get rejected ride requests by this driver
    $rejected_requests_stmt = $pdo->prepare("
        SELECT rr.*, u.name as user_name
        FROM ride_requests rr 
        JOIN users u ON rr.user_id = u.id 
        WHERE rr.driver_id = ? AND rr.status = 'rejected' 
        ORDER BY rr.ride_datetime DESC
    ");
    $rejected_requests_stmt->execute([$driver_id]);
    $rejected_requests = $rejected_requests_stmt->fetchAll();
    
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    $waiting_requests = [];
    $accepted_requests = [];
    $rejected_requests = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Driver Dashboard - CampusCarPool</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f8f9fa;
            min-height: 100vh;
        }

        .dashboard-header {
            background: linear-gradient(135deg, #7c45a9 0%, #5c2487 100%);
            color: white;
            padding: 2rem;
            text-align: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .dashboard-header h1 {
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
        }

        .welcome-text {
            font-size: 1.2rem;
            opacity: 0.9;
        }

        .driver-info {
            background: white;
            margin: -2rem auto 2rem auto;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            max-width: 1200px;
            text-align: center;
        }

        .driver-name {
            font-size: 1.8rem;
            color: #7c45a9;
            margin-bottom: 0.5rem;
            font-weight: 600;
        }

        .driver-phone {
            color: #666;
            font-size: 1.1rem;
            margin-bottom: 1.5rem;
        }

        .alert {
            padding: 1rem;
            border-radius: 8px;
            margin: 1rem auto;
            max-width: 1200px;
            text-align: center;
            font-weight: 600;
        }

        .alert-success {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
        }

        .alert-error {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }

        .section {
            background: white;
            margin: 2rem auto;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            max-width: 1200px;
        }

        .section-title {
            font-size: 1.5rem;
            color: #7c45a9;
            margin-bottom: 1.5rem;
            font-weight: 600;
            border-bottom: 2px solid #e9ecef;
            padding-bottom: 0.5rem;
        }

        .requests-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
        }

        .requests-table th {
            background: #f8f9fa;
            padding: 1rem;
            text-align: left;
            font-weight: 600;
            color: #495057;
            border-bottom: 2px solid #dee2e6;
        }

        .requests-table td {
            padding: 1rem;
            border-bottom: 1px solid #dee2e6;
            vertical-align: top;
        }

        .requests-table tr:hover {
            background: #f8f9fa;
        }

        .action-buttons {
            display: flex;
            gap: 0.5rem;
            justify-content: flex-end;
        }

        .btn {
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 5px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
            text-align: center;
        }

        .btn-confirm {
            background: #28a745;
            color: white;
        }

        .btn-confirm:hover {
            background: #218838;
            transform: translateY(-1px);
        }

        .btn-reject {
            background: #dc3545;
            color: white;
        }

        .btn-reject:hover {
            background: #c82333;
            transform: translateY(-1px);
        }

        .status-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 15px;
            font-size: 0.85rem;
            font-weight: 600;
        }

        .status-waiting {
            background: #fff3cd;
            color: #856404;
        }

        .status-accepted {
            background: #d1ecf1;
            color: #0c5460;
        }

        .status-rejected {
            background: #f8d7da;
            color: #721c24;
        }

        .no-requests {
            text-align: center;
            padding: 2rem;
            color: #6c757d;
            font-style: italic;
        }

        .logout-section {
            text-align: center;
            margin: 2rem auto;
            max-width: 1200px;
        }

        .btn-logout {
            background: #dc3545;
            color: white;
            padding: 0.75rem 2rem;
            border: none;
            border-radius: 8px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-logout:hover {
            background: #c82333;
            transform: translateY(-2px);
        }

        .nav-links {
            text-align: center;
            margin: 2rem auto;
            max-width: 1200px;
        }

        .nav-links a {
            color: #7c45a9;
            text-decoration: none;
            margin: 0 1rem;
            font-weight: 600;
            padding: 0.5rem 1rem;
            border: 2px solid #7c45a9;
            border-radius: 5px;
            transition: all 0.3s ease;
        }

        .nav-links a:hover {
            background: #7c45a9;
            color: white;
        }

        @media (max-width: 768px) {
            .dashboard-header h1 {
                font-size: 2rem;
            }
            
            .driver-info, .section {
                margin: 1rem;
                padding: 1.5rem;
            }
            
            .requests-table {
                font-size: 0.9rem;
            }
            
            .requests-table th,
            .requests-table td {
                padding: 0.5rem;
            }
            
            .action-buttons {
                flex-direction: column;
                gap: 0.25rem;
            }
            
            .btn {
                padding: 0.4rem 0.8rem;
                font-size: 0.9rem;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard-header">
        <h1>Driver Dashboard</h1>
        <p class="welcome-text">Manage your ride requests</p>
    </div>

    <div class="driver-info">
        <div class="driver-name">Welcome, <?= htmlspecialchars($_SESSION['driver_name']) ?>! 👋</div>
    </div>

    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success">
            <?= htmlspecialchars($_SESSION['success_message']) ?>
        </div>
        <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="alert alert-error">
            <?= htmlspecialchars($_SESSION['error_message']) ?>
        </div>
        <?php unset($_SESSION['error_message']); ?>
    <?php endif; ?>

    <!-- Available Rides Section (Waiting for driver confirmation) -->
    <div class="section">
        <h2 class="section-title">Rides Waiting for Your Confirmation</h2>
        <?php if (!empty($waiting_requests)): ?>
            <table class="requests-table">
                <thead>
                    <tr>
                        <th>User Name</th>
                        <th>Pickup Location</th>
                        <th>Drop Location</th>
                        <th>Ride Date & Time</th>
                        <th>Request Time</th>
                        <th>Ride Type</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($waiting_requests as $request): ?>
                        <tr>
                            <td><?= htmlspecialchars($request['user_name']) ?></td>
                            <td><?= htmlspecialchars($request['pickup_location']) ?></td>
                            <td><?= htmlspecialchars($request['drop_location']) ?></td>
                            <td><?= date('M j, Y g:i A', strtotime($request['ride_datetime'])) ?></td>
                            <td><?= date('M j, Y g:i A', strtotime($request['request_time'])) ?></td>
                            <td><?= htmlspecialchars(ucfirst($request['ride_type'])) ?></td>
                            <td>
                                <span class="status-badge status-waiting">Waiting</span>
                            </td>
                            <td>
                                <form method="POST" class="action-buttons">
                                    <input type="hidden" name="request_id" value="<?= $request['id'] ?>">
                                    <button type="submit" name="action" value="confirm" class="btn btn-confirm">Confirm</button>
                                    <button type="submit" name="action" value="reject" class="btn btn-reject">Reject</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="no-requests">No ride requests waiting for your confirmation.</div>
        <?php endif; ?>
    </div>

    <!-- Accepted Rides Section -->
    <div class="section">
        <h2 class="section-title">Your Confirmed Rides</h2>
        <?php if (!empty($accepted_requests)): ?>
            <table class="requests-table">
                <thead>
                    <tr>
                        <th>User Name</th>
                        <th>Pickup Location</th>
                        <th>Drop Location</th>
                        <th>Ride Date & Time</th>
                        <th>Request Time</th>
                        <th>Ride Type</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($accepted_requests as $request): ?>
                        <tr>
                            <td><?= htmlspecialchars($request['user_name']) ?></td>
                            <td><?= htmlspecialchars($request['pickup_location']) ?></td>
                            <td><?= htmlspecialchars($request['drop_location']) ?></td>
                            <td><?= date('M j, Y g:i A', strtotime($request['ride_datetime'])) ?></td>
                            <td><?= date('M j, Y g:i A', strtotime($request['request_time'])) ?></td>
                            <td><?= htmlspecialchars(ucfirst($request['ride_type'])) ?></td>
                            <td>
                                <span class="status-badge status-accepted">Confirmed</span>
                            </td>
                            <td>
                                <form method="POST" class="action-buttons">
                                    <input type="hidden" name="request_id" value="<?= $request['id'] ?>">
                                    <button type="submit" name="action" value="reject" class="btn btn-reject">Cancel</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="no-requests">No confirmed rides found.</div>
        <?php endif; ?>
    </div>

    <!-- Rejected Rides Section -->
    <div class="section">
        <h2 class="section-title">Your Rejected Rides</h2>
        <?php if (!empty($rejected_requests)): ?>
            <table class="requests-table">
                <thead>
                    <tr>
                        <th>User Name</th>
                        <th>Pickup Location</th>
                        <th>Drop Location</th>
                        <th>Ride Date & Time</th>
                        <th>Request Time</th>
                        <th>Ride Type</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rejected_requests as $request): ?>
                        <tr>
                            <td><?= htmlspecialchars($request['user_name']) ?></td>
                            <td><?= htmlspecialchars($request['pickup_location']) ?></td>
                            <td><?= htmlspecialchars($request['drop_location']) ?></td>
                            <td><?= date('M j, Y g:i A', strtotime($request['ride_datetime'])) ?></td>
                            <td><?= date('M j, Y g:i A', strtotime($request['request_time'])) ?></td>
                            <td><?= htmlspecialchars(ucfirst($request['ride_type'])) ?></td>
                            <td>
                                <span class="status-badge status-rejected">Rejected</span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="no-requests">No rejected rides found.</div>
        <?php endif; ?>
    </div>

    <div class="logout-section">
        <form action="driver_logout.php" method="POST">
            <button type="submit" class="btn-logout">Logout</button>
        </form>
    </div>
</body>
</html>