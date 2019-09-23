<?php

return array
(
    'default' => array(
        'type'       => 'pdo',
        'connection' => array(
            'dsn'        => 'mysql:host=localhost;dbname=seor_db',
            'username'   => 'root',
            'password'   => '852456a',
            'persistent' => FALSE,
        ),
        'table_prefix' => '',
        'charset'      => 'utf8',
        'caching'      => FALSE,
        'profiling'    => TRUE,
        'prefix' => '',
    ),
);