<?php

use TakiElias\Tablar\Menu\Filters\ActiveFilter;
use TakiElias\Tablar\Menu\Filters\ClassesFilter;
use TakiElias\Tablar\Menu\Filters\DataFilter;
use TakiElias\Tablar\Menu\Filters\GateFilter;
use TakiElias\Tablar\Menu\Filters\HrefFilter;
use TakiElias\Tablar\Menu\Filters\LangFilter;
use TakiElias\Tablar\Menu\Filters\SearchFilter;

return [

    /*
    |--------------------------------------------------------------------------
    | Title
    |--------------------------------------------------------------------------
    | Here you can change the default title of your admin panel.
    |
    */

    'title' => 'RFIDs Tag Managemant',
    'title_prefix' => '',
    'title_postfix' => '',
    'bottom_title' => 'RFIDs Tag',
    'current_version' => 'v11.11',

    /*
    |--------------------------------------------------------------------------
    | Admin Panel Logo
    |--------------------------------------------------------------------------
    |
    | Here you can change the logo of your admin panel.
    |
    */

    'logo' => '<b>Tab</b>LAR',
    'logo_img_alt' => 'Admin Logo',

    /*
    |--------------------------------------------------------------------------
    | Authentication Logo
    |--------------------------------------------------------------------------
    |
    | Here you can set up an alternative logo to use on your login and register
    | screens. When disabled, the admin panel logo will be used instead.
    |
    */

    'auth_logo' => [
        'enabled' => false,
        'img' => [
            'path' => 'static/wavelinx-logo.png',
            'alt' => 'Auth Logo',
            'class' => '',
            'width' => 50,
            'height' => 50,
        ],
    ],

    /*
     *
     * Default path is 'resources/views/vendor/tablar' as null. Set your custom path here If you need.
     */

    'views_path' => null,

    /*
    |--------------------------------------------------------------------------
    | Layout
    |--------------------------------------------------------------------------
    | Here we change the layout of your admin panel.
    |
    | For detailed instructions you can look at the layout section here:
    |
    */

    'layout' => 'vertical',
    'sidebar_dark' => true,
    'dark_mode' => true,

    // 2. Add global JS injection to force Bootstrap 5 dark theme attributes on every view
    /*'js' => [
        '<script>document.addEventListener("DOMContentLoaded", function() { document.body.setAttribute("data-bs-theme", "dark"); document.body.classList.add("theme-dark"); });</script>',
    ],*/
    'layout_light_sidebar' => null,
    'layout_light_topbar' => true,
    'layout_enable_top_header' => false,

    /*
    |--------------------------------------------------------------------------
    | Sticky Navbar for Top Nav
    |--------------------------------------------------------------------------
    |
    | Here you can enable/disable the sticky functionality of Top Navigation Bar.
    |
    | For detailed instructions, you can look at the Top Navigation Bar classes here:
    |
    */

    'sticky_top_nav_bar' => true,

    /*
    |--------------------------------------------------------------------------
    | Admin Panel Classes
    |--------------------------------------------------------------------------
    |
    | Here you can change the look and behavior of the admin panel.
    |
    | For detailed instructions, you can look at the admin panel classes here:
    |
    */

    'classes_body' => '',

    /*
    |--------------------------------------------------------------------------
    | URLs
    |--------------------------------------------------------------------------
    |
    | Here we can modify the url settings of the admin panel.
    |
    | For detailed instructions, you can look at the urls section here:
    |
    */

    'use_route_url' => true,
    'dashboard_url' => 'home',
    'logout_url' => 'logout',
    'login_url' => 'login',
    'register_url' => 'register',
    'password_reset_url' => 'password.request',
    'password_email_url' => 'password.email',
    'profile_url' => 'profile',
    'setting_url' => 'settings',

    /*
    |--------------------------------------------------------------------------
    | Display Alert
    |--------------------------------------------------------------------------
    |
    | Display Alert Visibility.
    |
    */
    'display_alert' => true,

    /*
    |--------------------------------------------------------------------------
    | Chrome (notifications, header & footer buttons)
    |--------------------------------------------------------------------------
    |
    | Toggle the notifications dropdown and customise top-bar / footer
    | buttons. Each button is `['name' => ..., 'url' => ..., 'icon' => 'ti ti-*']`.
    | Empty arrays render no buttons.
    |
    */

    'enable_notifications' => true,

    'header_buttons' => [
        ['name' => 'Source code', 'url' => 'https://github.com/takielias/tablar', 'icon' => 'ti ti-brand-github'],
        ['name' => 'Sponsor', 'url' => 'https://www.buymeacoffee.com/takielias', 'icon' => 'ti ti-heart text-pink'],
    ],

    'footer_buttons' => [
        ['name' => 'Source code', 'url' => 'https://github.com/takielias/tablar', 'icon' => 'ti ti-brand-github'],
        ['name' => 'Sponsor', 'url' => 'https://www.buymeacoffee.com/takielias', 'icon' => 'ti ti-heart text-pink'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Menu Items
    |--------------------------------------------------------------------------
    |
    | Here we can modify the sidebar/top navigation of the admin panel.
    |
    | For detailed instructions you can look here:
    |
    */

    'menu' => [
        // 1. DASHBOARD
        [
            'text' => 'DASHBOARD',
            'url' => 'home',
            'icon' => 'ti ti-dashboard',
        ],

        // 2. USER MANAGEMENT
        [
            'text' => 'USER MANAGEMENT',
            'icon' => 'ti ti-users',
            'submenu' => [
                [
                    'text' => 'User List',
                    'url' => '#',
                    'icon' => 'ti ti-user-list',
                ],
                [
                    'text' => 'Roles & Permissions',
                    'url' => '#',
                    'icon' => 'ti ti-shield-lock',
                ],
                [
                    'text' => 'Add New User',
                    'url' => '#',
                    'icon' => 'ti ti-user-plus',
                ],
            ],
        ],

        // 3. AREA
        [
            'text' => 'AREA',
            'icon' => 'ti ti-map-pin',
            'submenu' => [
                [
                    'text' => 'All Areas',
                    'url' => '#',
                    'icon' => 'ti ti-list',
                ],
                [
                    'text' => 'Add Area',
                    'url' => '#',
                    'icon' => 'ti ti-plus',
                ],
            ],
        ],

        // 4. LOCATION
        [
            'text' => 'LOCATION',
            'icon' => 'ti ti-location',
            'submenu' => [
                [
                    'text' => 'All Locations',
                    'url' => '#',
                    'icon' => 'ti ti-building-warehouse',
                ],
                [
                    'text' => 'Location Types',
                    'url' => '#',
                    'icon' => 'ti ti-category',
                ],
                [
                    'text' => 'Add Location',
                    'url' => '#',
                    'icon' => 'ti ti-circle-plus',
                ],
            ],
        ],

        // 5. ASSETS
        [
            'text' => 'ASSETS',
            'icon' => 'ti ti-box',
            'submenu' => [
                [
                    'text' => 'Asset List',
                    'url' => '#',
                    'icon' => 'ti ti-packages',
                ],
                [
                    'text' => 'Asset Mapping',
                    'url' => '#',
                    'icon' => 'ti ti-link',
                ],
                [
                    'text' => 'Asset Categories',
                    'url' => '#',
                    'icon' => 'ti ti-tags',
                ],
                [
                    'text' => 'Register Asset',
                    'url' => '#',
                    'icon' => 'ti ti-square-plus',
                ],
            ],
        ],

        // 6. DEVICES
        [
            'text' => 'DEVICES',
            'icon' => 'ti ti-device-desktop',
            'submenu' => [
                [
                    'text' => 'Device Status',
                    'url' => '#',
                    'icon' => 'ti ti-activity',
                ],
                [
                    'text' => 'Reader Configuration',
                    'url' => '#',
                    'icon' => 'ti ti-settings',
                ],
                [
                    'text' => 'Add Device',
                    'url' => '#',
                    'icon' => 'ti ti-device-laptop-plus',
                ],
            ],
        ],

        // 7. REPORTS
        [
            'text' => 'REPORTS',
            'icon' => 'ti ti-file-analytics',
            'submenu' => [
                [
                    'text' => 'Summary Report',
                    'url' => '#',
                    'icon' => 'ti ti-report',
                ],
                [
                    'text' => 'IN / OUT Transactions',
                    'url' => '#',
                    'icon' => 'ti ti-arrows-left-right',
                ],
                [
                    'text' => 'Scan Logs History',
                    'url' => '#',
                    'icon' => 'ti ti-history',
                ],
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Menu Filters
    |--------------------------------------------------------------------------
    |
    | Here we can modify the menu filters of the admin panel.
    |
    | For detailed instructions you can look the menu filters section here:
    |
    */

    'filters' => [
        GateFilter::class,
        HrefFilter::class,
        SearchFilter::class,
        ActiveFilter::class,
        ClassesFilter::class,
        LangFilter::class,
        DataFilter::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Vite
    |--------------------------------------------------------------------------
    |
    | Here we can enable the Vite support.
    |
    | For detailed instructions you can look the Vite here:
    | https://laravel-vite.dev
    |
    */

    'vite' => true,

    /*
    |--------------------------------------------------------------------------
    | Livewire
    |--------------------------------------------------------------------------
    |
    | Here we can enable the Livewire support.
    |
    | For detailed instructions you can look the livewire here:
    | https://livewire.laravel.com
    |
    */

    'livewire' => false,

];
