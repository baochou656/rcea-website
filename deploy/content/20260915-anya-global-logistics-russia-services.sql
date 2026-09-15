BEGIN IMMEDIATE;

UPDATE members
SET
  summary_zh = 'ANYA Global Logistics（深圳市安亚供应链）提供全球物流及定制化解决方案，整合空运、海运、铁路和汽运等多式联运渠道。作为俄罗斯市场一站式跨境全链路物流服务商，公司深度服务 Ozon、Wildberries 等平台中国卖家，提供合规清关、海外仓、FBO/FBS 履约及门到门交付。',
  summary_ru = 'ANYA Global Logistics предоставляет глобальные логистические услуги и индивидуальные мультимодальные решения по авиационным, морским, железнодорожным и автомобильным перевозкам. На российском направлении компания оказывает китайским продавцам Ozon и Wildberries комплексные услуги: официальное таможенное оформление, собственный склад в России, FBO/FBS-фулфилмент и доставку от двери до двери.',
  summary_en = 'ANYA Global Logistics provides global logistics services and customized multimodal solutions across air, ocean, rail and road transportation. For Russia, the company supports Chinese sellers on Ozon and Wildberries with compliant customs clearance, a self-operated Russian warehouse, FBO/FBS fulfillment and factory-to-consumer door-to-door delivery.',
  bio_zh = 'ANYA Global Logistics 深圳市安亚供应链专注全球物流服务，根据客户的货物类型、时效要求与目的市场提供定制化解决方案。公司整合航空、海运、铁路与公路运输资源，形成多式联运和全球专线优势，持续深耕东南亚、中东、非洲、南美、北美、俄罗斯及欧洲等区域，为客户提供稳定、高效且具有成本竞争力的国际物流专线。

在俄罗斯市场，ANYA 是一站式跨境全链路物流服务商，深度服务 Ozon、Wildberries 等俄罗斯主流电商平台的中国卖家。公司提供空运、铁路、海运及重点发展的卡航干线运输，支持第 9 类危险品运输；坚持俄罗斯白色清关和合规通关，并适配 SPOT、EAC、KIZ 标签等新规。公司配备俄罗斯自营海外仓，支持 FBO/FBS 履约及 GPS 全程可视化追踪，同时提供合规咨询、库内操作和俄语售后等增值服务，实现从中国工厂到俄罗斯消费者的门到门交付，助力中国卖家合规拓展俄罗斯市场。',
  bio_ru = 'ANYA Global Logistics специализируется на глобальных логистических услугах и разрабатывает индивидуальные решения с учётом типа груза, сроков и рынка назначения. Компания объединяет авиационные, морские, железнодорожные и автомобильные перевозки, развивая мультимодальные и специализированные маршруты в Юго-Восточную Азию, на Ближний Восток, в Африку, Южную и Северную Америку, Россию и Европу. Клиентам предлагаются стабильные, эффективные и конкурентоспособные по стоимости международные логистические решения.

На российском направлении ANYA выступает как оператор комплексной трансграничной логистики полного цикла и глубоко обслуживает китайских продавцов на ведущих маркетплейсах Ozon и Wildberries. Компания организует авиационные, железнодорожные, морские и магистральные автомобильные перевозки, включая перевозку опасных грузов класса 9. ANYA придерживается официального и соответствующего требованиям таможенного оформления в России, а также учитывает новые требования SPOT, EAC и маркировки KIZ. Собственный склад в России поддерживает модели FBO и FBS, а GPS-трекинг обеспечивает видимость груза на всём маршруте. Дополнительные услуги включают консультации по соответствию требованиям, складские операции и русскоязычное послепродажное обслуживание. Это обеспечивает доставку от китайского завода до российского потребителя «от двери до двери» и помогает китайским продавцам выходить на российский рынок в соответствии с требованиями.',
  bio_en = 'ANYA Global Logistics specializes in global logistics services and develops customized solutions based on cargo type, delivery requirements and destination market. The company integrates air, ocean, rail and road resources, providing multimodal and dedicated routes across Southeast Asia, the Middle East, Africa, South and North America, Russia and Europe. Customers receive stable, efficient and cost-competitive international logistics solutions.

For the Russian market, ANYA operates as a one-stop cross-border logistics provider covering the full delivery chain and serves Chinese sellers on leading marketplaces including Ozon and Wildberries. Its services include air, rail and ocean freight, with a focus on scheduled trunk-road transport, as well as Class 9 dangerous goods transportation. The company uses formal, compliant Russian customs clearance and supports evolving requirements including SPOT, EAC and KIZ labeling. A self-operated warehouse in Russia supports FBO and FBS fulfillment, while end-to-end GPS tracking provides shipment visibility. Value-added services include compliance consulting, in-warehouse operations and Russian-language after-sales support, enabling door-to-door delivery from Chinese factories to Russian consumers and helping Chinese sellers expand into Russia compliantly.',
  updated_at = strftime('%Y-%m-%d %H:%M:%S', 'now')
WHERE slug = 'anya-global-logistics'
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
  (SELECT id FROM members WHERE slug = 'anya-global-logistics'),
  '{"fields":["summary","bio"],"source":"user-approved profile update"}',
  '0000000000000000000000000000000000000000000000000000000000000000',
  strftime('%Y-%m-%d %H:%M:%S', 'now')
);

COMMIT;
