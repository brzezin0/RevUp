<?php
header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline'; img-src 'self';");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $servername = "localhost";
    $user = "root";
    $password = "haslo";
    $dbname = "bazacar"; 

    $conn = new mysqli($servername, $user, $password, $dbname, 3307);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Sanityzacja i przygotowanie danych wejściowych - usuwanie niepożadanych znaków które mogą być interpretowane jako część html lub JS
    //przekształca znaki na ich bezpieczne odpowiedniki
    //real escape string wstawia ukośniki przed pewnymi znakami - bezpieczeństwo
    $name = trim($conn->real_escape_string($_POST["name"]));
    $lastname = trim($conn->real_escape_string($_POST["lastname"]));
    $username = trim($conn->real_escape_string($_POST["username"]));
    $email = trim($conn->real_escape_string($_POST["email"]));
    $phone = trim($conn->real_escape_string($_POST["phone"]));
    $password = password_hash(trim($_POST["password"]), PASSWORD_BCRYPT); 
    $roleID = 1; // RoleID na 1 dla zwykłego odwiedzającego (klienta)
    $nip = trim($conn->real_escape_string($_POST["nip"]));
    $city = trim($conn->real_escape_string($_POST["city"]));
    $street = trim($conn->real_escape_string($_POST["street"]));
    $house = trim($conn->real_escape_string($_POST["house"]));
    $postcode = trim($conn->real_escape_string($_POST["postcode"]));


    $CheckQuery = $conn->prepare("SELECT Username FROM visitor WHERE Username = ?");
    $CheckQuery->bind_param("s", $username);
    $CheckQuery->execute();
    $CheckResult1 = $CheckQuery->get_result();
    $CheckQuery->close();
 
    $CheckQuery = $conn->prepare("SELECT Phone FROM contact WHERE Phone = ?");
    $CheckQuery->bind_param("s", $phone);
    $CheckQuery->execute();
    $CheckResult2 = $CheckQuery->get_result();
    $CheckQuery->close();

    $CheckQuery = $conn->prepare("SELECT Email FROM visitor WHERE Email = ?");
    $CheckQuery->bind_param("s", $email);
    $CheckQuery->execute();
    $CheckResult3 = $CheckQuery->get_result();
    $CheckQuery->close();


    if ($CheckResult1->num_rows > 0 || $CheckResult2->num_rows > 0 || $CheckResult3->num_rows > 0) {
        $error_message = "This username/phone/email is taken";
    }else{
// Użycie prepared statements - sql injection
    //instrukcja sql jest kompilowana z góry a dane są przesyłane oddzielnie i 
    //nie mogą zostać zinterpretowane jako część kodu SQL
    $stmt = $conn->prepare("INSERT INTO contact (phone, city, street, housenumber, postcode) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssis", $phone, $city, $street, $house, $postcode);
    if ($stmt->execute()) {
        $contactID = $conn->insert_id;
    } else {
        $error_message = 'Error: ';
        $stmt->close();
        $conn->close();
        exit;
    }
    $stmt->close();

    $stmt2 = $conn->prepare("INSERT INTO visitor (Name,Lastname, Username, Email, Password, ContactID, RoleID, NIP) VALUES (?,?, ?, ?, ?, ?, ?, ?)");
    $stmt2->bind_param("sssssiii", $name, $lastname, $username, $email, $password, $contactID, $roleID, $nip);
    $stmt2->execute();
    $stmt2->close();

    $conn->close();
    }
    $error_message = "Success!";

}
?>




<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="../styles/register.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>
    <main>
        <div class="container">
            <div class="title">Registration</div>
            <div id="error-message" style="color: red;"></div>

            <div class="content">
                <form action="register.php" method="post">
                    <div class="user-details">
                        <div class="input-box">
                            <label for="name">Name</label>
                            <input type="text" id="name" name="name" placeholder="Enter your name" required>
                        </div>
                        <div class="input-box">
                            <label for="lastname">Last name</label>
                            <input type="text" id="lastname" name="lastname" placeholder="Enter your last name" required>
                        </div>
                        <div class="input-box">
                            <label for="username">Username</label>
                            <input type="text" id="username" name="username" placeholder="Enter your username" required>
                        </div>
                        <div class="input-box">
                            <label for="email">Email</label>
                            <input type="text" id="email" name="email" placeholder="Enter your email" required>
                        </div>
                       
                        <div class="input-box">
                            <label for="password">Password</label>
                            <input type="password" id="password" name="password" placeholder="Enter your password" required>
                        </div>
                        <div class="input-box">
                            <label for="confirmedPassword">Confirm password</label>
                            <input type="password" id="confirmedPassword" name="confirmedPassword" placeholder="Confirm password" required>
                        </div>
                        <div class="input-box">
                            <label for="postcode">Postcode</label>
                            <input type="text" id="postcode" name="postcode" placeholder="Enter your postcode" required>
                        </div>
                        <div class="input-box">
                            <label for="postcode">City</label>
                            <input type="text" id="city" name="city" placeholder="Enter your city" required>
                        </div>
                        <div class="input-box">
                            <label for="postcode">Street</label>
                            <input type="text" id="street" name="street" placeholder="Enter your street" required>
                        </div>
                        <div class="input-box">
                            <label for="house">House Number</label>
                            <input type="text" id="house" name="house" placeholder="Enter your house number" required>
                        </div>
                        <div class="input-box">
                            <label for="phone">Phone Number</label>
                            <input type="text" id="phone" name="phone" placeholder="Enter your phone number" required>
                        </div>
                        <div class="input-box">
                            <label for="nip">NIP</label>
                            <input type="text" id="nip" name="nip" placeholder="Only if you need invoices in the future">
                        </div>
                    </div>

                    <div class="button">
                        <input type="submit" value="Register">
                    </div>
                    <div class="button">
                        <input type="button" onclick="window.location.href='../index.php'" value="Back to the main page">
                    </div>
                </form>
            </div>
        </div>
    </main>

 
</div>
<script src="../pages/validate.js"></script>

</body>
</html>


<script>
    var errorMessage = "<?php echo isset($error_message) ? htmlspecialchars($error_message) : ''; ?>";
    var errorMessageElement = document.getElementById("error-message");

    if (errorMessage) {
        errorMessageElement.textContent = errorMessage;
    }
</script>
