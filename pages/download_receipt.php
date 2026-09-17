<?php
include_once __DIR__ . '/../conn.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['isLogin'])) {
    header("Location: login.php");
    exit();
}

$booking_id = isset($_GET['booking_id']) ? intval($_GET['booking_id']) : 0;
if ($booking_id <= 0) {
    die("Invalid booking ID.");
}

// Fetch booking details
$sql = "SELECT booking.*, 
               rooms.label AS room_label, 
               rooms.price AS room_price, 
               rooms.features AS room_features, 
               rooms.description AS room_description, 
               users.username AS guest_name, 
               users.email AS guest_email
        FROM booking 
        LEFT JOIN rooms ON booking.room_id = rooms.room_id 
        LEFT JOIN users ON booking.user_id = users.id 
        WHERE booking.booking_id = ?";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $booking_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (!$result || mysqli_num_rows($result) === 0) {
    die("Booking not found.");
}

$booking = mysqli_fetch_assoc($result);

// Access control
$current_user_id = $_SESSION['user_id'] ?? $_SESSION['id'] ?? 0;
$user_role = $_SESSION['role'] ?? 'user';
if ($user_role !== 'admin' && intval($booking['user_id']) !== intval($current_user_id)) {
    die("Access denied.");
}

// Calculate dates & nights
$date1 = new DateTime($booking['checkin_date']);
$date2 = new DateTime($booking['checkout_date']);
$interval = $date1->diff($date2);
$nights = max(1, $interval->days);

$booking_date = !empty($booking['created_At']) ? date('M d, Y', strtotime($booking['created_At'])) : date('M d, Y');
$checkin_fmt = date('D, M d, Y', strtotime($booking['checkin_date']));
$checkout_fmt = date('D, M d, Y', strtotime($booking['checkout_date']));

// Include FPDF
require_once __DIR__ . '/../includes/fpdf/fpdf.php';

class ReceiptPDF extends FPDF {
    function RoundedRect($x, $y, $w, $h, $r, $style = '') {
        $k = $this->k;
        $hp = $this->h;
        if($style=='F')
            $op='f';
        elseif($style=='FD' || $style=='DF')
            $op='B';
        else
            $op='S';
        $MyArc = 4/3 * (sqrt(2) - 1);
        $this->_out(sprintf('%.2F %.2F m',($x+$r)*$k,($hp-$y)*$k ));
        $xc = $x+$w-$r ;
        $yc = $y+$r;
        $this->_out(sprintf('%.2F %.2F l', $xc*$k,($hp-$y)*$k ));

        $this->_Arc($xc + $r*$MyArc, $yc - $r, $xc + $r, $yc - $r*$MyArc, $xc + $r, $yc);
        $xc = $x+$w-$r ;
        $yc = $y+$h-$r;
        $this->_out(sprintf('%.2F %.2F l',($x+$w)*$k,($hp-$yc)*$k));
        $this->_Arc($xc + $r, $yc + $r*$MyArc, $xc + $r*$MyArc, $yc + $r, $xc, $yc + $r);
        $xc = $x+$r ;
        $yc = $y+$h-$r;
        $this->_out(sprintf('%.2F %.2F l',$xc*$k,($hp-($y+$h))*$k));
        $this->_Arc($xc - $r*$MyArc, $yc + $r, $xc - $r, $yc + $r*$MyArc, $xc - $r, $yc);
        $xc = $x+$r ;
        $yc = $y+$r;
        $this->_out(sprintf('%.2F %.2F l',($x)*$k,($hp-$yc)*$k ));
        $this->_Arc($xc - $r, $yc - $r*$MyArc, $xc - $r*$MyArc, $yc - $r, $xc, $yc - $r);
        $this->_out($op);
    }

    function _Arc($x1, $y1, $x2, $y2, $x3, $y3) {
        $h = $this->h;
        $this->_out(sprintf('%.2F %.2F %.2F %.2F %.2F %.2F c ', $x1*$this->k, ($h-$y1)*$this->k,
            $x2*$this->k, ($h-$y2)*$this->k, $x3*$this->k, ($h-$y3)*$this->k));
    }
}

$pdf = new ReceiptPDF('P', 'mm', 'A4');
$pdf->SetMargins(15, 15, 15);
$pdf->SetAutoPageBreak(true, 15);
$pdf->AddPage();

// Colors
$cNavy = [25, 51, 102];
$cGold = [212, 175, 55];
$cDark = [33, 37, 41];
$cMuted = [108, 117, 125];
$cBg = [248, 250, 252];
$cBorder = [226, 232, 240];

// ==================== HEADER ====================
// Logo Icon box
$pdf->SetFillColor($cNavy[0], $cNavy[1], $cNavy[2]);
$pdf->SetDrawColor($cGold[0], $cGold[1], $cGold[2]);
$pdf->SetLineWidth(0.6);
$pdf->RoundedRect(15, 15, 14, 14, 3, 'FD');
$pdf->SetFont('Helvetica', 'B', 16);
$pdf->SetTextColor(255, 255, 255);
$pdf->SetXY(15, 16.5);
$pdf->Cell(14, 11, 'L', 0, 0, 'C');

// Hotel Title
$pdf->SetXY(33, 15);
$pdf->SetFont('Helvetica', 'B', 20);
$pdf->SetTextColor($cDark[0], $cDark[1], $cDark[2]);
$pdf->Cell(60, 8, 'LOTUS', 0, 2, 'L');
$pdf->SetFont('Helvetica', 'B', 8);
$pdf->SetTextColor($cGold[0], $cGold[1], $cGold[2]);
$pdf->Cell(60, 4, 'LUXURY BOUTIQUE HOTEL', 0, 0, 'L');

// Header Right (Receipt Info)
$pdf->SetXY(110, 15);
$pdf->SetFont('Helvetica', '', 8);
$pdf->SetTextColor($cMuted[0], $cMuted[1], $cMuted[2]);
$pdf->Cell(85, 4, 'BOOKING RECEIPT', 0, 2, 'R');

$pdf->SetFont('Helvetica', 'B', 14);
$pdf->SetTextColor($cNavy[0], $cNavy[1], $cNavy[2]);
$pdf->Cell(85, 6, '#LOTUS-BK-' . $booking['booking_id'], 0, 2, 'R');

$pdf->SetFont('Helvetica', '', 9);
$pdf->SetTextColor($cMuted[0], $cMuted[1], $cMuted[2]);
$statusText = strtoupper($booking['status']);
$pdf->Cell(85, 5, 'Date: ' . $booking_date . '   |   Status: ' . $statusText, 0, 0, 'R');

// Horizontal Divider
$pdf->SetDrawColor($cBorder[0], $cBorder[1], $cBorder[2]);
$pdf->SetLineWidth(0.4);
$pdf->Line(15, 34, 195, 34);

$pdf->Ln(25);

// ==================== RESERVATION OVERVIEW ====================
$overviewY = 38;
$pdf->SetXY(15, $overviewY);

// Overview Container Box
$pdf->SetFillColor($cBg[0], $cBg[1], $cBg[2]);
$pdf->SetDrawColor($cBorder[0], $cBorder[1], $cBorder[2]);
$pdf->SetLineWidth(0.3);

$boxHeight = !empty($booking['special_request']) ? 58 : 46;
$pdf->RoundedRect(15, $overviewY, 180, $boxHeight, 4, 'FD');

// Section Heading
$pdf->SetXY(20, $overviewY + 4);
$pdf->SetFont('Helvetica', 'B', 10);
$pdf->SetTextColor($cNavy[0], $cNavy[1], $cNavy[2]);
$pdf->Cell(170, 5, 'RESERVATION OVERVIEW', 0, 1, 'L');

// Row 1: Guest Details & Room
$r1Y = $overviewY + 12;

// Left: Guest Info
$pdf->SetXY(20, $r1Y);
$pdf->SetFont('Helvetica', '', 8);
$pdf->SetTextColor($cMuted[0], $cMuted[1], $cMuted[2]);
$pdf->Cell(85, 4, 'GUEST NAME', 0, 2, 'L');
$pdf->SetFont('Helvetica', 'B', 11);
$pdf->SetTextColor($cDark[0], $cDark[1], $cDark[2]);
$pdf->Cell(85, 5, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $booking['guest_name'] ?? 'Guest'), 0, 2, 'L');

$pdf->SetFont('Helvetica', '', 8);
$pdf->SetTextColor($cMuted[0], $cMuted[1], $cMuted[2]);
$contactLine = ($booking['guest_email'] ?? '');
if (!empty($booking['phone_number'])) {
    $contactLine .= '  |  ' . $booking['phone_number'];
}
$pdf->Cell(85, 4, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $contactLine), 0, 0, 'L');

// Right: Room Reserved
$pdf->SetXY(110, $r1Y);
$pdf->SetFont('Helvetica', '', 8);
$pdf->SetTextColor($cMuted[0], $cMuted[1], $cMuted[2]);
$pdf->Cell(80, 4, 'ROOM RESERVED', 0, 2, 'L');
$pdf->SetFont('Helvetica', 'B', 11);
$pdf->SetTextColor($cDark[0], $cDark[1], $cDark[2]);
$pdf->Cell(80, 5, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $booking['room_label'] ?? 'Hotel Room'), 0, 2, 'L');

$pdf->SetFont('Helvetica', '', 8);
$pdf->SetTextColor($cMuted[0], $cMuted[1], $cMuted[2]);
$pdf->Cell(80, 4, ($booking['no_of_guests'] ?? 1) . ' Guest(s)', 0, 0, 'L');

// Inner separator
$r2Y = $r1Y + 16;
$pdf->SetDrawColor($cBorder[0], $cBorder[1], $cBorder[2]);
$pdf->Line(20, $r2Y, 190, $r2Y);

// Row 2: Dates and Stay duration
$r2ContentY = $r2Y + 3;

// Check-in
$pdf->SetXY(20, $r2ContentY);
$pdf->SetFont('Helvetica', '', 8);
$pdf->SetTextColor($cMuted[0], $cMuted[1], $cMuted[2]);
$pdf->Cell(55, 4, 'CHECK-IN', 0, 2, 'L');
$pdf->SetFont('Helvetica', 'B', 10);
$pdf->SetTextColor($cDark[0], $cDark[1], $cDark[2]);
$pdf->Cell(55, 5, $checkin_fmt, 0, 2, 'L');
$pdf->SetFont('Helvetica', '', 7);
$pdf->SetTextColor($cMuted[0], $cMuted[1], $cMuted[2]);
$pdf->Cell(55, 3, 'From 02:00 PM', 0, 0, 'L');

// Check-out
$pdf->SetXY(80, $r2ContentY);
$pdf->SetFont('Helvetica', '', 8);
$pdf->SetTextColor($cMuted[0], $cMuted[1], $cMuted[2]);
$pdf->Cell(55, 4, 'CHECK-OUT', 0, 2, 'L');
$pdf->SetFont('Helvetica', 'B', 10);
$pdf->SetTextColor($cDark[0], $cDark[1], $cDark[2]);
$pdf->Cell(55, 5, $checkout_fmt, 0, 2, 'L');
$pdf->SetFont('Helvetica', '', 7);
$pdf->SetTextColor($cMuted[0], $cMuted[1], $cMuted[2]);
$pdf->Cell(55, 3, 'Until 12:00 PM', 0, 0, 'L');

// Total Stay
$pdf->SetXY(140, $r2ContentY);
$pdf->SetFont('Helvetica', '', 8);
$pdf->SetTextColor($cMuted[0], $cMuted[1], $cMuted[2]);
$pdf->Cell(50, 4, 'TOTAL STAY', 0, 2, 'L');
$pdf->SetFont('Helvetica', 'B', 10);
$pdf->SetTextColor($cDark[0], $cDark[1], $cDark[2]);
$pdf->Cell(50, 5, $nights . ($nights == 1 ? ' Night' : ' Nights'), 0, 2, 'L');
$pdf->SetFont('Helvetica', '', 7);
$pdf->SetTextColor($cMuted[0], $cMuted[1], $cMuted[2]);
$pdf->Cell(50, 3, 'Duration', 0, 0, 'L');

// Special Requests (if present)
if (!empty($booking['special_request'])) {
    $reqY = $r2ContentY + 14;
    $pdf->SetDrawColor($cBorder[0], $cBorder[1], $cBorder[2]);
    $pdf->Line(20, $reqY - 2, 190, $reqY - 2);

    $pdf->SetXY(20, $reqY);
    $pdf->SetFont('Helvetica', 'B', 8);
    $pdf->SetTextColor($cDark[0], $cDark[1], $cDark[2]);
    $pdf->Write(4, 'Special Request: ');
    $pdf->SetFont('Helvetica', '', 8);
    $pdf->SetTextColor($cMuted[0], $cMuted[1], $cMuted[2]);
    $pdf->Write(4, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $booking['special_request']));
}

// ==================== ITEM & DESCRIPTION ====================
$tableStartY = $overviewY + $boxHeight + 8;
$pdf->SetXY(15, $tableStartY);
$pdf->SetFont('Helvetica', 'B', 10);
$pdf->SetTextColor($cNavy[0], $cNavy[1], $cNavy[2]);
$pdf->Cell(180, 6, 'ITEM & DESCRIPTION', 0, 1, 'L');

$tableHeaderY = $tableStartY + 8;
$pdf->SetXY(15, $tableHeaderY);

// Header columns
$pdf->SetFillColor(247, 248, 249);
$pdf->SetDrawColor($cBorder[0], $cBorder[1], $cBorder[2]);
$pdf->SetLineWidth(0.3);
$pdf->SetFont('Helvetica', 'B', 8);
$pdf->SetTextColor($cDark[0], $cDark[1], $cDark[2]);

$pdf->Cell(95, 8, '  ITEM & DESCRIPTION', 1, 0, 'L', true);
$pdf->Cell(25, 8, 'NIGHTS', 1, 0, 'C', true);
$pdf->Cell(30, 8, 'RATE / NIGHT', 1, 0, 'R', true);
$pdf->Cell(30, 8, 'AMOUNT  ', 1, 1, 'R', true);

// Row Data
$rowY = $pdf->GetY();
$pdf->SetXY(15, $rowY);
$pdf->SetFillColor(255, 255, 255);

// Left cell with room title and features
$pdf->Cell(95, 16, '', 1, 0, 'L');
$pdf->SetXY(18, $rowY + 3);
$pdf->SetFont('Helvetica', 'B', 10);
$pdf->SetTextColor($cDark[0], $cDark[1], $cDark[2]);
$pdf->Cell(90, 5, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $booking['room_label'] ?? 'Hotel Room'), 0, 2, 'L');

$pdf->SetFont('Helvetica', '', 7);
$pdf->SetTextColor($cMuted[0], $cMuted[1], $cMuted[2]);
$featText = !empty($booking['room_features']) ? $booking['room_features'] : 'Room accommodation with modern amenities';
$pdf->Cell(90, 4, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', substr($featText, 0, 55)), 0, 0, 'L');

// Nights cell
$pdf->SetXY(110, $rowY);
$pdf->SetFont('Helvetica', '', 9);
$pdf->SetTextColor($cDark[0], $cDark[1], $cDark[2]);
$pdf->Cell(25, 16, $nights, 1, 0, 'C');

// Rate cell
$pdf->SetXY(135, $rowY);
$pdf->Cell(30, 16, 'Rs. ' . number_format($booking['room_price']), 1, 0, 'R');

// Amount cell
$pdf->SetXY(165, $rowY);
$pdf->SetFont('Helvetica', 'B', 10);
$pdf->Cell(30, 16, 'Rs. ' . number_format($booking['tprice']) . '  ', 1, 1, 'R');

// Total Row
$totalY = $pdf->GetY();
$pdf->SetXY(15, $totalY);
$pdf->SetFillColor(248, 250, 252);
$pdf->SetFont('Helvetica', 'B', 10);
$pdf->SetTextColor($cDark[0], $cDark[1], $cDark[2]);
$pdf->Cell(150, 10, 'TOTAL AMOUNT  ', 1, 0, 'R', true);

$pdf->SetFont('Helvetica', 'B', 12);
$pdf->SetTextColor($cNavy[0], $cNavy[1], $cNavy[2]);
$pdf->Cell(30, 10, 'Rs. ' . number_format($booking['tprice']) . '  ', 1, 1, 'R', true);

// ==================== FOOTER ====================
$footerY = $totalY + 16;
$pdf->SetDrawColor($cBorder[0], $cBorder[1], $cBorder[2]);
$pdf->Line(15, $footerY, 195, $footerY);

$pdf->SetXY(15, $footerY + 3);
$pdf->SetFont('Helvetica', 'B', 8);
$pdf->SetTextColor($cDark[0], $cDark[1], $cDark[2]);
$pdf->Cell(80, 5, 'Payment: Pay at Hotel / Check-in', 0, 0, 'L');

$pdf->SetFont('Helvetica', '', 8);
$pdf->SetTextColor($cMuted[0], $cMuted[1], $cMuted[2]);
$pdf->Cell(100, 5, 'Thank you for choosing LOTUS Luxury Boutique Hotel!', 0, 0, 'R');

// Clean output buffer to ensure clean binary output
if (ob_get_length()) {
    ob_end_clean();
}

$filename = 'LOTUS_Receipt_BK-' . $booking['booking_id'] . '.pdf';
$pdf->Output('D', $filename);
exit();
