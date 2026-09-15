BEGIN IMMEDIATE;

INSERT INTO members (
  slug,
  member_type,
  section,
  name_zh,
  name_ru,
  name_en,
  role_zh,
  role_ru,
  role_en,
  organization_zh,
  organization_ru,
  organization_en,
  summary_zh,
  summary_ru,
  summary_en,
  bio_zh,
  bio_ru,
  bio_en,
  photo_path,
  status,
  is_demo,
  sort_order,
  created_at,
  updated_at
) VALUES (
  'shenzhen-cedpa',
  'organization',
  'council',
  '深圳市跨境电子商务行业发展促进会',
  'Шэньчжэньская ассоциация содействия развитию индустрии трансграничной электронной коммерции',
  'Shenzhen Cross-border E-Commerce Industry Development Promotion Association',
  '特邀理事单位',
  'Специально приглашённая организация — член совета',
  'Specially Invited Council Member Organization',
  '',
  '',
  '',
  '深圳市跨境电子商务行业发展促进会成立于2016年，由深圳海关、税务局、外管局、商务局等十余个政府部门和金融机构联合发起，是覆盖60万跨境从业者、联动产业成交规模超4万亿元的非营利性行业组织。',
  'Шэньчжэньская ассоциация содействия развитию индустрии трансграничной электронной коммерции — некоммерческая отраслевая организация, созданная в 2016 году по совместной инициативе более десяти государственных ведомств и финансовых учреждений. Ассоциация охватывает 600 тыс. специалистов трансграничной торговли и объединяет отраслевую экосистему с совокупным объёмом сделок свыше 4 трлн.',
  'The Shenzhen Cross-border E-Commerce Industry Development Promotion Association is a nonprofit industry organization founded in 2016 through a joint initiative by more than ten government departments and financial institutions. It serves 600,000 cross-border commerce professionals and connects an industry ecosystem with transaction volume exceeding 4 trillion.',
  '深圳市跨境电子商务行业发展促进会成立于2016年，由深圳海关、税务局、外管局、商务局等十余个政府部门和金融机构联合发起，是覆盖60万跨境从业者、联动产业成交规模超4万亿元的非营利性行业组织。

促进会旨在鼓励企业探索创新，建设综合服务体系，规范跨境电子商务经营行为，发挥行业组织作用，营造更加便利的发展环境，促进跨境电子商务健康快速发展。',
  'Шэньчжэньская ассоциация содействия развитию индустрии трансграничной электронной коммерции — некоммерческая отраслевая организация, созданная в 2016 году по совместной инициативе более десяти государственных ведомств и финансовых учреждений, включая Шэньчжэньскую таможню, налоговые органы, органы валютного регулирования и управление торговли. Ассоциация охватывает 600 тыс. специалистов трансграничной торговли и объединяет отраслевую экосистему с совокупным объёмом сделок свыше 4 трлн.

Ассоциация поощряет предприятия к поиску инновационных решений, развивает комплексную систему обслуживания, содействует упорядочению деловой практики в сфере трансграничной электронной коммерции и выполняет координирующую роль отраслевой организации. Её работа направлена на формирование более благоприятной среды и здоровое, быстрое развитие трансграничной электронной коммерции.',
  'Founded in 2016, the Shenzhen Cross-border E-Commerce Industry Development Promotion Association is a nonprofit industry organization jointly initiated by more than ten government departments and financial institutions, including Shenzhen Customs, tax authorities, the foreign-exchange authority and the commerce bureau. The association serves 600,000 cross-border commerce professionals and connects an industry ecosystem with transaction volume exceeding 4 trillion.

The association encourages enterprises to explore innovation, develops an integrated service system, promotes standardized cross-border e-commerce practices and fulfills the coordinating role of an industry organization. Its work aims to create a more enabling business environment and advance the healthy, rapid development of cross-border e-commerce.',
  '/uploads/members/shenzhen-cedpa-logo-d14647684f5f813a.png',
  'published',
  0,
  11,
  strftime('%Y-%m-%d %H:%M:%S', 'now'),
  strftime('%Y-%m-%d %H:%M:%S', 'now')
);

CREATE TEMP TABLE _member_create_guard (
  inserted_rows INTEGER NOT NULL CHECK (inserted_rows = 1)
);

INSERT INTO _member_create_guard (inserted_rows) VALUES (changes());
DROP TABLE _member_create_guard;

INSERT INTO audit_logs (
  user_id,
  action,
  entity_type,
  entity_id,
  details_json,
  ip_hash,
  created_at
) VALUES (
  NULL,
  'member_create_cli',
  'member',
  (SELECT id FROM members WHERE slug = 'shenzhen-cedpa'),
  '{"status":"published","source":"user-approved profile material"}',
  '0000000000000000000000000000000000000000000000000000000000000000',
  strftime('%Y-%m-%d %H:%M:%S', 'now')
);

COMMIT;
