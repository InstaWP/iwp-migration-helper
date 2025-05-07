<?php return array(
    'root' => array(
        'name' => 'instawp/hosting-migration',
        'pretty_version' => 'dev-main',
        'version' => 'dev-main',
        'reference' => 'b33afd045426a5dc75acee453752654a4832fafe',
        'type' => 'wordpress-plugin',
        'install_path' => __DIR__ . '/../../',
        'aliases' => array(),
        'dev' => true,
    ),
    'versions' => array(
        'instawp/connect-helpers' => array(
            'pretty_version' => 'dev-main',
            'version' => 'dev-main',
            'reference' => '7d59e8a9bf40d0bd81a9cabc69f28671d0a6c6d2',
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
            'reference' => 'b33afd045426a5dc75acee453752654a4832fafe',
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
