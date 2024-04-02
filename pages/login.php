<?php
session_start();
header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline'; img-src 'self';");

if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role_id'] == 0) {
        header("Location: ../pages/technicpanel.php");
    } elseif ($_SESSION['role_id'] == 1) {
        header("Location: ../pages/customerpanel.php");
    }
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Zastosowanie funkcji htmlspecialchars do sanityzacji danych wejściowych
    $username = trim(htmlspecialchars($_POST['username']));
    $pass = trim(htmlspecialchars($_POST['password']));

    if (empty($username)) {
      $error_message = 'Username is empty';
    } else if (empty($pass)) {
      $error_message = 'Password is empty';
    } else {
        $conn = mysqli_connect("localhost", "your_database_username", "your_database_password", "bazacar", 3307) or die("Connection failed");
        if (!$conn) {
          exit;
      }
        $query = $conn->prepare("SELECT VisitorID, Username, Password, RoleID FROM Visitor WHERE Username = ?");

        if ($query) {
            $query->bind_param("s", $username);
            $query->execute();
        } else {
            $error = $conn->errno . ' ' . $conn->error;
        }

        $query->store_result();

        if ($query->num_rows != 1) {
          $error_message = 'User not found.';
        } else {
            $query->bind_result($visitorId, $dbname, $hashedPassword, $roleId);
            $query->fetch();

            if (password_verify($pass, $hashedPassword)) {
                $_SESSION['user_id'] = $visitorId;
                $_SESSION['role_id'] = $roleId;
                session_regenerate_id(true);

                if ($roleId == 0) {
                    header("Location: ../pages/technicpanel.php");
                } elseif ($roleId == 1) {
                    header("Location: ../pages/customerpanel.php");
                } 
                exit();
            } else {
              $error_message = 'Invalid password';
            }
        }

        $query->close();
        $conn->close();
    }
}
?>


<!DOCTYPE html>
<html lang="en" dir="ltr">
  <head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="../styles/login.css">
     <meta name="viewport" content="width=device-width, initial-scale=1.0">
   </head>
<body>
  <div class="container">
    <div class="title">Sign in into your profile</div>
    <div id="error-message" style="color: red;"></div>

    <div class="content">
      <form action="#" method="post">
        <div class="user-details">
          <div class="input-box">
            <span class="details">Username</span>
            <input type="text" name="username" placeholder="Enter your username or e-mail" required>
          </div>
          <div class="input-box">
            <span class="details">Password</span>
            <input type="password" name="password" placeholder="Enter your password" required>
          </div>
        </div>
        <div class="button">
          <input type="submit" value="Login">
        </div>
        <div class="button">
            <input type="button" value="Password change">
        </div>
        <div class="button">
            <input type="button" onclick="window.location.href='../index.php'" value="Back to main page">
        </div>
      </form>
    </div>
  </div>
</body>
</html>
<script src="../pages/validate.js"></script>
<script>
    var errorMessage = "<?php echo isset($error_message) ? htmlspecialchars($error_message) : ''; ?>";
    var errorMessageElement = document.getElementById("error-message");

    if (errorMessage) {
        errorMessageElement.textContent = errorMessage;
    }
</script>
