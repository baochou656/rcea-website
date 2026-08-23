<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/cms/layout.php';
$user=require_super();
if($_SERVER['REQUEST_METHOD']==='POST'){
    require_csrf();$op=(string)($_POST['operation']??'');$target=(int)($_POST['id']??0);
    if($op==='create'){
        $username=trim((string)($_POST['username']??''));$display=trim((string)($_POST['display_name']??''));$password=(string)($_POST['password']??'');$role=($_POST['role']??'')===CMS_ROLE_SUPER?CMS_ROLE_SUPER:CMS_ROLE_ADMIN;
        if(!preg_match('/^[A-Za-z][A-Za-z0-9._-]{3,31}$/',$username)){flash('error','账号需为 4-32 位英文、数字、点、下划线或连字符。');redirect('/admin/users.php');}
        if($display===''||!valid_password($password)){flash('error','请填写显示名称，密码至少 12 位且包含字母和数字。');redirect('/admin/users.php');}
        try{$s=db()->prepare('INSERT INTO users(username,display_name,password_hash,role,status,must_change_password,created_by,created_at,updated_at) VALUES(?,?,?,?,?,?,?,?,?)');$s->execute([$username,$display,password_hash($password,PASSWORD_DEFAULT),$role,'active',1,$user['id'],now_utc(),now_utc()]);$target=(int)db()->lastInsertId();audit('user_create','user',$target,['role'=>$role]);flash('success','管理员已创建，首次登录必须修改密码。');}catch(PDOException $e){flash('error','该管理员账号已存在。');}redirect('/admin/users.php');
    }
    if($op==='toggle'){
        if($target===(int)$user['id']){flash('error','不能停用当前登录账号。');redirect('/admin/users.php');}
        $s=db()->prepare('SELECT role,status FROM users WHERE id=?');$s->execute([$target]);$row=$s->fetch();if(!$row){redirect('/admin/users.php');}$next=$row['status']==='active'?'disabled':'active';
        if($row['role']===CMS_ROLE_SUPER&&$next==='disabled'&&(int)db()->query("SELECT COUNT(*) FROM users WHERE role='super_admin' AND status='active'")->fetchColumn()<=1){flash('error','必须保留至少一个启用状态的超级管理员。');redirect('/admin/users.php');}
        db()->prepare('UPDATE users SET status=?,updated_at=? WHERE id=?')->execute([$next,now_utc(),$target]);audit('user_status','user',$target,['status'=>$next]);flash('success','账号状态已更新。');redirect('/admin/users.php');
    }
    if($op==='reset'){
        $password=(string)($_POST['password']??'');if(!valid_password($password)){flash('error','新密码至少 12 位且包含字母和数字。');redirect('/admin/users.php');}
        db()->prepare('UPDATE users SET password_hash=?,must_change_password=1,failed_attempts=0,locked_until=NULL,updated_at=? WHERE id=?')->execute([password_hash($password,PASSWORD_DEFAULT),now_utc(),$target]);audit('user_password_reset','user',$target);flash('success','临时密码已重置。');redirect('/admin/users.php');
    }
}
$rows=db()->query('SELECT id,username,display_name,role,status,must_change_password,last_login_at,created_at FROM users ORDER BY role DESC,id')->fetchAll();admin_header('管理员账号',$user);?>
<section class="panel"><div class="panel-head"><h2>新建管理员</h2></div><form method="post"><?=csrf_input()?><input type="hidden" name="operation" value="create"><div class="form-grid"><div class="field"><label>登录账号 *</label><input name="username" required pattern="[A-Za-z][A-Za-z0-9._-]{3,31}" autocomplete="off"></div><div class="field"><label>显示名称 *</label><input name="display_name" required maxlength="80"></div><div class="field"><label>账号类型</label><select name="role"><option value="admin">普通管理员（内容管理）</option><option value="super_admin">超级管理员（全部权限）</option></select></div><div class="field"><label>临时密码 *</label><input type="password" name="password" required minlength="12" autocomplete="new-password"><small>至少 12 位，包含字母和数字；首次登录强制更换。</small></div></div><div class="form-actions"><button class="btn primary" type="submit">创建账号</button></div></form></section>
<section class="panel"><div class="panel-head"><h2>账号列表</h2></div><div class="table-wrap"><table><thead><tr><th>账号</th><th>姓名</th><th>级别</th><th>状态</th><th>最后登录</th><th>操作</th></tr></thead><tbody><?php foreach($rows as $row):?><tr><td><strong><?=e($row['username'])?></strong></td><td><?=e($row['display_name'])?></td><td><?=$row['role']===CMS_ROLE_SUPER?'超级管理员':'普通管理员'?></td><td><?=status_badge($row['status'])?> <?=(int)$row['must_change_password']?'<span class="badge">待改密码</span>':''?></td><td><?=e($row['last_login_at']?:'尚未登录')?></td><td><div class="actions"><?php if((int)$row['id']!==(int)$user['id']):?><form method="post" class="inline-form"><?=csrf_input()?><input type="hidden" name="operation" value="toggle"><input type="hidden" name="id" value="<?=(int)$row['id']?>"><button class="btn small" data-confirm="确认更改该账号的启停状态？"><?=$row['status']==='active'?'停用':'启用'?></button></form><?php endif;?><details><summary class="btn small">重置密码</summary><form method="post" style="margin-top:8px;width:230px"><?=csrf_input()?><input type="hidden" name="operation" value="reset"><input type="hidden" name="id" value="<?=(int)$row['id']?>"><div class="field"><input type="password" name="password" minlength="12" required placeholder="新临时密码"><button class="btn small primary" type="submit">确认重置</button></div></form></details></div></td></tr><?php endforeach;?></tbody></table></div></section><?php admin_footer();
