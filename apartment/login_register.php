<?php
require_once 'session_init.php';
require_once 'loginConfig.php';

if (isset($_POST['register'])) {
    $userName = $_POST['userName'];
    $userEmail = $_POST['userEmail'];
    $userPassword = password_hash($_POST['userPassword'], PASSWORD_DEFAULT);

    // Use sqlsrv_query() instead of $conn->query()
    $checkEmailQuery = "SELECT userEmail FROM users WHERE userEmail = ?";
    $params = array($userEmail);
    $stmt = sqlsrv_query($conn, $checkEmailQuery, $params);

    if ($stmt === false) {
        die(print_r(sqlsrv_errors(), true));
    }

    if (sqlsrv_has_rows($stmt)) {
        $_SESSION['register_error'] = 'Email is already registered!';
        $_SESSION['active_form'] = 'register';
        header("Location: login.php");
        exit();
    } else {
        $insertQuery = "INSERT INTO users (userName, userEmail, userPassword) VALUES (?, ?, ?)";
        $params = array($userName, $userEmail, $userPassword);
        $stmt = sqlsrv_query($conn, $insertQuery, $params);

        if ($stmt === false) {
            die(print_r(sqlsrv_errors(), true));
        }

        header("Location: login.php");
        exit();
    }
}

if (isset($_POST['login'])) {
    $userEmail = $_POST['userEmail'];
    $userPassword = $_POST['userPassword'];

    $loginQuery = "SELECT * FROM users WHERE userEmail = ?";
    $params = array($userEmail);
    $stmt = sqlsrv_query($conn, $loginQuery, $params);

    if ($stmt === false) {
        die(print_r(sqlsrv_errors(), true));
    }

    if (sqlsrv_has_rows($stmt)) {
        $user = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
        if (password_verify($userPassword, $user['userPassword'])) {
            $_SESSION['userName'] = $user['userName'];
            $_SESSION['userEmail'] = $user['userEmail'];
            header("Location: index.php");
            exit();
        }
    }

    $_SESSION['login_error'] = 'Incorrect email or password';
    $_SESSION['active_form'] = 'login';
    header("Location: login.php");
    exit();
}
?>