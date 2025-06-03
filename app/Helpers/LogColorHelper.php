<?php
if (!function_exists('getLogBadgeColor')) {
    function getLogBadgeColor($title)
    {
        $colors = [
            'Survey' => 'bg-info',
            'Instalasi' => 'bg-success',
            'Upgrade' => 'bg-warning',
            'Downgrade' => 'bg-warning',
            'Ganti Vendor' => 'bg-secondary',
            'Dismantle' => 'bg-danger',
            'Relokasi' => 'bg-dark',
            'Maintenance' => 'bg-primary',
            'Default' => 'bg-secondary',
        ];

        return $colors[$title] ?? $colors['Default'];
    }
}
