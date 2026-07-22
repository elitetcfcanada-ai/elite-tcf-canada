<?php



declare(strict_types=1);



/**

 * Notifications réservées au staff (admin + super_admin).

 * Une ligne par compte staff pour que chacun voie l’alerte indépendamment.

 * deep_link : chemin relatif site, ex. admin/superAdmin.php?sa_focus=user&id=12

 */

function tcf_add_staff_notification(PDO $pdo, string $type, string $title, string $content, ?string $deepLink = null): void

{

    if (!in_array($type, ['video_comment', 'testimonial', 'user'], true)) {

        return;

    }



    try {

        $stStaff = $pdo->query(

            "SELECT id FROM users

             WHERE role IN ('admin', 'super_admin')

               AND (status IS NULL OR status = '' OR status = 'active')"

        );

        $staffIds = $stStaff ? $stStaff->fetchAll(PDO::FETCH_COLUMN) : [];

        if (!$staffIds) {

            return;

        }



        if ($deepLink !== null && $deepLink !== '') {

            $st = $pdo->prepare(

                'INSERT INTO notifications (user_id, type, title, content, deep_link) VALUES (?, ?, ?, ?, ?)'

            );

            foreach ($staffIds as $sid) {

                $st->execute([(int) $sid, $type, $title, $content, $deepLink]);

            }

        } else {

            $st = $pdo->prepare(

                'INSERT INTO notifications (user_id, type, title, content) VALUES (?, ?, ?, ?)'

            );

            foreach ($staffIds as $sid) {

                $st->execute([(int) $sid, $type, $title, $content]);

            }

        }

    } catch (Throwable $e) {

        error_log('tcf_add_staff_notification: ' . $e->getMessage());

    }

}


