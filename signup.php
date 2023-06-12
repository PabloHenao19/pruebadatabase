<?php
// Con el método require se llama al archivo database.php
require 'database.php';

// Validación del formulario
if (!empty($_POST['email']) && !empty($_POST['password']) && !empty($_POST['confirm_password']) && !empty($_POST['nombre']) && !empty($_POST['apellido']) && !empty($_POST['fecha_nacimiento']) && !empty($_POST['direccion']) && !empty($_POST['telefono'])) {
  // Verificar si las contraseñas coinciden
  if ($_POST['password'] !== $_POST['confirm_password']) {
    echo "<script>alert('Las contraseñas no coinciden');</script>";
  } else {
    // Validar la fecha de nacimiento
    $fechaNacimiento = $_POST['fecha_nacimiento'];
    $edadMinima = 18;
    $fechaActual = new DateTime();
    $fechaNacimiento = new DateTime($fechaNacimiento);
    $diferencia = $fechaActual->diff($fechaNacimiento);
    $edad = $diferencia->y;

    if ($edad < $edadMinima) {
      echo "<script>alert('Debes ser mayor de 18 años');</script>";
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
        // Validar nombre y apellido (caracteres especiales no permitidos)
        $nombre = $_POST['nombre'];
        $apellido = $_POST['apellido'];

        if (!preg_match("/^[a-zA-Z ]*$/", $nombre) || !preg_match("/^[a-zA-Z ]*$/", $apellido)) {
          echo "<script>alert('Los caracteres especiales no están permitidos en el nombre y apellido');</script>";
        } else {
          // Validar teléfono (no debe contener letras)
          $telefono = $_POST['telefono'];

          if (!is_numeric($telefono)) {
            echo "<script>alert('El teléfono no debe contener letras');</script>";
          } else {
            // Continuar con la inserción en la base de datos
            $sql = "INSERT INTO users (email, password, nombre, apellido, fecha_nacimiento, direccion, telefono) VALUES (:email, :password, :nombre, :apellido, :fecha_nacimiento, :direccion, :telefono)";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':email', $_POST['email']);

            // password_hash crea una contraseña encriptada y segura
            $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
            $stmt->bindParam(':password', $password);
            $stmt->bindParam(':nombre', $nombre);
            $stmt->bindParam(':apellido', $apellido);
            $stmt->bindParam(':fecha_nacimiento', $_POST['fecha_nacimiento']);
            $stmt->bindParam(':direccion', $_POST['direccion']);
            $stmt->bindParam(':telefono', $telefono);

            if ($stmt->execute()) {
              $message = 'USUARIO CREADO CON ÉXITO';
              echo "<script>alert('USUARIO CREADO CON ÉXITO');</script>";
            } else {
              $message = 'ERROR DE CONEXIÓN';
              echo "<script>alert('ERROR DE CONEXIÓN');</script>";
            }
          }
        }
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
    <script>showAlert("<?php echo $message; ?>");</script>
  <?php endif; ?>

  <h1>SignUp</h1>
  <span>or <a href="login.php">Login</a></span>

  <form action="signup.php" method="post">
    <input type="text" name="email" placeholder="Ingresar correo" required>
    <input type="password" name="password" placeholder="Ingresar contraseña" required>
    <input type="password" name="confirm_password" placeholder="Confirmar tu contraseña" required>
    <input type="text" name="nombre" placeholder="Nombre" required>
    <input type="text" name="apellido" placeholder="Apellido" required>
    <input type="date" name="fecha_nacimiento" placeholder="Fecha de Nacimiento" required>
    <input type="text" name="direccion" placeholder="Dirección" required>
    <input type="text" name="telefono" placeholder="Teléfono" required>
    <input type="submit" value="Send">
  </form>

</body>
</html>


