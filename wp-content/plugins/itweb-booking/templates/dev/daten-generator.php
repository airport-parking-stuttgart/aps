<?php
$data = [
    ["spalte1" => "Wert1", "spalte2" => "Wert2"],
    ["spalte1" => "Wert3", "spalte2" => "Wert4"],
    // Weitere Daten hier
];

header("Content-Type: application/json");
echo json_encode($data);
?>
