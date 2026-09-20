<?php
include '../conn.php';
require_once '../includes/mailer.php';
session_start();

if (!isset($_SESSION['isLogin'])) {
    header("location: login.php");
    exit();
}

if (isset($_GET['roomId']) && isset($_GET['bookingId'])) {
    $room_id   = intval($_GET['roomId']);
    $bookingId = intval($_GET['bookingId']);

    // Fetch booking details + user info for email before cancelling
    $stmtInfo = mysqli_prepare($conn,
        "SELECT b.booking_id, b.checkin_date, b.checkout_date, b.tprice,
                r.label AS room_label,
                u.email AS user_email, u.username AS user_name
         FROM booking b
         LEFT JOIN rooms r ON b.room_id = r.room_id
         LEFT JOIN users u ON b.user_id = u.id
         WHERE b.booking_id = ?");
    mysqli_stmt_bind_param($stmtInfo, "i", $bookingId);
    mysqli_stmt_execute($stmtInfo);
    $bookingInfo = mysqli_fetch_assoc(mysqli_stmt_get_result($stmtInfo));
    mysqli_stmt_close($stmtInfo);

    // Update room availability
    $stmtRoom = mysqli_prepare($conn, "UPDATE rooms SET available = available + 1 WHERE room_id = ?");
    mysqli_stmt_bind_param($stmtRoom, "i", $room_id);
    $resRoom = mysqli_stmt_execute($stmtRoom);
    mysqli_stmt_close($stmtRoom);

    if ($resRoom) {
        // Update booking status
        $stmtCancel = mysqli_prepare($conn, "UPDATE booking SET status = 'cancelled' WHERE booking_id = ?");
        mysqli_stmt_bind_param($stmtCancel, "i", $bookingId);
        $resCancel = mysqli_stmt_execute($stmtCancel);
        mysqli_stmt_close($stmtCancel);

        if ($resCancel) {
            // Send cancellation email if we have user info
            if ($bookingInfo && !empty($bookingInfo['user_email'])) {
                send_cancellation_email($bookingInfo['user_email'], $bookingInfo['user_name'], [
                    'booking_id'   => $bookingInfo['booking_id'],
                    'room_label'   => $bookingInfo['room_label'],
                    'checkin_date' => $bookingInfo['checkin_date'],
                    'checkout_date'=> $bookingInfo['checkout_date'],
                    'tprice'       => $bookingInfo['tprice'],
                ]);
            }

            header("location: cartpage.php");
            exit();
        } else {
            echo "Something went wrong updating booking status.";
        }
    } else {
        echo "Something went wrong. Failed to update room availability.";
    }
}
?>