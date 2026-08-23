<?php
declare(strict_types=1);
require_once __DIR__ . '/bootstrap.php';

function admin_header(string $title, array $user): void
{
    $page = basename($_SERVER['SCRIPT_NAME'] ?? '');
    $flashes = take_flashes();
    ?><!doctype html>
<html lang="zh-CN"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="robots" content="noindex,nofollow"><title><?= e($title) ?> · RCECA 管理后台</title>
<link rel="icon" href="/img/favicon.png"><link rel="stylesheet" href="/admin/assets/admin.css?v=1"></head>
<body><aside class="sidebar"><a class="admin-brand" href="/admin/"><img src="/img/logo-mark.png" alt="RCECA"><span>RCECA<small>网站管理后台</small></span></a>
<nav><a class="<?= $page === 'index.php' ? 'active' : '' ?>" href="/admin/">▦ 仪表盘</a><a class="<?= $page === 'materials.php' ? 'active' : '' ?>" href="/admin/materials.php">▣ 网站资料</a><a class="<?= $page === 'members.php' ? 'active' : '' ?>" href="/admin/members.php">◎ 会员资料</a><a class="<?= $page === 'trash.php' ? 'active' : '' ?>" href="/admin/trash.php">◌ 回收站</a>
<?php if ($user['role'] === CMS_ROLE_SUPER): ?><div class="nav-label">超级管理</div><a class="<?= $page === 'users.php' ? 'active' : '' ?>" href="/admin/users.php">◈ 管理员</a><a class="<?= $page === 'audit.php' ? 'active' : '' ?>" href="/admin/audit.php">☷ 审计日志</a><?php endif; ?>
</nav><div class="sidebar-user"><strong><?= e($user['display_name']) ?></strong><span><?= $user['role'] === CMS_ROLE_SUPER ? '超级管理员' : '普通管理员' ?></span><a href="/admin/profile.php">修改密码</a><a href="/admin/logout.php">退出登录</a></div></aside>
<main class="admin-main"><header class="topbar"><button class="menu-toggle" type="button" aria-label="打开菜单">☰</button><div><h1><?= e($title) ?></h1><p><?= e(gmdate('Y-m-d H:i')) ?> UTC</p></div><a class="site-link" href="/" target="_blank" rel="noopener">查看官网 ↗</a></header><div class="content">
<?php foreach ($flashes as $item): ?><div class="flash <?= e($item['type']) ?>"><?= e($item['message']) ?></div><?php endforeach; ?>
<?php
}

function admin_footer(): void
{
    ?></div></main><script src="/admin/assets/admin.js?v=1"></script></body></html><?php
}

function status_badge(string $status): string
{
    $label = $status === 'published' ? '已发布' : ($status === 'active' ? '启用' : ($status === 'disabled' ? '停用' : '草稿'));
    return '<span class="badge ' . e($status) . '">' . $label . '</span>';
}
