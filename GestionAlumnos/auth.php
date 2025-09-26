<?php
// auth.php
// Funciones de autenticación y permisos

function user_is_logged()
{
    return isset($_SESSION['user']);
}

// Devuelve el array de usuario actual guardado en la sesión
function get_logged_user()
{
    return $_SESSION['user'] ?? null;
}

// Profesor de la clase
function user_is_professor_of_class(PDO $pdo, $userId, $classId)
{
    $st = $pdo->prepare("
        SELECT COUNT(*) 
        FROM clase c
        JOIN usuario u ON u.ID_Profesor = c.ID_Profesor
        WHERE u.ID_Usuario = ? AND c.ID_Clase = ?
    ");
    $st->execute([$userId, $classId]);
    return $st->fetchColumn() > 0;
}

// Alumno de la clase
function user_is_student_of_class(PDO $pdo, $userId, $classId)
{
    $st = $pdo->prepare("
        SELECT COUNT(*) 
        FROM alumno_clase ac
        JOIN usuario u ON u.ID_Alumno = ac.ID_Alumno
        WHERE u.ID_Usuario = ? AND ac.ID_Clase = ?
    ");
    $st->execute([$userId, $classId]);
    return $st->fetchColumn() > 0;
}

// Permiso de edición
function can_edit_class(PDO $pdo, $userId, $classId)
{
    return user_is_professor_of_class($pdo, $userId, $classId);
}

// Puede ver un registro Alumno_Clase
function can_view_alumno_clase(PDO $pdo, $userId, $id_alumno_clase)
{
    $st = $pdo->prepare("
        SELECT ID_Clase, ID_Alumno 
        FROM alumno_clase 
        WHERE ID_Alumno_Clase = ?
    ");
    $st->execute([$id_alumno_clase]);
    $row = $st->fetch(PDO::FETCH_ASSOC);
    if (!$row) return false;

    $classId  = $row['ID_Clase'];
    $alumnoId = $row['ID_Alumno'];

    if (user_is_professor_of_class($pdo, $userId, $classId)) return true;

    $st2 = $pdo->prepare("SELECT ID_Alumno FROM usuario WHERE ID_Usuario = ?");
    $st2->execute([$userId]);
    $u = $st2->fetch(PDO::FETCH_ASSOC);

    return ($u && $u['ID_Alumno'] == $alumnoId);
}

// Clases de un usuario
function get_user_classes(PDO $pdo, $userId, $roleFilter = null)
{
    if ($roleFilter === 'Profesor') {
        $sql = "
            SELECT c.* 
            FROM clase c
            JOIN usuario u ON u.ID_Profesor = c.ID_Profesor
            WHERE u.ID_Usuario = ?
        ";
        $st = $pdo->prepare($sql);
        $st->execute([$userId]);
        return $st->fetchAll(PDO::FETCH_ASSOC);
    }

    if ($roleFilter === 'Alumno') {
        $sql = "
            SELECT c.*
            FROM clase c
            JOIN alumno_clase ac ON ac.ID_Clase = c.ID_Clase
            JOIN usuario u ON u.ID_Alumno = ac.ID_Alumno
            WHERE u.ID_Usuario = ?
        ";
        $st = $pdo->prepare($sql);
        $st->execute([$userId]);
        return $st->fetchAll(PDO::FETCH_ASSOC);
    }

    return array_merge(
        get_user_classes($pdo, $userId, 'Profesor'),
        get_user_classes($pdo, $userId, 'Alumno')
    );
}
