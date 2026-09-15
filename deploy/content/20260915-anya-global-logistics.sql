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
  'anya-global-logistics',
  'organization',
  'council',
  '深圳市安亚供应链（ANYA Global Logistics）',
  'ANYA Global Logistics / «Шэньчжэнь АньЯ Саплай Чейн»',
  'ANYA Global Logistics (Shenzhen Anya Supply Chain)',
  '特邀理事单位',
  'Специально приглашённая организация — член совета',
  'Specially Invited Council Member Organization',
  '',
  '',
  '',
  'ANYA Global Logistics（深圳市安亚供应链）提供全球物流及定制化解决方案，整合空运、海运、铁路和汽运等多式联运渠道，业务覆盖东南亚、中东、非洲、南美、北美、俄罗斯及欧洲等市场，为客户提供具有竞争力的国际物流专线方案。',
  'ANYA Global Logistics предоставляет глобальные логистические услуги и индивидуальные решения, объединяя авиационные, морские, железнодорожные и автомобильные перевозки. Компания развивает специализированные маршруты в Юго-Восточную Азию, на Ближний Восток, в Африку, Южную и Северную Америку, Россию и Европу и предлагает клиентам конкурентоспособные международные логистические решения.',
  'ANYA Global Logistics (Shenzhen Anya Supply Chain) provides global logistics services and customized solutions across air, ocean, rail and road transportation. Its dedicated-route network covers Southeast Asia, the Middle East, Africa, South and North America, Russia and Europe, delivering competitive international logistics solutions for customers.',
  'ANYA Global Logistics 深圳市安亚供应链专注全球物流服务，根据客户的货物类型、时效要求与目的市场提供定制化解决方案。公司整合航空、海运、铁路与公路运输资源，形成多式联运和全球专线优势，持续深耕东南亚、中东、非洲、南美、北美、俄罗斯及欧洲等区域。依托多区域渠道布局和方案整合能力，为客户匹配稳定、高效且具有成本竞争力的国际物流专线，支持企业跨境贸易与海外市场拓展。',
  'ANYA Global Logistics специализируется на глобальных логистических услугах и разрабатывает индивидуальные решения с учётом типа груза, требований к срокам и рынка назначения. Компания объединяет ресурсы авиационных, морских, железнодорожных и автомобильных перевозок, формируя преимущества мультимодальных и специализированных международных маршрутов. География работы охватывает Юго-Восточную Азию, Ближний Восток, Африку, Южную и Северную Америку, Россию и Европу. Благодаря развитой сети каналов и способности интегрировать логистические решения компания предлагает стабильные, эффективные и конкурентоспособные по стоимости маршруты, поддерживая трансграничную торговлю и международное развитие клиентов.',
  'ANYA Global Logistics specializes in global logistics services and develops customized solutions based on cargo type, delivery requirements and destination market. The company integrates air, ocean, rail and road resources to provide multimodal transportation and dedicated international routes. Its network covers Southeast Asia, the Middle East, Africa, South and North America, Russia and Europe. Through broad route coverage and solution integration, the company matches customers with stable, efficient and cost-competitive logistics services that support cross-border trade and international market expansion.',
  '/uploads/members/anya-global-logistics-ae10673bd59e2360.png',
  'published',
  0,
  10,
  strftime('%Y-%m-%d %H:%M:%S', 'now'),
  strftime('%Y-%m-%d %H:%M:%S', 'now')
);

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
  (SELECT id FROM members WHERE slug = 'anya-global-logistics'),
  '{"status":"published","source":"user-approved profile material"}',
  '0000000000000000000000000000000000000000000000000000000000000000',
  strftime('%Y-%m-%d %H:%M:%S', 'now')
);

COMMIT;
