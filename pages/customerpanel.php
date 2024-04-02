<?php
session_start();
header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline'; img-src 'self' data:;");

if (isset($_GET['logout'])) {
    $_SESSION = array();

    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }

    session_destroy();

    header("Location: ../index.php");
    exit;
}
$name = $lastname = $username = $email = $postcode = $city = $street = $phone = $nip = $housenumber = "";

$db = mysqli_connect("localhost", "your_database_username", "your_database_password", "bazacar", 3307) or die("Connection failed");

if (isset($_SESSION['user_id'])) {
    $visitorId = $_SESSION['user_id'];
    $query = $db->prepare("SELECT v.Name, v.Lastname, v.Username, v.Email, ct.Postcode, ct.City, ct.Street, ct.Phone, v.NIP, ct.HouseNumber
    FROM visitor AS v
    JOIN contact AS ct ON v.ContactID = ct.ContactID
    WHERE v.VisitorID = ?");
    $query->bind_param("i", $visitorId);
    $query->execute();
    $query->bind_result($name, $lastname, $username, $email, $postcode, $city, $street, $phone, $nip, $housenumber);
    $query->fetch();
    $query->close();

    $vehicleQuery = $db->prepare("SELECT Mark, Model, Year, VIN, EngineCapacity, Power, Mileage, Plates FROM Vehicles WHERE VisitorID = ?");
    $vehicleQuery->bind_param("i", $visitorId);
    $vehicleQuery->execute();
    $vehicleQuery->bind_result($mark, $model, $year, $vin, $engineCapacity, $power, $mileage, $plates);
    $vehicleQuery->fetch();
    $vehicleQuery->close();
} 
if (isset($_POST['cancelButton'])) {
    $orderIDToDelete = $_POST['order_id']; 

    $deleteQuery = "DELETE FROM Orders WHERE OrderID = ?";

    if ($stmt = mysqli_prepare($db, $deleteQuery)) {
        mysqli_stmt_bind_param($stmt, "i", $orderIDToDelete);

        if (mysqli_stmt_execute($stmt)) {
              
        }

        mysqli_stmt_close($stmt);
    } 
}
if (isset($_POST['saveAccountDetails'])) {
    $name = trim(mysqli_real_escape_string($db, $_POST['name']));
    $lastname = trim(mysqli_real_escape_string($db, $_POST['lastname']));
    $username = trim(mysqli_real_escape_string($db, $_POST['username']));
    $email = trim(mysqli_real_escape_string($db, $_POST['email']));
    $postcode = trim(mysqli_real_escape_string($db, $_POST['postcode']));
    $city = trim(mysqli_real_escape_string($db, $_POST['city']));
    $street = trim(mysqli_real_escape_string($db, $_POST['street']));
    $phone = trim(mysqli_real_escape_string($db, $_POST['phone']));
    $nip = trim(mysqli_real_escape_string($db, $_POST['nip']));
    $housenumber = trim(mysqli_real_escape_string($db, $_POST['housenumber']));

    $updateQuery = $db->prepare("UPDATE visitor AS v
        JOIN contact AS ct ON v.ContactID = ct.ContactID
        SET v.Name = ?, v.Lastname = ?, v.Username = ?, v.Email = ?, v.NIP = ?, ct.Phone = ?, ct.City = ?, ct.Street = ?, ct.HouseNumber = ?, ct.Postcode = ?
        WHERE v.VisitorID = ?");

    $updateQuery->bind_param("ssssssssssi", $name, $lastname, $username, $email, $nip, $phone, $city, $street, $housenumber, $postcode, $visitorId);


    $updateQuery->close();
}
if (isset($_POST['saveNewVehicleDetails'])) { 
    $visitorID = $_SESSION['user_id'];
    $mark = trim(mysqli_real_escape_string($db, $_POST['mark']));
    $model = trim(mysqli_real_escape_string($db, $_POST['model']));
    $year = $_POST['year'];
    $vin = trim(mysqli_real_escape_string($db, $_POST['vin']));
    $power = $_POST['power'];
    $mileage =$_POST['mileage'];
    $plates = trim(mysqli_real_escape_string($db, $_POST['plates']));
    $engineCapacity =$_POST['engineCapacity'];
    
    $vinCheckQuery = $db->prepare("SELECT VIN FROM Vehicles WHERE VIN = ?");
    $vinCheckQuery->bind_param("s", $vin);
    $vinCheckQuery->execute();
    $vinCheckResult = $vinCheckQuery->get_result();
    $vinCheckQuery->close();
    $deleteQuery = $db->prepare("DELETE FROM Vehicles WHERE VisitorID = ?");
    $deleteQuery->bind_param("i", $visitorID);
    
    $deleteQuery->close();
    if ($vinCheckResult->num_rows > 0) {
    } else {
        if ($mark && $model && $year && $vin && $power && $engineCapacity && $mileage && $plates) {
            $query = $db->prepare("INSERT INTO Vehicles (VisitorID, Mark, Model, Year, VIN, Power, EngineCapacity, Mileage, Plates) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $query->bind_param("issisisss", $visitorID, $mark, $model, $year, $vin,  $power,$engineCapacity, $mileage, $plates);

            if ($query->execute()) {
            } else {
            }

            $query->close();
        }
    }
}

if (isset($_POST['saveVehicleDetails'])) {
    $visitorID = $_SESSION['user_id']; 
    $mark = trim(mysqli_real_escape_string($db, $_POST['mark']));
    $model = trim(mysqli_real_escape_string($db, $_POST['model']));
    $year = $_POST['year'];
    $vin = trim(mysqli_real_escape_string($db, $_POST['vin']));
    $power = $_POST['power'];
    $engineCapacity = $_POST['engineCapacity'];
    $plates = trim(mysqli_real_escape_string($db, $_POST['plates']));
    $mileage = $_POST['mileage'];

    $updateQuery = $db->prepare("UPDATE vehicles
        SET Mark = ?, Model = ?, Year = ?, VIN = ?, Power = ?, EngineCapacity = ?, Plates = ?, Mileage = ?
        WHERE VisitorID = ? AND VIN = ?");

    $updateQuery->bind_param("sssssdisss", $mark, $model, $year, $vin, $power, $engineCapacity, $visitorID, $vin,$mileage,$plates);

    if ($updateQuery->execute()) {
    } 

    $updateQuery->close();
}
if (isset($_POST['bookService'])) {
    $serviceID = $_POST['service_id'];
    $orderValue = $_POST['service_price'];
    $visitorID = $_SESSION['user_id'];

      $orderStart = date('Y-m-d H:i:s', strtotime($_POST['chosen_date'] . ' ' . $_POST['chosen_time']));
    $orderEnd = date('Y-m-d H:i:s', strtotime($orderStart . ' +1 hour'));
    
    $checkOrderQuery = $db->prepare("SELECT * FROM Orders WHERE OrderStart < ? AND DATE_ADD(OrderStart, INTERVAL 1 HOUR) > ?");
    $checkOrderQuery->bind_param("ss", $orderEnd, $orderStart);
    $checkOrderQuery->execute();
    $orderExists = $checkOrderQuery->get_result()->num_rows > 0;
    $checkOrderQuery->close();
    if ($orderExists) {
    } else {
        $vehicleQuery = $db->prepare("SELECT VehicleID FROM vehicles WHERE VisitorID = ?");
        $vehicleQuery->bind_param("i", $visitorID);
        $vehicleQuery->execute();
        $vehicleResult = $vehicleQuery->get_result();
        $vehicleQuery->close();
    
        if ($vehicleResult->num_rows > 0) {
            $vehicleRow = $vehicleResult->fetch_assoc();
            $vehicleID = $vehicleRow['VehicleID']; 
    
            $insertOrder = $db->prepare("INSERT INTO Orders (ServiceID, VehicleID, VisitorID, OrderStart, OrderValue) VALUES (?, ?, ?, ?, ?)");
            $insertOrder->bind_param("iiisd", $serviceID, $vehicleID, $visitorID, $orderStart, $orderValue);
    
            if ($insertOrder->execute()) {
            } else {
            }
    
            $insertOrder->close();
        } 
    }
}    

$query = "SELECT * FROM services";
$result = mysqli_query($db, $query);
?>
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="../styles/customerpanel.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Dashboard</title>
</head>

<body>
<div class="app">
    <div class="leftBox">
        <div class="logo">
            <img src="../assets/logo.png" alt="image">
        </div>

        <div class="navbar">
            <div class="list">
                <div class="button-64" role="button" id="accountTab"><span class="text">Account</span></div>
                <div class="button-64" role="button" id="vehicleTab"><span class="text">Vehicle</span></div>
                <div class="button-64" role="button" id="servicesTab"><span class="text">Services</span></div>
                <div class="button-64" role="button" id="bookingsTab"><span class="text">Bookings</span></div>
                <div class="button-64" role="button" onclick="window.location='?logout=true'"><span class="text">Sign out</span>
                </div>

            </div>

        </div>

        <div class="footer">
            <h1>Revup<small>©</small></h1>
        </div>

    </div>


    <div class="rightBox">
        <div id="accountContent" class="tabContent">
            <div class="welcome">
                <h1>Welcome <?php echo htmlspecialchars($name); ?></h1>
            </div>
            <form method="POST">
                <div class="boxes" id="boxes">

                    <div class="tiles">
                        <div class="tile">
                            <h3>
                                <span>Name</span>
                            </h3>
                            <input type="text" id="name" class="type" name="name" placeholder="Your name"
                                   value="<?php echo htmlspecialchars($name); ?>">

                        </div>

                        <div class="tile">
                            <h3>
                                <span>Last name</span>
                            </h3>
                            <input type="text" id="lastname" class="type" name="lastname" placeholder="Your last name"
                                   value="<?php echo htmlspecialchars($lastname); ?>">
                        </div>
                    </div>

                    <div class="tiles">
                        <div class="tile">
                            <h3>
                                <span>Username</span>
                            </h3>
                            <input type="text" id="username" class="type" name="username" placeholder="Your username"
                                   value="<?php echo htmlspecialchars($username); ?>">
                        </div>

                        <div class="tile">
                            <h3>
                                <span>E-mail</span>
                            </h3>
                            <input type="text" id="email" class="type" name="email" placeholder="Your e-mail"
                                   value="<?php echo htmlspecialchars($email); ?>">
                        </div>
                    </div>

                    <div class="tiles">
                        <div class="tile">
                            <h3>
                                <span>Postcode</span>
                            </h3>
                            <input type="text" id="postcode" class="type" name="postcode" placeholder="Postcode"
                                   value="<?php echo htmlspecialchars($postcode); ?>">
                        </div>

                        <div class="tile">
                            <h3>
                                <span>City</span>
                            </h3>
                            <input type="text" id="city" class="type" name="city" placeholder="City"
                                   value="<?php echo htmlspecialchars($city); ?>">
                        </div>

                    </div>
                    <div class="tiles">
                        <div class="tile">
                            <h3>
                                <span>Street</span>
                            </h3>
                            <input type="text" id="street" class="type" name="street" placeholder="Street"
                                   value="<?php echo htmlspecialchars($street); ?>">
                        </div>

                        <div class="tile">
                            <h3>
                                <span>Phone</span>
                            </h3>
                            <input type="text" id="phone" class="type" name="phone" placeholder="Phone"
                                   value="<?php echo htmlspecialchars($phone); ?>">
                        </div>

                    </div>
                    <div class="tiles">
                        <div class="tile">
                            <h3>
                                <span>House number</span>
                            </h3>
                            <input type="text" id="housenumber" class="type" name="housenumber"
                                   placeholder="housenumber" value="<?php echo htmlspecialchars($housenumber); ?>">
                        </div>

                        <div class="tile">
                            <h3>
                                <span>NIP</span>
                            </h3>
                            <input type="text" id="nip" class="type" name="nip" placeholder="NIP (optional)"
                                   value="<?php echo htmlspecialchars($nip); ?>">
                        </div>

                    </div>


                    <div class="buttons">
                        <button type="submit" name="saveAccountDetails" class="button-65"><span class="text">Save</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <div id="vehicleOptions" class="tabContent">
            <div class="pickButtons">
                <div class="button-67" role="button" id="addVehicle"><span>Add new vehicle</span></div>
                <div class="button-66" role="button" id="editVehicle"><span>Edit Vehicle data</span></div>
            </div>


            <div class="boxes2" id="boxes3">
                <form method="POST">
                    <div class="tiles">
                        <div class="tile"><h3><span>Mark</span></h3><input type="text" class="type" name="mark"
                                                                           placeholder="Mark"></div>
                        <div class="tile"><h3><span>Model</span></h3><input type="text" class="type" name="model"
                                                                            placeholder="Model"></div>

                    </div>
                    <div class="tiles">
                        <div class="tile"><h3><span>Year</span></h3><input type="text" class="type" name="year"
                                                                           placeholder="Year"></div>
                        <div class="tile"><h3><span>VIN</span></h3><input type="text" class="type" name="vin"
                                                                          placeholder="VIN"></div>
                    </div>
                    <div class="tiles">
                        <div class="tile"><h3><span>Engine (L)</span></h3><input type="text" class="type"
                                                                                 name="engineCapacity"
                                                                                 placeholder="Engine Capacity"></div>
                        <div class="tile"><h3><span>Power (hp)</span></h3><input type="text" class="type" name="power"
                                                                                 placeholder="Power"></div>

                    </div>
                    <div class="tiles">
                        <div class="tile"><h3><span>Mileage</span></h3><input type="text" class="type"
                                                                                 name="mileage"
                                                                                 placeholder="Mileage"></div>
                        <div class="tile"><h3><span>License plates</span></h3><input type="text" class="type" name="plates"
                                                                                 placeholder="Plates"></div>

                    </div>
                    <div class="buttons">
                        <button type="submit" name="saveNewVehicleDetails" class="button-65"><span
                                    class="text">Save</span>
                        </button>
                    </div>
                </form>
            </div>
            <div class="boxes2" id="boxes4" style="display:none">
                <form method="POST">
                    <div class="tiles">
                        <div class="tile"><h3><span>Mark</span></h3><input type="text" class="type" name="mark"
                                                                           placeholder="Mark"
                                                                           value="<?php echo htmlspecialchars($mark); ?>">
                        </div>
                        <div class="tile"><h3><span>Model</span></h3><input type="text" class="type" name="model"
                                                                            placeholder="Model"
                                                                            value="<?php echo htmlspecialchars($model); ?>">
                        </div>

                    </div>
                    <div class="tiles">
                        <div class="tile"><h3><span>Year</span></h3><input type="text" class="type" name="year"
                                                                           placeholder="Year"
                                                                           value="<?php echo htmlspecialchars($year); ?>">
                        </div>
                        <div class="tile"><h3><span>VIN</span></h3><input type="text" class="type" name="vin"
                                                                          placeholder="VIN"
                                                                          value="<?php echo htmlspecialchars($vin); ?>">
                        </div>
                    </div>
                    <div class="tiles">
                        <div class="tile"><h3><span>Engine (L)</span></h3><input type="text" class="type"
                                                                                 name="engineCapacity"
                                                                                 placeholder="Engine Capacity"
                                                                                 value="<?php echo htmlspecialchars($engineCapacity); ?>">
                        </div>
                        <div class="tile"><h3><span>Power (hp)</span></h3><input type="text" class="type" name="power"
                                                                                 placeholder="Power"
                                                                                 value="<?php echo htmlspecialchars($power); ?>">
                        </div>
                        <input type="hidden" name="vehicleID" value="<?php echo htmlspecialchars($vehicleID); ?>">

                    </div>
                    <div class="tiles">
                        <div class="tile"><h3><span>License Plates</span></h3><input type="text" class="type"
                                                                                 name="plates"
                                                                                 placeholder="Plates"
                                                                                 value="<?php echo htmlspecialchars($plates); ?>">
                        </div>
                        <div class="tile"><h3><span>Mileage</span></h3><input type="text" class="type" name="mileage"
                                                                                 placeholder="Mileage"
                                                                                 value="<?php echo htmlspecialchars($mileage); ?>">
                        </div>
                        <input type="hidden" name="vehicleID" value="<?php echo htmlspecialchars($vehicleID); ?>">

                    </div>
                    <div class="buttons">
                        <button type="submit" name="saveVehicleDetails" class="button-65"><span class="text">Save</span>
                        </button>
                    </div>
                </form>

            </div>
        </div>

        <div id="servicesContent" class="tabContent">
            <div class="servicesBox">
                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                    <?php
                    $service_id = htmlspecialchars($row['ServiceID']);
                    $service_name = htmlspecialchars($row['Name']);
                    $service_description = htmlspecialchars($row['Description']);
                    $service_price = htmlspecialchars($row['Price']);
                    $service_image = $row['image'];
                    ?>
                    <form method="post">
                        <div class="Card">
                            <div class="serviceImage">
                                <?php if ($service_image): ?>
                                    <?php
                                    $base64Image = base64_encode($service_image);
                                    $imageMimeType = 'image/png'; 
                                    ?>
                                    <img src="data:<?php echo $imageMimeType; ?>;base64,<?php echo $base64Image; ?>"
                                         alt="Service Image">
                                <?php else: ?>
                                    <img src="placeholder_image.png" alt="Default Image">
                                <?php endif; ?>
                            </div>
                            <div class="rightService">
                                <div class="serviceHeader"><h2><?php echo $service_name; ?></h2></div>
                                <div class="serviceDescription"><?php echo $service_description; ?></div>
                                <div class="ServiceLowerPanel">
                                    <div class="servicePrice">$<?php echo $service_price; ?></div>
                                    <input type="hidden" name="vehicle_id" value="<?php echo $vehicleID; ?>">
                                    <input type="hidden" name="service_id" value="<?php echo $service_id; ?>">
                                    <input type="hidden" name="service_price" value="<?php echo $service_price; ?>">
                                    <div class="datetimepicker">
                                        <?php
                                        $tomorrow = date('Y-m-d', strtotime('+1 day'));
                                        ?>
                                        <input type="date" id="date" name="chosen_date" value="<?php echo $tomorrow; ?>"
                                               min="<?php echo $tomorrow; ?>">
                                        <input type="time" id="time" name="chosen_time" value="08:00">

                                    </div>
                                    <div class="buttonContainer">
                                        <button type="submit" name="bookService" class="button-68"><span>Book</span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                <?php endwhile; ?>
            </div>
        </div>
        <div id="bookingsContent" class="tabContent">
    <table>
        <thead>
            <tr>
                <th>Service name</th>
                <th>Service Price</th>
                <th>Mark</th>
                <th>Model</th>
                <th>License plates</th>
                <th>Service date</th>
                <th>Cancellation</th>

            </tr>
        </thead>
        <tbody>
            <?php
            $visitorID = $_SESSION['user_id'];
            $ordersQuery = $db->prepare("SELECT o.OrderID, s.Name AS ServiceName, s.Price AS ServicePrice, v.Mark, v.Model, v.Plates, o.OrderStart
                                         FROM Orders AS o
                                         JOIN services AS s ON o.ServiceID = s.ServiceID
                                         JOIN vehicles AS v ON o.VehicleID = v.VehicleID
                                         WHERE o.VisitorID = ?
                                         ORDER BY o.OrderStart ASC");
            $ordersQuery->bind_param("i", $visitorID);
            $ordersQuery->execute();
            $ordersResult = $ordersQuery->get_result();

            while ($row = mysqli_fetch_assoc($ordersResult)):
                $orderID = htmlspecialchars($row['OrderID']);
                $service_name = htmlspecialchars($row['ServiceName']);
                $service_price = htmlspecialchars($row['ServicePrice']);
                $mark = htmlspecialchars($row['Mark']);
                $model = htmlspecialchars($row['Model']);
                $plates = htmlspecialchars($row['Plates']);
                $date = htmlspecialchars($row['OrderStart']);
            ?>
            <tr>
                <td><?php echo $service_name; ?></td>
                <td>$<?php echo $service_price; ?></td>
                <td><?php echo $mark; ?></td>
                <td><?php echo $model; ?></td>
                <td><?php echo $plates; ?></td>
                <td><?php echo $date; ?></td>
                <form method="POST"><td>
                <input type="hidden" name="order_id" value="<?php echo $orderID; ?>">

            <button type="submit" class="cancelButton" name="cancelButton">&#x2716;</button>
        
        </td>
        </form>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

    </div>


</div>

<script src="./tabsSwitcher.js"></script>
</body>

</html>
<script>var dateEl = document.getElementById('date');
    var timeEl = document.getElementById('time');

    document.getElementById('date-output').innerHTML = dateEl.type === 'date';
    document.getElementById('time-output').innerHTML = timeEl.type === 'time';


</script>