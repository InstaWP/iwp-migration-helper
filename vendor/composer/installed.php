<?php return array(
    'root' => array(
        'pretty_version' => 'dev-main',
        'version' => 'dev-main',
        'type' => 'wordpress-plugin',
        'install_path' => __DIR__ . '/../../',
        'aliases' => array(),
        'reference' => 'ad064334f22ee574fbf4781661144f126c4904ef',
        'name' => 'instawp/hosting-migration',
        'dev' => true,
    ),
    'versions' => array(
        'instawp/connect-helpers' => array(
            'pretty_version' => 'dev-main',
            'version' => 'dev-main',
            'type' => 'library',
            'install_path' => __DIR__ . '/../instawp/connect-helpers',
            'aliases' => array(
                0 => '9999999-dev',
            ),
            'reference' => 'c1b990ed12e2401f79a5ede041829da9fb71b11c',
            'dev_requirement' => false,
        ),
        'instawp/hosting-migration' => array(
            'pretty_version' => 'dev-main',
            'version' => 'dev-main',
            'type' => 'wordpress-plugin',
            'install_path' => __DIR__ . '/../../',
            'aliases' => array(),
            'reference' => 'ad064334f22ee574fbf4781661144f126c4904ef',
            'dev_requirement' => false,
        ),
        'wp-cli/wp-config-transformer' => array(
            'pretty_version' => 'v1.3.6',
            'version' => '1.3.6.0',
            'type' => 'library',
            'install_path' => __DIR__ . '/../wp-cli/wp-config-transformer',
            'aliases' => array(),
            'reference' => '88f516f44dce1660fc4b780da513e3ca12d7d24f',
            'dev_requirement' => false,
        ),
    ),
);
