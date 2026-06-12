<?php
function scholarin_room_capacity($room = []) {
    return isset($room['max_units']) && $room['max_units'] > 0 ? intval($room['max_units']) : 3;
}

function scholarin_conflicting_bookings($conn, $room_id, $checkin, $checkout) {
    if (empty($checkin) || empty($checkout)) {
        return 0;
    }

    $room_id = intval($room_id);
    $checkin = mysqli_real_escape_string($conn, date('Y-m-d', strtotime($checkin)));
    $checkout = mysqli_real_escape_string($conn, date('Y-m-d', strtotime($checkout)));

    $result = mysqli_query($conn,
        "SELECT COUNT(*) AS booked_units
         FROM bookings
         WHERE room_id = '$room_id'
           AND status IN ('Pending', 'Approved')
           AND checkin_date < '$checkout'
           AND checkout_date > '$checkin'"
    );

    if (!$result) {
        return 0;
    }

    $row = mysqli_fetch_assoc($result);
    return intval($row['booked_units'] ?? 0);
}

function scholarin_available_units_for_room($conn, $room, $checkin, $checkout) {
    $capacity = scholarin_room_capacity($room);
    $booked = scholarin_conflicting_bookings($conn, $room['id'], $checkin, $checkout);
    return max(0, $capacity - $booked);
}

function scholarin_room_status($conn, $room, $checkin, $checkout) {
    if (empty($checkin) || empty($checkout)) {
        return [
            'availability' => 'Available',
            'available_units' => scholarin_room_capacity($room),
            'booked_units' => 0,
        ];
    }

    $available_units = scholarin_available_units_for_room($conn, $room, $checkin, $checkout);
    $status = ($available_units > 0) ? 'Available' : 'Unavailable';

    return [
        'availability' => $status,
        'available_units' => $available_units,
        'booked_units' => scholarin_conflicting_bookings($conn, $room['id'], $checkin, $checkout),
    ];
}
