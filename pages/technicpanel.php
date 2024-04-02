<?php
session_start();
header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline'; img-src 'self';");

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

if (isset($_POST['saveAccountDetails'])) {
    $name = mysqli_real_escape_string($db, $_POST['name']);
    $lastname = mysqli_real_escape_string($db, $_POST['lastname']);
    $username = mysqli_real_escape_string($db, $_POST['username']);
    $email = mysqli_real_escape_string($db, $_POST['email']);

    $updateQuery = $db->prepare("UPDATE visitor as v
        SET v.Name = ?, v.Lastname = ?, v.Username = ?, v.Email = ? WHERE v.VisitorID = ?");

$updateQuery->bind_param("ssssi", $name, $lastname, $username, $email, $visitorID);
    $updateQuery->close();
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

if (isset($_POST['removeButton'])) {
    $serviceIDToDelete = $_POST['service_id']; 

    $deleteQuery = "DELETE FROM services WHERE ServiceID = ?";

    if ($stmt = mysqli_prepare($db, $deleteQuery)) {
        mysqli_stmt_bind_param($stmt, "i", $serviceIDToDelete);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    } 
}

if(isset($_POST['insertNewService']) && isset($_FILES['img'])){
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    $serviceName = $_POST['serviceName'];
    $servicePrice = $_POST['price'];
    $serviceDescription = $_POST['serviceDescription'];

    $image = NULL;
    if ($_FILES['img']['error'] == 0) {
        $image = file_get_contents($_FILES['img']['tmp_name']);
    }

    $st = $db->prepare("INSERT INTO services (Name, Description, Price, image) VALUES (?,?,?,?)");
    $st->bind_param("ssds", $serviceName, $serviceDescription, $servicePrice, $image);
    $st->execute();
    $st->close();

}

if (isset($_POST['insertNewEmployee'])) {
    $employeeName = trim(mysqli_real_escape_string($db, $_POST['name']));
    $employeeLastname = trim(mysqli_real_escape_string($db, $_POST['lastName']));
    $employeeUsername = trim(mysqli_real_escape_string($db, $_POST['username']));
    $employeePassword = trim(mysqli_real_escape_string($db, $_POST['password']));
    $employeeConfirmedPassword = trim(mysqli_real_escape_string($db, $_POST['confirmedPassword']));
    $employeePhone = trim(mysqli_real_escape_string($db, $_POST['phone']));
    $employeeEmail = trim(mysqli_real_escape_string($db, $_POST['email']));
    $employeeCity = trim(mysqli_real_escape_string($db, $_POST['city']));
    $employeePostcode = trim(mysqli_real_escape_string($db, $_POST['postcode']));
    $employeeStreet = trim(mysqli_real_escape_string($db, $_POST['street']));
    $employeeHouseNumber = trim(mysqli_real_escape_string($db, $_POST['houseNumber']));
    $employeeSpecialization  = trim(mysqli_real_escape_string($db, $_POST['specialization']));
    if ($employeePassword !== $employeeConfirmedPassword) {
        return;
    }

    $hashedPassword = password_hash($employeePassword, PASSWORD_DEFAULT);
    $roleID = 0; 
    
    $stmtContact = $db->prepare("INSERT INTO contact (Phone,City,Street, HouseNumber) VALUES (?,?,?,?)");
    $stmtContact->bind_param("sssi", $employeePhone, $employeeCity, $employeeStreet, $employeeHouseNumber);
    
    if ($stmtContact->execute()) {
        $contactID = $db->insert_id; 
        $stmtContact->close();

   
        $stmtVisitor = $db->prepare("INSERT INTO visitor (Name, LastName, Email, Username, Password, ContactID, RoleID) VALUES (?,?, ?,?, ?, ?, ?)");
        $stmtVisitor->bind_param("sssssii", $employeeName, $employeeLastname, $employeeEmail, $employeeUsername, $hashedPassword, $contactID, $roleID);
        
        if ($stmtVisitor->execute()) {
            $visitorID = $db->insert_id; 

            $stmtTechnician = $db->prepare("INSERT INTO technicians (Name, LastName, ContactID, VisitorID, Specialization) VALUES (?,?, ?, ?,?)");
            $stmtTechnician->bind_param("ssiis", $employeeName, $employeeLastname, $contactID, $visitorID, $employeeSpecialization);
            $stmtTechnician->execute();
           
            $stmtTechnician->close();
        } 
        $stmtVisitor->close();
    } else {
        $stmtContact->close();
    }

    $db->close();
    header('Location: technicpanel.php');

}


?>
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="../styles/customerpanel.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
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
                <div class="button-64" role="button" id="employeeTab"><span class="text">New employee</span></div>
                <div class="button-64" role="button" id="bookingsTab"><span class="text">Bookings</span></div>
                <div class="button-64" role="button" id="newServiceTab"><span class="text">New service</span></div>
                <div class="button-64" role="button" id="servicesTab"><span class="text">Services</span></div>

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

                    
                  


                    <div class="buttons">
                        <button type="submit" name="saveAccountDetails" class="button-65"><span class="text">Save</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>

        

        <div id="employeeContent" class="tabContent">
     
        <form method="POST">
        <div class="welcome">
                <h1>New employee registration</h1>
            </div>
                <div class="boxes" id="boxes">

                    <div class="tiles">
                    <div class="tile"><h3><span>Name</span></h3><input type="text" class="type" name="name"
                                                                           placeholder="Name"></div>

                     <div class="tile"><h3><span>Last Name</span></h3><input type="text" class="type" name="lastName"
                                                                           placeholder="Last name"></div>
                    </div>

                    <div class="tiles">
                    <div class="tile"><h3><span>Username</span></h3><input type="text" class="type" name="username"
                                                                           placeholder="Username"></div>

                    <div class="tile"><h3><span>Phone</span></h3><input type="text" class="type" name="phone"
                                                                           placeholder="Phone"></div>
                    </div>
                    
                    <div class="tiles">
                    <div class="tile"><h3><span>E-mail</span></h3><input type="text" class="type" name="email"
                                                                           placeholder="Email"></div>

                    <div class="tile"><h3><span>City</span></h3><input type="text" class="type" name="city"
                                                                           placeholder="City"></div>
                    </div>
                    <div class="tiles">
                    <div class="tile"><h3><span>Postcode</span></h3><input type="text" class="type" name="postcode"
                                                                           placeholder="Postcode"></div>

                    <div class="tile"><h3><span>Street</span></h3><input type="text" class="type" name="street"
                                                                           placeholder="Street"></div>
                    </div>
                    <div class="tiles">
                    <div class="tile"><h3><span>House number</span></h3><input type="text" class="type" name="houseNumber"
                                                                           placeholder="House number"></div>

                    <div class="tile"><h3><span>Specialization</span></h3><input type="text" class="type" name="specialization"
                                                                           placeholder="Specialization"></div>
                    </div>
                    <div class="tiles">
                    <div class="tile"><h3><span>Password</span></h3><input type="password" class="type" name="password"
                                                                           placeholder="Password"></div>

                    <div class="tile"><h3><span>Confirm password</span></h3><input type="password" class="type" name="confirmedPassword"
                                                                           placeholder="Confirm password"></div>
                    </div>
                    <div class="buttons">
                        <button type="submit" name="insertNewEmployee" class="button-65"><span class="text">Save</span>
                        </button>
                    </div>
                </div>
            </form>
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
                <th>Contact</th>
                <th>Cancellation</th>

            </tr>
        </thead>
        <tbody>
            <?php
            $visitorID = $_SESSION['user_id'];
            $ordersQuery = $db->prepare("SELECT o.OrderID, s.Name AS ServiceName, s.Price AS ServicePrice,  v.Mark, v.Model, v.Plates, o.OrderStart, c.Phone
                                         FROM Orders AS o
                                         JOIN visitor as vis ON vis.VisitorID = o.VisitorID
                                         JOIN contact as c on c.ContactID = vis.ContactID
                                         JOIN services AS s ON o.ServiceID = s.ServiceID
                                         JOIN vehicles AS v ON o.VehicleID = v.VehicleID
                                         
                                         ORDER BY o.OrderStart ASC");
                                        
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
                $ePhone = htmlspecialchars($row['Phone']);
            ?>
            <tr>
                <td><?php echo $service_name; ?></td>
                <td>$<?php echo $service_price; ?></td>
                <td><?php echo $mark; ?></td>
                <td><?php echo $model; ?></td>
                <td><?php echo $plates; ?></td>
                <td><?php echo $date; ?></td>
                <td><?php echo $ePhone; ?></td>

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
<div id="newServiceContent" class="tabContent">
     
     <form method="POST" enctype="multipart/form-data">
     <div class="welcome">
             <h1>Add a new service</h1>
         </div>
             <div class="boxes" id="boxes">

                 <div class="tiles">
                 <div class="tile"><h3><span>Service name</span></h3><input type="text" class="type" name="serviceName"
                                                                        placeholder="Service name"></div>

                  <div class="tile"><h3><span>Price</span></h3>    <input type="number" class="type" name="price" placeholder="Price $" step="0.01">
</div>
                 </div>
                 <div class="widerTiles">
                 <div class="tile"><h3><span>Service description</span></h3><input type="text" class="type" name="serviceDescription"
                                                                        placeholder="Service description"></div>

                 
                 </div>
                 <div class="tiles">
                
                 <div class="tile"><h3><span>Upload image</span></h3>
<input type="file" id="img" name="img" accept="image/*">

                                                                    </div>
                 </div>
                
                 <div class="buttons">
                     <button type="submit" name="insertNewService" class="button-65"><span class="text">Save</span>
                     </button>
                 </div>
             </div>
         </form>
     </div>

     
     <div id="servicesContent" class="tabContent">
    <table>
        <thead>
            <tr>
                <th>Service name</th>
                <th>Service Price</th>
                <th>Service Description</th>
                <th>Removal</th>

            </tr>
        </thead>
        <tbody>
            <?php
            $visitorID = $_SESSION['user_id'];
            $ordersQuery = $db->prepare("SELECT ServiceID,Name, Price, Description
                                         FROM Services
                                         ");
                                        
            $ordersQuery->execute();
            $ordersResult = $ordersQuery->get_result();

            while ($row = mysqli_fetch_assoc($ordersResult)):
                $serviceID = htmlspecialchars($row['ServiceID']);
                $description = htmlspecialchars($row['Description']);
                $service_price = htmlspecialchars($row['Price']);
                $service_name = htmlspecialchars($row['Name']);
                            ?>
            <tr>
                <td><?php echo $service_name; ?></td>
                <td>$<?php echo $service_price; ?></td>
                <td><?php echo $description; ?></td>
                <form method="POST"><td>
                <input type="hidden" name="service_id" value="<?php echo $serviceID; ?>">

            <button type="submit" class="cancelButton" name="removeButton">&#x2716;</button>
        
        </td>
        </form>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>
    </div>


</div>

<script src="./tabsSwitcherAdmin.js"></script>
</body>

</html>
<script>var dateEl = document.getElementById('date');
    var timeEl = document.getElementById('time');

    document.getElementById('date-output').innerHTML = dateEl.type === 'date';
    document.getElementById('time-output').innerHTML = timeEl.type === 'time';


</script>