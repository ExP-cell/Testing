<?php
// setup_database.php - Complete database setup

echo "<h2>📦 Waste Management System - Database Setup</h2>";

$servername = "localhost";
$username = "root";
$password = "";

$conn = new mysqli($servername, $username, $password);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create database using your table name
$dbname = "s25101180_im2";
$conn->query("CREATE DATABASE IF NOT EXISTS $dbname");
$conn->select_db($dbname);

echo "<p style='color:green;'>✅ Using database: $dbname</p>";

// Drop existing tables to start fresh
$tables = ['pickup_logs', 'collection_fleet', 'collection_routes', 'waste_categories', 'disposal_facilities', 'users'];
foreach ($tables as $table) {
    $conn->query("DROP TABLE IF EXISTS $table");
}

// Create tables based on your schema
$sqls = [
    "collection_routes" => "CREATE TABLE collection_routes (
        RouteID VARCHAR(20) PRIMARY KEY,
        RouteName VARCHAR(150) NOT NULL,
        AreaDescription TEXT,
        ScheduledDay VARCHAR(50),
        Frequency VARCHAR(50)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
    
    "collection_fleet" => "CREATE TABLE collection_fleet (
        TruckID VARCHAR(20) PRIMARY KEY,
        TruckNumber VARCHAR(50) UNIQUE NOT NULL,
        Capacity VARCHAR(50),
        Status VARCHAR(50) DEFAULT 'Active',
        AssignedRouteID VARCHAR(20),
        VehicleType VARCHAR(100),
        CurrentDriver VARCHAR(100),
        LastService DATE,
        FOREIGN KEY (AssignedRouteID) REFERENCES collection_routes(RouteID) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
    
    "waste_categories" => "CREATE TABLE waste_categories (
        CategoryID VARCHAR(20) PRIMARY KEY,
        CategoryName VARCHAR(100) NOT NULL,
        IsRecyclable TINYINT(1) DEFAULT 0
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
    
    "disposal_facilities" => "CREATE TABLE disposal_facilities (
        FacilityID VARCHAR(20) PRIMARY KEY,
        FacilityName VARCHAR(150) NOT NULL,
        FacilityType VARCHAR(100),
        Address TEXT,
        ContactPerson VARCHAR(100),
        Phone VARCHAR(50)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
    
    "pickup_logs" => "CREATE TABLE pickup_logs (
        LogID INT AUTO_INCREMENT PRIMARY KEY,
        RouteID VARCHAR(20),
        TruckID VARCHAR(20),
        CategoryID VARCHAR(20),
        FacilityID VARCHAR(20),
        CollectionDate DATE,
        StartTime TIME,
        EndTime TIME,
        Weight DECIMAL(8,2),
        Remarks TEXT,
        Status VARCHAR(50) DEFAULT 'Completed',
        FOREIGN KEY (RouteID) REFERENCES collection_routes(RouteID) ON DELETE SET NULL,
        FOREIGN KEY (TruckID) REFERENCES collection_fleet(TruckID) ON DELETE SET NULL,
        FOREIGN KEY (CategoryID) REFERENCES waste_categories(CategoryID) ON DELETE SET NULL,
        FOREIGN KEY (FacilityID) REFERENCES disposal_facilities(FacilityID) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
    
    "users" => "CREATE TABLE users (
        UserID INT AUTO_INCREMENT PRIMARY KEY,
        Username VARCHAR(50) UNIQUE NOT NULL,
        Password VARCHAR(255) NOT NULL,
        FullName VARCHAR(100),
        Role VARCHAR(50) DEFAULT 'User',
        CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
];

foreach ($sqls as $name => $sql) {
    if ($conn->query($sql) === TRUE) {
        echo "<p style='color:green;'>✅ Table '$name' created</p>";
    } else {
        echo "<p style='color:red;'>❌ Error creating '$name': " . $conn->error . "</p>";
    }
}

// Insert sample data
echo "<hr><h3>📊 Inserting Sample Data...</h3>";

$conn->query("INSERT INTO collection_routes VALUES
    ('R001', 'Route1-Downtown', 'Downtown commercial area', 'Monday', 'Daily'),
    ('R002', 'Route2-NorthZone', 'North residential area', 'Tuesday', 'Daily'),
    ('R003', 'Route3-SouthZone', 'South residential area', 'Wednesday', 'Daily'),
    ('R004', 'Route4-EastZone', 'East commercial area', 'Thursday', 'Daily'),
    ('R005', 'Route5-WestZone', 'West industrial area', 'Friday', 'Daily')");
echo "<p>✅ Routes inserted</p>";

$conn->query("INSERT INTO collection_fleet VALUES
    ('V001', 'ABC-1234', '15,000kg', 'Active', 'R001', 'Compactor Truck', 'John Smith', '2024-05-10'),
    ('V002', 'DEF-5678', '12,000kg', 'Active', 'R002', 'Compactor Truck', 'Michael Brown', '2024-05-08'),
    ('V003', 'GHI-9012', '10,000kg', 'Under Maintenance', 'R003', 'Rear Loader', NULL, '2024-05-02'),
    ('V004', 'JKL-3456', '16,000kg', 'Active', 'R004', 'Compactor Truck', 'David Wilson', '2024-05-12'),
    ('V005', 'MNO-7890', '8,000kg', 'Out of Service', 'R005', 'Tipper Truck', NULL, '2024-04-25'),
    ('V006', 'PQR-2468', '10,000kg', 'Active', NULL, 'Rear Loader', 'James Taylor', '2024-05-09')");
echo "<p>✅ Fleet inserted</p>";

$conn->query("INSERT INTO waste_categories VALUES
    ('C001', 'Recyclable (Plastic)', 1),
    ('C002', 'Recyclable (Paper)', 1),
    ('C003', 'Organic Waste', 0),
    ('C004', 'General Waste', 0),
    ('C005', 'Metal', 1),
    ('C006', 'Glass', 1)");
echo "<p>✅ Categories inserted</p>";

$conn->query("INSERT INTO disposal_facilities VALUES
    ('F001', 'North Recycling Center', 'Recycling Facility', '123 North Ave', 'Maria Santos', '123-4567'),
    ('F002', 'South Waste Hub', 'Transfer Station', '456 South Road', 'Juan Cruz', '456-7890'),
    ('F003', 'East Processing Plant', 'Processing Facility', '789 East Park', 'Ana Reyes', '789-0123'),
    ('F004', 'West Compost Site', 'Composting Facility', '321 West Blvd', 'Pedro Dimagiba', '321-6547'),
    ('F005', 'Central MRF', 'Materials Recovery Facility', '654 City Center', 'Luz Mercado', '654-9870')");
echo "<p>✅ Facilities inserted</p>";

// Insert sample pickup logs
for ($i = 0; $i < 20; $i++) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $weight = rand(200, 1000);
    $routes = ['R001','R002','R003','R004','R005'];
    $trucks = ['V001','V002','V003','V004','V005','V006'];
    $categories = ['C001','C002','C003','C004','C005','C006'];
    $facilities = ['F001','F002','F003','F004','F005'];
    
    $route = $routes[array_rand($routes)];
    $truck = $trucks[array_rand($trucks)];
    $category = $categories[array_rand($categories)];
    $facility = $facilities[array_rand($facilities)];
    
    $conn->query("INSERT INTO pickup_logs 
        (CollectionDate, RouteID, TruckID, CategoryID, FacilityID, StartTime, EndTime, Weight, Remarks) 
        VALUES 
        ('$date', '$route', '$truck', '$category', '$facility', 
        '08:00:00', '10:30:00', $weight, 'Regular collection')");
}
echo "<p>✅ 20 pickup logs inserted</p>";

// Create admin user
$conn->query("INSERT IGNORE INTO users (Username, Password, FullName, Role) VALUES
    ('admin', 'admin123', 'Operations Team', 'Administrator'),
    ('user', 'user123', 'Staff Member', 'User')");
echo "<p>✅ Users created (admin/admin123)</p>";

// Create view for dashboard
$conn->query("CREATE OR REPLACE VIEW dashboard_stats AS
    SELECT 
        COUNT(*) as total_collections,
        SUM(Weight) as total_waste,
        AVG(Weight) as avg_weight,
        COUNT(DISTINCT DATE(CollectionDate)) as days_active,
        SUM(CASE WHEN wc.IsRecyclable = 1 THEN Weight ELSE 0 END) as recyclable_waste,
        COUNT(DISTINCT RouteID) as active_routes,
        COUNT(DISTINCT TruckID) as active_trucks
    FROM pickup_logs pl
    LEFT JOIN waste_categories wc ON pl.CategoryID = wc.CategoryID");

echo "<p style='color:green;'>✅ View 'dashboard_stats' created</p>";

echo "<hr>";
echo "<h3>✅ Setup Complete!</h3>";
echo "<p><strong>Database:</strong> $dbname</p>";
echo "<p><strong>Login:</strong> admin / admin123</p>";
echo "<p><a href='index.php'>Go to Waste Management System →</a></p>";

$conn->close();
?>