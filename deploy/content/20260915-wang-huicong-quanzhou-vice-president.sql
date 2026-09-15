BEGIN IMMEDIATE;

UPDATE members
SET
  role_zh = '泉州分会副会长',
  role_ru = 'Заместитель председателя отделения Ассоциации в Цюаньчжоу',
  role_en = 'Vice President, Quanzhou Branch',
  summary_zh = '王惠聪，福建泉州晋兴电子商务有限公司总经理、俄中电子商务协会泉州分会副会长。公司成立于2025年4月24日，专注俄罗斯 Wildberries（WB）跨境电商，依托泉州鞋服产业带优势，建设“中国供应链+俄罗斯本地仓配”的跨境零售体系。',
  summary_ru = 'Ван Хуэйцун — генеральный директор ООО «Фуцзянь Цюаньчжоу Цзиньсин Электронная коммерция» и заместитель председателя отделения Ассоциации в Цюаньчжоу. Компания основана 24 апреля 2025 года, специализируется на трансграничной торговле на Wildberries (WB) и развивает модель «китайская цепочка поставок + локальные склады и доставка в России».',
  summary_en = 'Wang Huicong is General Manager of Fujian Quanzhou Jinxing E-commerce Co., Ltd. and Vice President of the RCECA Quanzhou Branch. Founded on April 24, 2025, the company specializes in Russia-facing cross-border e-commerce on Wildberries (WB) and is building a retail model combining Chinese supply chains with local warehousing and fulfillment in Russia.',
  bio_zh = '王惠聪现任福建泉州晋兴电子商务有限公司总经理、俄中电子商务协会泉州分会副会长。

福建泉州晋兴电子商务有限公司成立于2025年4月24日，坐落于中国福建泉州，是一家专注俄罗斯 Wildberries（WB）跨境电商的新锐企业。公司依托泉州“中国鞋服之都”的产业带优势，深耕俄罗斯主流电商市场，聚焦 Wildberries 平台跨境店运营，主营服饰鞋包、家居百货、美妆护肤、电子产品等热销品类，构建“中国供应链+俄罗斯本地仓配”的高效跨境零售体系。',
  bio_ru = 'Ван Хуэйцун — генеральный директор ООО «Фуцзянь Цюаньчжоу Цзиньсин Электронная коммерция» и заместитель председателя отделения Российско-Китайской ассоциации электронной коммерции в Цюаньчжоу.

Компания основана 24 апреля 2025 года и находится в городе Цюаньчжоу провинции Фуцзянь, Китай. Это новая компания, специализирующаяся на трансграничной электронной коммерции на российской платформе Wildberries (WB). Опираясь на преимущества производственного кластера Цюаньчжоу, известного как «китайская столица обуви и одежды», компания углубляет работу на ведущих российских маркетплейсах и сосредоточена на управлении трансграничными магазинами Wildberries. Основные категории включают одежду, обувь и сумки, товары для дома и повседневного использования, косметику и средства ухода, а также электронику. Компания формирует эффективную модель трансграничной розничной торговли, объединяющую китайскую цепочку поставок с локальными складами и доставкой в России.',
  bio_en = 'Wang Huicong is General Manager of Fujian Quanzhou Jinxing E-commerce Co., Ltd. and Vice President of the Russia-China E-Commerce Association’s Quanzhou Branch.

Founded on April 24, 2025 and based in Quanzhou, Fujian, China, Fujian Quanzhou Jinxing E-commerce Co., Ltd. is an emerging enterprise focused on Russia-facing cross-border e-commerce through Wildberries (WB). Leveraging Quanzhou’s manufacturing-cluster advantages as China’s renowned footwear and apparel hub, the company serves Russia’s mainstream e-commerce market with a focus on operating cross-border Wildberries stores. Its principal categories include apparel, footwear and bags, home and general merchandise, beauty and skincare products, and electronics. The company is building an efficient cross-border retail system that combines Chinese supply chains with local warehousing and fulfillment in Russia.',
  photo_path = '/uploads/members/wang-huicong-logo-fcd0ae308ed4e0bd.jpg',
  updated_at = strftime('%Y-%m-%d %H:%M:%S', 'now')
WHERE slug = 'wang-huicong'
  AND member_type = 'person'
  AND section = 'council'
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
  '{"fields":["role","summary","bio","photo"],"source":"user-approved profile update"}',
  '0000000000000000000000000000000000000000000000000000000000000000',
  strftime('%Y-%m-%d %H:%M:%S', 'now')
);

COMMIT;
