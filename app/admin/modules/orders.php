<?php
declare(strict_types=1);

$action = input('a', 'list');
$statuses = order_statuses();

/** Joriy filtrlar bo'yicha WHERE qismi. */
function orders_filter(): array
{
    $where = [];
    $params = [];
    $status = input('status');
    if ($status !== '' && array_key_exists($status, order_statuses())) {
        $where[] = 'a.status = :status';
        $params[':status'] = $status;
    } elseif ($status === '') {
        $where[] = "a.status <> 'spam'";
    }
    $service = input('service');
    if ($service !== '' && ctype_digit($service)) {
        $where[] = 'a.service_id = :service';
        $params[':service'] = (int)$service;
    }
    $q = input('q');
    if ($q !== '') {
        $where[] = '(a.name LIKE :q1 OR a.phone LIKE :q2 OR a.message LIKE :q3 OR a.id = :qid)';
        $like = '%' . addcslashes($q, '%_\\') . '%';
        $params += [':q1' => $like, ':q2' => $like, ':q3' => $like, ':qid' => ctype_digit($q) ? (int)$q : 0];
    }
    $from = input('from');
    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $from)) {
        $where[] = 'a.created_at >= :from';
        $params[':from'] = $from . ' 00:00:00';
    }
    $to = input('to');
    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $to)) {
        $where[] = 'a.created_at <= :to';
        $params[':to'] = $to . ' 23:59:59';
    }
    return [$where ? 'WHERE ' . implode(' AND ', $where) : '', $params];
}

function orders_find(int $id): ?array
{
    $stmt = db()->prepare('SELECT a.*, s.name_uz AS service_name, l.name_uz AS location_name, l.status AS location_status, l.lat, l.lng, d.name_uz AS location_district
        FROM applications a LEFT JOIN services s ON s.id = a.service_id
        LEFT JOIN ad_locations l ON l.id = a.location_id LEFT JOIN districts d ON d.id = l.district_id
        WHERE a.id = :id');
    $stmt->execute([':id' => $id]);
    return $stmt->fetch() ?: null;
}

function orders_event(int $appId, string $type, ?string $from = null, ?string $to = null, ?string $note = null): void
{
    db()->prepare('INSERT INTO application_events (application_id, admin_id, type, from_status, to_status, note) VALUES (:a, :ad, :t, :f, :to, :n)')
        ->execute([':a' => $appId, ':ad' => $_SESSION['admin_id'] ?? null, ':t' => $type, ':f' => $from, ':to' => $to, ':n' => $note]);
}

/* ---------------------------------------------------------------- POST amallar */
if (is_post()) {
    csrf_check();
    $id = (int)input('id');
    $order = orders_find($id);
    if (!$order) {
        flash('error', 'Ariza topilmadi.');
        redirect_to('orders');
    }
    switch ($action) {
        case 'status':
            $new = input('status');
            if (array_key_exists($new, $statuses) && $new !== $order['status']) {
                db()->prepare('UPDATE applications SET status = :s WHERE id = :id')->execute([':s' => $new, ':id' => $id]);
                orders_event($id, 'status', $order['status'], $new);
                audit('status', 'orders', $id, ['from' => $order['status'], 'to' => $new]);
                flash('success', 'Holat o‘zgartirildi: ' . $statuses[$new][0] . '.');
            }
            break;
        case 'note':
            $note = mb_substr(input('note'), 0, 2000);
            if ($note !== '') {
                orders_event($id, 'note', null, null, $note);
                flash('success', 'Izoh qo‘shildi.');
            }
            break;
        case 'telegram':
            $error = null;
            if (Telegram::send(Telegram::applicationMessage($order), $error)) {
                db()->prepare('UPDATE applications SET telegram_sent = 1 WHERE id = :id')->execute([':id' => $id]);
                orders_event($id, 'telegram', null, null, 'Telegram’ga qayta yuborildi');
                flash('success', 'Telegram’ga yuborildi.');
            } else {
                flash('error', 'Telegram’ga yuborilmadi: ' . $error);
            }
            break;
        case 'delete':
            db()->prepare('DELETE FROM applications WHERE id = :id')->execute([':id' => $id]);
            audit('delete', 'orders', $id, ['name' => $order['name'], 'phone' => $order['phone']]);
            flash('success', 'Ariza #' . $id . ' o‘chirildi.');
            redirect_to('orders');
    }
    redirect_to('orders', ['a' => 'view', 'id' => $id]);
}

/* ---------------------------------------------------------------- CSV eksport */
if ($action === 'export') {
    [$where, $params] = orders_filter();
    $stmt = db()->prepare("SELECT a.id, a.created_at, a.name, a.phone, COALESCE(s.name_uz, a.service) AS service, a.message, a.status, a.lang
        FROM applications a LEFT JOIN services s ON s.id = a.service_id {$where} ORDER BY a.id DESC");
    $stmt->execute($params);
    audit('export', 'orders');
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="advertico-arizalar-' . date('Y-m-d') . '.csv"');
    $out = fopen('php://output', 'wb');
    fwrite($out, "\xEF\xBB\xBF"); // Excel UTF-8 ni to'g'ri ochishi uchun
    $safe = static fn($v) => preg_match('/^[=+\-@\t\r]/', (string)$v) ? "'" . $v : (string)$v; // CSV formula injection
    fputcsv($out, ['ID', 'Sana', 'Ism', 'Telefon', 'Xizmat', 'Xabar', 'Holat', 'Til'], ';', '"', '\\');
    while ($row = $stmt->fetch()) {
        fputcsv($out, [
            $row['id'], $row['created_at'], $safe($row['name']), $safe($row['phone']), $safe($row['service']),
            $safe($row['message']), $statuses[$row['status']][0] ?? $row['status'], strtoupper($row['lang']),
        ], ';', '"', '\\');
    }
    fclose($out);
    exit;
}

/* ---------------------------------------------------------------- Bitta ariza */
if ($action === 'view') {
    $order = orders_find((int)input('id'));
    if (!$order) {
        flash('error', 'Ariza topilmadi.');
        redirect_to('orders');
    }
    $stmt = db()->prepare('SELECT e.*, ad.name AS admin_name FROM application_events e LEFT JOIN admins ad ON ad.id = e.admin_id WHERE e.application_id = :id ORDER BY e.id DESC');
    $stmt->execute([':id' => $order['id']]);
    $events = $stmt->fetchAll();
    // Shu raqamdan boshqa arizalar
    $stmt = db()->prepare('SELECT id, created_at, status FROM applications WHERE phone = :p AND id <> :id ORDER BY id DESC LIMIT 5');
    $stmt->execute([':p' => $order['phone'], ':id' => $order['id']]);
    $related = $stmt->fetchAll();

    render_admin('Ariza #' . $order['id'], 'orders', static function () use ($order, $events, $related, $statuses) { ?>
        <div class="page-head">
            <div>
                <a class="back" href="<?= e(admin_url('orders')) ?>">← Arizalar</a>
                <h1>Ariza #<?= (int)$order['id'] ?> <?= status_badge($order['status']) ?></h1>
                <p class="muted"><?= e(format_date($order['created_at'])) ?> · <?= $order['lang'] === 'ru' ? 'Rus tilida' : 'O‘zbek tilida' ?></p>
            </div>
            <a class="btn" href="<?= e(tel_href($order['phone'])) ?>"><?= icon('phone') ?>Qo‘ng‘iroq qilish</a>
        </div>
        <div class="grid-2 wide-left">
            <div class="stack">
                <section class="card">
                    <dl class="details">
                        <div><dt>Ism</dt><dd><?= e($order['name']) ?></dd></div>
                        <div><dt>Telefon</dt><dd><a href="<?= e(tel_href($order['phone'])) ?>"><?= e($order['phone']) ?></a></dd></div>
                        <div><dt>Xizmat</dt><dd><?= e($order['service_name'] ?? ($order['service'] ?: '—')) ?></dd></div>
                        <?php if (!empty($order['location_id'])): ?>
                        <div class="full"><dt>Reklama joyi</dt><dd><?php if ($order['location_name'] !== null): ?><a href="<?= e(admin_url('points', ['a' => 'edit', 'id' => $order['location_id']])) ?>">📍 <?= e($order['location_name']) ?></a> <span class="muted">· <?= e((string)$order['location_district']) ?> · #<?= (int)$order['location_id'] ?></span><?php else: ?><span class="muted">O‘chirilgan joy</span><?php endif; ?></dd></div>
                        <?php endif; ?>
                        <div class="full"><dt>Xabar</dt><dd class="pre"><?= $order['message'] !== null && $order['message'] !== '' ? e($order['message']) : '<span class="muted">Kiritilmagan</span>' ?></dd></div>
                        <div><dt>Telegram</dt><dd><?= (int)$order['telegram_sent'] === 1 ? 'Yuborilgan' : '<span class="muted">Yuborilmagan</span>' ?></dd></div>
                        <div><dt>IP manzil</dt><dd class="muted"><?= e($order['ip_address'] ?? '—') ?></dd></div>
                    </dl>
                </section>
                <section class="card">
                    <div class="card-head"><h2>Tarix va izohlar</h2></div>
                    <form method="post" action="<?= e(admin_url('orders', ['a' => 'note', 'id' => $order['id']])) ?>" class="note-form">
                        <?= csrf_field() ?>
                        <textarea name="note" rows="2" maxlength="2000" placeholder="Izoh qoldiring: qo‘ng‘iroq natijasi, kelishuv, eslatma…" required></textarea>
                        <button class="btn btn-sm" type="submit">Qo‘shish</button>
                    </form>
                    <ul class="timeline">
                        <?php foreach ($events as $ev): ?>
                            <li>
                                <span class="tl-dot"></span>
                                <div>
                                    <?php if ($ev['type'] === 'status'): ?>
                                        Holat: <?= status_badge((string)$ev['from_status']) ?> → <?= status_badge((string)$ev['to_status']) ?>
                                    <?php else: ?>
                                        <div class="pre"><?= e((string)$ev['note']) ?></div>
                                    <?php endif; ?>
                                    <small class="muted"><?= e(format_date($ev['created_at'])) ?><?= $ev['admin_name'] ? ' · ' . e($ev['admin_name']) : '' ?></small>
                                </div>
                            </li>
                        <?php endforeach; ?>
                        <li><span class="tl-dot"></span><div>Ariza saytdan kelib tushdi<small class="muted"><?= e(format_date($order['created_at'])) ?></small></div></li>
                    </ul>
                </section>
            </div>
            <div class="stack">
                <section class="card">
                    <div class="card-head"><h2>Holatni o‘zgartirish</h2></div>
                    <div class="status-buttons">
                        <?php foreach ($statuses as $key => [$label, $color]): ?>
                            <form method="post" action="<?= e(admin_url('orders', ['a' => 'status', 'id' => $order['id']])) ?>">
                                <?= csrf_field() ?>
                                <input type="hidden" name="status" value="<?= e($key) ?>">
                                <button type="submit" class="status-btn badge-<?= e($color) ?><?= $order['status'] === $key ? ' on' : '' ?>"<?= $order['status'] === $key ? ' disabled' : '' ?>><?= e($label) ?></button>
                            </form>
                        <?php endforeach; ?>
                    </div>
                </section>
                <?php if ($related): ?>
                <section class="card">
                    <div class="card-head"><h2>Shu raqamdan boshqa arizalar</h2></div>
                    <ul class="plain-list">
                        <?php foreach ($related as $r): ?>
                            <li><a href="<?= e(admin_url('orders', ['a' => 'view', 'id' => $r['id']])) ?>">#<?= (int)$r['id'] ?></a> <?= status_badge($r['status']) ?> <small class="muted"><?= e(format_date($r['created_at'], false)) ?></small></li>
                        <?php endforeach; ?>
                    </ul>
                </section>
                <?php endif; ?>
                <section class="card">
                    <div class="card-head"><h2>Amallar</h2></div>
                    <div class="stack-sm">
                        <form method="post" action="<?= e(admin_url('orders', ['a' => 'telegram', 'id' => $order['id']])) ?>">
                            <?= csrf_field() ?>
                            <button class="btn btn-ghost btn-block" type="submit"<?= Telegram::isConfigured() ? '' : ' disabled title="Telegram sozlanmagan"' ?>><?= icon('send') ?>Telegram’ga qayta yuborish</button>
                        </form>
                        <form method="post" action="<?= e(admin_url('orders', ['a' => 'delete', 'id' => $order['id']])) ?>" data-confirm="Ariza #<?= (int)$order['id'] ?> butunlay o‘chirilsinmi? Bu amalni ortga qaytarib bo‘lmaydi.">
                            <?= csrf_field() ?>
                            <button class="btn btn-danger btn-block" type="submit"><?= icon('trash') ?>O‘chirish</button>
                        </form>
                    </div>
                </section>
            </div>
        </div>
    <?php });
    return;
}

/* ---------------------------------------------------------------- Ro'yxat */
[$where, $params] = orders_filter();
$perPage = 25;
$page = max(1, (int)input('page', '1'));
$stmt = db()->prepare("SELECT COUNT(*) FROM applications a {$where}");
$stmt->execute($params);
$total = (int)$stmt->fetchColumn();
$pages = max(1, (int)ceil($total / $perPage));
$page = min($page, $pages);
$offset = ($page - 1) * $perPage;

$stmt = db()->prepare("SELECT a.id, a.name, a.phone, a.status, a.created_at, a.message, COALESCE(s.name_uz, NULLIF(a.service, ''), '—') AS service_name
    FROM applications a LEFT JOIN services s ON s.id = a.service_id {$where} ORDER BY a.id DESC LIMIT {$perPage} OFFSET {$offset}");
$stmt->execute($params);
$rows = $stmt->fetchAll();

$counts = ['' => 0];
foreach (db()->query('SELECT status, COUNT(*) AS c FROM applications GROUP BY status') as $r) {
    $counts[$r['status']] = (int)$r['c'];
    if ($r['status'] !== 'spam') {
        $counts[''] += (int)$r['c'];
    }
}
$services = crud_options('SELECT id, name_uz FROM services ORDER BY sort_order, id');
$filters = array_filter(['status' => input('status'), 'service' => input('service'), 'q' => input('q'), 'from' => input('from'), 'to' => input('to')], static fn($v) => $v !== '');

render_admin('Arizalar', 'orders', static function () use ($rows, $total, $page, $pages, $counts, $statuses, $services, $filters) {
    $current = $filters['status'] ?? '';
    ?>
    <div class="page-head">
        <div>
            <h1>Arizalar</h1>
            <p class="muted">Saytdagi aloqa formasi orqali kelgan murojaatlar. Jami: <?= $total ?> ta.</p>
        </div>
        <a class="btn btn-ghost" href="<?= e(admin_url('orders', ['a' => 'export'] + $filters)) ?>"><?= icon('download') ?>CSV (Excel)</a>
    </div>
    <div class="tabs">
        <a class="tab<?= $current === '' ? ' on' : '' ?>" href="<?= e(admin_url('orders', array_diff_key($filters, ['status' => 1]))) ?>">Barchasi <b><?= $counts[''] ?></b></a>
        <?php foreach ($statuses as $key => [$label]): ?>
            <a class="tab<?= $current === $key ? ' on' : '' ?>" href="<?= e(admin_url('orders', ['status' => $key] + $filters)) ?>"><?= e($label) ?> <b><?= $counts[$key] ?? 0 ?></b></a>
        <?php endforeach; ?>
    </div>
    <form class="filters card" method="get" action="<?= e(url('admin/')) ?>">
        <input type="hidden" name="r" value="orders">
        <?php if ($current !== ''): ?><input type="hidden" name="status" value="<?= e($current) ?>"><?php endif; ?>
        <label class="field"><span class="label">Qidiruv</span><input type="search" name="q" value="<?= e($filters['q'] ?? '') ?>" placeholder="Ism, telefon, xabar yoki #ID"></label>
        <label class="field"><span class="label">Xizmat</span><select name="service"><option value="">Barchasi</option><?php foreach ($services as $id => $name): ?><option value="<?= e($id) ?>"<?= ($filters['service'] ?? '') === (string)$id ? ' selected' : '' ?>><?= e($name) ?></option><?php endforeach; ?></select></label>
        <label class="field"><span class="label">Sanadan</span><input type="date" name="from" value="<?= e($filters['from'] ?? '') ?>"></label>
        <label class="field"><span class="label">Sanagacha</span><input type="date" name="to" value="<?= e($filters['to'] ?? '') ?>"></label>
        <div class="filter-actions"><button class="btn btn-sm" type="submit">Filtrlash</button><?php if (array_diff_key($filters, ['status' => 1])): ?><a class="btn btn-ghost btn-sm" href="<?= e(admin_url('orders', $current !== '' ? ['status' => $current] : [])) ?>">Tozalash</a><?php endif; ?></div>
    </form>
    <div class="card table-card">
        <?php if (!$rows): ?>
            <div class="empty">Bu filtr bo‘yicha arizalar topilmadi.</div>
        <?php else: ?>
        <table class="table orders-table">
            <thead><tr><th>#</th><th>Mijoz</th><th>Telefon</th><th>Xizmat</th><th>Holat</th><th>Sana</th></tr></thead>
            <tbody>
            <?php foreach ($rows as $row): $link = admin_url('orders', ['a' => 'view', 'id' => $row['id']]); ?>
                <tr class="<?= $row['status'] === 'new' ? 'is-new' : '' ?>" data-href="<?= e($link) ?>">
                    <td class="muted">#<?= (int)$row['id'] ?></td>
                    <td class="strong"><a href="<?= e($link) ?>"><?= e($row['name']) ?></a><?php if ($row['message']): ?><div class="muted small"><?= e(str_limit((string)$row['message'], 70)) ?></div><?php endif; ?></td>
                    <td class="nowrap"><a href="<?= e(tel_href($row['phone'])) ?>"><?= e($row['phone']) ?></a></td>
                    <td><?= e($row['service_name']) ?></td>
                    <td><?= status_badge($row['status']) ?></td>
                    <td class="muted small nowrap"><?= e(format_date($row['created_at'])) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
    <?php if ($pages > 1): ?>
        <nav class="pager" aria-label="Sahifalar">
            <?php for ($p = 1; $p <= $pages; $p++): ?>
                <?php if ($p === 1 || $p === $pages || abs($p - $page) <= 2): ?>
                    <a class="<?= $p === $page ? 'on' : '' ?>" href="<?= e(admin_url('orders', ['page' => $p] + $filters)) ?>"><?= $p ?></a>
                <?php elseif (abs($p - $page) === 3): ?><span>…</span><?php endif; ?>
            <?php endfor; ?>
        </nav>
    <?php endif; ?>
    <?php
});
