<?php

return [

    'roles' => [
        'activity_logs' => ['super_admin'],

        'users' => ['super_admin'],
        'vehicles' => ['super_admin', 'admin'],
        'cities' => ['super_admin', 'admin'],
        'meeting_points' => ['super_admin', 'admin'],
        'schedules' => ['super_admin', 'admin'],
        'tariffs' => ['super_admin', 'admin'],

        'finance' => ['super_admin', 'admin'],
        'laporan' => ['super_admin', 'admin'],
        'setoran' => ['super_admin', 'admin'],

        'driver' => ['driver'],
        'trip' => ['driver'],
        'earnings' => ['driver'],

        'booking' => ['passenger'],

        'dashboard' => ['super_admin', 'admin', 'driver', 'passenger'],
        'settings' => ['super_admin', 'admin', 'driver', 'passenger'],
    ]

];