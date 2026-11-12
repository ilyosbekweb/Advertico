<?php
declare(strict_types=1);

$pdo = db();
$kpi = $pdo->query("SELECT
        SUM(status = 'new') AS new_count,
        SUM(DATE(created_at) = CURDATE() AND status <> 'spam') AS today,
        SUM(created_at >= (CURDATE() - INTERVAL 6 DAY) AND status <> 'spam') AS week,
        SUM(created_at >= (CURDATE() - INTERVAL 29 DAY) AND status <> 'spam') AS month,
        SUM(created_at >= (CURDATE() - INTERVAL 59 DAY) AND created_at < (CURDATE() - INTERVAL 29 DAY) AND status <> 'spam') AS prev_month,
        SUM(status = 'won') AS won,
        SUM(status <> 'spam') AS total
    FROM applications")->fetch();
$kpi = array_map('intval', $kpi);
$conversion = $kpi['total'] > 0 ? round($kpi['won'] / $kpi['total'] * 100, 1) : 0;
$trend = $kpi['prev_month'] > 0 ? (int)round(($kpi['month'] - $kpi['prev_month']) / $kpi['prev_month'] * 100) : null;

// Oxirgi 30 kun bo'yicha kunlik arizalar
$daily = [];
for ($i = 29; $i >= 0; $i--) {
    $daily[date('Y-m-d', strtotime("-{$i} days"))] = 0;
}
foreach ($pdo->query("SELECT DATE(created_at) AS d, COUNT(*) AS c FROM applications
        WHERE created_at >= (CURDATE() - INTERVAL 29 DAY) AND status <> 'spam' GROUP BY DATE(created_at)") as $row) {
    $daily[$row['d']] = (int)$row['c'];
}
$maxDaily = max(1, ...array_values($daily));

$byService = $pdo->query("SELECT COALESCE(s.name_uz, NULLIF(a.service, ''), 'Tanlanmagan') AS service_label, COUNT(*) AS c
    FROM applications a LEFT JOIN services s ON s.id = a.service_id
    WHERE a.status <> 'spam' GROUP BY service_label ORDER BY c DESC LIMIT 8")->fetchAll();
$maxService = max(1, ...array_map(static fn($r) => (int)$r['c'], $byService ?: [['c' => 1]]));

$byStatus = [];
foreach ($pdo->query('SELECT status, COUNT(*) AS c FROM applications GROUP BY status') as $row) {
    $byStatus[$row['status']] = (int)$row['c'];
}
$latest = $pdo->query('SELECT id, name, phone, service, status, created_at FROM applications ORDER BY id DESC LIMIT 8')->fetchAll();

$checks = [
    ['Telegram xabarnomalari', Telegram::isEnabled(), Telegram::isConfigured() ? (Telegram::isEnabled() ? 'Yoqilgan' : 'O‘chirilgan') : 'Sozlanmagan — Sozlamalar bo‘limiga o‘ting'],
    ['HTTPS', is_https(), is_https() ? 'Xavfsiz ulanish' : 'Production serverda SSL yoqilishi shart'],
    ['Debug rejimi', !config('debug'), config('debug') ? 'Yoqilgan — production’da o‘chiring (app/config.php)' : 'O‘chirilgan'],
    ['Rasmlarni qayta kodlash (GD)', extension_loaded('gd'), extension_loaded('gd') ? 'Mavjud' : 'GD yo‘q — rasmlar tekshiriladi, lekin qayta kodlanmaydi'],
];

render_admin('Boshqaruv paneli', 'dashboard', static function () use ($kpi, $conversion, $trend, $daily, $maxDaily, $byService, $maxService, $byStatus, $latest, $checks) {
    $admin = Auth::user();
    ?>
    <div class="page-head">
        <div>
            <h1>Xush kelibsiz, <?= e((string)$admin['name']) ?></h1>
            <p class="muted">Bugun: <?= e(date('d.m.Y')) ?><?php if (!empty($admin['last_login_at'])): ?> · Oxirgi kirish: <?= e(format_date($admin['last_login_at'])) ?><?php endif; ?></p>
        </div>
        <a class="btn" href="<?= e(admin_url('orders', ['status' => 'new'])) ?>"><?= icon('orders') ?>Yangi arizalar</a>
    </div>

    <div class="kpis">
        <a class="kpi card accent" href="<?= e(admin_url('orders', ['status' => 'new'])) ?>">
            <span class="kpi-label">Yangi arizalar</span>
            <strong><?= $kpi['new_count'] ?></strong>
            <span class="kpi-sub">ko‘rib chiqilmagan</span>
        </a>
        <div class="kpi card">
            <span class="kpi-label">Bugun</span>
            <strong><?= $kpi['today'] ?></strong>
            <span class="kpi-sub">7 kunda: <?= $kpi['week'] ?></span>
        </div>
        <div class="kpi card">
            <span class="kpi-label">30 kun</span>
            <strong><?= $kpi['month'] ?></strong>
            <span class="kpi-sub"><?php if ($trend === null): ?>oldingi davr ma’lumoti yo‘q<?php else: ?><b class="<?= $trend >= 0 ? 'up' : 'down' ?>"><?= $trend >= 0 ? '▲' : '▼' ?> <?= abs($trend) ?>%</b> oldingi 30 kunga nisbatan<?php endif; ?></span>
        </div>
        <div class="kpi card">
            <span class="kpi-label">Konversiya</span>
            <strong><?= e(number_format($conversion, 1, ',', ' ')) ?>%</strong>
            <span class="kpi-sub"><?= $kpi['won'] ?> ta shartnoma / <?= $kpi['total'] ?> ta ariza</span>
        </div>
    </div>

    <div class="grid-2">
        <section class="card">
            <div class="card-head"><h2>Arizalar — oxirgi 30 kun</h2></div>
            <div class="bars" role="img" aria-label="Oxirgi 30 kundagi kunlik arizalar soni">
                <?php foreach ($daily as $day => $count): ?>
                    <div class="bar" style="--h:<?= $count / $maxDaily * 100 ?>%" title="<?= e(date('d.m', strtotime($day))) ?>: <?= $count ?> ta"><span></span></div>
                <?php endforeach; ?>
            </div>
            <div class="bars-axis"><span><?= e(date('d.m', strtotime(array_key_first($daily)))) ?></span><span>Bugun</span></div>
        </section>
        <section class="card">
            <div class="card-head"><h2>Xizmatlar bo‘yicha</h2></div>
            <?php if (!$byService): ?>
                <div class="empty small">Hali arizalar yo‘q.</div>
            <?php else: ?>
                <div class="hbars">
                    <?php foreach ($byService as $row): ?>
                        <div class="hbar"><span class="hbar-label"><?= e($row['service_label']) ?></span><span class="hbar-track"><span style="width:<?= (int)$row['c'] / $maxService * 100 ?>%"></span></span><b><?= (int)$row['c'] ?></b></div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
    </div>

    <div class="grid-2 wide-left">
        <section class="card table-card">
            <div class="card-head"><h2>So‘nggi arizalar</h2><a class="link" href="<?= e(admin_url('orders')) ?>">Barchasi →</a></div>
            <?php if (!$latest): ?>
                <div class="empty small">Arizalar hali kelib tushmagan. Saytdagi aloqa formasi orqali yuborilgan arizalar shu yerda ko‘rinadi.</div>
            <?php else: ?>
            <table class="table">
                <tbody>
                <?php foreach ($latest as $row): ?>
                    <tr>
                        <td class="strong"><a href="<?= e(admin_url('orders', ['a' => 'view', 'id' => $row['id']])) ?>"><?= e($row['name']) ?></a><div class="muted small"><?= e($row['service'] ?: '—') ?></div></td>
                        <td class="nowrap"><a href="<?= e(tel_href($row['phone'])) ?>"><?= e($row['phone']) ?></a></td>
                        <td><?= status_badge($row['status']) ?></td>
                        <td class="muted small nowrap"><?= e(format_date($row['created_at'])) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </section>
        <div class="stack">
            <section class="card">
                <div class="card-head"><h2>Holatlar</h2></div>
                <ul class="status-list">
                    <?php foreach (order_statuses() as $key => [$label, $color]): ?>
                        <li><a href="<?= e(admin_url('orders', ['status' => $key])) ?>"><span class="badge badge-<?= e($color) ?>"><?= e($label) ?></span><b><?= $byStatus[$key] ?? 0 ?></b></a></li>
                    <?php endforeach; ?>
                </ul>
            </section>
            <section class="card">
                <div class="card-head"><h2>Tizim holati</h2></div>
                <ul class="checks">
                    <?php foreach ($checks as [$label, $ok, $note]): ?>
                        <li class="<?= $ok ? 'ok' : 'warn' ?>"><span class="check-dot"></span><div><b><?= e($label) ?></b><small><?= e($note) ?></small></div></li>
                    <?php endforeach; ?>
                </ul>
            </section>
        </div>
    </div>
    <?php
});
