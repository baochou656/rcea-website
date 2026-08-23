<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/cms/bootstrap.php';
if (current_user() !== null) { redirect('/admin/'); }
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf();
    if (attempt_login((string)($_POST['username'] ?? ''), (string)($_POST['password'] ?? ''))) {
        redirect('/admin/');
    }
    $error = '账号或密码错误，或账号已被锁定。';
}
?><!doctype html><html lang="zh-CN"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="robots" content="noindex,nofollow"><title>管理员登录 · RCECA</title><link rel="icon" href="/img/favicon.png"><link rel="stylesheet" href="/admin/assets/admin.css?v=1"></head><body class="login-page"><section class="login-visual"><div class="login-copy"><img src="/img/logo-mark.png" alt="RCECA"><h1>俄中电子商务协会</h1><p>Russia-China E-Commerce Association<br>安全管理协会资料、会员信息与发布状态。</p></div></section><section class="login-panel"><form class="login-box" method="post" autocomplete="on"><?= csrf_input() ?><h2>管理员登录</h2><p>请使用已授权的管理员账号</p><?php if ($error): ?><div class="flash error"><?= e($error) ?></div><?php endif; ?><div class="field"><label for="username">管理员账号</label><input id="username" name="username" required maxlength="64" autocomplete="username" autofocus></div><div class="field"><label for="password">密码</label><input id="password" type="password" name="password" required autocomplete="current-password"></div><button class="btn primary" type="submit">安全登录</button><div class="login-security">连续 5 次登录失败将锁定 15 分钟。登录后 30 分钟无操作会自动退出。</div></form></section></body></html>
