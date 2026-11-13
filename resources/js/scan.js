import jsQR from "jsqr";
import QRCode from "qrcode";

const $ = (s, r = document) => r.querySelector(s);
const $$ = (s, r = document) => [...r.querySelectorAll(s)];
const el = (tag, cls = "", text = "") => { const e = document.createElement(tag); if (cls) e.className = cls; if (text) e.textContent = text; return e; };

/* ---------- Pembuat QR (kartu anggota & label buku) ---------- */
$$("canvas[data-code]").forEach((c) =>
  QRCode.toCanvas(c, c.dataset.code, { width: 320, margin: 1, errorCorrectionLevel: "M", color: { dark: "#191163", light: "#ffffff" } }).catch(() => {}));

if ($("#cam")) initScanner();

function initScanner() {
  const csrf = $('meta[name="csrf-token"]').content;
  const root = $("#scan-root");
  const staff = root.dataset.staff === "1", isMember = root.dataset.member === "1", joinUrl = root.dataset.join || "/jadi-anggota", preset = (root.dataset.preset || "").trim();
  const video = $("#cam"), cv = $("#cv"), ctx = cv.getContext("2d", { willReadFrequently: true });
  let stream = null, raf = 0, last = 0, busy = false, lastCode = "", lastAt = 0, active = null;

  /* ----- util ----- */
  const toastEl = $("#toast");
  let tt;
  const toast = (m) => { if (!toastEl) return; toastEl.textContent = m; toastEl.classList.remove("translate-y-24", "opacity-0"); clearTimeout(tt); tt = setTimeout(() => toastEl.classList.add("translate-y-24", "opacity-0"), 3200); };
  async function post(url, body) {
    const r = await fetch(url, { method: "POST", headers: { "Content-Type": "application/json", Accept: "application/json", "X-CSRF-TOKEN": csrf }, body: JSON.stringify(body) });
    return { ok: r.ok, json: await r.json().catch(() => ({})) };
  }
  const panel = () => { const r = $("#result"); r.replaceChildren(); r.className = "rise rounded-3xl bg-neutral-50 p-5 text-sm ring-1 ring-neutral-200"; return r; };
  const btn = (label, fn, cls = "btn-o !h-[2.6rem] !w-auto px-5 !text-sm") => { const b = el("button", cls, label); b.type = "button"; b.onclick = async () => { b.disabled = true; try { await fn(); } finally { b.disabled = false; } }; return b; };

  function showMsg(title, msg, ok) {
    const r = panel();
    r.className = `rise rounded-3xl p-5 text-sm ring-1 ${ok ? "bg-emerald-50 text-emerald-800 ring-emerald-200" : "bg-rose-50 text-rose-700 ring-rose-200"}`;
    r.append(el("p", "font-semibold", title), el("p", "mt-1", msg));
    if (ok) r.append(el("p", "mt-2 text-xs opacity-70", "Pindai QR berikutnya untuk melanjutkan."));
  }

  function setActive(m) {
    active = m ? { no: m.no, name: m.name, bookings: (m.bookings || []).map((b) => b.title) } : null;
    const box = $("#active");
    box.classList.toggle("hidden", !m); box.classList.toggle("flex", !!m);
    if (m) { $("#active-name").textContent = m.name; $("#active-no").textContent = `(${m.no})`; }
  }
  $("#active-clear").addEventListener("click", () => { setActive(null); toast("Transaksi anggota diakhiri."); });

  /* ----- tampilan hasil ----- */
  const statusText = (c, shelf) => c.status === "tersedia" ? `Status: tersedia · ${shelf}`
    : c.status === "dipinjam" ? `Status: sedang dipinjam${c.borrower ? ` oleh ${c.borrower}` : ""}${c.due ? `, tenggat ${c.due}` : ""}`
    : `Status: ${c.status}`;

  function renderBook(d) {
    const b = d.book, c = d.copy, r = panel();
    const top = el("div", "flex gap-4");
    const img = el("img", "h-36 w-24 shrink-0 rounded-md object-cover shadow-md"); img.src = b.cover; img.alt = "";
    const info = el("div", "min-w-0 flex-1");
    const tags = el("div", "flex flex-wrap gap-2");
    (b.tags.length ? b.tags : [b.cat]).forEach((t) => tags.append(el("span", "rounded-full bg-chip px-3 py-0.5 text-xs font-bold text-white", t)));
    info.append(tags, el("h3", "mt-2 text-xl font-bold leading-tight text-ink", b.title), el("p", "text-sm text-neutral-600", b.author),
      el("span", "mt-2 inline-block rounded-full border-2 border-ink px-3 py-0.5 text-xs font-bold text-ink", `Tersedia ${b.available} dari ${b.total} eksemplar`));
    top.append(img, info);
    const copy = el("div", "mt-4 rounded-2xl bg-white p-3 ring-1 ring-neutral-200");
    copy.append(el("p", "font-semibold", `Eksemplar ${c.code}`), el("p", "text-neutral-600", statusText(c, b.shelf)));
    const actions = el("div", "mt-4 flex flex-wrap items-center gap-3");

    if (!staff && !isMember) {
      const a = el("a", "btn-line !h-[2.6rem] !w-auto px-5 !text-sm", "Jadi anggota untuk booking"); a.href = joinUrl;
      actions.append(a, el("p", "text-xs text-neutral-500", "Booking khusus anggota perpustakaan."));
    } else if (!staff) {
      actions.append(btn("Booking buku", async () => {
        const x = await post(`/booking/${b.id}`, {});
        showMsg(x.ok ? "Booking berhasil" : "Booking gagal", x.json.message || "Terjadi kesalahan.", x.ok);
      }));
    } else if (c.status === "dipinjam") {
      actions.append(btn("Terima pengembalian", async () => {
        const x = await post("/scan/kembali", { copy: c.code });
        showMsg(x.ok ? "Pengembalian dicatat" : "Gagal", x.json.message || "Terjadi kesalahan.", x.ok);
      }));
    } else if (active) {
      if (active.bookings.includes(b.title)) copy.append(el("p", "mt-1 text-xs font-semibold text-emerald-700", `${active.name} punya booking untuk buku ini (otomatis menjadi "diambil").`));
      else if (c.status === "dibooking") copy.append(el("p", "mt-1 text-xs font-semibold text-rose-600", "Eksemplar ini sedang disiapkan untuk anggota lain."));
      actions.append(btn(`Pinjamkan ke ${active.name}`, async () => {
        const x = await post("/scan/pinjam", { member: active.no, copy: c.code });
        if (x.ok && x.json.member) setActive(x.json.member);
        showMsg(x.ok ? "Peminjaman dicatat" : "Gagal", x.json.message || "Terjadi kesalahan.", x.ok);
      }));
    } else {
      actions.append(el("p", "text-xs text-neutral-500", "Pindai kartu anggota dulu untuk meminjamkan buku ini."));
    }
    r.append(top, copy, actions);
  }

  function list(title, rows) {
    const box = el("div", "mt-3");
    box.append(el("p", "text-xs font-semibold uppercase tracking-wide text-neutral-500", title));
    if (!rows.length) { box.append(el("p", "mt-1 text-sm text-neutral-500", "Tidak ada.")); return box; }
    rows.forEach((x) => { const row = el("div", "mt-1 flex items-center justify-between gap-2 rounded-xl bg-white px-3 py-2 ring-1 ring-neutral-200"); row.append(...x); box.append(row); });
    return box;
  }

  function renderMember(d) {
    const m = d.member, r = panel();
    if (staff) setActive(m);
    const head = el("div", "flex items-center justify-between gap-3");
    const left = el("div", "min-w-0"); left.append(el("p", "text-xl font-bold text-ink", m.name), el("p", "text-sm text-neutral-600", `${m.no} · ${m.status}`));
    head.append(left, el("span", "rounded-full bg-emerald-100 px-3 py-1 text-xs font-bold text-emerald-800", "Anggota"));
    const stats = el("div", "mt-4 grid grid-cols-3 gap-3 text-center");
    [[m.loans.length, "Dipinjam"], [m.bookings.length, "Booking"], [`Rp ${m.denda.toLocaleString("id-ID")}`, "Denda"]].forEach(([v, l], i) => {
      const s = el("div", "rounded-2xl bg-white p-3 ring-1 ring-neutral-200"); s.append(el("p", `text-lg font-bold ${i === 2 && m.denda ? "text-amber" : "text-ink"}`, String(v)), el("p", "text-xs text-neutral-500", l)); stats.append(s);
    });
    r.append(head, stats,
      list("Sedang dipinjam", m.loans.map((l) => [el("span", "min-w-0 truncate text-sm", l.title), el("span", `shrink-0 text-xs font-semibold ${l.late ? "text-rose-600" : "text-neutral-500"}`, `${l.late ? "Terlambat · " : ""}${l.due}`)])),
      list("Booking aktif", m.bookings.map((b) => [el("span", "min-w-0 truncate text-sm", b.title), el("span", "shrink-0 text-xs font-semibold text-neutral-500", b.status)])));
    if (staff) r.append(el("p", "mt-4 rounded-xl bg-ink/5 p-3 text-xs text-ink", "Anggota ini sekarang aktif. Pindai QR buku untuk meminjamkan."));
  }

  /* ----- proses kode ----- */
  async function handle(code) {
    code = String(code || "").trim();
    if (!code || busy) return;
    if (code === lastCode && Date.now() - lastAt < 2500) return;
    lastCode = code; lastAt = Date.now(); busy = true;
    navigator.vibrate?.(60);
    try {
      const x = await post("/scan/resolve", { code });
      if (!x.ok) showMsg("Tidak bisa diproses", x.json.message || "Kode tidak valid.", false);
      else x.json.type === "book" ? renderBook(x.json) : renderMember(x.json);
    } catch { showMsg("Gagal terhubung", "Tidak dapat menghubungi server.", false); }
    finally { busy = false; }
  }

  /* ----- kamera ----- */
  const setCam = (on) => {
    $("#cam-off").classList.toggle("hidden", on); $("#cam-frame").classList.toggle("hidden", !on);
    $("#cam-toggle").textContent = on ? "Matikan kamera" : "Nyalakan kamera";
  };
  function tick(t) {
    raf = requestAnimationFrame(tick);
    if (t - last < 140 || busy || video.readyState < 2 || !video.videoWidth) return;
    last = t;
    const s = Math.min(1, 640 / video.videoWidth);
    cv.width = Math.round(video.videoWidth * s); cv.height = Math.round(video.videoHeight * s);
    ctx.drawImage(video, 0, 0, cv.width, cv.height);
    const im = ctx.getImageData(0, 0, cv.width, cv.height);
    const res = jsQR(im.data, im.width, im.height, { inversionAttempts: "dontInvert" });
    if (res && res.data) handle(res.data);
  }
  function stop() { cancelAnimationFrame(raf); stream?.getTracks().forEach((t) => t.stop()); stream = null; video.srcObject = null; setCam(false); }
  async function start() {
    if (!navigator.mediaDevices?.getUserMedia) { showMsg("Kamera tidak tersedia", "Browser ini tidak mendukung kamera atau halaman tidak aman. Buka lewat http://127.0.0.1:8000 / https, atau gunakan unggah gambar atau ketik kode.", false); return; }
    try {
      stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: { ideal: "environment" }, width: { ideal: 1280 } }, audio: false });
      video.srcObject = stream; await video.play(); setCam(true); raf = requestAnimationFrame(tick);
    } catch { showMsg("Kamera tidak bisa dibuka", "Izin kamera ditolak atau kamera tidak ditemukan. Gunakan unggah gambar atau ketik kode.", false); }
  }
  $("#cam-toggle").addEventListener("click", () => (stream ? stop() : start()));
  document.addEventListener("visibilitychange", () => { if (document.hidden && stream) stop(); });

  /* ----- unggah gambar & ketik kode ----- */
  $("#qr-file").addEventListener("change", async (e) => {
    const f = e.target.files[0]; if (!f) return;
    try {
      const bmp = await createImageBitmap(f), s = Math.min(1, 1024 / Math.max(bmp.width, bmp.height));
      cv.width = Math.round(bmp.width * s); cv.height = Math.round(bmp.height * s);
      ctx.drawImage(bmp, 0, 0, cv.width, cv.height);
      const im = ctx.getImageData(0, 0, cv.width, cv.height);
      const res = jsQR(im.data, im.width, im.height, { inversionAttempts: "attemptBoth" });
      lastCode = "";
      res ? handle(res.data) : showMsg("QR tidak terbaca", "Tidak ada QR yang terdeteksi pada gambar ini.", false);
    } catch { showMsg("Gambar tidak bisa dibaca", "Coba gambar lain.", false); }
    e.target.value = "";
  });
  $("#manual").addEventListener("submit", (e) => { e.preventDefault(); lastCode = ""; handle($("#manual-code").value); });
  if (preset) { lastCode = ""; handle(preset); }   // ?member=DP-... dari halaman Anggota petugas
}
