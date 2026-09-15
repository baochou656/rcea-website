BEGIN IMMEDIATE;

UPDATE members
SET
  member_type = 'organization',
  name_zh = '福建泉州晋兴电子商务有限公司',
  name_ru = 'ООО «Фуцзянь Цюаньчжоу Цзиньсин Электронная коммерция»',
  name_en = 'Fujian Quanzhou Jinxing E-commerce Co., Ltd.',
  organization_zh = '负责人：王惠聪｜总经理',
  organization_ru = 'Руководитель: Ван Хуэйцун | генеральный директор',
  organization_en = 'Principal: Wang Huicong | General Manager',
  updated_at = strftime('%Y-%m-%d %H:%M:%S', 'now')
WHERE slug = 'wang-huicong'
  AND member_type = 'person'
  AND section = 'council'
  AND status = 'published'
  AND deleted_at IS NULL;

CREATE TEMP TABLE _member_update_guard (
  updated_rows INTEGER NOT NULL CHECK (updated_rows = 1)
);

INSERT INTO _member_update_guard (updated_rows) VALUES (changes());
DROP TABLE _member_update_guard;

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
  'member_update_cli',
  'member',
  (SELECT id FROM members WHERE slug = 'wang-huicong'),
  '{"fields":["member_type","name","organization"],"source":"user-approved enterprise council conversion"}',
  '0000000000000000000000000000000000000000000000000000000000000000',
  strftime('%Y-%m-%d %H:%M:%S', 'now')
);

COMMIT;
