<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/cms/bootstrap.php';
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
$command=$argv[1]??'help';
if($command==='init'){cms_schema();echo "schema-ready\n";exit;}
if($command==='create-user'){
    $username=$argv[2]??'';$display=$argv[3]??'';$role=($argv[4]??'')===CMS_ROLE_ADMIN?CMS_ROLE_ADMIN:CMS_ROLE_SUPER;$password=getenv('RCECA_BOOTSTRAP_PASSWORD')?:'';
    if(!preg_match('/^[A-Za-z][A-Za-z0-9._-]{3,31}$/',$username)||$display===''||!valid_password($password)){fwrite(STDERR,"invalid-user-input\n");exit(2);}
    $existing=db()->prepare('SELECT id FROM users WHERE username=? COLLATE NOCASE');$existing->execute([$username]);if($existing->fetchColumn()){fwrite(STDERR,"user-exists\n");exit(3);}
    db()->prepare('INSERT INTO users(username,display_name,password_hash,role,status,must_change_password,created_at,updated_at) VALUES(?,?,?,?,?,?,?,?)')->execute([$username,$display,password_hash($password,PASSWORD_DEFAULT),$role,'active',1,now_utc(),now_utc()]);
    echo "user-created\n";exit;
}
if($command==='seed-demo'){
    $items=[
      ['alexei-volkov','council','阿列克谢·沃尔科夫','Алексей Волков','Alexei Volkov','会长','Президент ассоциации','President','示例方向：协会战略、中俄产业协同与俄罗斯本土资源建设。','Demo: strategy, industry cooperation and local network development in Russia.','Demo focus: Association strategy, industry cooperation and local network development.','alexei-volkov.webp'],
      ['chen-wei','council','陈伟','Чэнь Вэй','Chen Wei','执行会长','Исполнительный президент','Executive President','示例方向：协会运营统筹、中国企业出海与跨境电商生态合作。','Demo: operations, Chinese market entry and cross-border e-commerce ecosystems.','Demo focus: operations, Chinese market entry and cross-border e-commerce.','chen-wei.webp'],
      ['natalia-sokolova','council','娜塔莉娅·索科洛娃','Наталья Соколова','Natalia Sokolova','副会长','Вице-президент','Vice President','示例方向：俄罗斯渠道合作、品牌本地化与公共关系。','Demo: Russian channels, brand localization and public affairs.','Demo focus: Russian channels, brand localization and public affairs.','natalia-sokolova.webp'],
      ['wang-haoran','council','王浩然','Ван Хаожань','Wang Haoran','常务理事','Член президиума','Executive Council Member','示例方向：跨境供应链、海外仓网络与平台履约体系。','Demo: cross-border supply chains, overseas warehousing and fulfillment.','Demo focus: cross-border supply chains and fulfillment.','wang-haoran.webp'],
      ['irina-petrova','council','伊琳娜·彼得罗娃','Ирина Петрова','Irina Petrova','理事','Член совета','Council Member','示例方向：EAC 合规、俄罗斯公司法务与跨境交易风险。','Demo: EAC compliance, corporate law and transaction risk.','Demo focus: EAC compliance, corporate law and transaction risk.','irina-petrova.webp'],
      ['li-na','secretariat','李娜','Ли На','Li Na','秘书长','Генеральный секретарь','Secretary-General','示例方向：秘书处统筹、年度计划、会员项目与跨部门协作。','Demo: Secretariat coordination, annual planning and member programs.','Demo focus: Secretariat coordination and member programs.','li-na.webp'],
      ['mikhail-orlov','secretariat','米哈伊尔·奥尔洛夫','Михаил Орлов','Mikhail Orlov','副秘书长','Заместитель генерального секретаря','Deputy Secretary-General','示例方向：俄方事务协调、活动执行与合作伙伴联络。','Demo: Russia-side coordination, events and partner relations.','Demo focus: Russia-side coordination and events.','mikhail-orlov.webp'],
      ['zhao-qing','secretariat','赵晴','Чжао Цин','Zhao Qing','会员部主管','Руководитель отдела по работе с членами','Head of Membership','示例方向：入会服务、会员需求分层、权益交付和社群运营。','Demo: onboarding, benefits delivery and community operations.','Demo focus: onboarding, benefits and community operations.','zhao-qing.webp'],
      ['north-star-digital','member','中俄北辰数字贸易有限公司','Компания «Северная звезда Диджитал»','North Star Digital Trade Co.','企业会员','Корпоративный член','Corporate Member','跨境电商本地化运营示例单位。','Demo organization for e-commerce localization.','Demo member for e-commerce localization.',''],
      ['volga-home','member','伏尔加家居供应链联盟','Альянс поставок «Волга Дом»','Volga Home Supply Alliance','机构会员','Институциональный член','Institutional Member','家居制造与区域分销协作示例机构。','Demo home manufacturing and distribution alliance.','Demo home manufacturing and distribution alliance.',''],
      ['silkroad-compliance','member','丝路欧亚合规服务中心','Центр комплаенса «Шёлковый путь — Евразия»','Silk Road Eurasia Compliance Center','企业会员','Корпоративный член','Corporate Member','EAC 认证与市场准入支持示例单位。','Demo EAC certification and market-access organization.','Demo EAC certification and market-access organization.','']
    ];
    $stmt=db()->prepare('INSERT OR IGNORE INTO members(slug,member_type,section,name_zh,name_ru,name_en,role_zh,role_ru,role_en,summary_zh,summary_ru,summary_en,bio_zh,bio_ru,bio_en,photo_path,status,is_demo,sort_order,created_at,updated_at) VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)');$order=10;
    foreach($items as $i){$type=$i[1]==='member'?'organization':'person';$photo=$i[11]!==''?'/img/members/'.$i[11]:'';$stmt->execute([$i[0],$type,$i[1],$i[2],$i[3],$i[4],$i[5],$i[6],$i[7],$i[8],$i[9],$i[10],$i[8],$i[9],$i[10],$photo,'published',1,$order,now_utc(),now_utc()]);$order+=10;}
    echo "demo-seed-ready\n";exit;
}
fwrite(STDERR,"usage: init | create-user <username> <display> <super_admin|admin> | seed-demo\n");exit(1);
