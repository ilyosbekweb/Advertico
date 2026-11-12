<?php
declare(strict_types=1);

/** Chiziqli SVG ikonkalar (currentColor). */
function x_icon(string $name, string $class = 'x-ico'): string
{
    static $paths = [
        'arrow'      => '<path d="M5 12h14M13 6l6 6-6 6"/>',
        'arrow-left' => '<path d="M19 12H5M11 6l-6 6 6 6"/>',
        'arrow-up'   => '<path d="M7 17 17 7M8 7h9v9"/>',
        'caret'      => '<path d="m6 9 6 6 6-6"/>',
        'play'       => '<path d="M8 5.5v13l11-6.5z" fill="currentColor" stroke="none"/>',
        'phone'      => '<path d="M6.6 3.8 9 3.3l2 4.8-2 1.5a15 15 0 0 0 5.4 5.4l1.5-2 4.8 2-.5 2.4a2 2 0 0 1-2 1.6A15.8 15.8 0 0 1 5 5.8a2 2 0 0 1 1.6-2Z"/>',
        'instagram'  => '<rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.5" cy="6.5" r=".6" fill="currentColor"/>',
        'pin'        => '<path d="M20 10c0 5-8 12-8 12S4 15 4 10a8 8 0 1 1 16 0Z"/><circle cx="12" cy="10" r="2.5"/>',
        // statistika
        'briefcase'  => '<rect x="3" y="7" width="18" height="13" rx="2"/><path d="M8 7V5a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2M3 12h18M10 12v2h4v-2"/>',
        'building'   => '<path d="M4 21V5l8-2v18M12 8l8 2v11M2 21h20M7 8h2M7 12h2M7 16h2M15 13h2M15 17h2"/>',
        'calendar'   => '<rect x="3" y="5" width="18" height="16" rx="2"/><path d="M3 10h18M8 3v4M16 3v4M7 14h2M11 14h2M15 14h2M7 17h2M11 17h2"/>',
        // xizmatlar
        'billboard'    => '<rect x="2.5" y="3.5" width="19" height="11" rx="1.5"/><path d="M7 14.5V21M17 14.5V21M5 21h14M6 7.5h7M6 10.5h4"/>',
        'led'          => '<rect x="2.5" y="4" width="19" height="13" rx="2"/><path d="M8 21h8M12 17v4"/><path d="M6 8h.01M9 8h.01M12 8h.01M15 8h.01M18 8h.01M6 11h.01M9 11h.01M12 11h.01M15 11h.01M18 11h.01M6 14h.01M9 14h.01M12 14h.01" stroke-width="2.2"/>',
        'cityboard'    => '<rect x="6" y="2.5" width="12" height="15" rx="1.5"/><path d="M9 6h6M9 9h4M12 17.5V21M8 21h8"/><path d="M4 8v4M20 8v4"/>',
        'roof'         => '<path d="M3 21V10l9-6 9 6v11M3 21h18"/><rect x="7" y="12" width="10" height="5" rx="1"/><path d="M9.5 14.5h5"/>',
        'letters'      => '<path d="M4 20 9.5 4h1L16 20M6.2 14h7.6"/><path d="M15 9h5M17.5 6.5v5" />',
        'bus-stops'    => '<path d="M3 4h18M5 4v16M19 4v8M5 9h14"/><rect x="9" y="12" width="10" height="8" rx="1.5"/><path d="M11 15h6"/>',
        'bus-branding' => '<rect x="3" y="4" width="18" height="13" rx="2.5"/><path d="M3 11h18M7 17v3M17 17v3M7 14h.01M17 14h.01" /><path d="M8 7.5h8"/>',
        'underpass'    => '<path d="M2 20h20M4 20V12a8 8 0 0 1 16 0v8"/><path d="M8 20v-4h3v-3h3v-3h3"/>',
        'sparkle'      => '<path d="M12 3v4M12 17v4M3 12h4M17 12h4M6 6l2.5 2.5M15.5 15.5 18 18M18 6l-2.5 2.5M8.5 15.5 6 18"/>',
    ];
    return '<svg class="' . e($class) . '" viewBox="0 0 24 24" aria-hidden="true">' . ($paths[$name] ?? $paths['sparkle']) . '</svg>';
}

/** Xizmat slug'iga mos ikonka (yangi xizmatlar uchun umumiy ikonka). */
function x_service_icon(string $slug): string
{
    $known = ['billboard', 'led', 'cityboard', 'roof', 'letters', 'bus-stops', 'bus-branding', 'underpass'];
    return x_icon(in_array($slug, $known, true) ? $slug : 'sparkle');
}
