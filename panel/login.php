<?php
session_start();

// Charger le thème par défaut pour la page de connexion
$themes = [];
if (file_exists('../theme.json')) {
    $themes = json_decode(file_get_contents('../theme.json'), true);
}
$current_theme = $themes['default'] ?? [];

$error = '';

if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $panel_users = [];
    if (file_exists('panel_users.json')) {
        $panel_users = json_decode(file_get_contents('panel_users.json'), true);
    }

    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    if (isset($panel_users[$username]) && password_verify($password, $panel_users[$username]['password_hash'])) {
        $_SESSION['loggedin'] = true;
        $_SESSION['username'] = $username;
        $_SESSION['role'] = $panel_users[$username]['role'];
        $_SESSION['project'] = $panel_users[$username]['project'] ?? 'Tyrolium'; // Ajout du projet en session
        header('Location: index.php');
        exit;
    } else {
        $error = 'Nom d\'utilisateur ou mot de passe incorrect.';
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <meta name="robots" content="noindex, nofollow">
    <meta name="googlebot" content="noindex, nofollow">
    <meta name="bingbot" content="noindex, nofollow">

    <link href="https://tyrolium.fr/Contenu/Image/Tyrolium Site.png" rel="shortcut icon">
    <title>Connexion - Card.Tyrolium</title>
    <style>
        :root {
            <?php foreach ($current_theme as $variable => $color): ?>
            <?= htmlspecialchars($variable) ?>: <?= htmlspecialchars($color) ?>;
            <?php endforeach; ?>
        }
    </style>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div id="particles-js"></div>
    <div class="login-container">
        <form action="login.php" method="post" class="login-form">
            <h2>Connexion Card.Tyrolium</h2>
            <?php if ($error): ?><p class="error"><?= $error ?></p><?php endif; ?>
            <div class="form-group">
                <label for="username">Utilisateur</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="password">Mot de passe</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit" class="btn-submit">Se connecter</button>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/particles.js/2.0.0/particles.min.js"></script>
    <script src="../script.js"></script>
</body>
</html>