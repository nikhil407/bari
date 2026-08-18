<?php
require_once 'config/db.php';

if (isLoggedIn()) { redirect('store.php'); }

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['full_name'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $phone = sanitize($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';
    $address = sanitize($_POST['address'] ?? '');
    $city = sanitize($_POST['city'] ?? 'Mumbai');
    $pincode = sanitize($_POST['pincode'] ?? '');
    if (empty($name) || empty($email) || empty($phone) || empty($password)) { $error = 'Please complete the required details.'; }
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) { $error = 'Enter a valid email address.'; }
    elseif (strlen($password) < 6) { $error = 'Choose a password with at least 6 characters.'; }
    elseif ($password !== $confirm) { $error = 'Your passwords do not match.'; }
    else {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?"); $stmt->execute([$email]);
        if ($stmt->fetch()) { $error = 'An account with this email already exists.'; }
        else {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (full_name, email, phone, password, address, city, pincode) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$name, $email, $phone, $hashedPassword, $address, $city, $pincode]);
            redirect('login.php?registered=1');
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create your account — Bari &amp; Saha</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Sora:wght@500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/auth.css">
</head>
<body>
    <main class="auth-shell auth-register-shell">
        <section class="auth-visual auth-register-visual">
            <div class="auth-visual-image"></div><div class="auth-visual-shade"></div>
            <a href="store.php" class="auth-brand"><span class="brand-mark">B</span><span>Bari <i>&amp;</i> Saha</span></a>
            <div class="auth-visual-content"><p class="auth-kicker"><span></span> MAKE EVERYDAY EASIER</p><h1>Your new<br>favourite <em>market.</em></h1><p>From fresh produce to pantry staples, your everyday essentials are just a few taps away.</p><div class="visual-list"><span><b>01</b> Freshness you can taste</span><span><b>02</b> Value you can feel good about</span><span><b>03</b> At your door in 30 minutes</span></div></div><div class="visual-stamp"><b>Hello,</b><span>good living</span></div>
        </section>
        <section class="auth-form-panel">
            <div class="auth-topbar"><a href="store.php">← Back to market</a><span>Already a member? <a href="login.php">Sign in</a></span></div>
            <div class="auth-form-container register-container">
                <div class="form-heading"><p class="auth-kicker">JOIN THE MARKET</p><h2>Let’s make life delicious.</h2><p class="auth-subtitle">Create your account and make your first fresh order.</p></div>
                <?php if ($error): ?><div class="alert alert-error"><span>!</span><?= $error ?></div><?php endif; ?>
                <form method="POST" class="auth-form" id="registerForm">
                    <div class="form-row"><div class="form-group"><label for="full_name">Full name <b>*</b></label><div class="input-icon"><span class="field-icon">◯</span><input type="text" id="full_name" name="full_name" placeholder="Your name" required value="<?= $name ?? '' ?>"></div></div><div class="form-group"><label for="phone">Phone number <b>*</b></label><div class="input-icon"><span class="field-icon">#</span><input type="tel" id="phone" name="phone" placeholder="98765 43210" required value="<?= $phone ?? '' ?>"></div></div></div>
                    <div class="form-group"><label for="email">Email address <b>*</b></label><div class="input-icon"><span class="field-icon">@</span><input type="email" id="email" name="email" placeholder="you@example.com" required value="<?= $email ?? '' ?>"></div></div>
                    <div class="form-row"><div class="form-group"><label for="password">Password <b>*</b></label><div class="input-icon"><span class="field-icon">⌁</span><input type="password" id="password" name="password" placeholder="6+ characters" required minlength="6"></div></div><div class="form-group"><label for="confirm_password">Confirm password <b>*</b></label><div class="input-icon"><span class="field-icon">⌁</span><input type="password" id="confirm_password" name="confirm_password" placeholder="Repeat password" required></div></div></div>
                    <details class="address-details"><summary>Add delivery details <span>Optional</span></summary><div class="address-grid"><div class="form-group form-wide"><label for="address">Street address</label><div class="input-icon"><span class="field-icon">⌖</span><input type="text" id="address" name="address" placeholder="House no., street, landmark" value="<?= $address ?? '' ?>"></div></div><div class="form-group"><label for="city">City</label><div class="input-icon"><span class="field-icon">⌂</span><input type="text" id="city" name="city" placeholder="Mumbai" value="<?= $city ?? 'Mumbai' ?>"></div></div><div class="form-group"><label for="pincode">Pincode</label><div class="input-icon"><span class="field-icon">#</span><input type="text" id="pincode" name="pincode" placeholder="400001" value="<?= $pincode ?? '' ?>"></div></div></div></details>
                    <button type="submit" class="btn-submit">Create my account <span>→</span></button>
                </form>
                <p class="auth-switch">Already a member? <a href="login.php">Sign in instead</a></p>
            </div>
        </section>
    </main>
    <script>
        document.getElementById('registerForm').addEventListener('submit', function(event) { if (document.getElementById('password').value !== document.getElementById('confirm_password').value) { event.preventDefault(); alert('Passwords do not match.'); } });
        document.querySelectorAll('.input-icon input').forEach(input => { input.addEventListener('focus', () => input.parentElement.classList.add('focused')); input.addEventListener('blur', () => input.parentElement.classList.remove('focused')); });
    </script>
</body>
</html>
