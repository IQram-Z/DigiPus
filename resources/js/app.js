const $ = (s, r = document) => r.querySelector(s);
const $$ = (s, r = document) => [...r.querySelectorAll(s)];
const reduce = matchMedia("(prefers-reduced-motion: reduce)").matches;
const ease = "cubic-bezier(.2,.9,.25,1)";

/* ---------- Toast ---------- */
const toastEl = $("#toast");
let toastTimer;
function toast(msg) {
  if (!toastEl || !msg) return;
  toastEl.textContent = msg;
  toastEl.classList.remove("translate-y-24", "opacity-0");
  clearTimeout(toastTimer);
  toastTimer = setTimeout(() => toastEl.classList.add("translate-y-24", "opacity-0"), 3200);
}
toast(toastEl?.dataset.flash);
$$("[data-soon]").forEach((el) => el.addEventListener("click", (e) => { e.preventDefault(); toast(`${el.dataset.soon}: segera hadir`); }));

/* ---------- Konfirmasi form (data-confirm), pratinjau gambar, pilih-semua QR ---------- */
document.addEventListener("submit", (e) => { const m = e.target.dataset?.confirm; if (m && !confirm(m)) e.preventDefault(); }, true);
$$("input[data-preview]").forEach((i) => i.addEventListener("change", () => { const f = i.files[0]; const img = $(i.dataset.preview); if (f && img) img.src = URL.createObjectURL(f); }));
document.addEventListener("click", (e) => { const a = e.target.closest("[data-check-all]"); if (a) $$("input[data-eks]").forEach((c) => (c.checked = a.dataset.checkAll === "1")); });
document.addEventListener("change", (e) => { const t = e.target; if (t.matches?.("[data-book-all]")) $$(`input[data-book="${t.dataset.bookAll}"]`).forEach((c) => (c.checked = t.checked)); });

/* ---------- Tampilkan/sembunyikan kata sandi ---------- */
$$("[data-eye]").forEach((b) => b.addEventListener("click", () => {
  const i = b.parentElement.querySelector("input");
  i.type = i.type === "password" ? "text" : "password";
  b.classList.toggle("opacity-40");
}));

/* ---------- Login 2 langkah (username/email -> kata sandi) ---------- */
const lf = $("#login-form");
if (lf) {
  const p1 = $('[data-pane="1"]', lf), p2 = $('[data-pane="2"]', lf), li = $("#login"), pw = $("#password"), err = $("[data-err]", lf);
  async function swap(from, to, dir) {
    if (!reduce) await from.animate([{ opacity: 1, transform: "none" }, { opacity: 0, transform: `translateX(${-34 * dir}px)` }], { duration: 220, easing: "ease-in" }).finished;
    from.classList.add("hidden"); to.classList.remove("hidden");
    if (!reduce) to.animate([{ opacity: 0, transform: `translateX(${34 * dir}px)` }, { opacity: 1, transform: "none" }], { duration: 420, easing: ease });
    (to === p2 ? pw : li).focus();
  }
  const next = () => {
    if (!li.value.trim()) {
      err.textContent = "Username atau email wajib diisi."; li.classList.add("err");
      if (!reduce) li.animate([{ transform: "none" }, { transform: "translateX(-8px)" }, { transform: "translateX(8px)" }, { transform: "translateX(-4px)" }, { transform: "none" }], { duration: 360 });
      return;
    }
    err.textContent = ""; li.classList.remove("err"); swap(p1, p2, 1);
  };
  $("[data-next]", lf).addEventListener("click", next);
  li.addEventListener("keydown", (e) => { if (e.key === "Enter") { e.preventDefault(); next(); } });
  $("[data-back]", lf).addEventListener("click", () => swap(p2, p1, -1));
}

/* ---------- Sidebar mobile & menu pengguna ---------- */
const side = $("#side"), scrim = $("#scrim");
function setMenu(open) {
  side?.classList.toggle("-translate-x-full", !open);
  scrim?.classList.toggle("opacity-0", !open);
  scrim?.classList.toggle("pointer-events-none", !open);
}
$("#menu")?.addEventListener("click", () => setMenu(side.classList.contains("-translate-x-full")));
scrim?.addEventListener("click", () => setMenu(false));

const um = $("#user-menu");
function setUM(open) { um?.classList.toggle("invisible", !open); um?.classList.toggle("opacity-0", !open); um?.classList.toggle("scale-90", !open); }
$("#user-btn")?.addEventListener("click", (e) => { e.stopPropagation(); setUM(um.classList.contains("invisible")); });
document.addEventListener("click", (e) => { if (um && !um.contains(e.target)) setUM(false); });

/* ---------- Dashboard ---------- */
const dataEl = $("#books-data");
if (dataEl) {
  const books = JSON.parse(dataEl.textContent);
  const isMember = dataEl.dataset.member === "1";
  const all = $("#collection"), cards = $$(".book-card");
  let cat = "", q = "";

  const openAll = (scroll) => {
    if (scroll) all.scrollIntoView({ behavior: reduce ? "auto" : "smooth", block: "start" });
  };
  function apply() {
    let shown = 0;
    cards.forEach((c) => {
      const ok = (!cat || c.dataset.cat === cat) && c.dataset.title.includes(q);
      if (ok) { shown++; if (c.classList.contains("hide")) { c.classList.remove("hide"); c.classList.add("pop"); } }
      else { c.classList.remove("pop"); c.classList.add("hide"); }
    });
    $("#empty").classList.toggle("hidden", shown > 0);
    const f = [];
    if (cat) f.push(`kategori ${cat}`);
    if (q) f.push(`“${q}”`);
    $("#info").textContent = f.length ? `${shown} buku untuk ${f.join(" · ")}` : `${shown} buku`;
    $("[data-clear]").classList.toggle("invisible", !f.length);
    $$(".tile-cat").forEach((t) => t.classList.toggle("on", t.dataset.cat === cat));
    $("#cat").value = cat;
  }
  apply();

  $("#search").addEventListener("submit", (e) => { e.preventDefault(); cat = $("#cat").value; q = $("#q").value.trim().toLowerCase(); apply(); openAll(true); });
  $("#q").addEventListener("input", (e) => { if (all.classList.contains("open")) { q = e.target.value.trim().toLowerCase(); apply(); } });
  $("[data-viewall]").addEventListener("click", (e) => {
    const rail = $("#rail"), btn = e.currentTarget, grid = rail.classList.toggle("grid-mode");
    btn.firstChild.nodeValue = grid ? "Show less " : "View all ";
    $$("[data-rail]").forEach((b) => { b.style.display = grid ? "none" : ""; });
    if (grid) { rail.scrollLeft = 0; $$(".rail-item", rail).slice(4).forEach((it, i) => { it.style.animationDelay = `${i * 70}ms`; it.classList.remove("shelf-in"); it.classList.add("pop"); }); }
  });
  $("[data-clear]").addEventListener("click", () => { cat = ""; q = ""; $("#q").value = ""; apply(); });
  $$(".tile-cat").forEach((t) => t.addEventListener("click", () => { cat = cat === t.dataset.cat ? "" : t.dataset.cat; apply(); openAll(true); }));
  $("[data-morecat]").addEventListener("click", (e) => {
    const btn = e.currentTarget, open = btn.getAttribute("aria-expanded") !== "true";
    btn.getAttribute("aria-expanded"); btn.setAttribute("aria-expanded", open);
    $$(".extra").forEach((el, i) => { el.classList.toggle("hidden", !open); if (open) { el.style.animationDelay = `${i * 90}ms`; el.classList.add("pop"); } });
  });

  /* ----- Rak rekomendasi: geser dengan sentuh, mouse (drag), roda, atau tombol panah ----- */
  const rail = $("#rail");
  if (rail) {
    const prev = $('[data-rail="prev"]'), next = $('[data-rail="next"]');
    const sync = () => {
      prev.disabled = rail.scrollLeft < 8;
      next.disabled = rail.scrollLeft + rail.clientWidth >= rail.scrollWidth - 8;
    };
    rail.addEventListener("scroll", sync, { passive: true });
    addEventListener("resize", sync); sync();
    const step = () => (rail.children[1] ? rail.children[1].offsetLeft - rail.children[0].offsetLeft : rail.clientWidth * 0.8);
    prev.addEventListener("click", () => rail.scrollBy({ left: -step(), behavior: "smooth" }));
    next.addEventListener("click", () => rail.scrollBy({ left: step(), behavior: "smooth" }));

    let down = false, sx = 0, sl = 0, moved = 0;
    rail.addEventListener("pointerdown", (e) => {
      if (e.pointerType !== "mouse" || e.button !== 0) return;
      down = true; moved = 0; sx = e.clientX; sl = rail.scrollLeft;
    });
    addEventListener("pointermove", (e) => {
      if (!down) return;
      const dx = e.clientX - sx; moved = Math.max(moved, Math.abs(dx));
      if (moved > 6) rail.classList.add("dragging");
      rail.scrollLeft = sl - dx;
    });
    addEventListener("pointerup", () => { down = false; setTimeout(() => rail.classList.remove("dragging"), 0); });
    rail.addEventListener("click", (e) => { if (moved > 6) { e.stopPropagation(); e.preventDefault(); moved = 0; } }, true);
  }

  /* ----- Pop-up detail: sampul terbang dari rak, detail terbuka di sampingnya (hanya transform/opacity = ringan) ----- */
  const modal = $("#modal"), back = $("#m-back"), cov = $("#m-cover"), info = $("#m-info"), img = $("#m-img");
  const desktop = matchMedia("(min-width: 768px)");
  const star = '<svg viewBox="0 0 24 24" class="size-[1.625rem]" fill="#f2c94c"><path d="M12 2l3 6.5 7 .8-5.2 4.8 1.5 7L12 17.6 5.7 21l1.5-7L2 9.3l7-.8z"/></svg>';
  let origin = null, closing = false;
  const stop = () => [cov, info, back, ...$$("[data-s]", info)].forEach((el) => el.getAnimations().forEach((a) => a.cancel()));
  const wait = (anim, ms) => Promise.race([anim.finished.catch(() => {}), new Promise((r) => setTimeout(r, ms))]);

  function fill(b) {
    img.src = b.cover; img.alt = `Sampul ${b.title}`;
    $("#m-tags").replaceChildren(...(b.tags.length ? b.tags : [b.cat]).map((t) => {
      const s = document.createElement("span");
      s.className = "rounded-full bg-chip px-5 py-1.5 text-[0.8125rem] font-bold text-white"; s.textContent = t; return s;
    }));
    $("#m-title").textContent = b.title;
    $("#m-author").textContent = b.author;
    const stars = $("#m-stars"); stars.innerHTML = star.repeat(5);
    const r = document.createElement("span"); r.className = "ml-2 text-[1.375rem]"; r.textContent = `/ ${b.rating}`; stars.append(r);
    const ok = b.available > 0;
    $("#m-avail").textContent = ok ? `Tersedia ${b.available} dari ${b.total} eksemplar` : `Semua ${b.total} eksemplar sedang dipinjam`;
    $("#m-shelf").textContent = b.shelf;
    $("#m-syn").textContent = b.synopsis || "Sinopsis belum tersedia.";
    const btn = $("#m-book");
    btn.textContent = ok ? "Booking buku" : "Masuk antrean";
    if (!isMember) { // pengunjung: booking khusus anggota
      btn.textContent = "Jadi anggota untuk booking";
      btn.onclick = () => { location.href = dataEl.dataset.join; };
      return;
    }
    btn.onclick = async () => {
      btn.disabled = true;
      try {
        const res = await fetch(`/booking/${b.id}`, { method: "POST", headers: { "X-CSRF-TOKEN": $('meta[name="csrf-token"]').content, Accept: "application/json" } });
        toast((await res.json()).message || "Terjadi kesalahan. Coba lagi.");
      } catch { toast("Tidak dapat terhubung ke server."); }
      btn.disabled = false;
      close();
    };
  }

  function open(card) {
    if (origin) return;
    try {
      const b = books.find((x) => x.id == card.dataset.open);
      origin = card.querySelector("img");
      stop(); fill(b);
      modal.classList.remove("hidden"); modal.classList.add("flex");
      document.body.style.overflow = "hidden";
      const from = origin.getBoundingClientRect(), to = cov.getBoundingClientRect();
      origin.style.visibility = "hidden";
      if (!reduce) {
        const ctr = (r) => [r.left + r.width / 2, r.top + r.height / 2];
        const [fx, fy] = ctr(from), [tx, ty] = ctr(to);
        const s = from.width / to.width;
        const mx = innerWidth / 2 - tx, my = innerHeight / 2 - ty; // titik tengah layar
        const D = 600;
        back.animate([{ opacity: 0 }, { opacity: 1 }], { duration: 220, fill: "backwards" });
        // 1) sampul muncul dari depan di tengah layar  2) lalu bergeser ke posisi akhir (kiri)
        cov.animate([
          { transform: `translate(${fx - tx}px,${fy - ty}px) scale(${s})`, offset: 0, easing: "cubic-bezier(.2,.9,.25,1.1)" },
          { transform: `translate(${mx}px,${my}px) scale(1.08)`, offset: 0.4, easing: "cubic-bezier(.3,.8,.3,1)" },
          { transform: "none", offset: 1 },
        ], { duration: D, fill: "backwards" });
        // 3) detail keluar dari balik sampul saat sampul bergeser
        const hid = desktop.matches ? "translateX(-32%) scale(.94)" : "translateY(-22%) scale(.94)";
        info.animate([{ opacity: 0, transform: hid }, { opacity: 1, transform: "none" }],
          { duration: 440, delay: D * 0.4, easing: ease, fill: "backwards" });
        $$("[data-s]", info).forEach((el, i) => el.animate(
          [{ opacity: 0, transform: "translateX(24px)" }, { opacity: 1, transform: "none" }],
          { duration: 320, delay: D * 0.4 + 120 + i * 40, easing: ease, fill: "backwards" }));
      }
      $("#m-close").focus({ preventScroll: true });
    } catch (err) { console.error(err); cleanup(); }
  }

  function cleanup() {
    stop();
    if (origin) origin.style.visibility = "";
    modal.classList.add("hidden"); modal.classList.remove("flex");
    document.body.style.overflow = "";
    origin = null; closing = false;
  }

  async function close() {
    if (!origin || closing) return;
    closing = true;
    try {
      if (!reduce) {
        const from = origin.getBoundingClientRect(), to = cov.getBoundingClientRect();
        const dx = from.left + from.width / 2 - (to.left + to.width / 2), dy = from.top + from.height / 2 - (to.top + to.height / 2);
        const s = from.width / to.width;
        info.animate([{ opacity: 1 }, { opacity: 0, transform: "translateX(-6%) scale(.96)" }], { duration: 160, fill: "forwards" });
        back.animate([{ opacity: 1 }, { opacity: 0 }], { duration: 300, fill: "forwards" });
        await wait(cov.animate([{ transform: "none" }, { transform: `translate(${dx}px,${dy}px) scale(${s})` }], { duration: 300, easing: "cubic-bezier(.5,0,.2,1)", fill: "forwards" }), 500);
      }
    } finally { cleanup(); }
  }

  $$("[data-open]").forEach((c) => c.addEventListener("click", () => open(c)));
  $("#m-close").addEventListener("click", close);
  back.addEventListener("click", close);
  addEventListener("keydown", (e) => e.key === "Escape" && close());
}

/* ---------- Sidebar: tandai "Book Collection" saat bagian itu terlihat ---------- */
{
  const col = $("#collection"), dis = $('[data-nav="discover"]'), nav = $('[data-nav="collection"]');
  if (col && dis && nav && "IntersectionObserver" in window) {
    new IntersectionObserver(([en]) => { nav.classList.toggle("on", en.isIntersecting); dis.classList.toggle("on", !en.isIntersecting); },
      { rootMargin: "-35% 0px -45% 0px" }).observe(col);
  }
}
