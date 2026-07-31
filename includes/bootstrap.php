<?php

declare(strict_types=1);

require_once __DIR__ . '/auth.php';
require_once dirname(__DIR__) . '/config/database.php';

$pageTitle = $pageTitle ?? 'Portfolio';
$bodyClass = $bodyClass ?? '';
$currentPage = $currentPage ?? '';
