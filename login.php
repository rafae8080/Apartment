<?php
require_once 'session_init.php';

$errors= [
    'login' =>$_SESSION['login_error'] ?? '',
    'register'=>$_SESSION['register_error'] ?? '',
    'register_success'=>$_SESSION['register_success'] ?? ''
];

$activeForm=$_SESSION['active_form'] ?? 'login';

session_unset();

function showError($error, $isSuccess = false) {
    if (!empty($error)) {
        $class = $isSuccess ? 'success-message' : 'error-message';
        return "<p class='$class'>$error</p>";
    }
    return '';
}

function isActiveForm($formName,$activeForm){
    return $formName == $activeForm ? 'active' :'';
}

?>


<!DOCTYPE html> 
<html lang = "en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Page</title>
    <link rel="stylesheet" href="css/loginStyle.css">

</head>

<body>
    <div class ="container">
        <div class="form-box <?= isActiveForm('login',$activeForm); ?>" id="login-form">
        <form action="login_register.php" method ="post">
            <h2>Login</h2>
            <?=showError($errors['login']); ?>
            <?=showError($errors['register_success'], true); ?>
            <input type ="email" name="userEmail" placeholder="Email" required>
            <input type ="password" name="userPassword" placeholder="Password" required>
            <button type ="submit" name="login">Login</button>

            <p> Want to add an account? <a href="#" onclick="showForm('register-form')"> Register </a></p>
        </form>
        </div>

        <div class="form-box <?= isActiveForm('register',$activeForm); ?>" id="register-form">
        <form action="login_register.php" method ="post">
            <h2>Register</h2>
            <?=showError($errors['register']); ?>
            <input type ="text" name="userName" placeholder="Name" required>
            <input type ="email" name="userEmail" placeholder="Email" required>
            <input type ="password" name="userPassword" placeholder="Password" required>
            <select name ="userRole" required>
                <option value ="">--Select Role--</option>
                <option value ="admin">Admin</option>
                <option value ="landlord">Landlord</option>
            </select>
            <button type ="submit" name="register"> Register</button>

            <p> Already have an account? <a href="#" onclick="showForm('login-form')"> Login </a></p>
        </form>
        </div>
    </div>
    <script src="javascript/loginScript.js"></script>
</body>

</html>