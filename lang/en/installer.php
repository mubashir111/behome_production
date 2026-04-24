<?php

return [
    'title'       => 'Behom Installer',
    'next'        => 'Next Step',
    'welcome'     => [
        'templateTitle' => 'Welcome',
        'title'         => 'Behom Installer',
        'message'       => 'Easy Installation and Setup Wizard.',
        'next'          => 'Check Requirements',
    ],
    'requirement' => [
        'templateTitle' => 'Step 1 | Server Requirements',
        'title'         => 'Server Requirements',
        'next'          => 'Check Permissions',
        'version'       => 'version',
        'required'      => 'required'
    ],
    'permission'  => [
        'templateTitle'       => 'Step 2 | Permissions',
        'title'               => 'Permissions',
        'next'                => 'Site Setup',
        'permission_checking' => 'Permission Checking'
    ],
    'license' => [
        'templateTitle' => 'Step 3 | Terms',
        'title'         => 'Terms & Conditions',
        'next'          => 'Site Setup',
        'label'         => [
            'accept' => 'I have read and agree to the terms and conditions'
        ]
    ],
    'site'        => [
        'templateTitle' => 'Step 4 | Site Setup',
        'title'         => 'Site Setup',
        'next'          => 'Database Setup',
        'label'         => [
            'app_name'          => 'App Name',
            'app_url'           => 'Backend URL',
            'frontend_url'      => 'Frontend URL',
            'mail_section'      => 'Mail (SMTP) Configuration',
            'mail_host'         => 'SMTP Host',
            'mail_port'         => 'SMTP Port',
            'mail_encryption'   => 'Encryption',
            'mail_username'     => 'Mail Username',
            'mail_password'     => 'Mail Password',
            'mail_from_address' => 'From Address',
            'mail_from_name'    => 'From Name',
        ]
    ],
    'database'    => [
        'templateTitle'            => 'Step 5 | Database Setup',
        'title'                    => 'Database Setup',
        'next'                     => 'Final Setup',
        'fail_message'             => 'Could not connect to the database.',
        'fail_mysql_version'       => 'Use MySQL version 8.0 or later.',
        'fail_mariadb_version'     => 'Use MariaDB version 10.2 or later.',
        'fail_postgresql_version'  => 'Use PostgreSQL version 9.4 or later.',
        'fail_sqlserver_version'   => 'Use SQL Server 2008 or later.',
        'fail_singlestore_version' => 'Use SingleStore version 8.1 or later.',
        'label'                    => [
            'database_connection' => 'Database Connection',
            'database_host'       => 'Database Host',
            'database_port'       => 'Database Port',
            'database_name'       => 'Database Name',
            'database_username'   => 'Database Username',
            'database_password'   => 'Database Password',
        ]
    ],
    'final'       => [
        'templateTitle'   => 'Step 6 | Final Setup',
        'title'           => 'Final Setup',
        'success_message' => 'Application has been successfully installed.',
        'login_info'      => 'Default Login Credentials',
        'email'           => 'Email',
        'password'        => 'Password',
        'email_info'      => 'admin@example.com',
        'password_info'   => '123456',
        'next'            => 'Finish & Launch',
    ],
    'installed'   => [
        'success_log_message' => 'Behom successfully INSTALLED on ',
        'update_log_message'  => 'Behom successfully UPDATED on ',
    ],
];
