<?php
session_start();
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "banco3";

// Crear la conexión
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar la conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $action = $_POST["action"];

    switch ($action) {
        case 'obtener_saldo':
            $numeroCuenta = $_POST["numero_cuenta"];
            $usuarioId = $_POST["usuario_id"]; // Asegúrate de tener este campo en tu formulario
            $resultado = obtenerSaldo($numeroCuenta, $usuarioId);
            break;
        
        case 'retirar':
            $numeroCuenta = $_POST["numero_cuenta"];
            $monto = $_POST["monto"];
            $usuarioId = $_POST["usuario_id"];
            $resultado = retirar($numeroCuenta, $monto, $usuarioId);
            break;
    
        case 'consignar':
            $numeroCuenta = $_POST["numero_cuenta"];
            $monto = $_POST["monto"];
            $usuarioId = $_POST["usuario_id"];
            $resultado = consignar($numeroCuenta, $monto, $usuarioId);
            break;
    
        case 'transferir':
            $cuentaOrigen = $_POST["cuenta_origen"];
            $cuentaDestino = $_POST["cuenta_destino"];
            $monto = $_POST["monto"];
            $usuarioId = $_POST["usuario_id"];
            $resultado = transferir($cuentaOrigen, $cuentaDestino, $monto, $usuarioId);
            break;
    
        case 'obtener_todos_numeros_cuenta':
            $usuarioId = $_POST["usuario_id"];
            $resultado = obtenerTodosNumerosCuenta($usuarioId);
            break;
            
            case 'login':
                $nombreUsuario = $_POST["nombre_usuario"];
                $contrasena = $_POST["contrasena"];
                $resultado = iniciarSesion($nombreUsuario, $contrasena);
                break;
    
            case 'ver_cuenta':
                // Verificar si el usuario ha iniciado sesión
                if (!isset($_SESSION['user_id'])) {
                    $resultado = json_encode(array('status' => 'error', 'message' => 'Debe iniciar sesión primero'));
                } else {
                    $resultado = verCuenta($_SESSION['user_id']);
                }
                break;
                case 'cerrar_sesion':
                    $resultado = cerrarSesion();
                    break;
        default:
            $resultado = json_encode(array('status' => 'error', 'message' => 'Acción no válida'));
            break;
    }

    header('Content-Type: application/json'); // Asegura que la respuesta se interprete como JSON
    echo $resultado;
    exit();
}

function obtenerSaldo($numeroCuenta, $usuarioId) {
    global $conn;
    $sql = "SELECT numero_cuenta, saldo FROM cuentas WHERE numero_cuenta = '$numeroCuenta' AND usuario_id = $usuarioId";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return json_encode(array('status' => 'success', 'numero_cuenta' => $row["numero_cuenta"], 'saldo' => $row["saldo"]));
    } else {
        return json_encode(array('status' => 'error', 'message' => 'Cuenta no encontrada'));
    }
}

function retirar($numeroCuenta, $monto, $usuarioId) {
    global $conn;
    $saldoActual = json_decode(obtenerSaldo($numeroCuenta, $usuarioId), true);

    if ($saldoActual['status'] === "error") {
        return json_encode($saldoActual);
    }

    if ($saldoActual['saldo'] >= $monto) {
        $nuevoSaldo = $saldoActual['saldo'] - $monto;
        $sql = "UPDATE cuentas SET saldo = $nuevoSaldo WHERE numero_cuenta = '$numeroCuenta' AND usuario_id = $usuarioId";
        if ($conn->query($sql) === TRUE) {
            return json_encode(array('status' => 'success', 'message' => 'Retiro exitoso', 'nuevo_saldo' => $nuevoSaldo));
        } else {
            return json_encode(array('status' => 'error', 'message' => 'Error al actualizar el saldo: ' . $conn->error));
        }
    } else {
        return json_encode(array('status' => 'error', 'message' => 'Saldo insuficiente'));
    }
}

function consignar($numeroCuenta, $monto, $usuarioId) {
    global $conn;
    $saldoActual = json_decode(obtenerSaldo($numeroCuenta, $usuarioId), true);

    if ($saldoActual['status'] === "error") {
        return json_encode($saldoActual);
    }

    $nuevoSaldo = $saldoActual['saldo'] + $monto;
    $sql = "UPDATE cuentas SET saldo = $nuevoSaldo WHERE numero_cuenta = '$numeroCuenta' AND usuario_id = $usuarioId";
    if ($conn->query($sql) === TRUE) {
        return json_encode(array('status' => 'success', 'message' => 'Consignación exitosa', 'nuevo_saldo' => $nuevoSaldo));
    } else {
        return json_encode(array('status' => 'error', 'message' => 'Error al actualizar el saldo: ' . $conn->error));
    }
}

function transferir($cuentaOrigen, $cuentaDestino, $monto, $usuarioId) {
    global $conn;

    // Verificar si la cuenta de origen pertenece al usuario
    $sqlOrigen = "SELECT id FROM cuentas WHERE numero_cuenta = '$cuentaOrigen' AND usuario_id = $usuarioId";
    $resultOrigen = $conn->query($sqlOrigen);

    if ($resultOrigen->num_rows > 0) {
        // Buscar la cuenta de destino y obtener el usuario_id correspondiente
        $sqlDestino = "SELECT id, usuario_id FROM cuentas WHERE numero_cuenta = '$cuentaDestino'";
        $resultDestino = $conn->query($sqlDestino);

        if ($resultDestino->num_rows > 0) {
            $rowDestino = $resultDestino->fetch_assoc();
            $usuarioIdDestino = $rowDestino['usuario_id'];

            // Realizar el retiro de la cuenta de origen
            $retiro = json_decode(retirar($cuentaOrigen, $monto, $usuarioId), true);

            if ($retiro['status'] === "success") {
                // Realizar la consignación a la cuenta de destino
                $consignacion = json_decode(consignar($cuentaDestino, $monto, $usuarioIdDestino), true);
                return json_encode($consignacion);
            } else {
                return json_encode($retiro);
            }
        } else {
            return json_encode(array('status' => 'error', 'message' => 'Cuenta de destino no encontrada'));
        }
    } else {
        return json_encode(array('status' => 'error', 'message' => 'Cuenta de origen no válida'));
    }
}


function obtenerTodosNumerosCuenta($usuarioId) {
    global $conn;
    $sql = "SELECT numero_cuenta FROM cuentas WHERE usuario_id = $usuarioId";
    $result = $conn->query($sql);

    $numerosCuenta = array();

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $numerosCuenta[] = $row["numero_cuenta"];
        }
    }

    return json_encode(array('status' => 'success', 'numeros_cuenta' => $numerosCuenta));
}


function iniciarSesion($nombreUsuario, $contrasena) {
    global $conn;

    $sql = "SELECT usuarios.id, nombre_usuario, numero_cuenta, saldo
            FROM usuarios 
            JOIN cuentas ON usuarios.id = cuentas.usuario_id
            WHERE nombre_usuario = '$nombreUsuario' AND contrasena = '$contrasena'";

    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();

        // Almacenar información del usuario en la sesión
        $_SESSION['user_id'] = $row['id'];
        $_SESSION['nombre_usuario'] = $row['nombre_usuario'];

        $infoUsuario = array(
            'id' => $row['id'],
            'numero_cuenta' => $row['numero_cuenta'],
            'saldo' => $row['saldo'],
        );

        return json_encode(array('status' => 'success', 'message' => 'Inicio de sesión exitoso', 'infoUsuario' => $infoUsuario));
    } else {
        return json_encode(array('status' => 'error', 'message' => 'Nombre de usuario o contraseña incorrectos'));
    }
}

function verCuenta($userId) {
    global $conn;

    $sql = "SELECT numero_cuenta, saldo FROM cuentas WHERE usuario_id = $userId";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return json_encode(array('status' => 'success', 'numero_cuenta' => $row["numero_cuenta"], 'saldo' => $row["saldo"]));
    } else {
        return json_encode(array('status' => 'error', 'message' => 'Cuenta no encontrada'));
    }
}
function cerrarSesion() {
    // Iniciar la sesión si aún no se ha iniciado
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }

    // Destruir todas las variables de sesión
    session_unset();

    // Destruir la sesión
    session_destroy();

    return json_encode(array('status' => 'success', 'message' => 'Sesión cerrada correctamente'));
}
// Cerrar la conexión
$conn->close();
?>
