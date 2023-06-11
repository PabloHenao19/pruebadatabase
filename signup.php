<?php
//Con el metodo require se llama el archivo database.php
require 'database.php';

//validación del formulario
if (!empty($_POST['email']) && !empty($_POST['password']) && !empty($_POST['confirm_password'])) {
  
  // Verificar si las contraseñas coinciden

  if ($_POST['password'] !== $_POST['confirm_password']) {
    
    echo "<script>alert('Las contraseñas no coinciden');</script>";
  } else {
    // Verificar si el correo electrónico ya existe en la base de datos
    $existingEmail = $_POST['email'];
    $sql = "SELECT * FROM users WHERE email = :email";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':email', $existingEmail);
    $stmt->execute();

    // Si la consulta devuelve algún resultado (es decir, si rowCount() es mayor que 0),
    // se establece un mensaje de error indicando que el correo electrónico ya está registrado.
    if ($stmt->rowCount() > 0) {
  
      echo "<script>alert('El correo electrónico ya está registrado');</script>";
    } else {
      // Continuar con la inserción en la base de datos
      $sql = "INSERT INTO users (email, password) VALUES (:email, :password)";
      $stmt = $conn->prepare($sql);
      $stmt->bindParam(':email', $_POST['email']);

      // password_hash crea una contraseña encriptada y segura
      $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
      $stmt->bindParam(':password', $password);

      if ($stmt->execute()) {
        $message = 'USUARIO CREADO CON ÉXITO';
        echo "<script>alert('USUARIO CREADO CON ÉXITO');</script>";
      } else {
        $message = 'ERROR DE CONEXIÓN';
        echo "<script>alert('ERROR DE CONEXIÓN');</script>";
      }
    }
  }
} else {
  $message = 'Por favor, completa todos los campos';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SignUp</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<!-- Esta línea de código verifica si la variable $message no está vacía. Si $message contiene un mensaje 
(es decir, no está vacío), se ejecuta el bloque de código que sigue a continuación.-->
<?php if(!empty($message)): ?>
  <p> <?= $message ?></p>
<?php endif; ?>

<h1>SignUp</h1>
    <span>or <a href="login.php">Login</a></span>

    <form action="signup.php" method="post">
<input type="text" name="email" placeholder="Ingresar correo">
<input type="password" name="password" placeholder="Ingresar contraseña">
<input type="password" name="confirm_password" placeholder="Confirmar tu contraseña">
<input type="submit" value="Send">

    </form>


</body>
</html>