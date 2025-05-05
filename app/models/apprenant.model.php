<?php

function get_all_apprenants() {
    $data = read_data();
    return $data['apprenants'] ?? [];
}

function get_apprenant_by_id($id) {
    $apprenants = get_all_apprenants();
    return array_find($apprenants, fn($a) => $a['id'] === $id);
}

function add_apprenant($apprenant) {
    $data = read_data();
    $data['apprenants'][] = $apprenant;
    return write_data($data);
}

function update_apprenant_status($id, $status) {
    $data = read_data();
    $index = array_findindex($data['apprenants'], fn($a) => $a['id'] === $id);
    if ($index !== -1) {
        $data['apprenants'][$index]['status'] = $status;
        return write_data($data);
    }
    return false;
}