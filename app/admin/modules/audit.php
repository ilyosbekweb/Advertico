<?php
declare(strict_types=1);

$actions = [
    'login' => 'Tizimga kirdi', 'logout' => 'Tizimdan chiqdi', 'setup' => 'Administrator yaratildi',
    'create' => 'Yaratdi', 'update' => 'Tahrirladi', 'delete' => 'O‘chirdi', 'reorder' => 'Tartibni o‘zgartirdi',
    'status' => 'Ariza holatini o‘zgartirdi', 'export' => 'Arizalarni eksport qildi', 'upload' => 'Rasm yukladi',
    'password_change' => 'Parolni o‘zgartirdi', 'password_fail' => 'Noto‘g‘ri joriy parol kiritdi',
];
$entities = ['orders' => 'Arizalar', 'points' => 'Reklama nuqtalari', 'texts' => 'Sahifa matnlari', 'media' => 'Media', 'settings' => 'Sozlamalar', 'admin' => 'Hisob'];
$actions += ['publish' => 'Nashr qildi', 'unpublish' => 'Nashrdan oldi'];
foreach (crud_entities() as $key => $cfg) {
    $entities[$key] = $cfg['title'];
}

$rows = db()->query('SELECT l.*, a.name AS admin_name FROM audit_log l LEFT JOIN admins a ON a.id = l.admin_id ORDER BY l.id DESC LIMIT 200')->fetchAll();
$failed = db()->query("SELECT ip, COUNT(*) AS c, MAX(created_at) AS last FROM login_attempts WHERE success = 0 AND created_at > (NOW() - INTERVAL 7 DAY) GROUP BY ip ORDER BY c DESC LIMIT 10")->fetchAll();

render_admin('Faollik jurnali', 'audit', static function () use ($rows, $actions, $entities, $failed) { ?>
    <div class="page-head"><div><h1>Faollik jurnali</h1><p class="muted">Admin paneldagi so‘nggi 200 ta amal.</p></div></div>
    <?php if ($failed): ?>
        <section class="card">
            <div class="card-head"><h2>Muvaffaqiyatsiz kirish urinishlari (7 kun)</h2></div>
            <table class="table"><thead><tr><th>IP manzil</th><th>Urinishlar</th><th>Oxirgisi</th></tr></thead><tbody>
            <?php foreach ($failed as $f): ?><tr><td><code><?= e($f['ip']) ?></code></td><td><?= (int)$f['c'] ?></td><td class="muted"><?= e(format_date($f['last'])) ?></td></tr><?php endforeach; ?>
            </tbody></table>
        </section>
    <?php endif; ?>
    <div class="card table-card">
        <?php if (!$rows): ?><div class="empty">Jurnal bo‘sh.</div><?php else: ?>
        <table class="table">
            <thead><tr><th>Vaqt</th><th>Amal</th><th>Bo‘lim</th><th>Tafsilot</th><th>IP</th></tr></thead>
            <tbody>
            <?php foreach ($rows as $row):
                $details = $row['details'] ? json_decode($row['details'], true) : [];
                $detailText = is_array($details) ? implode(', ', array_map(static fn($k, $v) => $k . ': ' . (is_scalar($v) ? $v : json_encode($v, JSON_UNESCAPED_UNICODE)), array_keys($details), $details)) : ''; ?>
                <tr>
                    <td class="muted small nowrap"><?= e(format_date($row['created_at'])) ?></td>
                    <td class="strong"><?= e($actions[$row['action']] ?? $row['action']) ?></td>
                    <td><?= e($entities[$row['entity']] ?? $row['entity']) ?><?= $row['entity_id'] ? ' <span class="muted">#' . (int)$row['entity_id'] . '</span>' : '' ?></td>
                    <td class="muted small"><?= e(str_limit($detailText, 90)) ?></td>
                    <td class="muted small"><code><?= e($row['ip']) ?></code></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
<?php });
