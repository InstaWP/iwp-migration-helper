<?php return array(
    'root' => array(
        'name' => 'instawp/hosting-migration',
        'pretty_version' => 'dev-main',
        'version' => 'dev-main',
        'reference' => '3ce0b71307c050a412114f3e996370c2acd0a9be',
        'type' => 'wordpress-plugin',
        'install_path' => __DIR__ . '/../../',
        'aliases' => array(),
        'dev' => true,
    ),
    'versions' => array(
        'instawp/connect-helpers' => array(
            'pretty_version' => 'dev-main',
            'version' => 'dev-main',
            'reference' => '21d10dc721b3e92621131a06e2ec6dbd813e7479',
            'type' => 'library',
            'install_path' => __DIR__ . '/../instawp/connect-helpers',
            'aliases' => array(
                0 => '9999999-dev',
            ),
            'dev_requirement' => false,
        ),
        'instawp/hosting-migration' => array(
            'pretty_version' => 'dev-main',
            'version' => 'dev-main',
            'reference' => '3ce0b71307c050a412114f3e996370c2acd0a9be',
            'type' => 'wordpress-plugin',
            'install_path' => __DIR__ . '/../../',
            'aliases' => array(),
            'dev_requirement' => false,
        ),
        'wp-cli/wp-config-transformer' => array(
            'pretty_version' => 'v1.4.2',
            'version' => '1.4.2.0',
            'reference' => 'b78cab1159b43eb5ee097e2cfafe5eab573d2a8a',
            'type' => 'library',
            'install_path' => __DIR__ . '/../wp-cli/wp-config-transformer',
            'aliases' => array(),
            'dev_requirement' => false,
        ),
    ),
);
