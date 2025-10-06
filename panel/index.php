<?php
session_start();
require_once 'functions.php';

// --- AUTHENTIFICATION ET AUTORISATION ---
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: login.php');
    exit;
}

$current_user_role = $_SESSION['role'] ?? 'user';
$current_username = $_SESSION['username'] ?? null;
$current_project = $_SESSION['project'] ?? 'Tyrolium';

$success_message = '';
$error_message = '';

// --- LOGIQUE DE FORMULAIRES (POST) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_card']) || isset($_POST['add_card'])) {
        $pseudo = trim($_POST['pseudo']);
        $is_update = isset($_POST['update_card']);
        if ($current_user_role === 'admin' || ($is_update && $current_user_role === 'user' && $pseudo === $current_username)) {
            clearstatcache(); // Force PHP to clear file status cache
            $data = get_data();
            if (!$is_update && isset($data[$pseudo])) { $error_message = "Ce pseudo de carte existe déjà."; }
            else {
                $data[$pseudo] = ['name' => $_POST['name']??'', 'description' => $_POST['description']??'', 'profile_picture' => $_POST['profile_picture']??'', 'commission_link' => $_POST['commission_link']??'', 'commission_text' => $_POST['commission_text']??'Commission', 'theme' => $_POST['theme']??'default', 'links' => []];
                if (isset($_POST['links']) && is_array($_POST['links'])) {
                    $socials = json_decode(file_get_contents('../socials.json'), true);
                    foreach ($_POST['links'] as $link) {
                        if (!empty($link['url']) && !empty($link['social']) && isset($socials[$link['social']])) {
                            $social_data = $socials[$link['social']];
                            $data[$pseudo]['links'][] = [
                                'icon' => $social_data['icon'],
                                'text' => $social_data['text'],
                                'url' => $link['url']
                            ];
                        }
                    }
                }
                save_data($data);
                header('Location: index.php?' . ($is_update ? 'edit='.$pseudo.'&' : '') . 'status=' . ($is_update ? 'card_updated' : 'card_created'));
                exit;
            }
        } else { $error_message = "Permission refusée."; }
    }
    if (isset($_POST['add_panel_user']) && $current_user_role === 'admin') {
        $panel_users = json_decode(file_get_contents('panel_users.json'), true);
        if (password_verify($_POST['admin_password_confirm'], $panel_users[$current_username]['password_hash'])) {
            $new_username = trim($_POST['new_panel_username']);
            if (empty($new_username) || empty($_POST['new_panel_password'])) { $error_message = "Le nom d'utilisateur et le mot de passe sont requis."; }
            elseif (isset($panel_users[$new_username])) { $error_message = "Ce nom d'utilisateur existe déjà."; }
            else {
                $panel_users[$new_username] = ['password_hash' => password_hash($_POST['new_panel_password'], PASSWORD_DEFAULT), 'role' => $_POST['role'], 'project' => $_POST['project']];
                file_put_contents('panel_users.json', json_encode($panel_users, JSON_PRETTY_PRINT));
                if ($_POST['role'] === 'user' && !isset(get_data()[$new_username])) {
                    $cards = get_data();
                    $cards[$new_username] = ['name' => $new_username, 'description' => 'Bienvenue !', 'profile_picture' => '', 'commission_link' => '', 'theme' => 'default', 'links' => []];
                    save_data($cards);
                }
                header('Location: index.php?status=panel_user_created');
                exit;
            }
        }
        else { $error_message = "Votre mot de passe admin est incorrect."; }
    }
}

// --- GESTION DE L'ACCÈS UTILISATEUR (après la gestion POST pour ne pas bloquer la soumission)
if ($current_user_role === 'user' && (!isset($_GET['edit']) || $_GET['edit'] !== $current_username)) {
    header('Location: index.php?edit=' . $current_username);
    exit;
}

// --- LOGIQUE DE GESTION (Admin seulement) ---
if ($current_user_role === 'admin') {
    if (isset($_GET['delete'])) {
        $data = get_data();
        if (isset($data[$_GET['delete']])) { unset($data[$_GET['delete']]); save_data($data); header('Location: index.php?status=card_deleted'); exit; }
    }
    if (isset($_GET['delete_panel_user'])) {
        $user_to_delete = $_GET['delete_panel_user'];
        if ($user_to_delete === $current_username) { header('Location: index.php?status=self_delete_error'); exit; }
        $panel_users = json_decode(file_get_contents('panel_users.json'), true);
        if (isset($panel_users[$user_to_delete])) { unset($panel_users[$user_to_delete]); file_put_contents('panel_users.json', json_encode($panel_users, JSON_PRETTY_PRINT)); header('Location: index.php?status=panel_user_deleted'); exit; }
    }
}

// --- LOGIQUE DE FORMULAIRES (POST) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_card']) || isset($_POST['add_card'])) {
        $pseudo = trim($_POST['pseudo']);
        $is_update = isset($_POST['update_card']);
        if ($current_user_role === 'admin' || ($is_update && $current_user_role === 'user' && $pseudo === $current_username)) {
            clearstatcache(); // Force PHP to clear file status cache
            $data = get_data();
            if (!$is_update && isset($data[$pseudo])) { $error_message = "Ce pseudo de carte existe déjà."; }
            else {
                $data[$pseudo] = ['name' => $_POST['name']??'', 'description' => $_POST['description']??'', 'profile_picture' => $_POST['profile_picture']??'', 'commission_link' => $_POST['commission_link']??'', 'commission_text' => $_POST['commission_text']??'Commission', 'theme' => $_POST['theme']??'default', 'links' => []];
                if (isset($_POST['links']) && is_array($_POST['links'])) {
                    $socials = json_decode(file_get_contents('../socials.json'), true);
                    foreach ($_POST['links'] as $link) {
                        if (!empty($link['url']) && !empty($link['social']) && isset($socials[$link['social']])) {
                            $social_data = $socials[$link['social']];
                            $data[$pseudo]['links'][] = [
                                'icon' => $social_data['icon'],
                                'text' => $social_data['text'],
                                'url' => $link['url']
                            ];
                        }
                    }
                }
                save_data($data);
                header('Location: index.php?' . ($is_update ? 'edit='.$pseudo.'&' : '') . 'status=' . ($is_update ? 'card_updated' : 'card_created'));
                exit;
            }
        } else { $error_message = "Permission refusée."; }
    }
    if (isset($_POST['add_panel_user']) && $current_user_role === 'admin') {
        $panel_users = json_decode(file_get_contents('panel_users.json'), true);
        if (password_verify($_POST['admin_password_confirm'], $panel_users[$current_username]['password_hash'])) {
            $new_username = trim($_POST['new_panel_username']);
            if (empty($new_username) || empty($_POST['new_panel_password'])) { $error_message = "Le nom d'utilisateur et le mot de passe sont requis."; }
            elseif (isset($panel_users[$new_username])) { $error_message = "Ce nom d'utilisateur existe déjà."; }
            else {
                $panel_users[$new_username] = ['password_hash' => password_hash($_POST['new_panel_password'], PASSWORD_DEFAULT), 'role' => $_POST['role'], 'project' => $_POST['project']];
                file_put_contents('panel_users.json', json_encode($panel_users, JSON_PRETTY_PRINT));
                if ($_POST['role'] === 'user' && !isset(get_data()[$new_username])) {
                    $cards = get_data();
                    $cards[$new_username] = ['name' => $new_username, 'description' => 'Bienvenue !', 'profile_picture' => '', 'commission_link' => '', 'theme' => 'default', 'links' => []];
                    save_data($cards);
                }
                header('Location: index.php?status=panel_user_created');
                exit;
            }
        } else { $error_message = "Votre mot de passe admin est incorrect."; }
    }
}

// --- PRÉPARATION DES DONNÉES POUR L'AFFICHAGE ---
if (isset($_GET['status'])) {
    $s = $_GET['status'];
    if ($s === 'card_created') $success_message = "La carte a été créée.";
    if ($s === 'card_updated') $success_message = "La carte a été mise à jour.";
    if ($s === 'card_deleted') $success_message = "La carte a été supprimée.";
    if ($s === 'panel_user_created') $success_message = "L'accès a été créé (et une carte de base si nécessaire).";
    if ($s === 'panel_user_deleted') $success_message = "L'accès a été supprimé.";
    if ($s === 'self_delete_error') $error_message = "Vous ne pouvez pas supprimer votre propre compte.";
}

$cards = get_data();
$themes = get_themes();
$panel_users = json_decode(file_get_contents('panel_users.json'), true);
$default_theme = $themes['default'] ?? [];

$edit_mode = isset($_GET['edit']);
$card_to_edit = null;
if ($edit_mode) {
    $pseudo_to_edit = $_GET['edit'];
    if ($current_user_role === 'admin' || ($current_user_role === 'user' && $pseudo_to_edit === $current_username)) {
        if (isset($cards[$pseudo_to_edit])) { $card_to_edit = $cards[$pseudo_to_edit]; }
        else { $error_message = "Cette carte n'existe pas."; $edit_mode = false; }
    } else { $error_message = "Permission refusée."; $edit_mode = false; }
}

$domain = ($current_project === 'Vturias') ? 'card.vturias.fr' : 'card.tyrolium.fr';

?>
<!DOCTYPE html>
<html lang="fr"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <meta name="googlebot" content="noindex, nofollow">
    <meta name="bingbot" content="noindex, nofollow">

    <link href="https://tyrolium.fr/Contenu/Image/Tyrolium Site.png" rel="shortcut icon"><title><?= $edit_mode ? 'Éditer une carte' : 'Dashboard' ?> - Card.Tyrolium</title>
<style>:root { <?php foreach ($default_theme as $var => $color) { echo htmlspecialchars($var) . ':' . htmlspecialchars($color) . ';'; } ?> }</style>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" />
<link rel="stylesheet" href="style.css"></head><body>
<div id="particles-js"></div>
<div class="container">
    <header><h1>Card.Tyrolium</h1><a href="logout.php">Déconnexion</a></header>
    <?php if ($success_message): ?><p class="success"><?= $success_message ?></p><?php endif; ?>
    <?php if ($error_message): ?><p class="error"><?= $error_message ?></p><?php endif; ?>
    <main>
        <?php if ($current_user_role === 'user' && $edit_mode): ?>
        <section class="share-section">
            <label for="share-url">Votre lien public</label>
            <div class="share-input-wrapper">
                <input type="text" id="share-url" value="https://<?= $domain ?>/?u=<?= htmlspecialchars($current_username) ?>" readonly>
                <button id="copy-btn" title="Copier dans le presse-papier"><i class="fa-regular fa-copy"></i></button>
                <a href="https://<?= $domain ?>/?u=<?= htmlspecialchars($current_username) ?>" target="_blank" id="view-btn" title="Ouvrir le lien"><i class="fa-solid fa-eye"></i></a>
            </div>
        </section>
        <?php endif; ?>

        <?php if ($edit_mode && $card_to_edit): /* VUE ÉDITION */ ?>
        <section class="card-ui">
            <div class="card-header"><h2>Édition de la carte : <?= htmlspecialchars($_GET['edit']) ?></h2><?php if ($current_user_role === 'admin'): ?><a href="index.php">Retour</a><?php endif; ?></div>
            <form action="index.php" method="post"><input type="hidden" name="pseudo" value="<?= htmlspecialchars($_GET['edit']) ?>"><div class="form-grid">
                <div class="form-group"><label>Pseudo</label><input type="text" value="<?= htmlspecialchars($_GET['edit']) ?>" disabled></div>
                <div class="form-group"><label for="name">Nom</label><input type="text" id="name" name="name" value="<?= htmlspecialchars($card_to_edit['name']) ?>" required></div>
                <div class="form-group full-width"><label for="description">Description</label><textarea id="description" name="description"><?= htmlspecialchars($card_to_edit['description']) ?></textarea></div>
                <div class="form-group"><label for="profile_picture">URL Photo</label><input type="text" id="profile_picture" name="profile_picture" value="<?= htmlspecialchars($card_to_edit['profile_picture']) ?>"></div>
                <div class="form-group"><label for="theme">Thème</label><select id="theme" name="theme">
                    <?php foreach ($themes as $theme_name => $theme_data): ?><option value="<?= htmlspecialchars($theme_name) ?>" <?= ($card_to_edit['theme'] ?? 'default') === $theme_name ? 'selected' : '' ?>><?= ucfirst(htmlspecialchars($theme_name)) ?></option><?php endforeach; ?>
                </select></div>
            </div>
            <fieldset class="form-fieldset">
                <legend>Bouton Important</legend>
                <div class="form-grid">
                    <div class="form-group"><label for="commission_link">Lien</label><input type="text" id="commission_link" name="commission_link" value="<?= htmlspecialchars($card_to_edit['commission_link']) ?>"></div>
                    <div class="form-group"><label for="commission_text">Texte</label><input type="text" id="commission_text" name="commission_text" value="<?= htmlspecialchars($card_to_edit['commission_text'] ?? 'Commission') ?>"></div>
                </div>
            </fieldset><div class="links-section"><h3>Liens</h3><div id="links-container">
    <?php 
    $socials = json_decode(file_get_contents('../socials.json'), true);
    if(isset($card_to_edit['links'])) { 
        foreach($card_to_edit['links'] as $i => $link): 
    ?>
    <div class="link-row">
        <select name="links[<?= $i ?>][social]" onchange="updateLink(this, <?= $i ?>)">
            <?php foreach($socials as $name => $details): ?>
                <option value="<?= htmlspecialchars($name) ?>" <?= ($link['text'] == $name) ? 'selected' : '' ?>><?= htmlspecialchars($name) ?></option>
            <?php endforeach; ?>
        </select>
        <input type="hidden" name="links[<?= $i ?>][icon]" value="<?= htmlspecialchars($link['icon']) ?>">
        <input type="hidden" name="links[<?= $i ?>][text]" value="<?= htmlspecialchars($link['text']) ?>">
        <input type="text" name="links[<?= $i ?>][url]" placeholder="URL" value="<?= htmlspecialchars($link['url']) ?>" required>
        <button type="button" class="btn-delete-link" onclick="this.parentElement.remove()">X</button>
    </div>
    <?php 
        endforeach; 
    } 
    ?>
</div><button type="button" id="add-link-btn">+ Ajouter un lien</button></div><button type="submit" name="update_card" class="btn-submit">Mettre à jour</button></form>
</section>
        <?php elseif ($current_user_role === 'admin'): /* VUE ADMIN */ ?>
        <section class="card-ui"><h2>Gestion des Cartes</h2><div class="table-wrapper"><table><thead><tr><th>Pseudo</th><th>Nom</th><th>Thème</th><th>Actions</th></tr></thead><tbody>
            <?php foreach ($cards as $pseudo => $card): ?><tr><td><?= htmlspecialchars($pseudo) ?></td><td><?= htmlspecialchars($card['name']) ?></td><td><?= htmlspecialchars($card['theme'] ?? 'default') ?></td><td class="actions"><a href="index.php?edit=<?= htmlspecialchars($pseudo) ?>" class="btn-edit">Éditer</a><a href="https://card.tyrolium.fr/?u=<?= htmlspecialchars($pseudo) ?>" class="btn-view" target="_blank">Voir</a><a href="index.php?delete=<?= htmlspecialchars($pseudo) ?>" class="btn-delete" onclick="return confirm('Supprimer cette carte ?')">Supprimer</a></td></tr><?php endforeach; ?>
        </tbody></table></div><hr><h3>Créer une Carte</h3><form action="index.php" method="post"><div class="form-grid"><div class="form-group"><label for="pseudo">Pseudo</label><input type="text" id="pseudo" name="pseudo" required></div><div class="form-group"><label for="name">Nom</label><input type="text" id="name" name="name" required></div></div><button type="submit" name="add_card" class="btn-submit">Créer</button><p class="form-hint">Plus de détails pourront être ajoutés via "Éditer".</p></form></section>
        <section class="card-ui"><h2>Gestion des Accès</h2><div class="table-wrapper"><table><thead><tr><th>Utilisateur</th><th>Rôle</th><th>Projet</th><th>Action</th></tr></thead><tbody>
            <?php foreach ($panel_users as $username => $user_data): ?><tr><td><?= htmlspecialchars($username) ?></td><td><?= htmlspecialchars($user_data['role']) ?></td><td><?= htmlspecialchars($user_data['project'] ?? 'N/A') ?></td><td class="actions"><?php if ($username !== $current_username): ?><a href="index.php?delete_panel_user=<?= htmlspecialchars($username) ?>" class="btn-delete" onclick="return confirm('Supprimer cet accès ?')">Supprimer</a><?php endif; ?></td></tr><?php endforeach; ?>
        </tbody></table></div><hr><h3>Créer un Accès</h3><form action="index.php" method="post"><div class="form-grid"><div class="form-group"><label for="new_panel_username">Utilisateur</label><input type="text" name="new_panel_username" required></div><div class="form-group"><label for="new_panel_password">Mot de passe</label><input type="password" name="new_panel_password" required></div><div class="form-group"><label for="role">Rôle</label><select name="role"><option value="user">Utilisateur</option><option value="admin">Admin</option></select></div><div class="form-group"><label for="project">Projet</label><select name="project"><option value="Tyrolium">Tyrolium</option><option value="Vturias">Vturias</option></select></div><div class="form-group"><label for="admin_password_confirm">Votre Mot de passe</label><input type="password" name="admin_password_confirm" required></div></div><button type="submit" name="add_panel_user" class="btn-submit">Créer</button></form></section>
        <?php endif; ?>
    </main>
</div>
<script>
    const copyBtn = document.getElementById('copy-btn');
    if (copyBtn) {
        copyBtn.addEventListener('click', function() {
            const urlInput = document.getElementById('share-url');
            navigator.clipboard.writeText(urlInput.value).then(() => {
                const originalIcon = copyBtn.innerHTML;
                copyBtn.innerHTML = '<i class="fa-solid fa-check"></i>';
                setTimeout(() => { copyBtn.innerHTML = originalIcon; }, 2000);
            }).catch(err => { console.error('Erreur de copie: ', err); });
        });
    }

        const socials = <?php echo json_encode($socials); ?>;

    function updateLink(selectElement, index) {
        const selectedSocial = selectElement.value;
        const social = socials[selectedSocial];
        const linkRow = selectElement.parentElement;
        linkRow.querySelector(`input[name='links[${index}][icon]']`).value = social.icon;
        linkRow.querySelector(`input[name='links[${index}][text]']`).value = social.text;
    }

    function setupAddLink(containerId, buttonId) {
        const addButton = document.getElementById(buttonId);
        if (!addButton) return;

        addButton.addEventListener("click", function() {
            const container = document.getElementById(containerId);
            const index = Date.now();
            let options = '';
            for (const socialName in socials) {
                options += `<option value="${socialName}">${socialName}</option>`;
            }

            const newLinkRowHTML = `
                <div class="link-row">
                    <select name="links[${index}][social]" onchange="updateLink(this, ${index})">
                        ${options}
                    </select>
                    <input type="hidden" name="links[${index}][icon]" value="${socials[Object.keys(socials)[0]].icon}">
                    <input type="hidden" name="links[${index}][text]" value="${socials[Object.keys(socials)[0]].text}">
                    <input type="text" name="links[${index}][url]" placeholder="URL" required>
                    <button type="button" class="btn-delete-link" onclick="this.parentElement.remove()">X</button>
                </div>
            `;
            container.insertAdjacentHTML("beforeend", newLinkRowHTML);
        });
    }

    setupAddLink("links-container", "add-link-btn");
</script>
<script src="https://cdn.jsdelivr.net/particles.js/2.0.0/particles.min.js"></script>
<script src="../script.js"></script>
</body></html>