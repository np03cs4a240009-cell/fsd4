<?php
// Initialize variables
$name = $email = $password = $confirmPassword = "";
$errors = [];
$successMessage = "";

// If form submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // 1. Collect form data
    $name = trim($_POST["name"]);
    $email = trim($_POST["email"]);
    $password = $_POST["password"];
    $confirmPassword = $_POST["confirm_password"];

    // 2. Validation
    if (empty($name)) {
        $errors['name'] = "Name is required.";
    }

    if (empty($email)) {
        $errors['email'] = "Email is required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = "Invalid email format.";
    }

    if (empty($password)) {
        $errors['password'] = "Password is required.";
    } elseif (strlen($password) < 6) {
        $errors['password'] = "Password must be at least 6 characters.";
    } elseif (!preg_match('/[!@#$%^&*]/', $password)) {
        $errors['password'] = "Password must contain at least one special character (!@#$%^&*).";
    }

    if ($password !== $confirmPassword) {
        $errors['confirm_password'] = "Passwords do not match.";
    }

    // 3. If no errors → Save user
    if (empty($errors)) {
        $file = "users.json";

        // Read JSON file
        if (!file_exists($file)) {
            file_put_contents($file, "[]");
        }

        $jsonData = file_get_contents($file);
        if ($jsonData === false) {
            $errors['file'] = "Error reading the JSON file.";
        } else {
            $users = json_decode($jsonData, true);

            if ($users === null && json_last_error() !== JSON_ERROR_NONE) {
                $errors['file'] = "Error decoding JSON data.";
            } else {
                // 4. Hash password
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

                // 5. Create new user array
                $newUser = [
                    "name" => $name,
                    "email" => $email,
                    "password" => $hashedPassword
                ];

                // 6. Add to array
                $users[] = $newUser;

                // 7. Save back to JSON file
                if (file_put_contents($file, json_encode($users, JSON_PRETTY_PRINT)) === false) {
                    $errors['file'] = "Error writing to JSON file.";
                } else {
                    $successMessage = "Registration successful!";
                    // Clear fields
                    $name = $email = $password = $confirmPassword = "";
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>User Registration</title>
    <style>
        .error { color: red; font-size: 14px; }
        .success { color: green; font-size: 16px; margin-bottom: 15px; }
        form { width: 350px; padding: 20px; border: 1px solid #ccc; }
        label { font-weight: bold; }
        input { width: 100%; padding: 8px; margin-bottom: 8px; }
    </style>
</head>
<body>

<h2>User Registration</h2>

<?php if (!empty($successMessage)) echo "<div class='success'>$successMessage</div>"; ?>

<form action="" method="POST">

    <label>Name:</label>
    <input type="text" name="name" value="<?php echo htmlspecialchars($name); ?>">
    <div class="error"><?php echo $errors['name'] ?? ''; ?></div>

    <label>Email Address:</label>
    <input type="text" name="email" value="<?php echo htmlspecialchars($email); ?>">
    <div class="error"><?php echo $errors['email'] ?? ''; ?></div>

    <label>Password:</label>
    <input type="password" name="password">
    <div class="error"><?php echo $errors['password'] ?? ''; ?></div>

    <label>Confirm Password:</label>
    <input type="password" name="confirm_password">
    <div class="error"><?php echo $errors['confirm_password'] ?? ''; ?></div>

    <button type="submit">Register</button>

    <div class="error"><?php echo $errors['file'] ?? ''; ?></div>
</form>

</body>
</html>