(function () {
  'use strict';

  function t(row, key) {
    return {
      zh: row[key + '_zh'] || '',
      ru: row[key + '_ru'] || row[key + '_zh'] || '',
      en: row[key + '_en'] || row[key + '_zh'] || ''
    };
  }

  function set3(el, values) {
    el.setAttribute('data-zh', values.zh);
    el.setAttribute('data-ru', values.ru);
    el.setAttribute('data-en', values.en);
    el.textContent = values.zh;
  }

  function initials(row) {
    var parts = (row.name_en || '').trim().split(/\s+/).filter(Boolean);
    if (parts.length > 1) {
      return (parts[0].charAt(0) + parts[parts.length - 1].charAt(0)).toUpperCase();
    }
    return (row.name_zh || 'RC').slice(0, 2);
  }

  function card(row) {
    var article = document.createElement('article');
    article.className = 'member-card reveal visible';
    var a = document.createElement('a');
    a.className = 'member-card-link';
    a.href = 'members/' + encodeURIComponent(row.slug) + '.html';
    var wrap = document.createElement('div');
    wrap.className = 'member-photo-wrap';
    if (row.photo_path) {
      var img = document.createElement('img');
      img.className = 'member-photo';
      img.src = row.photo_path;
      img.alt = row.name_zh;
      img.loading = 'lazy';
      wrap.appendChild(img);
    } else {
      var placeholder = document.createElement('div');
      placeholder.className = 'member-photo member-photo-placeholder';
      placeholder.setAttribute('role', 'img');
      placeholder.setAttribute('aria-label', row.name_zh);
      placeholder.textContent = initials(row);
      wrap.appendChild(placeholder);
    }
    if (Number(row.is_demo) === 1) {
      var badge = document.createElement('span');
      badge.className = 'member-demo-badge';
      set3(badge, { zh: '虚构示例', ru: 'Демо', en: 'Fictional demo' });
      wrap.appendChild(badge);
    }
    var body = document.createElement('div');
    body.className = 'member-card-body';
    var role = document.createElement('span');
    role.className = 'member-role';
    set3(role, t(row, 'role'));
    var name = document.createElement('h3');
    name.className = 'member-name';
    set3(name, t(row, 'name'));
    body.append(role, name);
    if (row.organization_zh) {
      var organization = document.createElement('div');
      organization.className = 'member-organization';
      set3(organization, t(row, 'organization'));
      body.appendChild(organization);
    }
    var summary = document.createElement('p');
    summary.className = 'member-summary';
    set3(summary, t(row, 'summary'));
    var more = document.createElement('span');
    more.className = 'member-more';
    set3(more, { zh: '查看独立介绍 →', ru: 'Открыть профиль →', en: 'View profile →' });
    body.append(summary, more);
    a.append(wrap, body);
    article.appendChild(a);
    return article;
  }

  function company(row) {
    var a = document.createElement('a');
    a.className = 'member-company-card reveal visible';
    a.href = 'members/' + encodeURIComponent(row.slug) + '.html';
    var mark = document.createElement('div');
    mark.className = 'company-mark';
    if (row.photo_path) {
      var logo = document.createElement('img');
      mark.classList.add('has-logo');
      logo.className = 'company-logo';
      logo.src = row.photo_path;
      logo.alt = row.name_zh;
      logo.loading = 'lazy';
      mark.appendChild(logo);
    } else {
      mark.textContent = initials(row);
    }
    var role = document.createElement('span');
    role.className = 'company-sector';
    set3(role, t(row, 'role'));
    var name = document.createElement('h3');
    name.className = 'company-name';
    set3(name, t(row, 'name'));
    var desc = document.createElement('p');
    desc.className = 'company-desc';
    set3(desc, t(row, 'summary'));
    var more = document.createElement('span');
    more.className = 'member-more';
    set3(more, { zh: '查看会员专页 →', ru: 'Открыть профиль →', en: 'View member profile →' });
    a.append(mark, role, name, desc, more);
    return a;
  }

  function emptyMessage(values) {
    var message = document.createElement('p');
    message.className = 'member-directory-fallback';
    set3(message, values);
    return message;
  }

  document.addEventListener('DOMContentLoaded', function () {
    fetch('/api/members.php', { headers: { Accept: 'application/json' } })
      .then(function (response) {
        if (!response.ok) throw new Error();
        return response.json();
      })
      .then(function (data) {
        var maps = {
          councilPerson: document.querySelector('#council .council-person-grid'),
          councilOrganization: document.querySelector('#council .council-company-grid'),
          secretariat: document.querySelector('#secretariat .member-grid'),
          individual: document.querySelector('#directory .member-directory-person-grid'),
          organization: document.querySelector('#directory .member-company-grid')
        };

        Object.keys(maps).forEach(function (key) {
          if (maps[key]) maps[key].textContent = '';
        });

        data.members.forEach(function (row) {
          var target;
          if (row.section === 'council') {
            target = row.member_type === 'organization' ? maps.councilOrganization : maps.councilPerson;
          } else if (row.section === 'member') {
            target = row.member_type === 'organization' ? maps.organization : maps.individual;
          } else {
            target = maps[row.section];
          }
          if (target) {
            target.appendChild(row.member_type === 'organization' ? company(row) : card(row));
          }
        });

        var emptyStates = {
          councilPerson: {
            zh: '暂无个人理事会成员资料。',
            ru: 'Данные индивидуальных членов совета пока не опубликованы.',
            en: 'No individual Council member profiles are currently published.'
          },
          councilOrganization: {
            zh: '暂无企业理事会成员资料。',
            ru: 'Данные организаций — членов совета пока не опубликованы.',
            en: 'No corporate Council member profiles are currently published.'
          },
          secretariat: {
            zh: '暂无秘书处成员资料。',
            ru: 'Данные членов секретариата пока не опубликованы.',
            en: 'No Secretariat member profiles are currently published.'
          },
          individual: {
            zh: '暂无个人会员资料。',
            ru: 'Данные индивидуальных членов пока не опубликованы.',
            en: 'No individual member profiles are currently published.'
          },
          organization: {
            zh: '暂无企业会员资料。',
            ru: 'Данные корпоративных членов пока не опубликованы.',
            en: 'No corporate member profiles are currently published.'
          }
        };

        Object.keys(maps).forEach(function (key) {
          if (maps[key] && !maps[key].children.length) {
            maps[key].appendChild(emptyMessage(emptyStates[key]));
          }
        });

        var active = document.querySelector('.lang-btn.active');
        if (active) active.click();
      })
      .catch(function () {
        /* Keep server-rendered demo fallback. */
      });
  });
})();
