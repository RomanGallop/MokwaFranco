<?php
session_start();
require_once __DIR__ . '/db.php';

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    $stmt = $pdo->prepare("SELECT * FROM usuario WHERE Username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $stored = $user['Password'];

        // Acepta contraseña en texto plano, md5 o password_hash
        $ok = false;
        if ($stored === $password) $ok = true;
        if (!$ok && md5($password) === $stored) $ok = true;
        if (!$ok && password_verify($password, $stored)) $ok = true;

        if ($ok) {
            $_SESSION['user'] = [
                'id' => $user['ID_Usuario'],
                'username' => $user['Username'],
                'role' => $user['Rol'],
                'ID_Alumno' => $user['ID_Alumno'],
                'ID_Profesor' => $user['ID_Profesor']
            ];
            header("Location: dashboard.php");
            exit;
        }
    }
    $error = "Usuario o contraseña incorrectos.";
}
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Login</title>
    <link rel="stylesheet" href="styles.css">
</head>

<body>
    <div class="container">
        <h1>Login</h1>
        <?php if ($error): ?>
            <div class="alert"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form method="post">
            <input type="text" name="username" placeholder="Usuario" required>
            <input type="password" name="password" placeholder="Contraseña" required>
            <button type="submit">Ingresar</button>
        </form>
    </div>
</body>

</html>