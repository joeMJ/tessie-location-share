<?php
// update.php - Empfängt die GPS-Daten von Home Assistant
$raw_input = file_get_contents('php://input');
$data = json_decode($raw_input, true);

if (!is_array($data)) {
    http_response_code(400);
    die('Ungültiges Datenformat');
}

require __DIR__ . '/config.php'; // enthält $geheimnis (siehe config.example.php)
$übermitteltes_geheimnis = isset($data['secret']) ? (string)$data['secret'] : '';

// Sofortiger Abbruch ohne sleep(), um die PHP-Worker nicht zu blockieren (Schutz vor DoS)
if (!hash_equals($geheimnis, $übermitteltes_geheimnis)) {
    http_response_code(401);
    die('Zugriff verweigert!');
}

// Reine Typ-Sicherung (Sanitization), KEIN HTML-Escaping hier!
$clean_data = [
    'status'      => substr((string)($data['status'] ?? ''), 0, 20),
    'lat'         => (float)($data['lat'] ?? 0),
    'lon'         => (float)($data['lon'] ?? 0),
    'dest'        => substr((string)($data['dest'] ?? ''), 0, 100),
    'eta'         => (int)($data['eta'] ?? 0),
    'soc_arrival' => (int)($data['soc_arrival'] ?? 0)
];

// Race-Condition Schutz beim Speichern
file_put_contents('data.json', json_encode($clean_data), LOCK_EX);

echo "OK";
?>