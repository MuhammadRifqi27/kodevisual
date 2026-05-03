<?php

return [
    # Menus
    'KT_MENU_MODE' => 'auto',
    /** 'manual' or 'auto' */

    'KT_MENUS' => [
        [
            'label' => 'Apps',
            'type' => 'item',
            'route' => 'dashboard',
            'active' => ['dashboard'],
            'permissionType' => 'gate',
            'icon' => 'ki',
            'iconName' => 'abstract-27',
        ],
        [
            'label' => 'Travel Planner',
            'type' => 'heading',
            'permission' => ['travel-planner.dashboard'],
            'permissionType' => 'gate'
        ],
        [
            'label' => 'Dashboard',
            'type' => 'item',
            'route' => 'travel.dashboard',
            'active' => ['travel.dashboard'],
            'permission' => ['travel-planner.dashboard'],
            'permissionType' => 'gate',
            'icon' => 'ki',
            'iconName' => 'home'
        ],
        [
            'label' => 'My Trips',
            'type' => 'item',
            'route' => 'travel.trips.index',
            'active' => ['travel.trips.*'],
            'permission' => ['travel-planner.trips'],
            'permissionType' => 'gate',
            'icon' => 'ki',
            'iconName' => 'map',
        ],
        [
            'label' => 'Travel Budgeting',
            'type' => 'item',
            'route' => 'travel.budgets.index',
            'active' => ['travel.budgets.*', 'travel.expenses.*'],
            'permission' => ['travel-planner.budgets'],
            'permissionType' => 'gate',
            'icon' => 'ki',
            'iconName' => 'dollar',
            'children' => [
                [
                    'label' => 'Budget Plans',
                    'type' => 'item',
                    'route' => 'travel.budgets.index',
                    'icon' => 'dot',
                ],
                [
                    'label' => 'Expenses Tracker',
                    'type' => 'item',
                    'route' => 'travel.expenses.index',
                    'icon' => 'dot',
                ],
            ]
        ],
        [
            'label' => 'Money Management System',
            'type' => 'heading',
            'permission' => ['money-management.dashboard'],
            'permissionType' => 'gate'
        ],
        [
            'label' => 'Dashboard',
            'type' => 'item',
            'route' => 'money-management.dashboard',
            'active' => ['money-management.dashboard'],
            'permission' => ['money-management.dashboard'],
            'permissionType' => 'gate',
            'icon' => 'ki',
            'iconName' => 'home'
        ],
        [
            'label' => 'Transactions',
            'type' => 'item',
            'route' => 'money-management.transactions.index',
            'active' => ['money-management.transactions.index', 'money-management.recurring.index'],
            'permission' => ['money-management.transactions'],
            'permissionType' => 'gate',
            'icon' => 'ki',
            'iconName' => 'dollar',
            'children' => [
                [
                    'label' => 'Transactions History',
                    'type' => 'item',
                    'route' => 'money-management.transactions.index',
                    'active' => ['money-management.transactions.index'],
                    'icon' => 'dot',
                ],
                [
                    'label' => 'Recurring (Automation)',
                    'type' => 'item',
                    'route' => 'money-management.recurring.index',
                    'active' => ['money-management.recurring.index'],
                    'icon' => 'dot',
                ]
            ]
        ],
        [
            'label' => 'Bitcoin Tracking',
            'type' => 'item',
            'route' => 'money-management.btc-tracking.index',
            'active' => ['money-management.btc-tracking.index', 'money-management.btc-tracking.show'],
            'permission' => ['money-management.btc-tracking'],
            'permissionType' => 'gate',
            'icon' => 'ki',
            'iconName' => 'bitcoin'
        ],
        [
            'label' => 'Monthly Budgets',
            'type' => 'item',
            'route' => 'money-management.budgets.index',
            'active' => ['money-management.budgets.index'],
            'permission' => ['money-management.budgets'],
            'permissionType' => 'gate',
            'icon' => 'ki',
            'iconName' => 'chart-line-up-2'
        ],
        [
            'label' => 'Summary',
            'type' => 'item',
            'route' => 'money-management.summary.index',
            'active' => ['money-management.summary.index'],
            'permission' => ['money-management.summary'],
            'permissionType' => 'gate',
            'icon' => 'ki',
            'iconName' => 'chart-pie-3'
        ],
        [
            'label' => 'Internal Transfers',
            'type' => 'item',
            'route' => 'money-management.transfers.index',
            'active' => ['money-management.transfers.index'],
            'permission' => ['money-management.internal-transfers'],
            'permissionType' => 'gate',
            'icon' => 'ki',
            'iconName' => 'arrows-loop'
        ],
        [
            'label' => 'Portfolio / Savings',
            'type' => 'item',
            'route' => 'money-management.portfolio.index',
            'active' => ['money-management.portfolio.index', 'money-management.portfolio.show'],
            'permission' => ['money-management.portfolio'],
            'permissionType' => 'gate',
            'icon' => 'ki',
            'iconName' => 'wallet'
        ],
        [
            'label' => 'Wedding Planner',
            'type' => 'item',
            'route' => 'money-management.wedding-planner.index',
            'active' => ['money-management.wedding-planner.index', 'money-management.wedding-planner.show'],
            'permissionType' => 'gate',
            'icon' => 'ki',
            'iconName' => 'wallet'
        ],
        [
            'label' => 'Master Data',
            'type' => 'item',
            'route' => 'money-management.master-data',
            'active' => ['money-management.master-data'],
            'permission' => ['money-management.settings'],
            'permissionType' => 'gate',
            'icon' => 'ki',
            'iconName' => 'setting',
            'children' => [
                [
                    'label' => 'Category Expenses',
                    'type' => 'item',
                    'route' => 'money-management.master-data.expenses.index',
                    'active' => ['money-management.master-data.expenses.index'],
                    'permission' => ['money-management.settings'],
                    'permissionType' => 'gate',
                    'icon' => 'ki',
                    'iconName' => 'minus-square',
                ],
                [
                    'label' => 'Category Income',
                    'type' => 'item',
                    'route' => 'money-management.master-data.income.index',
                    'active' => ['money-management.master-data.income.index'],
                    'permission' => ['money-management.settings'],
                    'permissionType' => 'gate',
                    'icon' => 'ki',
                    'iconName' => 'plus-square',
                ],
                [
                    'label' => 'Investment',
                    'type' => 'item',
                    'route' => 'money-management.master-data.investments.index',
                    'active' => ['money-management.master-data.investments.index'],
                    'permission' => ['money-management.settings'],
                    'permissionType' => 'gate',
                    'icon' => 'ki',
                    'iconName' => 'chart-line-up',
                ],
                [
                    'label' => 'Finance Settings',
                    'type' => 'item',
                    'route' => 'money-management.master-data.settings.index',
                    'active' => ['money-management.master-data.settings.index'],
                    'permission' => ['money-management.settings'],
                    'permissionType' => 'gate',
                    'icon' => 'ki',
                    'iconName' => 'setting-2',
                ]
            ]
        ],
        [
            'label' => 'Master Data',
            'type' => 'heading',
            'permission' => ['master_data'],
            'permissionType' => 'gate',
        ],
        [
            'label' => 'Management',
            'type' => 'item',
            'icon' => 'ki',
            'iconName' => 'setting',
            'permission' => ['master_data'],
            'permissionType' => 'gate',
            'children' => [
                [
                    'label' => 'User Management',
                    'type' => 'item',
                    'icon' => 'ki',
                    'iconName' => 'user',
                    'permission' => ['users'],
                    'permissionType' => 'gate',
                    'children' => [
                         [
                            'label' => 'User Approval',
                            'type' => 'item',
                            'route' => 'administrator.user-approval.index',
                            'icon' => 'ki',
                            'iconName' => 'check-circle',
                        ],
                        [
                            'label' => 'Users List',
                            'type' => 'item',
                            'route' => 'administrator.user-approval.listing',
                            'icon' => 'ki',
                            'iconName' => 'people',
                        ]
                    ]
                ],
                [
                    'label' => 'Role Management',
                    'type' => 'item',
                    'route' => 'administrator.roles.index',
                    'icon' => 'ki',
                    'iconName' => 'profile-circle',
                    'permission' => ['roles'],
                    'permissionType' => 'gate',
                ],
                [
                    'label' => 'Permission Management',
                    'type' => 'item',
                    'route' => 'administrator.permissions.index',
                    'icon' => 'ki',
                    'iconName' => 'key',
                    'permission' => ['permissions'],
                    'permissionType' => 'gate',
                ],
                [
                    'label' => 'App Logic Management',
                    'type' => 'item',
                    'icon' => 'ki',
                    'iconName' => 'element-plus',
                    'permission' => ['permissions'],
                    'permissionType' => 'gate',
                    'children' => [
                        [
                            'label' => 'Applications',
                            'type' => 'item',
                            'route' => 'administrator.apps.index',
                            'active' => ['administrator.apps.*', 'administrator.app-roles.*', 'administrator.app-permissions.*'],
                            'icon' => 'dot',
                        ],
                    ]
                ]
            ]
        ],
        [
            'label' => 'Category Expenses',
            'type' => 'item',
            'route' => 'administrator.category.index',
            'icon' => 'ki',
            'iconName' => 'category',
            'permission' => ['master_categorys'],
            'permissionType' => 'gate',
        ]
    ],
];
