<?php /** @var string $titulo */ ?>
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover">
<meta name="theme-color" content="#1846c4">
<meta name="csrf-header" content="<?= esc(config('Security')->headerName) ?>">
<meta name="csrf-token" content="<?= csrf_hash() ?>">
<title><?= esc($titulo) ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@600;700&family=Barlow:wght@400;500;600&display=swap">
<link rel="stylesheet" href="<?= base_url('assets/app.css') ?>?v=3">
</head>
