<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer-master/src/PHPMailer.php';
require 'PHPMailer-master/src/Exception.php';
require 'PHPMailer-master/src/SMTP.php';

// Verificar si se ha enviado un formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // Obtener el correo electrónico del formulario
  $email = $_POST['email'];

  // Verificar si el correo electrónico existe en la base de datos
  if (!empty($email)) {
    // Establecer la conexión con la base de datos
    $conn = new PDO("mysql:host=$server;dbname=php_login_database", 'root', '');
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $query = $conn->prepare('SELECT id FROM users WHERE email = :email');
    $query->bindParam(':email', $email);
    $query->execute();
    $user = $query->fetch(PDO::FETCH_ASSOC);

    if ($user) {
      // Generar un token único y seguro
      $token = bin2hex(random_bytes(32));

      // Almacenar el token y su fecha de expiración en la base de datos
      $query = $conn->prepare('UPDATE users SET reset_token = :token, reset_token_expires = DATE_ADD(NOW(), INTERVAL 1 HOUR) WHERE id = :user_id');
      $query->bindParam(':token', $token);
      $query->bindParam(':user_id', $user['id']);
      $query->execute();

      // Enviar correo electrónico al usuario con el enlace para restablecer la contraseña
      $resetLink = "https://example.com/reset_password.php?token=$token";

      // Configurar el correo electrónico
      $mail = new PHPMailer();
      $mail->isSMTP();
      $mail->Host = 'smtp.example.com'; // Configura el servidor SMTP que utilizarás
      $mail->SMTPAuth = true;
      $mail->Username = 'tu_correo@example.com'; // Coloca aquí tu dirección de correo electrónico
      $mail->Password = 'tu_contraseña'; // Coloca aquí tu contraseña de correo electrónico
      $mail->SMTPSecure = 'tls';
      $mail->Port = 587;

      $mail->setFrom('tu_correo@example.com', 'Tu Nombre'); // Configura la dirección y el nombre del remitente
      $mail->addAddress($email); // Agrega la dirección de correo electrónico del destinatario

      $mail->isHTML(true);
      $mail->Subject = 'Restablecimiento de contraseña';
      $mail->Body = "Haz clic en el siguiente enlace para restablecer tu contraseña: <a href='$resetLink'>$resetLink</a>";

      // Envía el correo electrónico
      if ($mail->send()) {
        $_SESSION['message'] = 'Se ha enviado un enlace de restablecimiento de contraseña a tu correo electrónico.';
        header('Location: ForgotPassword.php');
        exit();
      } else {
        $_SESSION['error'] = 'Ocurrió un error al enviar el correo electrónico. Por favor, inténtalo de nuevo más tarde.';
        header('Location: ForgotPassword.php');
        exit();
      }
    } else {
      // Código a ejecutar si no se encontró ningún usuario con el correo electrónico proporcionado
      $_SESSION['error'] = 'No se encontró ningún usuario con el correo electrónico proporcionado.';
      header('Location: ForgotPassword.php');
      exit();
    }
  } else {
    // Código a ejecutar si no se proporcionó un correo electrónico válido
    $_SESSION['error'] = 'Por favor, proporciona un correo electrónico válido.';
    header('Location: ForgotPassword.php');
    exit();
  }
}
?>
<!DOCTYPE html>
<html>
<head>
  <title>Forgot Password</title>
</head>
<body>
  <h1>Forgot Password</h1>
  <!-- Formulario para solicitar el correo electrónico -->
  <form method="POST" action="ForgotPassword.php">
    <label for="email">Correo electrónico:</label>
    <input type="email" name="email" required>
    <button type="submit">Enviar</button>
  </form>
</body>
</html>