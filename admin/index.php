<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/cms/layout.php';
$user = require_login();
$stats = [
    'materials' => (int)db()->query("SELECT COUNT(*) FROM materials WHERE deleted_at IS NULL")->fetchColumn(),
    'published' => (int)db()->query("SELECT COUNT(*) FROM materials WHERE deleted_at IS NULL AND status='published'")->fetchColumn(),
    'members' => (int)db()->query("SELECT COUNT(*) FROM members WHERE deleted_at IS NULL")->fetchColumn(),
    'trash' => (int)db()->query("SELECT (SELECT COUNT(*) FROM materials WHERE deleted_at IS NOT NULL)+(SELECT COUNT(*) FROM members WHERE deleted_at IS NOT NULL)")->fetchColumn(),
];
$recent = db()->query("SELECT action, entity_type, created_at FROM audit_logs ORDER BY id DESC LIMIT 8")->fetchAll();
admin_header('仪表盘', $user); ?>
<div class="stats"><div class="stat-card"><span>全部资料</span><strong><?= $stats['materials'] ?></strong></div><div class="stat-card"><span>已发布资料</span><strong><?= $stats['published'] ?></strong></div><div class="stat-card"><span>会员档案</span><strong><?= $stats['members'] ?></strong></div><div class="stat-card"><span>回收站</span><strong><?= $stats['trash'] ?></strong></div></div>
<section class="panel"><div class="panel-head"><h2>快速入口</h2></div><div class="help-grid"><a class="help-card" href="/admin/materials.php?action=new"><strong>上传网站资料</strong><p>上传 PDF、Office 文档或图片，选择草稿或公开发布。</p></a><a class="help-card" href="/admin/members.php?action=new"><strong>新增会员档案</strong><p>维护中文、俄文、英文三语会员资料和照片。</p></a><a class="help-card" href="/admin/trash.php"><strong>恢复误删内容</strong><p>删除默认是可恢复的软删除，不会立即清除文件。</p></a></div></section>
<section class="panel"><div class="panel-head"><h2>最近操作</h2><?php if ($user['role']===CMS_ROLE_SUPER): ?><a class="btn small" href="/admin/audit.php">全部日志</a><?php endif; ?></div><div class="table-wrap"><table><thead><tr><th>操作</th><th>对象</th><th>UTC 时间</th></tr></thead><tbody><?php foreach($recent as $row): ?><tr><td><?= e($row['action']) ?></td><td><?= e($row['entity_type']) ?></td><td><?= e($row['created_at']) ?></td></tr><?php endforeach; ?><?php if(!$recent): ?><tr><td class="empty" colspan="3">暂无操作记录</td></tr><?php endif; ?></tbody></table></div></section>
<?php admin_footer();
