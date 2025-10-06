<?php
// --- 1. CHARGEMENT DES DONNÉES ---
$user_data = null;
$error_message = '';
$data = [];
if (file_exists('data.json')) {
    $data = json_decode(file_get_contents('data.json'), true);
}

// --- 2. CHARGEMENT DES THÈMES ---
$themes = [];
if (file_exists('theme.json')) {
    $themes = json_decode(file_get_contents('theme.json'), true);
}

// --- 3. SÉLECTION DE L'UTILISATEUR ET DU THÈME ---
$username = isset($_GET['u']) ? trim($_GET['u']) : null;
$current_theme = $themes['default'] ?? []; // Thème par défaut robuste

if ($username) {
    if (isset($data[$username])) {
        $user_data = $data[$username];
        $theme_name = $user_data['theme'] ?? 'default';
        $current_theme = $themes[$theme_name] ?? $themes['default'];
    } else {
        $error_message = "L'utilisateur '" . htmlspecialchars($username) . "' n'a pas été trouvé.";
    }
} else {
    $error_message = "Veuillez spécifier un utilisateur (ex: ?u=maxime).";
}

if (empty($themes)) {
    $error_message = "Fichier de thèmes 'theme.json' introuvable ou vide.";
    $user_data = null; // Empêche l'affichage de la carte si les thèmes manquent
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

    <title><?php if ($user_data) { echo htmlspecialchars($user_data['name']) . ' - Card'; } else { echo 'Card.Tyrolium'; } ?></title>
    
    <!-- Injection dynamique des variables de couleur du thème -->
    <style>
        :root {
            <?php foreach ($current_theme as $variable => $color): ?>
            <?= htmlspecialchars($variable) ?>: <?= htmlspecialchars($color) ?>;
            <?php endforeach; ?>
        }
    </style>
    
    <link rel="stylesheet" href="style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" />
</head>
<body>

    <div id="particles-js"></div>

    <?php if ($user_data): ?>
    <div class="card">
        <img src="<?= htmlspecialchars($user_data['profile_picture']) ?>" alt="Photo de profil" class="profile-picture">

        <h1 class="name"><?= htmlspecialchars($user_data['name']) ?></h1>

        <?php if (!empty($user_data['description'])): ?>
            <p class="description"><?= htmlspecialchars($user_data['description']) ?></p>
        <?php endif; ?>

        <div class="links">
            <?php if (!empty($user_data['commission_link'])): ?>
                <a href="<?= htmlspecialchars($user_data['commission_link']) ?>" target="_blank" class="link commission">
                    <i class="fa-solid fa-star"></i>
                    <span><?= htmlspecialchars($user_data['commission_text'] ?? 'Commission') ?></span>
                </a>
            <?php endif; ?>

            <?php foreach ($user_data['links'] as $link): ?>
                <a href="<?= htmlspecialchars($link['url']) ?>" target="_blank" class="link">
                    <i class="<?= htmlspecialchars($link['icon']) ?>"></i>
                    <span><?= htmlspecialchars($link['text']) ?></span>
                </a>
            <?php endforeach; ?>
        </div>
    </div>

    <?php else: ?>
    <div class="card">
        <h1 class="name">Card.Tyrolium</h1>
        <p class="description"><?= $error_message ?></p>
    </div>
    <?php endif; ?>

    <script src="https://cdn.jsdelivr.net/particles.js/2.0.0/particles.min.js"></script>
    <!-- Le script JS doit être après la définition des couleurs pour pouvoir les utiliser -->
    <script src="script.js"></script>

</body>
</html>