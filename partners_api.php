<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/partners_helper.php';
require_once __DIR__ . '/includes/admin_roles.php';

header('Content-Type: application/json; charset=utf-8');

function partners_json(array $data, int $status = 200): void
{
    http_response_code($status);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function partners_is_admin(): bool
{
    return tcf_is_super_admin();
}

function partners_unlink_logo(?string $stored): void
{
    $rel = function_exists('tcf_uploads_relative_path')
        ? tcf_uploads_relative_path($stored)
        : trim((string) $stored);
    if ($rel === '' || strpos($rel, 'uploads/partners/') !== 0) {
        return;
    }
    $fs = function_exists('tcf_uploads_fs_path')
        ? tcf_uploads_fs_path($rel)
        : (__DIR__ . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $rel));
    if ($fs !== '' && is_file($fs)) {
        @unlink($fs);
    }
}

$action = trim((string) ($_POST['action'] ?? $_GET['action'] ?? ''));
tcf_partners_ensure_tables($pdo);

try {
    switch ($action) {
        case 'list_public': {
            partners_json([
                'success' => true,
                'data' => tcf_partners_list_published($pdo, 60),
            ]);
        }

        case 'admin_list': {
            if (!partners_is_admin()) {
                partners_json(['success' => false, 'message' => 'Accès refusé.'], 403);
            }
            partners_json(['success' => true, 'data' => tcf_partners_list_admin($pdo)]);
        }

        case 'admin_save': {
            if (!partners_is_admin()) {
                partners_json(['success' => false, 'message' => 'Accès refusé.'], 403);
            }
            $id = (int) ($_POST['id'] ?? 0);
            $name = trim((string) ($_POST['name'] ?? ''));
            if ($name === '' || (function_exists('mb_strlen') ? mb_strlen($name) : strlen($name)) > 160) {
                partners_json(['success' => false, 'message' => 'Nom de l’entreprise requis (max. 160 caractères).'], 422);
            }
            $rawWeb = trim((string) ($_POST['website_url'] ?? ''));
            $website = tcf_partners_normalize_url($rawWeb);
            if ($rawWeb !== '' && $website === null) {
                partners_json(['success' => false, 'message' => 'Site web invalide. Utilisez une URL http(s).'], 422);
            }
            $sortOrder = (int) ($_POST['sort_order'] ?? 0);
            $isPublished = isset($_POST['is_published']) && $_POST['is_published'] === '0' ? 0 : 1;
            $uid = (int) ($_SESSION['user_id'] ?? 0);

            $newLogo = null;
            if (isset($_FILES['logo']) && ($_FILES['logo']['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK) {
                $dir = __DIR__ . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'partners';
                if (!is_dir($dir) && !@mkdir($dir, 0755, true) && !is_dir($dir)) {
                    partners_json(['success' => false, 'message' => 'Impossible de créer le dossier uploads/partners.'], 500);
                }
                if (!is_writable($dir)) {
                    @chmod($dir, 0755);
                }
                if (!is_writable($dir)) {
                    partners_json(['success' => false, 'message' => 'Dossier uploads/partners non accessible en écriture sur le serveur.'], 500);
                }
                $ext = strtolower(pathinfo((string) $_FILES['logo']['name'], PATHINFO_EXTENSION));
                if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif'], true)) {
                    partners_json(['success' => false, 'message' => 'Logo : JPG, PNG, WebP ou GIF uniquement.'], 422);
                }
                $tmp = (string) $_FILES['logo']['tmp_name'];
                $size = (int) ($_FILES['logo']['size'] ?? 0);
                if ($size <= 0 || $size > 4 * 1024 * 1024) {
                    partners_json(['success' => false, 'message' => 'Logo trop volumineux (max. 4 Mo).'], 422);
                }
                $info = @getimagesize($tmp);
                if ($info === false) {
                    partners_json(['success' => false, 'message' => 'Fichier image invalide.'], 422);
                }
                $fname = 'partner_' . date('YmdHis') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
                $dest = $dir . DIRECTORY_SEPARATOR . $fname;
                if (!move_uploaded_file($tmp, $dest) || !is_file($dest)) {
                    partners_json(['success' => false, 'message' => 'Échec upload du logo sur le serveur.'], 500);
                }
                @chmod($dest, 0644);
                $newLogo = 'uploads/partners/' . $fname;
            }

            if ($id > 0) {
                $st = $pdo->prepare('SELECT logo_url FROM partners WHERE id = ?');
                $st->execute([$id]);
                $old = $st->fetch(PDO::FETCH_ASSOC);
                if (!$old) {
                    if ($newLogo) {
                        partners_unlink_logo($newLogo);
                    }
                    partners_json(['success' => false, 'message' => 'Partenaire introuvable.'], 404);
                }
                $finalLogo = (string) ($old['logo_url'] ?? '');
                if ($newLogo !== null) {
                    partners_unlink_logo($finalLogo);
                    $finalLogo = $newLogo;
                }
                if ($finalLogo === '') {
                    partners_json(['success' => false, 'message' => 'Un logo est obligatoire.'], 422);
                }
                $pdo->prepare(
                    'UPDATE partners SET name=?, logo_url=?, website_url=?, sort_order=?, is_published=?, updated_at=NOW() WHERE id=?'
                )->execute([$name, $finalLogo, $website, $sortOrder, $isPublished, $id]);
                partners_json(['success' => true, 'message' => 'Partenaire mis à jour.', 'id' => $id]);
            }

            if ($newLogo === null) {
                partners_json(['success' => false, 'message' => 'Veuillez ajouter le logo de l’entreprise.'], 422);
            }
            $pdo->prepare(
                'INSERT INTO partners (name, logo_url, website_url, sort_order, is_published, created_by) VALUES (?,?,?,?,?,?)'
            )->execute([$name, $newLogo, $website, $sortOrder, $isPublished, $uid > 0 ? $uid : null]);
            partners_json([
                'success' => true,
                'message' => 'Partenaire publié.',
                'id' => (int) $pdo->lastInsertId(),
            ]);
        }

        case 'admin_delete': {
            if (!partners_is_admin()) {
                partners_json(['success' => false, 'message' => 'Accès refusé.'], 403);
            }
            $id = (int) ($_POST['id'] ?? 0);
            if ($id <= 0) {
                partners_json(['success' => false, 'message' => 'Identifiant invalide.'], 422);
            }
            $st = $pdo->prepare('SELECT logo_url FROM partners WHERE id = ?');
            $st->execute([$id]);
            $old = $st->fetch(PDO::FETCH_ASSOC);
            if (!$old) {
                partners_json(['success' => false, 'message' => 'Partenaire introuvable.'], 404);
            }
            $pdo->prepare('DELETE FROM partners WHERE id = ?')->execute([$id]);
            partners_unlink_logo((string) ($old['logo_url'] ?? ''));
            partners_json(['success' => true, 'message' => 'Partenaire supprimé.']);
        }

        default:
            partners_json(['success' => false, 'message' => 'Action inconnue.'], 400);
    }
} catch (Throwable $e) {
    error_log('partners_api: ' . $e->getMessage());
    partners_json(['success' => false, 'message' => 'Erreur serveur.'], 500);
}
