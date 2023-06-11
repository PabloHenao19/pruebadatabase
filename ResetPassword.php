<?php
session_start(); // Iniciar la sesión para utilizar variables de sesión
require 'database.php'; // Requerir el archivo de conexión a la base de datos

if ($_SERVER['REQUEST_METHOD'] === 'POST') { // Verificar si se envió el formulario por el método POST
  $token = $_POST['token']; // Obtener el token enviado en el formulario
  $password = $_POST['password']; // Obtener la contraseña ingresada en el formulario
  $confirmPassword = $_POST['confirm_password']; // Obtener la confirmación de contraseña ingresada en el formulario

  // Verificar si el token es válido y aún no ha caducado
  $query = $conn->prepare('SELECT id FROM users WHERE reset_token = :token AND reset_token_expires > NOW()');
  $query->bindParam(':token', $token);
  $query->execute();
  $user = $query->fetch(PDO::FETCH_ASSOC);

  if ($user && $password === $confirmPassword) { // Verificar si el usuario existe y las contraseñas coinciden
    // Actualizar la contraseña en la base de datos y eliminar el token
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT); // Hashear la contraseña antes de almacenarla en la base de datos
    $query = $conn->prepare('UPDATE users SET password = :password, reset_token = NULL, reset_token_expires = NULL WHERE id = :user_id');
    $query->bindParam(':password', $hashedPassword);
    $query->bindParam(':user_id', $user['id']);
    $query->execute();

    $_SESSION['message'] = 'Contraseña actualizada correctamente.'; // Establecer un mensaje de éxito en la sesión
    header('Location: login.php'); // Redireccionar al usuario a la página de inicio de sesión
    exit();
  } else {
    $_SESSION['error'] = 'El token no es válido '; // Establecer un mensaje de error en la sesión
    header('Location: ResetPassword.php?token=' . $token); // Redireccionar al usuario de vuelta al formulario de restablecimiento de contraseña
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
  <?php if (isset($_SESSION['error'])): ?> <!--Verificar si hay un mensaje de error en la sesión-->
    <script>alert("Error: <?php echo $_SESSION['error']; ?>");</script> <!-- Mostrar el mensaje de error mediante una alerta de JavaScript -->
    <?php unset($_SESSION['error']); ?> <!-- Eliminar el mensaje de error de la sesión -->
  <?php endif; ?>

  <h2>Reset Password</h2>
  <form method="post">
    <input type="hidden" name="token" value="<?php echo isset($_GET['token']) ? $_GET['token'] : ''; ?>"> 
    <label for="password">Nueva contraseña:</label> 
    <input type="password" name="password" id="password" required> 
    <br>
    <label for="confirm_password">Confirmar contraseña:</label> 
    <input type="password" name="confirm_password" id="confirm_password" required> 
    <?php if (isset($_SESSION['error']) && $_SESSION['error'] === 'Contraseña no coincide.'): ?> 
      <script>alert("Contraseña no coincide.");</script> <!-- Mostrar la alerta de error específica mediante una alerta de JavaScript -->
    <?php endif; ?>
    <br>
    <input type="submit" value="Restablecer contraseña"> 
  </form>
</body>
</html>