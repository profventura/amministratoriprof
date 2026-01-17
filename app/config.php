<?php
return [
  'db' => [
    'host' => '127.0.0.1',
    'port' => 3306,
    'name' => 'am_professionisti',
    'charset' => 'utf8mb4',
    'user' => 'root',
    'pass' => '',
  ],
  'app' => [
    'base_url' => '/amministratoriprof/',
    'server_port' => 8080,
    'upload_dir' => __DIR__ . '/../public/uploads',
  ],
  'defaults' => [
    'laptop' => [
      'status' => 'in_progress',
      'condition_level' => 'good',
      'physical_condition' => 'good',
      'battery' => 'good',
      'brand_model' => 'Lenovo Thinkpad L390',
      'cpu' => 'Intel i5',
      'ram' => '8GB',
      'storage' => '256GB SSD',
      'screen' => '13.3" FHD'
    ],
  ],
];
