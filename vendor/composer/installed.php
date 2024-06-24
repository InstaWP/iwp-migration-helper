<?php return array(
    'root' => array(
        'pretty_version' => 'dev-main',
        'version' => 'dev-main',
        'type' => 'wordpress-plugin',
        'install_path' => __DIR__ . '/../../',
        'aliases' => array(),
        'reference' => '9be1438f9660a97e5f3ae1203bb7f4c48b5a7a85',
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
            'reference' => '5e83f0446bbbcc45148b14be4e359e42ca7a7178',
            'dev_requirement' => false,
        ),
        'instawp/hosting-migration' => array(
            'pretty_version' => 'dev-main',
            'version' => 'dev-main',
            'type' => 'wordpress-plugin',
            'install_path' => __DIR__ . '/../../',
            'aliases' => array(),
            'reference' => '9be1438f9660a97e5f3ae1203bb7f4c48b5a7a85',
            'dev_requirement' => false,
        ),
        'wp-cli/wp-config-transformer' => array(
            'pretty_version' => 'v1.3.5',
            'version' => '1.3.5.0',
            'type' => 'library',
            'install_path' => __DIR__ . '/../wp-cli/wp-config-transformer',
            'aliases' => array(),
            'reference' => '202aa80528939159d52bc4026cee5453aec382db',
            'dev_requirement' => false,
        ),
    ),
);
