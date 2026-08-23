<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/cms/layout.php';
$user = require_login();
$action = (string)($_GET['action'] ?? 'list');
$id = (int)($_GET['id'] ?? 0);

function material_upload(array $file): array
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        throw new RuntimeException('请选择需要上传的文件。');
    }
    if ((int)$file['size'] < 1 || (int)$file['size'] > CMS_MAX_UPLOAD) {
        throw new RuntimeException('文件大小必须在 20MB 以内。');
    }
    $original = basename((string)$file['name']);
    $extension = strtolower(pathinfo($original, PATHINFO_EXTENSION));
    $extensions = ['pdf','doc','docx','xls','xlsx','ppt','pptx','png','jpg','jpeg','webp'];
    if (!in_array($extension, $extensions, true)) {
        throw new RuntimeException('不支持该文件类型。');
    }
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file((string)$file['tmp_name']) ?: 'application/octet-stream';
    $allowedMimes = ['application/pdf','application/msword','application/vnd.ms-excel','application/vnd.ms-powerpoint','application/vnd.openxmlformats-officedocument.wordprocessingml.document','application/vnd.openxmlformats-officedocument.spreadsheetml.sheet','application/vnd.openxmlformats-officedocument.presentationml.presentation','application/zip','image/png','image/jpeg','image/webp'];
    if (!in_array($mime, $allowedMimes, true)) {
        throw new RuntimeException('文件内容与允许的类型不符。');
    }
    if ($mime === 'application/zip' && !in_array($extension, ['docx','xlsx','pptx'], true)) {
        throw new RuntimeException('压缩文件不允许上传。');
    }
    $directory = cms_upload_root() . '/materials';
    if (!is_dir($directory) && !mkdir($directory, 0750, true) && !is_dir($directory)) {
        throw new RuntimeException('无法创建上传目录。');
    }
    $storage = bin2hex(random_bytes(16)) . '.' . $extension;
    if (!move_uploaded_file((string)$file['tmp_name'], $directory . '/' . $storage)) {
        throw new RuntimeException('文件保存失败。');
    }
    chmod($directory . '/' . $storage, 0640);
    return ['original' => mb_substr($original, 0, 240), 'storage' => $storage, 'mime' => $mime, 'size' => (int)$file['size']];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf();
    $operation = (string)($_POST['operation'] ?? '');
    if ($operation === 'delete') {
        $target = (int)($_POST['id'] ?? 0);
        $stmt = db()->prepare('UPDATE materials SET deleted_at=?, deleted_by=?, updated_at=? WHERE id=? AND deleted_at IS NULL');
        $stmt->execute([now_utc(), $user['id'], now_utc(), $target]);
        audit('material_delete', 'material', $target);
        flash('success', '资料已移入回收站。');
        redirect('/admin/materials.php');
    }
    if ($operation === 'save') {
        $target = (int)($_POST['id'] ?? 0);
        $titles = [trim((string)($_POST['title_zh'] ?? '')), trim((string)($_POST['title_ru'] ?? '')), trim((string)($_POST['title_en'] ?? ''))];
        $status = ($_POST['status'] ?? '') === 'published' ? 'published' : 'draft';
        $error = '';
        if ($titles[0] === '') { $error = '中文标题不能为空。'; }
        if ($status === 'published' && ($titles[1] === '' || $titles[2] === '')) { $error = '公开发布前必须填写中、俄、英三语标题。'; }
        try {
            $upload = null;
            if (!empty($_FILES['file']) && ($_FILES['file']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
                $upload = material_upload($_FILES['file']);
            }
            if ($target === 0 && $upload === null) { $error = '新建资料必须上传文件。'; }
            if ($error !== '') { throw new RuntimeException($error); }
            $values = [
                $titles[0],$titles[1],$titles[2],
                trim((string)($_POST['description_zh'] ?? '')),trim((string)($_POST['description_ru'] ?? '')),trim((string)($_POST['description_en'] ?? '')),
                in_array(($_POST['category'] ?? ''), ['document','report','image','form'], true) ? $_POST['category'] : 'document',
                $status,(int)($_POST['sort_order'] ?? 100),$user['id'],now_utc()
            ];
            if ($target > 0) {
                $sql = 'UPDATE materials SET title_zh=?,title_ru=?,title_en=?,description_zh=?,description_ru=?,description_en=?,category=?,status=?,sort_order=?,updated_by=?,updated_at=?';
                if ($upload !== null) { $sql .= ',original_name=?,storage_name=?,mime_type=?,file_size=?'; array_push($values,$upload['original'],$upload['storage'],$upload['mime'],$upload['size']); }
                $sql .= ' WHERE id=? AND deleted_at IS NULL'; $values[] = $target;
                $stmt = db()->prepare($sql); $stmt->execute($values);
                audit('material_update', 'material', $target, ['status'=>$status]);
            } else {
                $stmt = db()->prepare('INSERT INTO materials (title_zh,title_ru,title_en,description_zh,description_ru,description_en,category,status,sort_order,created_by,updated_by,created_at,updated_at,original_name,storage_name,mime_type,file_size) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)');
                $stmt->execute([$titles[0],$titles[1],$titles[2],trim((string)($_POST['description_zh']??'')),trim((string)($_POST['description_ru']??'')),trim((string)($_POST['description_en']??'')),in_array(($_POST['category']??''),['document','report','image','form'],true)?$_POST['category']:'document',$status,(int)($_POST['sort_order']??100),$user['id'],$user['id'],now_utc(),now_utc(),$upload['original'],$upload['storage'],$upload['mime'],$upload['size']]);
                $target = (int)db()->lastInsertId(); audit('material_create','material',$target,['status'=>$status]);
            }
            flash('success', '资料已保存。'); redirect('/admin/materials.php');
        } catch (RuntimeException $exception) {
            flash('error', $exception->getMessage());
            redirect('/admin/materials.php?action=' . ($target ? 'edit&id='.$target : 'new'));
        }
    }
}

if ($action === 'new' || ($action === 'edit' && $id > 0)) {
    $row = ['id'=>0,'title_zh'=>'','title_ru'=>'','title_en'=>'','description_zh'=>'','description_ru'=>'','description_en'=>'','category'=>'document','status'=>'draft','sort_order'=>100,'original_name'=>''];
    if ($id > 0) { $stmt=db()->prepare('SELECT * FROM materials WHERE id=? AND deleted_at IS NULL'); $stmt->execute([$id]); $row=$stmt->fetch() ?: $row; }
    admin_header($id ? '编辑网站资料' : '上传网站资料', $user); ?>
<section class="panel"><form method="post" enctype="multipart/form-data"><?= csrf_input() ?><input type="hidden" name="operation" value="save"><input type="hidden" name="id" value="<?= (int)$row['id'] ?>"><div class="form-grid">
<div class="field"><label>中文标题 *</label><input name="title_zh" value="<?= e($row['title_zh']) ?>" required maxlength="180"></div><div class="field"><label>俄文标题</label><input name="title_ru" value="<?= e($row['title_ru']) ?>" maxlength="180"></div><div class="field full"><label>英文标题</label><input name="title_en" value="<?= e($row['title_en']) ?>" maxlength="180"></div>
<div class="field"><label>中文简介</label><textarea name="description_zh" maxlength="1200"><?= e($row['description_zh']) ?></textarea></div><div class="field"><label>俄文简介</label><textarea name="description_ru" maxlength="1200"><?= e($row['description_ru']) ?></textarea></div><div class="field full"><label>英文简介</label><textarea name="description_en" maxlength="1200"><?= e($row['description_en']) ?></textarea></div>
<div class="field"><label>分类</label><select name="category"><?php foreach(['document'=>'协会文件','report'=>'报告资讯','form'=>'表格下载','image'=>'图片资料'] as $k=>$v): ?><option value="<?= $k ?>" <?= $row['category']===$k?'selected':'' ?>><?= $v ?></option><?php endforeach; ?></select></div><div class="field"><label>发布状态</label><select name="status"><option value="draft" <?= $row['status']==='draft'?'selected':'' ?>>草稿（不对外）</option><option value="published" <?= $row['status']==='published'?'selected':'' ?>>已发布</option></select></div><div class="field"><label>排序值</label><input type="number" name="sort_order" min="0" max="9999" value="<?= (int)$row['sort_order'] ?>"></div>
<div class="field full"><label><?= $id ? '替换文件（可选）' : '选择文件 *' ?></label><input type="file" name="file" <?= $id?'':'required' ?> accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.png,.jpg,.jpeg,.webp"><small>最大 20MB；支持 PDF、Word、Excel、PPT、PNG/JPG/WebP。不允许程序和压缩包。</small><?php if($row['original_name']): ?><div class="file-note">当前文件：<?= e($row['original_name']) ?></div><?php endif; ?></div></div><div class="form-actions"><a class="btn" href="/admin/materials.php">取消</a><button class="btn primary" type="submit">保存资料</button></div></form></section><?php admin_footer(); exit;
}

$rows = db()->query('SELECT * FROM materials WHERE deleted_at IS NULL ORDER BY sort_order,id DESC')->fetchAll();
admin_header('网站资料', $user); ?>
<section class="panel"><div class="panel-head"><div><h2>资料库</h2><small>已发布的资料会在官网资料中心展示</small></div><a class="btn primary" href="?action=new">+上传资料</a></div><div class="table-wrap"><table><thead><tr><th>标题</th><th>分类</th><th>文件</th><th>状态</th><th>更新时间</th><th>操作</th></tr></thead><tbody><?php foreach($rows as $row): ?><tr><td><strong><?= e($row['title_zh']) ?></strong><br><small><?= e($row['title_ru'] ?: $row['title_en']) ?></small></td><td><?= e($row['category']) ?></td><td><?= e($row['original_name']) ?><br><small><?= number_format($row['file_size']/1024,1) ?> KB</small></td><td><?= status_badge($row['status']) ?></td><td><?= e($row['updated_at']) ?></td><td><div class="actions"><a class="btn small" href="?action=edit&id=<?= (int)$row['id'] ?>">编辑</a><form class="inline-form" method="post"><?= csrf_input() ?><input type="hidden" name="operation" value="delete"><input type="hidden" name="id" value="<?= (int)$row['id'] ?>"><button class="btn small danger" data-confirm="确认将该资料移入回收站？" type="submit">删除</button></form></div></td></tr><?php endforeach; ?><?php if(!$rows): ?><tr><td class="empty" colspan="6">暂无资料，请上传第一份。</td></tr><?php endif; ?></tbody></table></div></section><?php admin_footer();
