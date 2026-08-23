<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/cms/bootstrap.php';
if (current_user() !== null) { audit('logout', 'session'); }
session_unset(); session_destroy(); redirect('/admin/login.php');
