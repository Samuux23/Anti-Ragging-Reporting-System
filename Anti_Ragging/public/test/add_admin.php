<?php

$host = 'localhost';
$dbname = 'u192900825_anti_ragging';
$user = 'u192900825_ar_admin';
$pass = '8pqCpmwiC!F4Bq5';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("DB Connection failed: " . $e->getMessage());
}

// ---- Insert Admin ----
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username  = trim($_POST['username']);
    $password  = trim($_POST['password']);
    $full_name = trim($_POST['full_name']);
    $email     = trim($_POST['email']);
    $role      = $_POST['role'] ?? 'admin';

    // Hash the password securely
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    try {
        $sql = "INSERT INTO ar_admin (username, password, full_name, email, role) 
                VALUES (:username, :password, :full_name, :email, :role)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':username'   => $username,
            ':password'   => $hashedPassword,
            ':full_name'  => $full_name,
            ':email'      => $email,
            ':role'       => $role
        ]);
        echo "✅ Admin inserted successfully!";
    } catch (PDOException $e) {
        if ($e->getCode() == 23000) {
            echo "❌ Username already exists!";
        } else {
            echo "❌ Error: " . $e->getMessage();
        }
    }
}
?>

<!-- Simple HTML form -->
<form method="post">
  <label>Username:</label><br>
  <input type="text" name="username" required><br><br>

  <label>Password:</label><br>
  <input type="password" name="password" required><br><br>

  <label>Full Name:</label><br>
  <input type="text" name="full_name"><br><br>

  <label>Email:</label><br>
  <input type="email" name="email"><br><br>

  <label>Role:</label><br>
  <select name="role">
    <option value="super_admin">Super Admin</option>
    <option value="admin" selected>Admin</option>
    <option value="moderator">Moderator</option>
  </select><br><br>

  <button type="submit">Add Admin</button>
</form>
