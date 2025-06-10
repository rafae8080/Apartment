<?php
require_once 'session_init.php';

$errors= [
    'login' =>$_SESSION['login_error'] ?? '',
    'register'=>$_SESSION['register_error'] ?? '',
    'register_success'=>$_SESSION['register_success'] ?? ''
];

$activeForm=$_SESSION['active_form'] ?? 'login';

$alert = '';
if ($errors['login'])            { $alert = $errors['login']; }
elseif ($errors['register'])     { $alert = $errors['register']; }
elseif ($errors['register_success']) { $alert = $errors['register_success']; }

session_unset();

function showError($error, $isSuccess = false) {
    if (!empty($error)) {
        $class = $isSuccess ? 'success-message' : 'error-message';
        return "<p class='$class'>$error</p>";
    }
    return '';
}

function isActiveForm($formName,$activeForm){
    return $formName == $activeForm ? 'active' : '';
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Login Page</title>
  <link rel="stylesheet" href="css/login.css" />
  <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.4/css/all.css" crossorigin="anonymous" />
<style>
body {
  margin: 0;
  padding: 0;
  position: relative;
  min-height: 100vh;
  background-image: url('uploads/background.jpg');
  background-repeat: no-repeat;
  background-attachment: fixed;
  background-size: 100% 100%;
}

body::before {
  content: "";
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: black; /* or any overlay color */
  opacity: 0.6; /* simulate background opacity */
  z-index: -1;
}
</style>

</head>

<body>

  <div class="container" id="container">
    <!-- Register Form -->
    <div class="form-container sign-up-container">
      <form action="login_register.php" method="post">
        <h1>Register</h1>

        <span>use your email for registration</span>

        <input type="text" name="userName" placeholder="Name" required />
        <input type="email" name="userEmail" placeholder="Email" required />
        <input type="password" name="userPassword" placeholder="Password" required />

        <button type="submit" name="register">Sign Up</button>
        <p>Already have an account? <a href="#" id="switchToSignIn">Login</a></p>
      </form>
    </div>

    <!-- Login Form -->
    <div class="form-container sign-in-container">
      <form action="login_register.php" method="post">
        <h1>Login</h1>
        <span> use your account</span>

        <input type="email" name="userEmail" placeholder="Email" required />
        <input type="password" name="userPassword" placeholder="Password" required />
        <button type="submit" name="login">Sign In</button>
        <p>Want to add an account? <a href="#" id="switchToSignUp">Register</a></p>
      </form>
    </div>

    <!-- Overlay Panels -->
    <div class="overlay-container">
      <div class="overlay">
        <div class="overlay-panel overlay-left">
          <h1>Welcome Back!</h1>
          <p>To keep connected with us please login with your personal info</p>
          <button class="ghost" id="signIn">Sign In</button>
        </div>
        <div class="overlay-panel overlay-right">
          <h1>Hello, Landlord!</h1>
          <p>Enter your personal details and start your journey with us</p>
          <button class="ghost" id="signUp">Sign Up</button>
        </div>
      </div>
    </div>
  </div>


  <!-- PHP Alert -->
  <?php if ($alert): ?>
  <script>
    window.onload = () => alert(<?= json_encode($alert) ?>);
  </script>
  <?php endif; ?>

  <!-- Toggle Script -->
  <script src="javascript/loginScript.js"></script>
  <script>
    // Optional: fallback click events for text links
    document.getElementById('switchToSignUp')?.addEventListener('click', (e) => {
      e.preventDefault();
      document.getElementById('container').classList.add("right-panel-active");
    });

    document.getElementById('switchToSignIn')?.addEventListener('click', (e) => {
      e.preventDefault();
      document.getElementById('container').classList.remove("right-panel-active");
    });

    // Button toggle handlers (also in CSS/JS)
    document.getElementById('signUp')?.addEventListener('click', () => {
      document.getElementById('container').classList.add("right-panel-active");
    });

    document.getElementById('signIn')?.addEventListener('click', () => {
      document.getElementById('container').classList.remove("right-panel-active");
    });
  </script>
</body>

</html>
