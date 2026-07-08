<?php
// api.php - REST API for all CRUD operations

require_once 'config.php';

$method = $_SERVER['REQUEST_METHOD'];
$table = isset($_GET['table']) ? $_GET['table'] : null;
$id = isset($_GET['id']) ? $_GET['id'] : null;

if (!$table) {
    sendJsonResponse(['error' => 'Table parameter required'], 400);
}

// Primary keys for each table
$primaryKeys = [
    'collection_routes' => 'RouteID',
    'collection_fleet' => 'TruckID',
    'waste_categories' => 'CategoryID',
    'disposal_facilities' => 'FacilityID',
    'pickup_logs' => 'LogID',
    'users' => 'UserID'
];

$pk = $primaryKeys[$table] ?? 'id';

// Validate table name to prevent SQL injection
$allowedTables = ['collection_routes', 'collection_fleet', 'waste_categories', 'disposal_facilities', 'pickup_logs', 'users'];
if (!in_array($table, $allowedTables)) {
    sendJsonResponse(['error' => 'Invalid table name'], 400);
}

switch ($method) {
    case 'GET':
        if ($id) {
            // Get single record
            $stmt = $conn->prepare("SELECT * FROM $table WHERE $pk = ?");
            $stmt->bind_param("s", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            $data = $result->fetch_assoc();
            
            if ($data) {
                sendJsonResponse($data);
            } else {
                sendJsonResponse(['error' => 'Record not found'], 404);
            }
        } else {
            // Get all records
            $result = $conn->query("SELECT * FROM $table");
            $data = [];
            while ($row = $result->fetch_assoc()) {
                $data[] = $row;
            }
            sendJsonResponse($data);
        }
        break;
        
    case 'POST':
        // Create new record
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!$data) {
            sendJsonResponse(['error' => 'Invalid data'], 400);
        }
        
        $fields = array_keys($data);
        $values = array_values($data);
        $placeholders = implode(',', array_fill(0, count($fields), '?'));
        
        $sql = "INSERT INTO $table (" . implode(',', $fields) . ") VALUES ($placeholders)";
        $stmt = $conn->prepare($sql);
        
        // Determine types
        $types = '';
        foreach ($values as $v) {
            if (is_int($v)) $types .= 'i';
            elseif (is_float($v)) $types .= 'd';
            else $types .= 's';
        }
        
        $stmt->bind_param($types, ...$values);
        
        if ($stmt->execute()) {
            sendJsonResponse([
                'success' => true,
                'id' => $conn->insert_id,
                'message' => 'Record created successfully'
            ], 201);
        } else {
            sendJsonResponse(['error' => 'Failed to create: ' . $stmt->error], 500);
        }
        break;
        
    case 'PUT':
        // Update record
        if (!$id) {
            sendJsonResponse(['error' => 'ID required for update'], 400);
        }
        
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!$data) {
            sendJsonResponse(['error' => 'Invalid data'], 400);
        }
        
        $sets = [];
        $values = [];
        foreach ($data as $field => $value) {
            $sets[] = "$field = ?";
            $values[] = $value;
        }
        
        $values[] = $id;
        $sql = "UPDATE $table SET " . implode(',', $sets) . " WHERE $pk = ?";
        $stmt = $conn->prepare($sql);
        
        // Determine types
        $types = '';
        foreach ($values as $v) {
            if (is_int($v)) $types .= 'i';
            elseif (is_float($v)) $types .= 'd';
            else $types .= 's';
        }
        
        $stmt->bind_param($types, ...$values);
        
        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                sendJsonResponse(['success' => true, 'message' => 'Record updated successfully']);
            } else {
                sendJsonResponse(['error' => 'No changes made or record not found'], 404);
            }
        } else {
            sendJsonResponse(['error' => 'Failed to update: ' . $stmt->error], 500);
        }
        break;
        
    case 'DELETE':
        // Delete record
        if (!$id) {
            sendJsonResponse(['error' => 'ID required for delete'], 400);
        }
        
        $stmt = $conn->prepare("DELETE FROM $table WHERE $pk = ?");
        $stmt->bind_param("s", $id);
        
        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                sendJsonResponse(['success' => true, 'message' => 'Record deleted successfully']);
            } else {
                sendJsonResponse(['error' => 'Record not found'], 404);
            }
        } else {
            sendJsonResponse(['error' => 'Failed to delete: ' . $stmt->error], 500);
        }
        break;
        
    default:
        sendJsonResponse(['error' => 'Method not allowed'], 405);
}

$conn->close();
?>