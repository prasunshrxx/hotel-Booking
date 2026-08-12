<?php
include '../conn.php';
session_start();
if (!isset($_SESSION['isLogin'])) {
    header("location:http://localhost/hotelbooking/pages/login.php ");
}

if (isset($_GET['roomId']) && isset($_GET['bookingId'])) {
    $room_id = $_GET['roomId'];
    $bookingId = $_GET['bookingId'];
    $sql = "UPDATE rooms set available = available +1 where room_id = '$room_id'";
    $res = mysqli_query($conn, $sql);
    if ($res) {
        $sql = "Update booking set status='cancelled' where booking_id=$bookingId";
        $res2 = mysqli_query($conn, $sql);

        if ($res2) {
            header("location:http://localhost/hotelbooking/pages/cartpage.php ");
        } else {
            echo "Something went wrong.";
        }
    } else {
        echo "Something went wrong. Failed to delete";
    }
}




?>