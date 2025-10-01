<?php
// Fichier de fonctions pour le panel

function get_data() {
    if (!file_exists('../data.json')) {
        return [];
    }
    $json = file_get_contents('../data.json');
    return json_decode($json, true) ?? [];
}

function save_data($data) {
    // JSON_PRETTY_PRINT pour garder le fichier lisible
    $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    file_put_contents('../data.json', $json);
}

function get_themes() {
    if (!file_exists('../theme.json')) {
        return [];
    }
    $json = file_get_contents('../theme.json');
    return json_decode($json, true) ?? [];
}

?>