<?php
session_start();
require 'database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $token = $_POST['token'];
  $password = $_POST['password'];
  $confirmPassword = $_POST['confirm_password'];

  // Verificar si el token es válido y aún no ha caducado
  $query = $conn->prepare('SELECT id FROM users WHERE reset_token = :token AND reset_token_expires > NOW()');
  $query->bindParam(':token', $token);
  $query->execute();
  $user = $query->fetch(PDO::FETCH_ASSOC);

  if ($user && $password === $confirmPassword) {
    // Actualizar la contraseña en la base de datos y eliminar el token
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $query = $conn->prepare('UPDATE users SET password = :password, reset_token = NULL, reset_token_expires = NULL WHERE id = :user_id');
    $query->bindParam(':password', $hashedPassword);
    $query->bindParam(':user_id', $user['id']);
    $query->execute();

    $_SESSION['message'] = 'Contraseña actualizada correctamente.';
    header('Location: login.php');
    exit();
  } else {
    $_SESSION['error'] = 'El token no es válido o la contraseña no coincide.';
    header('Location: reset_password.php?token=' . $token);
    exit();
  }
}
?>

<!DOCTYPE html>
<html>
<head>
  <title>Reset Password</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <?php if (isset($_SESSION['error'])): ?>
    <p>Error: <?php echo $_SESSION['error']; ?></p>
    <?php unset($_SESSION['error']); ?>
  <?php endif; ?>

  <h2>Reset Password</h2>
  <form method="post">
    <input type="hidden" name="token" value="<?php echo $_GET['token']; ?>">
    <label for="password">Nueva contraseña:</label>
    <input type="password" name="password" id="password" required>
    <br>
    <label for="confirm_password">Confirmar contraseña:</label>
    <input type="password" name="confirm_password" id="confirm_password" required>
    <br>
    <input type="submit" value="Restablecer contraseña">
  </form>
</body>
</html>