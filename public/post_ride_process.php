<?php
require_once '../includes/auth_check.php';
require_once '../includes/db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $user_id = $_SESSION['user_id'];
  $ride_id = $_POST['ride_id'];
  $driver_id = $_POST['driver_id']; // may not be required here, but available
  $pickup_location = $_POST['pickup_location'];
  $destination = $_POST['destination']; // drop_location
  $ride_type = $_POST['ride_type'];
  $available_seats = intval($_POST['available_seats']);

  // Validate accepted ride request from ride_requests table
  try {
    $stmt_ride = $pdo->prepare("SELECT * FROM ride_requests WHERE id = ? AND user_id = ? AND status = 'accepted'");
    $stmt_ride->execute([$ride_id, $user_id]);
    $ride_request = $stmt_ride->fetch();

    if (!$ride_request) {
      throw new Exception("No accepted ride request found for this user.");
    }
  } catch (Exception $e) {
    header("Location: post_ride.php?error=" . urlencode($e->getMessage()));
    exit();
  }

  // Update ride_requests table with post_ride = 'yes' and available seats
  try {
    $stmt_update = $pdo->prepare("
      UPDATE ride_requests
      SET post_ride = 'yes',
          available_seats = ?,
          pickup_location = ?,
          drop_location = ?,
          ride_type = ?,
          ride_datetime = ?
      WHERE id = ? AND user_id = ?
    ");
    $stmt_update->execute([
      $available_seats,
      $pickup_location,
      $destination,
      $ride_type,
      $_POST['pickup_time'],
      $ride_id,
      $user_id
    ]);
  } catch (Exception $e) {
    header("Location: post_ride.php?error=" . urlencode("Failed to update ride request: " . $e->getMessage()));
    exit();
  }

  // Insert co-passengers data if any (linked to ride_request id)
  try {
    // Clear any existing co_passengers for this ride request (booking_id)
    $stmt_delete = $pdo->prepare("DELETE FROM co_passengers WHERE booking_id = ?");
    $stmt_delete->execute([$ride_id]);

    $names = $_POST['friends_name'] ?? [];
    $adms  = $_POST['friends_adm_no'] ?? [];
    $genders = $_POST['friends_gender'] ?? [];

    $stmt_insert_co = $pdo->prepare("INSERT INTO co_passengers (booking_id, name, admission_no, gender) VALUES (?, ?, ?, ?)");

    for ($i = 0; $i < count($names); $i++) {
      $name = trim($names[$i]);
      $adm = trim($adms[$i]);
      $gender = $genders[$i] ?? null;

      if ($name !== '') {
        $stmt_insert_co->execute([$ride_id, $name, $adm, $gender]);
      }
    }
  } catch (Exception $e) {
    // Could log this silently or notify user
    header("Location: post_ride.php?error=" . urlencode("Failed to update co-passengers: " . $e->getMessage()));
    exit();
  }

  // Redirect success
  header("Location: dashboard.php?success=1&message=" . urlencode("Ride posted successfully."));
  exit();
}
?>
