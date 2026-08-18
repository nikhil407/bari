<?php
require_once 'config/db.php';

if (isLoggedIn()) { redirect('store.php'); }

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    if (empty($email) || empty($password)) {
        $error = 'Enter both your email and password to continue.';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['full_name'];
            $_SESSION['user_email'] = $user['email'];
            redirect('store.php');
        }
        $error = 'That email or password does not look right.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome back — Bari &amp; Saha</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Sora:wght@500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/auth.css">
</head>
<body>
    <main class="auth-shell">
        <section class="auth-visual auth-login-visual">
            <div class="auth-visual-image"></div>
            <div class="auth-visual-shade"></div>
            <a href="store.php" class="auth-brand"><span class="brand-mark">B</span><span>Bari <i>&amp;</i> Saha</span></a>
            <div class="auth-visual-content">
                <p class="auth-kicker"><span></span> YOUR MARKET, YOUR WAY</p>
                <h1>Good food<br>is <em>waiting.</em></h1>
                <p>Sign in and get the best of your neighbourhood market delivered to your door.</p>
                <div class="auth-proof"><div><strong>30 min</strong><span>fast delivery</span></div><div><strong>10k+</strong><span>happy homes</span></div><div><strong>4.9/5</strong><span>local love</span></div></div>
            </div>
            <div class="visual-stamp"><b>Picked</b><span>fresh today</span></div>
        </section>
        <section class="auth-form-panel">
            <div class="auth-topbar"><a href="store.php">← Back to market</a><span>New here? <a href="register.php">Create account</a></span></div>
            <div class="auth-form-container login-container">
                <div class="form-heading"><p class="auth-kicker">WELCOME BACK</p><h2>Let’s stock up.</h2><p class="auth-subtitle">Sign in to continue your fresh-food ritual.</p></div>
                <?php if ($error): ?><div class="alert alert-error"><span>!</span><?= $error ?></div><?php endif; ?>
                <?php if (isset($_GET['registered'])): ?><div class="alert alert-success"><span>✓</span>Your account is ready. Sign in to start shopping.</div><?php endif; ?>
                <form method="POST" class="auth-form">
                    <div class="form-group"><label for="email">Email address</label><div class="input-icon"><span class="field-icon">@</span><input type="email" id="email" name="email" placeholder="you@example.com" required value="<?= $email ?? '' ?>" autofocus></div></div>
                    <div class="form-group"><div class="label-row"><label for="password">Password</label><a href="#" onclick="return false;">Forgot password?</a></div><div class="input-icon"><span class="field-icon">⌁</span><input type="password" id="password" name="password" placeholder="Your password" required><button type="button" class="toggle-pw" aria-label="Show password" onclick="togglePassword()">Show</button></div></div>
                    <button type="submit" class="btn-submit">Sign in to my market <span>→</span></button>
                </form>
                <div class="auth-divider"><span>or</span></div>
                <a href="store.php" class="btn-guest"><span>⌂</span> Continue as a guest <b>→</b></a>
                <p class="auth-switch">New to Bari &amp; Saha? <a href="register.php">Create your account</a></p>
            </div>
        </section>
    </main>
    <script>
        function togglePassword() { const input = document.getElementById('password'); input.type = input.type === 'password' ? 'text' : 'password'; }
        document.querySelectorAll('.input-icon input').forEach(input => { input.addEventListener('focus', () => input.parentElement.classList.add('focused')); input.addEventListener('blur', () => input.parentElement.classList.remove('focused')); });
    </script>
</body>
</html>
