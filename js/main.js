/* ============================================================
   俄中电子商务协会官网 — 三语切换与交互脚本
   Trilingual switcher (zh / ru / en) + UI interactions
   用法:元素加 data-zh / data-ru / data-en 属性即可被切换;
   输入框占位符用 data-zh-ph / data-ru-ph / data-en-ph。
   ============================================================ */
(function () {
  "use strict";

  var HTML_LANG = { zh: "zh-CN", ru: "ru", en: "en" };
  var DEFAULT_LANG = "zh";

  function getSavedLang() {
    try {
      var v = localStorage.getItem("rcea_lang");
      if (v && HTML_LANG[v]) return v;
    } catch (e) { /* file:// 或隐私模式下可能不可用 */ }
    return DEFAULT_LANG;
  }

  function saveLang(lang) {
    try { localStorage.setItem("rcea_lang", lang); } catch (e) { /* ignore */ }
  }

  function applyLang(lang) {
    if (!HTML_LANG[lang]) lang = DEFAULT_LANG;
    document.documentElement.lang = HTML_LANG[lang];
    document.documentElement.setAttribute("data-lang", lang);

    // 文本内容
    var nodes = document.querySelectorAll("[data-zh]");
    for (var i = 0; i < nodes.length; i++) {
      var el = nodes[i];
      var val = el.getAttribute("data-" + lang);
      if (val !== null) el.textContent = val;
    }
    // 占位符
    var phNodes = document.querySelectorAll("[data-zh-ph]");
    for (var j = 0; j < phNodes.length; j++) {
      var p = phNodes[j];
      var ph = p.getAttribute("data-" + lang + "-ph");
      if (ph !== null) p.setAttribute("placeholder", ph);
    }
    // 语言按钮高亮
    var btns = document.querySelectorAll(".lang-btn");
    for (var k = 0; k < btns.length; k++) {
      var b = btns[k];
      var active = b.getAttribute("data-lang") === lang;
      b.classList.toggle("active", active);
      b.setAttribute("aria-pressed", active ? "true" : "false");
    }
    saveLang(lang);
  }

  function initLangSwitch() {
    var btns = document.querySelectorAll(".lang-btn");
    for (var i = 0; i < btns.length; i++) {
      btns[i].addEventListener("click", function () {
        applyLang(this.getAttribute("data-lang"));
      });
    }
    applyLang(getSavedLang());
  }

  function initNav() {
    // 当前页高亮
    var page = document.body.getAttribute("data-page");
    if (page) {
      var links = document.querySelectorAll(".main-nav a[data-nav]");
      for (var i = 0; i < links.length; i++) {
        if (links[i].getAttribute("data-nav") === page) links[i].classList.add("active");
      }
    }
    // 移动端菜单
    var burger = document.querySelector(".burger");
    var nav = document.querySelector(".main-nav");
    if (burger && nav) {
      burger.addEventListener("click", function () {
        var open = nav.classList.toggle("open");
        burger.classList.toggle("open", open);
        burger.setAttribute("aria-expanded", open ? "true" : "false");
      });
      nav.addEventListener("click", function (e) {
        if (e.target.tagName === "A") {
          nav.classList.remove("open");
          burger.classList.remove("open");
        }
      });
    }
  }

  function initReveal() {
    var items = document.querySelectorAll(".reveal");
    if (!("IntersectionObserver" in window)) {
      for (var i = 0; i < items.length; i++) items[i].classList.add("visible");
      return;
    }
    var io = new IntersectionObserver(function (entries) {
      entries.forEach(function (en) {
        if (en.isIntersecting) {
          en.target.classList.add("visible");
          io.unobserve(en.target);
        }
      });
    }, { threshold: 0.12 });
    for (var j = 0; j < items.length; j++) io.observe(items[j]);
  }

  function initYear() {
    var y = document.querySelector("[data-year]");
    if (y) y.textContent = new Date().getFullYear();
  }

  function initForm() {
    var form = document.querySelector("#inquiry-form");
    if (!form) return;
    form.addEventListener("submit", function (e) {
      e.preventDefault();
      var msg = document.querySelector("#form-msg");
      if (msg) {
        msg.classList.add("show");
        msg.scrollIntoView({ behavior: "smooth", block: "nearest" });
      }
      form.reset();
    });
  }

  document.addEventListener("DOMContentLoaded", function () {
    initLangSwitch();
    initNav();
    initReveal();
    initYear();
    initForm();
  });
})();
