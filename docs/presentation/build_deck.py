"""Build the SoundKu presentation deck (Bahasa Indonesia)."""

import os

from pptx import Presentation
from pptx.dml.color import RGBColor
from pptx.enum.shapes import MSO_SHAPE
from pptx.enum.text import MSO_ANCHOR, PP_ALIGN
from pptx.util import Emu, Inches, Pt

HERE = os.path.dirname(os.path.abspath(__file__))
ASSETS = os.path.join(HERE, "assets")
OUT = os.path.join(HERE, "SoundKu-Presentasi.pptx")

# ---------------------------------------------------------------------------
# Design tokens
# ---------------------------------------------------------------------------

BG = RGBColor(0x0B, 0x0B, 0x12)
CARD = RGBColor(0x15, 0x14, 0x20)
CARD2 = RGBColor(0x1B, 0x19, 0x28)
BORDER = RGBColor(0x2A, 0x27, 0x40)
TEXT = RGBColor(0xF4, 0xF2, 0xFF)
MUTED = RGBColor(0xA7, 0xA3, 0xBD)
DIM = RGBColor(0x72, 0x6E, 0x8C)
ACCENT = RGBColor(0x8B, 0x5C, 0xF6)
ACCENT2 = RGBColor(0xFF, 0x4D, 0x8D)
CYAN = RGBColor(0x22, 0xD3, 0xEE)
GOLD = RGBColor(0xFF, 0xB0, 0x20)
GREEN = RGBColor(0x34, 0xD3, 0x99)
RED = RGBColor(0xFB, 0x71, 0x85)

HEAD = "Bahnschrift"
BODY = "Segoe UI"
MONO = "Consolas"

W = 13.333
H = 7.5
TOTAL = 20
SLIDE_INDEX = 0

prs = Presentation()
prs.slide_width = Inches(W)
prs.slide_height = Inches(H)
BLANK = prs.slide_layouts[6]


# ---------------------------------------------------------------------------
# Helpers
# ---------------------------------------------------------------------------

def new_slide():
    global SLIDE_INDEX
    SLIDE_INDEX += 1
    slide = prs.slides.add_slide(BLANK)
    slide.background.fill.solid()
    slide.background.fill.fore_color.rgb = BG
    return slide


def rect(slide, x, y, w, h, fill=CARD, line=None, radius=0.06, shape=MSO_SHAPE.ROUNDED_RECTANGLE):
    item = slide.shapes.add_shape(shape, Inches(x), Inches(y), Inches(w), Inches(h))
    if shape == MSO_SHAPE.ROUNDED_RECTANGLE:
        try:
            item.adjustments[0] = radius
        except Exception:
            pass
    if fill is None:
        item.fill.background()
    else:
        item.fill.solid()
        item.fill.fore_color.rgb = fill
    if line is None:
        item.line.fill.background()
    else:
        item.line.color.rgb = line
        item.line.width = Pt(0.9)
    item.shadow.inherit = False
    return item


def gradient_rect(slide, x, y, w, h, color_a=ACCENT, color_b=ACCENT2, radius=0.06,
                  shape=MSO_SHAPE.ROUNDED_RECTANGLE, angle=45):
    item = rect(slide, x, y, w, h, fill=color_a, line=None, radius=radius, shape=shape)
    item.fill.gradient()
    stops = item.fill.gradient_stops
    stops[0].position = 0.0
    stops[0].color.rgb = color_a
    stops[1].position = 1.0
    stops[1].color.rgb = color_b
    try:
        item.fill.gradient_angle = angle
    except Exception:
        pass
    return item


def text(slide, value, x, y, w, h, size=14, color=TEXT, bold=False, font=BODY,
         align=PP_ALIGN.LEFT, anchor=MSO_ANCHOR.TOP, spacing=1.0, italic=False,
         space_after=0):
    box = slide.shapes.add_textbox(Inches(x), Inches(y), Inches(w), Inches(h))
    tf = box.text_frame
    tf.word_wrap = True
    tf.margin_left = 0
    tf.margin_right = 0
    tf.margin_top = 0
    tf.margin_bottom = 0
    tf.vertical_anchor = anchor
    paragraph = tf.paragraphs[0]
    paragraph.alignment = align
    paragraph.line_spacing = spacing
    paragraph.space_after = Pt(space_after)
    run = paragraph.add_run()
    run.text = value
    run.font.size = Pt(size)
    run.font.color.rgb = color
    run.font.bold = bold
    run.font.italic = italic
    run.font.name = font
    return box


def rich(slide, runs, x, y, w, h, size=14, align=PP_ALIGN.LEFT,
         anchor=MSO_ANCHOR.TOP, spacing=1.0):
    """runs: list of (text, color, bold, font_or_None)."""
    box = slide.shapes.add_textbox(Inches(x), Inches(y), Inches(w), Inches(h))
    tf = box.text_frame
    tf.word_wrap = True
    tf.margin_left = 0
    tf.margin_right = 0
    tf.margin_top = 0
    tf.margin_bottom = 0
    tf.vertical_anchor = anchor
    paragraph = tf.paragraphs[0]
    paragraph.alignment = align
    paragraph.line_spacing = spacing
    for value, color, bold, font in runs:
        run = paragraph.add_run()
        run.text = value
        run.font.size = Pt(size)
        run.font.color.rgb = color
        run.font.bold = bold
        run.font.name = font or BODY
    return box


def bullets(slide, items, x, y, w, h, size=13.5, color=MUTED, gap=7, bullet_color=ACCENT):
    box = slide.shapes.add_textbox(Inches(x), Inches(y), Inches(w), Inches(h))
    tf = box.text_frame
    tf.word_wrap = True
    tf.margin_left = 0
    tf.margin_right = 0
    tf.margin_top = 0
    tf.margin_bottom = 0
    for index, item in enumerate(items):
        paragraph = tf.paragraphs[0] if index == 0 else tf.add_paragraph()
        paragraph.space_after = Pt(gap)
        paragraph.line_spacing = 1.12
        dot = paragraph.add_run()
        dot.text = "•  "
        dot.font.size = Pt(size)
        dot.font.bold = True
        dot.font.color.rgb = bullet_color
        dot.font.name = BODY
        run = paragraph.add_run()
        run.text = item
        run.font.size = Pt(size)
        run.font.color.rgb = color
        run.font.name = BODY
    return box


def chip(slide, value, x, y, w, color=ACCENT, size=11, fill=CARD, line=BORDER):
    height = 0.34
    rect(slide, x, y, w, height, fill=fill, line=line, radius=0.5)
    text(slide, value, x + 0.12, y + 0.055, w - 0.24, height - 0.1, size=size,
         color=color, bold=True, align=PP_ALIGN.CENTER)


def eyebrow(slide, value):
    text(slide, value.upper(), 0.75, 0.42, 8.0, 0.28, size=10.5, color=ACCENT,
         bold=True, font=BODY)


def header(slide, title, kicker="SoundKu · Proyek Web"):
    eyebrow(slide, kicker)
    text(slide, title, 0.72, 0.73, 11.9, 0.62, size=30, color=TEXT, bold=True, font=HEAD)
    gradient_rect(slide, 0.75, 1.44, 1.05, 0.055, radius=0.5)
    rect(slide, 1.95, 1.462, 10.6, 0.012, fill=BORDER, radius=0, shape=MSO_SHAPE.RECTANGLE)


def footer(slide, number=None):
    n = SLIDE_INDEX
    text(slide, "SoundKu · Streaming musik dari SoundCloud", 0.75, 7.06, 6.0, 0.24,
         size=9, color=DIM)
    text(slide, f"{n:02d} / {TOTAL}", 11.55, 7.06, 1.0, 0.24, size=9,
         color=DIM, align=PP_ALIGN.RIGHT)


def notes(slide, value):
    slide.notes_slide.notes_text_frame.text = value


def picture(slide, filename, x, y, w):
    path = os.path.join(ASSETS, filename)
    pic = slide.shapes.add_picture(path, Inches(x), Inches(y), width=Inches(w))
    ratio = pic.image.size[0] / pic.image.size[1]
    target = 16 / 9
    if ratio > target:
        pic.crop_right = (ratio - target) / ratio
    elif ratio < target:
        pic.crop_bottom = (target - ratio) / target
    pic.width = Inches(w)
    pic.height = Inches(w / target)
    pic.line.color.rgb = RGBColor(0x33, 0x2F, 0x52)
    pic.line.width = Pt(1.2)
    return pic


def big_number(slide, value, x, y, w, color=ACCENT, size=40):
    text(slide, value, x, y, w, 0.7, size=size, color=color, bold=True, font=HEAD,
         align=PP_ALIGN.CENTER)


def decorative_orb(slide, x, y, d, color):
    circle = slide.shapes.add_shape(MSO_SHAPE.OVAL, Inches(x), Inches(y), Inches(d), Inches(d))
    circle.fill.solid()
    circle.fill.fore_color.rgb = color
    circle.line.fill.background()
    circle.shadow.inherit = False
    return circle


def equalizer(slide, x, y, count=26, bar_w=0.15, gap=0.2, max_h=1.5,
              colors=(ACCENT, ACCENT2, ACCENT)):
    heights = [0.35, 0.55, 0.85, 1.2, 0.7, 1.0, 1.45, 0.9, 0.5, 0.8, 1.25, 1.1,
               0.6, 0.95, 1.4, 0.75, 0.45, 0.9, 1.3, 1.05, 0.55, 0.85, 1.15, 0.5]
    for index in range(count):
        height = max_h * heights[index % len(heights)]
        color = colors[index % len(colors)]
        gradient_rect(slide, x + index * (bar_w + gap), y + (max_h - height), bar_w,
                      height, color_a=color, color_b=color, radius=0.5, angle=0)


def card_with_rail(slide, x, y, w, h, rail=ACCENT, fill=CARD):
    rect(slide, x, y, w, h, fill=fill, line=BORDER, radius=0.055)
    rect(slide, x + 0.02, y + 0.16, 0.05, h - 0.32, fill=rail, radius=0.5,
         shape=MSO_SHAPE.ROUNDED_RECTANGLE)


def step_badge(slide, value, x, y, color=ACCENT, size=0.42):
    gradient_rect(slide, x, y, size, size, color_a=color, color_b=color, radius=0.5,
                  shape=MSO_SHAPE.OVAL)
    text(slide, value, x, y + 0.055, size, size - 0.1, size=12, color=BG, bold=True,
         align=PP_ALIGN.CENTER)


# ---------------------------------------------------------------------------
# Slide 1 — Cover
# ---------------------------------------------------------------------------

slide = new_slide()
decorative_orb(slide, 8.6, -1.4, 6.4, RGBColor(0x17, 0x10, 0x2E))
decorative_orb(slide, 10.4, 2.2, 4.6, RGBColor(0x24, 0x0F, 0x25))
equalizer(slide, 6.55, 5.25, count=26, bar_w=0.15, gap=0.2, max_h=1.85)

text(slide, "PROYEK WEB · 2026", 0.9, 0.95, 5.5, 0.3, size=12, color=ACCENT, bold=True)
rich(slide, [("Sound", TEXT, True, HEAD), ("Ku", ACCENT2, True, HEAD)],
     0.82, 1.55, 7.5, 1.5, size=88, spacing=0.9)
text(slide, "Streaming musik dari katalog publik SoundCloud.",
     0.9, 3.02, 7.4, 0.5, size=23, color=TEXT, bold=False)
text(slide, "Dari situs manga menjadi platform musik — dibangun ulang dengan Laravel 12,\n"
           "SoundCloud API v2, dan pemutar HLS di browser.",
     0.9, 3.62, 7.2, 0.9, size=13.5, color=MUTED, spacing=1.25)

labels = [("Laravel 12", 1.62), ("PHP 8.2", 1.28), ("SoundCloud API v2", 2.2), ("hls.js", 1.15)]
x = 0.9
for value, width in labels:
    chip(slide, value, x, 4.95, width, color=TEXT, size=10.5, fill=CARD)
    x += width + 0.18

text(slide, "Oleh: Alvin Yafiq Firas", 0.9, 6.32, 5.0, 0.3, size=12, color=MUTED)
text(slide, "2026", 11.4, 6.32, 1.2, 0.3, size=12, color=DIM, align=PP_ALIGN.RIGHT)
notes(slide, "Perkenalkan proyek SoundKu: web streaming musik yang dibangun ulang dari proyek manga sebelumnya. "
             "Semua katalog musik diambil dari API publik SoundCloud, dan pemutar audio berjalan langsung di browser.")

# ---------------------------------------------------------------------------
# Slide 2 — Latar belakang
# ---------------------------------------------------------------------------

slide = new_slide()
header(slide, "Dari situs manga ke platform musik", "Latar belakang")

text(slide, "Proyek ini awalnya adalah MangaKu — situs baca manga dengan katalog, "
           "halaman chapter, dan panel admin. Seluruh isi proyek kemudian diganti "
           "menjadi SoundKu: web streaming musik yang mengambil katalog publik dari SoundCloud.",
     0.75, 1.92, 5.35, 1.35, size=14, color=MUTED, spacing=1.3)

card_with_rail(slide, 0.75, 3.6, 5.35, 1.15, rail=ACCENT)
text(slide, "Yang dipertahankan", 0.98, 3.78, 4.9, 0.3, size=11, color=ACCENT, bold=True)
text(slide, "Laravel 12, PHP 8.2, Blade, autentikasi admin, dan struktur project yang rapi.",
     0.98, 4.09, 4.9, 0.55, size=12.5, color=TEXT)

card_with_rail(slide, 0.75, 4.95, 5.35, 1.15, rail=CYAN)
text(slide, "Yang diganti total", 0.98, 5.13, 4.9, 0.3, size=11, color=CYAN, bold=True)
text(slide, "Model, migration, controller, view manga, dan seluruh antarmuka baca manga.",
     0.98, 5.44, 4.9, 0.55, size=12.5, color=TEXT)

text(slide, "DULU", 6.7, 1.95, 3.0, 0.28, size=10.5, color=DIM, bold=True)
rect(slide, 6.65, 2.28, 5.9, 1.55, fill=CARD, line=BORDER, radius=0.05)
text(slide, "MangaKu", 6.9, 2.44, 3.0, 0.4, size=21, color=MUTED, bold=True, font=HEAD)
text(slide, "Katalog manga · chapter · halaman baca", 6.9, 2.94, 5.3, 0.3, size=12, color=DIM)
text(slide, "Fokus: membaca", 6.9, 3.3, 5.3, 0.3, size=11, color=DIM, italic=True)

rect(slide, 6.65, 4.12, 5.9, 1.98, fill=RGBColor(0x19, 0x14, 0x29), line=RGBColor(0x4A, 0x35, 0x6B),
     radius=0.05)
rect(slide, 6.65, 4.12, 0.06, 1.98, fill=ACCENT2, radius=0.5)
text(slide, "SEKARANG", 6.95, 4.28, 3.0, 0.28, size=10.5, color=ACCENT2, bold=True)
rich(slide, [("Sound", TEXT, True, HEAD), ("Ku", ACCENT2, True, HEAD)],
     6.9, 4.52, 3.2, 0.55, size=27, spacing=0.9)
text(slide, "Streaming musik · pencarian · genre · antrean · favorit", 6.95, 5.12, 5.3, 0.3,
     size=12, color=TEXT)
text(slide, "Fokus: mendengarkan", 6.95, 5.46, 5.3, 0.3, size=11, color=MUTED, italic=True)

chip(slide, "Tanpa akun pengguna", 6.65, 6.32, 2.0, color=MUTED, size=9.5)
chip(slide, "Favorit via localStorage", 8.78, 6.32, 2.15, color=MUTED, size=9.5)
chip(slide, "Admin untuk kurasi", 11.06, 6.32, 1.9, color=MUTED, size=9.5)
footer(slide, 2)
notes(slide, "Awalnya proyek ini adalah situs manga. Karena kebutuhan presentasi berubah, seluruh domain "
             "aplikasi diganti menjadi musik. Yang dipertahankan adalah fondasi Laravel dan autentikasi admin.")

# ---------------------------------------------------------------------------
# Slide 3 — Tujuan
# ---------------------------------------------------------------------------

slide = new_slide()
header(slide, "Tujuan & ruang lingkup", "Arah proyek")

items = [
    ("01", "Streaming langsung", "Memutar audio dari katalog publik SoundCloud tanpa mengunduh atau menyimpan file lagu.", ACCENT),
    ("02", "Pengalaman modern", "Pencarian, genre, antrean, favorit, dan riwayat putar dalam satu antarmuka gelap yang responsif.", ACCENT2),
    ("03", "Kurasi admin", "Admin memilih lagu unggulan dengan menempel URL SoundCloud; metadata diambil otomatis.", CYAN),
    ("04", "Ringan & mandiri", "Laravel 12 + SQLite, tanpa framework frontend tambahan dan tanpa akun pengguna.", GREEN),
]
positions = [(0.75, 1.92), (6.7, 1.92), (0.75, 4.28), (6.7, 4.28)]
for (number, title, body, color), (x, y) in zip(items, positions):
    rect(slide, x, y, 5.55, 2.06, fill=CARD, line=BORDER, radius=0.05)
    step_badge(slide, number, x + 0.28, y + 0.3, color=color, size=0.46)
    text(slide, title, x + 0.95, y + 0.32, 4.3, 0.4, size=18, color=TEXT, bold=True, font=HEAD)
    text(slide, body, x + 0.95, y + 0.86, 4.3, 1.0, size=12.5, color=MUTED, spacing=1.2)

text(slide, "Di luar ruang lingkup: akun pengguna, unggah lagu, dan penyimpanan audio di server.",
     0.75, 6.55, 11.9, 0.3, size=11, color=DIM, italic=True)
footer(slide, 3)
notes(slide, "Empat tujuan utama: streaming, pengalaman pengguna, kurasi admin, dan aplikasi yang ringan. "
             "Tidak ada akun pengguna maupun unggah lagu; aplikasi hanya memutar katalog publik.")

# ---------------------------------------------------------------------------
# Slide 4 — Tech stack
# ---------------------------------------------------------------------------

slide = new_slide()
header(slide, "Teknologi yang dipakai", "Tech stack")

stack = [
    ("Laravel 12", "Routing, controller, Blade, validasi, cache", ACCENT),
    ("PHP 8.2", "Service scraping & proxy audio di server", ACCENT),
    ("Blade + CSS", "Tema gelap responsif tanpa framework UI", ACCENT2),
    ("JavaScript", "Player, favorit, saran pencarian, layout", ACCENT2),
    ("hls.js", "Memutar stream HLS di browser", CYAN),
    ("SoundCloud API v2", "Sumber katalog: track, artis, playlist", GOLD),
    ("SQLite", "Tabel users dan featured_tracks", GREEN),
    ("PHPUnit", "15 tes otomatis untuk alur utama", GREEN),
]
x0, y0 = 0.75, 1.92
for index, (name, role, color) in enumerate(stack):
    col = index % 4
    row = index // 4
    x = x0 + col * 3.0
    y = y0 + row * 1.82
    rect(slide, x, y, 2.78, 1.58, fill=CARD, line=BORDER, radius=0.06)
    rect(slide, x + 0.22, y + 0.24, 0.34, 0.05, fill=color, radius=0.5)
    text(slide, name, x + 0.22, y + 0.4, 2.4, 0.4, size=15.5, color=TEXT, bold=True, font=HEAD)
    text(slide, role, x + 0.22, y + 0.83, 2.4, 0.62, size=10.5, color=MUTED, spacing=1.15)

rect(slide, 0.75, 5.72, 11.85, 0.78, fill=CARD2, line=BORDER, radius=0.05)
text(slide, "Frontend memakai HTML, CSS, dan JavaScript vanilla — tanpa build step, tanpa framework tambahan.",
     1.0, 5.94, 11.3, 0.35, size=12, color=MUTED)
footer(slide, 4)
notes(slide, "Stack sengaja dibuat sederhana: Laravel untuk backend dan Blade, CSS, serta JavaScript vanilla untuk frontend. "
             "hls.js diperlukan karena SoundCloud mengirim audio dalam format HLS.")

# ---------------------------------------------------------------------------
# Slide 5 — Arsitektur
# ---------------------------------------------------------------------------

slide = new_slide()
header(slide, "Arsitektur sistem", "Cara kerja")

boxes = [
    ("Browser", "Blade · JavaScript · hls.js", 0.9, ACCENT),
    ("Laravel", "Controller · Service · Cache", 4.95, ACCENT2),
    ("SoundCloud", "API v2 · CDN audio", 9.0, GOLD),
]
for name, role, x, color in boxes:
    rect(slide, x, 2.05, 3.25, 1.5, fill=CARD, line=BORDER, radius=0.06)
    rect(slide, x, 2.05, 3.25, 0.06, fill=color, radius=0.5)
    text(slide, name, x, 2.32, 3.25, 0.5, size=22, color=TEXT, bold=True, font=HEAD,
         align=PP_ALIGN.CENTER)
    text(slide, role, x, 2.88, 3.25, 0.4, size=11, color=MUTED, align=PP_ALIGN.CENTER)

for ax, label, color in [(4.15, "HTTP / JSON", ACCENT), (8.2, "HTTPS + client_id", GOLD)]:
    arrow = slide.shapes.add_shape(MSO_SHAPE.RIGHT_ARROW, Inches(ax), Inches(2.66),
                                   Inches(0.78), Inches(0.3))
    arrow.fill.solid()
    arrow.fill.fore_color.rgb = color
    arrow.line.fill.background()
    arrow.shadow.inherit = False
    text(slide, label, ax - 0.15, 2.28, 1.1, 0.3, size=9, color=color, bold=True,
         align=PP_ALIGN.CENTER)

rect(slide, 0.9, 4.02, 11.5, 0.72, fill=CARD2, line=BORDER, radius=0.05)
text(slide, "Jalur audio", 1.15, 4.17, 1.6, 0.35, size=11.5, color=ACCENT, bold=True)
rich(slide, [
    ("/api/stream", TEXT, True, MONO), ("  →  ", DIM, False, BODY),
    ("/api/hls", TEXT, True, MONO), ("  →  ", DIM, False, BODY),
    ("/api/segment", TEXT, True, MONO), ("  →  CDN SoundCloud", MUTED, False, BODY),
], 2.85, 4.17, 9.3, 0.4, size=12)

left = [
    ("Client ID", "Diambil otomatis dari bundle JS SoundCloud, di-cache 6 jam. Bisa dioverride lewat .env."),
    ("Cache server", "Respons API dan URL audio di-cache singkat agar tidak memanggil SoundCloud terus-menerus."),
]
for index, (title, body) in enumerate(left):
    x = 0.9 + index * 5.9
    rect(slide, x, 5.15, 5.65, 1.35, fill=CARD, line=BORDER, radius=0.05)
    text(slide, title, x + 0.28, 5.34, 4.9, 0.3, size=12.5, color=CYAN, bold=True)
    text(slide, body, x + 0.28, 5.7, 5.05, 0.7, size=10.7, color=MUTED, spacing=1.15)

text(slide, "Tidak ada file audio yang disimpan di server — hanya diteruskan (proxy) ke browser.",
     0.9, 6.72, 11.5, 0.3, size=10.5, color=DIM, italic=True)
footer(slide, 5)
notes(slide, "Alurnya: browser meminta data ke Laravel, Laravel memanggil SoundCloud API memakai client_id, "
             "lalu hasilnya dinormalisasi. Untuk audio, server mem-proxy manifest HLS dan segmennya karena CDN SoundCloud tidak mengizinkan CORS.")

# ---------------------------------------------------------------------------
# Slide 6 — Alur data
# ---------------------------------------------------------------------------

slide = new_slide()
header(slide, "Mengambil data dari SoundCloud", "Integrasi API")

steps = [
    ("1", "Ambil client_id", "Scan bundle JavaScript soundcloud.com, simpan di cache 6 jam. Bisa diisi manual lewat SOUNDCLOUD_CLIENT_ID.", ACCENT),
    ("2", "Panggil API v2", "Charts, pencarian track/artis/playlist, detail track, related, user, dan resolve URL.", ACCENT2),
    ("3", "Normalisasi", "Semua respons dipetakan ke format internal: id, judul, artis, artwork, durasi, genre.", CYAN),
    ("4", "Fallback chart", "Chart per genre sering 404 → otomatis jatuh ke pencarian genre yang sama.", GOLD),
]
for index, (number, title, body, color) in enumerate(steps):
    x = 0.75 + index * 3.02
    rect(slide, x, 1.95, 2.82, 2.55, fill=CARD, line=BORDER, radius=0.06)
    step_badge(slide, number, x + 0.28, 2.14, color=color, size=0.44)
    text(slide, title, x + 0.28, 2.75, 2.3, 0.35, size=15, color=TEXT, bold=True, font=HEAD)
    text(slide, body, x + 0.28, 3.15, 2.28, 1.2, size=10.5, color=MUTED, spacing=1.15)

text(slide, "Endpoint yang dipakai", 0.75, 4.78, 4.0, 0.3, size=11, color=ACCENT, bold=True)
endpoints = [
    ("/charts", 1.25), ("/search/tracks", 1.65), ("/tracks/{id}", 1.5),
    ("/tracks/{id}/related", 2.0), ("/users/{id}", 1.4), ("/playlists/{id}", 1.65),
    ("/resolve", 1.2),
]
x = 0.75
for value, width in endpoints:
    if x + width > 12.6:
        break
    chip(slide, value, x, 5.1, width, color=TEXT, size=9.5)
    x += width + 0.14

rect(slide, 0.75, 5.75, 11.85, 0.9, fill=RGBColor(0x1E, 0x17, 0x0E), line=RGBColor(0x5A, 0x42, 0x18), radius=0.05)
text(slide, "Catatan", 0.98, 5.92, 1.0, 0.3, size=11, color=GOLD, bold=True)
text(slide, "Kalau SoundCloud mengubah struktur bundle atau mencabut client_id, isi SOUNDCLOUD_CLIENT_ID manual di .env.",
     1.85, 5.9, 10.4, 0.5, size=11.5, color=MUTED)
footer(slide, 6)
notes(slide, "SoundCloud tidak lagi membagikan client_id secara publik, jadi service mencarinya otomatis dari bundle JavaScript mereka. "
             "Kalau gagal, nilai dari .env bisa dipakai sebagai fallback. Chart genre yang 404 ditangani dengan pencarian.")

# ---------------------------------------------------------------------------
# Slide 7 — Streaming
# ---------------------------------------------------------------------------

slide = new_slide()
header(slide, "Dua jenis stream: ALLOW vs SNIP", "Streaming audio")

rect(slide, 0.75, 1.92, 5.8, 3.55, fill=CARD, line=RGBColor(0x1C, 0x4C, 0x55), radius=0.06)
rect(slide, 0.75, 1.92, 5.8, 0.07, fill=CYAN, radius=0.5)
text(slide, "ALLOW", 1.02, 2.14, 2.0, 0.4, size=13, color=CYAN, bold=True)
text(slide, "Durasi penuh", 1.02, 2.48, 4.9, 0.35, size=17, color=TEXT, bold=True, font=HEAD)
bullets(slide, [
    "Audio tersedia sebagai HLS (.m3u8).",
    "CDN segmen tidak mengizinkan CORS.",
    "Server mengambil manifest dan menulis ulang URL segmen.",
    "Browser memutar lewat hls.js dari /api/segment.",
], 1.02, 2.98, 5.15, 2.2, size=11.3, bullet_color=CYAN, gap=6)

rect(slide, 6.8, 1.92, 5.8, 3.55, fill=CARD, line=RGBColor(0x5A, 0x42, 0x18), radius=0.06)
rect(slide, 6.8, 1.92, 5.8, 0.07, fill=GOLD, radius=0.5)
text(slide, "SNIP", 7.07, 2.14, 2.0, 0.4, size=13, color=GOLD, bold=True)
text(slide, "Preview 30 detik", 7.07, 2.48, 4.9, 0.35, size=17, color=TEXT, bold=True, font=HEAD)
bullets(slide, [
    "Hanya tersedia potongan 30 detik (MP3 progressive).",
    "Batasan kebijakan dari SoundCloud, bukan bug aplikasi.",
    "UI menandainya dengan badge “30 detik” / “Preview”.",
    "Tetap diputar langsung dari URL preview.",
], 7.07, 2.98, 5.15, 2.2, size=11.3, bullet_color=GOLD, gap=6)

rect(slide, 0.75, 5.68, 11.85, 0.95, fill=CARD2, line=BORDER, radius=0.05)
rich(slide, [("Kenapa perlu proxy?  ", ACCENT, True, BODY),
             ("URL audio ditandatangani dan cepat kedaluwarsa. Server menyimpan cache singkat (2–15 menit) "
              "sekaligus menambahkan allowlist domain agar proxy tidak dipakai sembarangan.", MUTED, False, BODY)],
     1.0, 5.92, 11.35, 0.55, size=11.5, spacing=1.2)
footer(slide, 7)
notes(slide, "Ada dua perilaku stream. Track ALLOW bisa diputar penuh lewat HLS, tapi segmennya diblokir CORS sehingga harus diproxy. "
             "Track SNIP hanya memberi preview 30 detik; ini keterbatasan SoundCloud dan sudah ditandai jelas di UI.")

# ---------------------------------------------------------------------------
# Slide 8 — Fitur
# ---------------------------------------------------------------------------

slide = new_slide()
header(slide, "Fitur utama", "Product")

features = [
    ("01", "Beranda terkurasi", "Hero, trending, pilihan editor, chill & lo-fi, dan riwayat putar.", ACCENT),
    ("02", "Pencarian realtime", "Lagu, artis, dan playlist dalam satu halaman + saran di topbar.", ACCENT2),
    ("03", "Jelajah genre", "12 genre dengan chart otomatis dan fallback pencarian.", CYAN),
    ("04", "Pemutar lengkap", "Antrean, acak, ulangi, seek, volume, HLS, Media Session.", GOLD),
    ("05", "Favorit & riwayat", "Tersimpan di localStorage tanpa perlu akun.", GREEN),
    ("06", "Panel admin", "Kurasi pilihan editor dari URL track atau playlist.", RED),
]
for index, (number, title, body, color) in enumerate(features):
    col = index % 3
    row = index // 3
    x = 0.75 + col * 4.02
    y = 1.92 + row * 2.18
    rect(slide, x, y, 3.78, 1.95, fill=CARD, line=BORDER, radius=0.06)
    text(slide, number, x + 0.28, y + 0.25, 0.9, 0.3, size=11, color=color, bold=True)
    text(slide, title, x + 0.28, y + 0.58, 3.2, 0.35, size=15.5, color=TEXT, bold=True, font=HEAD)
    text(slide, body, x + 0.28, y + 1.02, 3.2, 0.7, size=10.7, color=MUTED, spacing=1.15)

text(slide, "Semua halaman memakai satu layout, satu player, dan satu sumber data yang sama.",
     0.75, 6.6, 11.9, 0.3, size=11, color=DIM, italic=True)
footer(slide, 8)
notes(slide, "Enam fitur utama. Tekankan bahwa semuanya memakai satu player dan satu service SoundCloud, "
             "jadi pengalaman antar halaman tetap konsisten.")

# ---------------------------------------------------------------------------
# Slide 9 — Pembagian jobdesk
# ---------------------------------------------------------------------------

slide = new_slide()
header(slide, "Pembagian jobdesk 5 orang", "Tim & peran")

roles = [
    ("1", "Backend & Integrasi API", ACCENT,
     ["SoundCloudService + client_id", "Endpoint stream, HLS, segmen", "Normalisasi & cache data", "Fallback chart → pencarian"],
     "Services/SoundCloudService.php"),
    ("2", "Frontend & UI/UX", ACCENT2,
     ["Layout, tema, semua halaman", "Komponen kartu & responsive", "Saran pencarian realtime", "Empty & loading state"],
     "resources/views + public/css"),
    ("3", "Player & Streaming", CYAN,
     ["Antrean, seek, volume, shuffle", "HLS via hls.js + preview", "Media Session & shortcut", "Queue drawer + error state"],
     "public/js/player.js"),
    ("4", "Admin & Database", GOLD,
     ["Auth admin + dashboard", "Migration & model FeaturedTrack", "CRUD pilihan editor", "Seeder, validasi, resolve URL"],
     "Controllers/Admin + migrations"),
    ("5", "QA, Docs & Deploy", GREEN,
     ["15 tes PHPUnit + QA browser", "README & .env.example", "Deployment + migrate --seed", "Materi presentasi & demo"],
     "tests + README + docs/presentation"),
]

top = [(0.75, 1.9), (4.79, 1.9), (8.83, 1.9)]
bottom = [(0.75, 4.18), (6.78, 4.18)]
layout = []
for index, (x, y) in enumerate(top):
    layout.append((x, y, 3.78))
for index, (x, y) in enumerate(bottom):
    layout.append((x, y, 5.82))

for (number, title, color, items, file_ref), (x, y, width) in zip(roles, layout):
    rect(slide, x, y, width, 2.12, fill=CARD, line=BORDER, radius=0.055)
    rect(slide, x, y, width, 0.055, fill=color, radius=0.5)
    step_badge(slide, number, x + 0.24, y + 0.2, color=color, size=0.36)
    text(slide, title, x + 0.72, y + 0.22, width - 0.95, 0.34, size=13.5,
         color=TEXT, bold=True, font=HEAD)
    if width > 4:
        # dua kolom untuk kartu lebar di baris bawah
        for item_index, item in enumerate(items):
            col = item_index % 2
            row = item_index // 2
            bullets(slide, [item], x + 0.24 + col * 2.85, y + 0.72 + row * 0.4,
                    width / 2 - 0.3, 0.36, size=9.6, gap=0, bullet_color=color)
    else:
        bullets(slide, items, x + 0.24, y + 0.72, width - 0.45, 1.0, size=9.4,
                gap=2, bullet_color=color)
    text(slide, file_ref, x + 0.24, y + 1.83, width - 0.45, 0.24, size=7.8,
         color=DIM, font=MONO)

text(slide, "Setiap orang wajib bisa menjalankan proyek dan menjelaskan bagiannya saat demo.",
     0.75, 6.55, 11.9, 0.3, size=10.5, color=DIM, italic=True)
footer(slide)
notes(slide, "Pembagian lima peran mengikuti arsitektur aplikasi: backend/API, frontend, player/streaming, "
             "admin/database, lalu QA dan dokumentasi. Setiap peran punya file utama yang jelas sehingga "
             "pekerjaan tidak saling tumpang tindih. Saat presentasi, masing-masing menjelaskan bagiannya.")

# ---------------------------------------------------------------------------
# Slide 10 — Timeline & kolaborasi
# ---------------------------------------------------------------------------

slide = new_slide()
header(slide, "Timeline & alur kolaborasi", "Tim & peran")

weeks = [
    ("MINGGU 1", "Fondasi", ["Setup repo & environment", "Service SoundCloud", "Layout + tema dasar"], "Peran 1 · 2", ACCENT),
    ("MINGGU 2", "Fitur inti", ["Halaman & pencarian", "Player dasar", "Admin CRUD"], "Peran 2 · 3 · 4", ACCENT2),
    ("MINGGU 3", "Streaming & polish", ["HLS proxy & preview", "Favorit + riwayat", "Responsive & bugfix"], "Peran 1 · 2 · 3", CYAN),
    ("MINGGU 4", "QA & presentasi", ["Tes + QA browser", "Dokumentasi & bugfix", "Latihan demo"], "Peran 5 + semua", GREEN),
]
for index, (week, phase, items, who, color) in enumerate(weeks):
    x = 0.75 + index * 2.97
    rect(slide, x, 1.9, 2.85, 2.42, fill=CARD, line=BORDER, radius=0.055)
    rect(slide, x, 1.9, 2.85, 0.055, fill=color, radius=0.5)
    text(slide, week, x + 0.22, 2.08, 2.4, 0.25, size=8.5, color=color, bold=True)
    text(slide, phase, x + 0.22, 2.36, 2.45, 0.35, size=14, color=TEXT, bold=True, font=HEAD)
    bullets(slide, items, x + 0.22, 2.82, 2.45, 0.95, size=9.2, gap=3, bullet_color=color)
    text(slide, who, x + 0.22, 3.92, 2.45, 0.25, size=8.5, color=MUTED, italic=True)

rect(slide, 0.75, 4.55, 6.25, 1.82, fill=CARD, line=BORDER, radius=0.05)
text(slide, "Serah terima antar peran", 1.0, 4.72, 5.0, 0.28, size=10, color=ACCENT, bold=True)
handoffs = [
    ("1", "↔", "3", "Kontrak JSON stream & parameter HLS", ACCENT, CYAN),
    ("2", "↔", "3", "ID elemen, markup, styling player", ACCENT2, CYAN),
    ("1", "↔", "4", "Format data hasil resolve URL", ACCENT, GOLD),
    ("5", "→", "semua", "Tes & QA sebelum merge", GREEN, MUTED),
]
for index, (a, arrow, b, body, color_a, color_b) in enumerate(handoffs):
    y = 5.1 + index * 0.3
    rich(slide, [(a, color_a, True, BODY), ("  " + arrow + "  ", DIM, False, BODY),
                 (b, color_b, True, BODY), ("   " + body, MUTED, False, BODY)],
         1.0, y, 5.8, 0.28, size=9.4)

rect(slide, 7.2, 4.55, 5.4, 1.82, fill=CARD, line=BORDER, radius=0.05)
text(slide, "Aturan kolaborasi", 7.45, 4.72, 4.9, 0.28, size=10, color=ACCENT2, bold=True)
bullets(slide, [
    "Satu fitur = satu branch + pull request.",
    "Review silang minimal 1 orang.",
    "Demo singkat tiap akhir minggu.",
    "Konflik CSS/player diselesaikan bareng.",
], 7.45, 5.06, 4.9, 1.2, size=9.2, gap=2, bullet_color=ACCENT2)

text(slide, "Target: aplikasi jalan, teruji, dan siap didemokan pada minggu keempat.",
     0.75, 6.55, 11.9, 0.3, size=10.5, color=DIM, italic=True)
footer(slide)
notes(slide, "Timeline empat minggu: fondasi, fitur inti, streaming dan polish, lalu QA serta latihan presentasi. "
             "Bagian serah terima menjelaskan titik temu antar peran supaya tidak ada pekerjaan yang menunggu. "
             "Aturan kolaborasi sederhana: branch per fitur, review silang, dan demo tiap akhir minggu.")

# ---------------------------------------------------------------------------
# Slide 11 — Beranda
# ---------------------------------------------------------------------------

slide = new_slide()
header(slide, "Beranda: katalog yang hidup", "Tampilan")

picture(slide, "home.png", 0.75, 1.88, 7.55)
text(slide, "Screenshot beranda — hero, trending, dan pilihan editor.", 0.75, 6.28, 7.5, 0.3,
     size=9.5, color=DIM, italic=True)

bullets(slide, [
    "Hero menampilkan track unggulan dengan tombol putar.",
    "Trending diambil dari chart SoundCloud.",
    "Pilihan editor berasal dari tabel featured_tracks.",
    "Section chill & lo-fi memakai chart dengan fallback.",
    "Riwayat putar muncul lagi saat pengguna kembali.",
], 8.6, 1.95, 4.0, 2.6, size=11.5, gap=9)

rect(slide, 8.6, 4.65, 3.95, 1.55, fill=CARD, line=BORDER, radius=0.05)
text(slide, "31 kartu lagu", 8.85, 4.83, 3.4, 0.4, size=20, color=TEXT, bold=True, font=HEAD)
text(slide, "dimuat di beranda dari beberapa section sekaligus.", 8.85, 5.27, 3.4, 0.6,
     size=10.5, color=MUTED, spacing=1.15)
footer(slide, 9)
notes(slide, "Di beranda ada hero, trending, pilihan editor, chill, dan riwayat. "
             "Semuanya dirender server-side, lalu player mengambil stream saat tombol putar ditekan.")

# ---------------------------------------------------------------------------
# Slide 10 — Player
# ---------------------------------------------------------------------------

slide = new_slide()
header(slide, "Pemutar musik", "Tampilan")

picture(slide, "player.png", 0.75, 1.88, 7.55)
text(slide, "Screenshot player — track ALLOW diputar penuh (durasi 3:53).", 0.75, 6.28, 7.5, 0.3,
     size=9.5, color=DIM, italic=True)

bullets(slide, [
    "Antrean bisa dibuka, dipilih, dan dihapus.",
    "Acak serta ulangi (semua / satu lagu).",
    "Seek, volume, dan mute dengan state yang tersimpan.",
    "HLS otomatis; preview 30 detik diberi badge.",
    "Media Session + shortcut keyboard (Space, Shift + ←/→).",
], 8.6, 1.95, 4.0, 2.6, size=11.5, gap=9)

rect(slide, 8.6, 4.62, 3.95, 1.58, fill=RGBColor(0x19, 0x14, 0x29), line=RGBColor(0x4A, 0x35, 0x6B), radius=0.05)
text(slide, "Satu elemen audio", 8.85, 4.8, 3.4, 0.35, size=13, color=ACCENT2, bold=True)
text(slide, "Semua track memakai elemen <audio> yang sama; sumber diganti saat lagu berganti.",
     8.85, 5.15, 3.4, 0.85, size=10.5, color=MUTED, spacing=1.15)
footer(slide, 10)
notes(slide, "Player mendukung antrean, acak, ulangi, seek, dan volume. Untuk track ALLOW, hls.js memutar stream lewat proxy server. "
             "Saat track SNIP, durasi otomatis 30 detik dan badge preview muncul.")

# ---------------------------------------------------------------------------
# Slide 11 — Pencarian & genre
# ---------------------------------------------------------------------------

slide = new_slide()
header(slide, "Pencarian & jelajah genre", "Tampilan")

picture(slide, "search.png", 0.75, 1.9, 7.05)
picture(slide, "genre.png", 8.1, 1.9, 4.55)
text(slide, "Hasil pencarian “lofi”.", 0.75, 5.95, 7.0, 0.3, size=9.5, color=DIM, italic=True)
text(slide, "Halaman genre Chill / Lo-fi.", 8.1, 4.57, 4.5, 0.3, size=9.5, color=DIM, italic=True)
bullets(slide, [
    "Satu pencarian untuk track, artis, dan playlist.",
    "Saran realtime di topbar dengan debounce.",
    "12 genre siap pakai di config/music.php.",
    "Chart genre kosong → fallback ke searchTracks().",
], 8.1, 4.95, 4.5, 1.6, size=10.7, gap=6)
footer(slide, 11)
notes(slide, "Pencarian mengembalikan tiga tipe hasil sekaligus. Halaman genre memakai chart, dan kalau chart tidak tersedia "
             "otomatis memakai hasil pencarian genre tersebut.")

# ---------------------------------------------------------------------------
# Slide 12 — Admin
# ---------------------------------------------------------------------------

slide = new_slide()
header(slide, "Panel admin untuk kurasi", "Tampilan")

picture(slide, "admin.png", 0.75, 1.88, 7.55)
text(slide, "Screenshot daftar “Pilihan editor”.", 0.75, 6.25, 7.5, 0.3, size=9.5, color=DIM, italic=True)

steps = [
    ("1", "Tempel URL", "Track atau playlist SoundCloud."),
    ("2", "Resolve", "Server membaca metadata asli."),
    ("3", "Atur", "Urutan, status tampil, atau hapus."),
]
y = 1.95
for number, title, body in steps:
    step_badge(slide, number, 8.62, y + 0.02, color=ACCENT, size=0.38)
    text(slide, title, 9.18, y, 3.2, 0.32, size=14, color=TEXT, bold=True, font=HEAD)
    text(slide, body, 9.18, y + 0.34, 3.3, 0.55, size=10.7, color=MUTED, spacing=1.1)
    y += 1.12

rect(slide, 8.62, 5.35, 3.95, 0.9, fill=CARD, line=BORDER, radius=0.05)
text(slide, "Login demo", 8.85, 5.48, 3.4, 0.28, size=9.5, color=DIM, bold=True)
text(slide, "admin@soundku.test  ·  password", 8.85, 5.75, 3.4, 0.3, size=10.5,
     color=MUTED, font=MONO)
footer(slide, 12)
notes(slide, "Admin tidak mengunggah file. Cukup menempel URL SoundCloud, server melakukan resolve, "
             "lalu metadata track/playlist disimpan ke featured_tracks dan tampil di beranda.")

# ---------------------------------------------------------------------------
# Slide 13 — Struktur proyek
# ---------------------------------------------------------------------------

slide = new_slide()
header(slide, "Struktur proyek", "Kode")

rect(slide, 0.75, 1.88, 7.15, 4.85, fill=RGBColor(0x10, 0x0F, 0x18), line=BORDER, radius=0.04)
code_lines = [
    ("app/", ACCENT2, True),
    ("├── Services/SoundCloudService.php", TEXT, False),
    ("├── Http/Controllers/", ACCENT2, True),
    ("│   ├── HomeController.php", MUTED, False),
    ("│   ├── SearchController.php", MUTED, False),
    ("│   ├── TrackController.php", MUTED, False),
    ("│   └── Api/MusicController.php", MUTED, False),
    ("├── Models/FeaturedTrack.php", TEXT, False),
    ("└── Support/helpers.php", TEXT, False),
    ("config/music.php", ACCENT2, True),
    ("public/", ACCENT2, True),
    ("├── css/app.css", MUTED, False),
    ("├── js/app.js", MUTED, False),
    ("└── js/player.js", MUTED, False),
    ("resources/views/", ACCENT2, True),
    ("tests/Feature/MusicSiteTest.php", TEXT, False),
]
box = slide.shapes.add_textbox(Inches(1.02), Inches(2.08), Inches(6.6), Inches(4.45))
tf = box.text_frame
tf.word_wrap = True
tf.margin_left = 0
tf.margin_top = 0
for index, (line, color, bold) in enumerate(code_lines):
    paragraph = tf.paragraphs[0] if index == 0 else tf.add_paragraph()
    paragraph.space_after = Pt(3)
    paragraph.line_spacing = 1.05
    run = paragraph.add_run()
    run.text = line
    run.font.name = MONO
    run.font.size = Pt(10.5)
    run.font.color.rgb = color
    run.font.bold = bold

notes_cards = [
    ("Service terpusat", "Semua komunikasi SoundCloud ada di satu tempat: mudah diuji dan di-cache.", ACCENT),
    ("Controller tipis", "Controller hanya memvalidasi input dan merender view.", ACCENT2),
    ("View modular", "Partial untuk kartu, baris track, player, dan pagination.", CYAN),
]
y = 1.92
for title, body, color in notes_cards:
    rect(slide, 8.25, y, 4.35, 1.45, fill=CARD, line=BORDER, radius=0.05)
    rect(slide, 8.27, y + 0.18, 0.05, 1.09, fill=color, radius=0.5)
    text(slide, title, 8.55, y + 0.2, 3.8, 0.32, size=13, color=TEXT, bold=True, font=HEAD)
    text(slide, body, 8.55, y + 0.6, 3.8, 0.7, size=10.5, color=MUTED, spacing=1.15)
    y += 1.65
footer(slide, 13)
notes(slide, "Struktur dibuat mengikuti pola Laravel: service untuk integrasi eksternal, controller tipis, "
             "view modular, dan helper untuk format durasi serta jumlah pemutaran.")

# ---------------------------------------------------------------------------
# Slide 16 — Database
# ---------------------------------------------------------------------------

slide = new_slide()
header(slide, "Database & penyimpanan data", "Data")

schema_left = [
    ("id", "bigint · primary key"),
    ("name", "varchar"),
    ("email", "varchar · unique"),
    ("password", "varchar · hash"),
    ("remember_token", "varchar · nullable"),
    ("timestamps", "created_at, updated_at"),
]
schema_right = [
    ("id", "bigint · primary key"),
    ("soundcloud_id", "varchar · unique"),
    ("title", "varchar"),
    ("artist", "varchar"),
    ("artist_id", "varchar"),
    ("artwork_url", "text · nullable"),
    ("permalink_url", "text"),
    ("genre", "varchar · nullable"),
    ("duration", "integer · detik"),
    ("position", "integer · urutan"),
    ("is_active", "boolean · tampil?"),
    ("timestamps", "created_at, updated_at"),
]


def schema_card(x, y, w, h, name, rows, color, note=None):
    rect(slide, x, y, w, h, fill=CARD, line=BORDER, radius=0.045)
    rect(slide, x, y, w, 0.055, fill=color, radius=0.5)
    text(slide, name, x + 0.28, y + 0.16, w - 0.5, 0.32, size=14, color=TEXT,
         bold=True, font=MONO)
    text(slide, "KOLOM", x + 0.28, y + 0.55, 2.0, 0.22, size=7.8, color=DIM, bold=True)
    text(slide, "TIPE / CATATAN", x + 2.25, y + 0.55, 2.8, 0.22, size=7.8, color=DIM, bold=True)
    start = y + 0.8
    for index, (field, kind) in enumerate(rows):
        row_y = start + index * 0.215
        text(slide, field, x + 0.28, row_y, 1.9, 0.21, size=9.2, color=TEXT, font=MONO)
        text(slide, kind, x + 2.25, row_y, w - 2.5, 0.21, size=8.6, color=MUTED, font=MONO)
        if index < len(rows) - 1:
            rect(slide, x + 0.24, row_y + 0.195, w - 0.48, 0.008, fill=BORDER, radius=0,
                 shape=MSO_SHAPE.RECTANGLE)
    if note:
        text(slide, note, x + 0.28, y + h - 0.38, w - 0.5, 0.25, size=8.6, color=DIM, italic=True)


schema_card(0.75, 1.9, 5.8, 3.52, "users", schema_left, ACCENT,
            "Dipakai hanya untuk login admin.")
schema_card(6.78, 1.9, 5.82, 3.52, "featured_tracks", schema_right, ACCENT2)

storage = [
    ("SQLite (default)", "Bisa diganti MySQL / PostgreSQL lewat .env.", ACCENT),
    ("Favorit & riwayat", "Disimpan di localStorage, bukan database.", ACCENT2),
    ("Katalog musik", "Tidak disimpan; diambil live + cache singkat.", CYAN),
]
for index, (title, body, color) in enumerate(storage):
    x = 0.75 + index * 4.04
    rect(slide, x, 5.62, 3.78, 0.86, fill=CARD2, line=BORDER, radius=0.05)
    text(slide, title, x + 0.22, 5.74, 3.4, 0.26, size=10.2, color=color, bold=True)
    text(slide, body, x + 0.22, 6.04, 3.4, 0.35, size=9.2, color=MUTED)

text(slide, "Tabel manga lama dihapus lewat migration drop_manga_tables. Yang tersisa hanya users dan featured_tracks.",
     0.75, 6.68, 11.9, 0.3, size=10, color=DIM, italic=True)
footer(slide)
notes(slide, "Ada dua tabel inti. users hanya untuk login admin. featured_tracks menyimpan kurasi pilihan editor: "
             "soundcloud_id unik, metadata lagu, urutan, dan status tampil. Favorit serta riwayat tidak masuk database, "
             "melainkan localStorage; katalog musik tetap di SoundCloud dan hanya di-cache sementara.")

# ---------------------------------------------------------------------------
# Slide 17 — Menjalankan
# ---------------------------------------------------------------------------

slide = new_slide()
header(slide, "Menjalankan proyek", "Setup")

commands = [
    ("1", "Install dependency", "composer install", ACCENT),
    ("2", "Siapkan environment", "cp .env.example .env\nphp artisan key:generate", ACCENT2),
    ("3", "Migrasi & seed data", "php artisan migrate --seed", CYAN),
    ("4", "Jalankan server", "php artisan serve", GREEN),
]
for index, (number, title, code, color) in enumerate(commands):
    col = index % 2
    row = index // 2
    x = 0.75 + col * 6.08
    y = 1.92 + row * 2.18
    rect(slide, x, y, 5.82, 1.95, fill=CARD, line=BORDER, radius=0.06)
    step_badge(slide, number, x + 0.28, y + 0.28, color=color, size=0.42)
    text(slide, title, x + 0.88, y + 0.3, 4.6, 0.32, size=14.5, color=TEXT, bold=True, font=HEAD)
    rect(slide, x + 0.28, y + 0.9, 5.25, 0.78, fill=RGBColor(0x0F, 0x0E, 0x17), line=BORDER, radius=0.04)
    code_box = slide.shapes.add_textbox(Inches(x + 0.45), Inches(y + 1.02), Inches(4.95), Inches(0.6))
    tf = code_box.text_frame
    tf.word_wrap = True
    for li, line in enumerate(code.split("\n")):
        paragraph = tf.paragraphs[0] if li == 0 else tf.add_paragraph()
        run = paragraph.add_run()
        run.text = line
        run.font.name = MONO
        run.font.size = Pt(10.5)
        run.font.color.rgb = RGBColor(0xC9, 0xC3, 0xE8)

rect(slide, 0.75, 6.42, 11.85, 0.5, fill=CARD2, line=BORDER, radius=0.05)
rich(slide, [("Buka ", MUTED, False, BODY), ("http://localhost:8000", TEXT, True, MONO),
             ("  ·  admin: admin@soundku.test / password", MUTED, False, BODY)],
     1.0, 6.52, 11.3, 0.3, size=10.5)
footer(slide, 14)
notes(slide, "Empat langkah menjalankan proyek. Seeder membuat akun admin dan mengisi pilihan editor dengan track trending "
             "bila internet tersedia. Database default memakai SQLite.")

# ---------------------------------------------------------------------------
# Slide 15 — Pengujian
# ---------------------------------------------------------------------------

slide = new_slide()
header(slide, "Pengujian & hasil", "Quality")

stats = [("15", "tes lulus", ACCENT), ("41", "assertion", ACCENT2), ("8", "halaman / endpoint", CYAN), ("0", "error console", GREEN)]
for index, (value, label, color) in enumerate(stats):
    x = 0.75 + index * 3.02
    rect(slide, x, 1.9, 2.8, 1.35, fill=CARD, line=BORDER, radius=0.06)
    big_number(slide, value, x, 2.02, 2.8, color=color, size=38)
    text(slide, label, x, 2.78, 2.8, 0.3, size=10.5, color=MUTED, align=PP_ALIGN.CENTER)

checks = [
    "Semua halaman utama merender tanpa error.",
    "Halaman admin terlindungi autentikasi.",
    "CRUD pilihan editor: tambah, ubah, toggle, hapus.",
    "Stream JSON mengembalikan tipe dan URL yang benar.",
    "Manifest HLS ditulis ulang ke /api/segment.",
    "Segmen audio merespons 200 dengan audio/mp4.",
]
rect(slide, 0.75, 3.52, 7.35, 2.95, fill=CARD, line=BORDER, radius=0.05)
text(slide, "Skenario yang diverifikasi", 1.0, 3.72, 5.0, 0.3, size=11, color=ACCENT, bold=True)
for index, item in enumerate(checks):
    col = index % 2
    row = index // 2
    x = 1.0 + col * 3.6
    y = 4.12 + row * 0.74
    text(slide, "✓", x, y, 0.25, 0.3, size=11, color=GREEN, bold=True)
    text(slide, item, x + 0.28, y, 3.2, 0.65, size=10.3, color=MUTED, spacing=1.1)

rect(slide, 8.35, 3.52, 4.25, 2.95, fill=RGBColor(0x19, 0x14, 0x29), line=RGBColor(0x4A, 0x35, 0x6B), radius=0.05)
text(slide, "Uji di browser", 8.6, 3.72, 3.7, 0.3, size=11, color=ACCENT2, bold=True)
text(slide, "Playback nyata", 8.6, 4.1, 3.7, 0.32, size=15, color=TEXT, bold=True, font=HEAD)
text(slide, "Track ALLOW diputar penuh: durasi terbaca 3:53, posisi berjalan, antrean 1 lagu.",
     8.6, 4.5, 3.7, 0.75, size=10.5, color=MUTED, spacing=1.15)
text(slide, "Preview SNIP", 8.6, 5.4, 3.7, 0.32, size=15, color=TEXT, bold=True, font=HEAD)
text(slide, "Badge muncul dan durasi menyesuaikan 0:29.", 8.6, 5.78, 3.7, 0.5, size=10.5,
     color=MUTED)
footer(slide, 15)
notes(slide, "15 tes otomatis dengan 41 assertion semuanya lulus. Tes memakai mock SoundCloud agar tidak bergantung internet. "
             "Selain itu playback nyata sudah dicek di browser: stream ALLOW penuh dan preview SNIP 30 detik.")

# ---------------------------------------------------------------------------
# Slide 16 — Tantangan
# ---------------------------------------------------------------------------

slide = new_slide()
header(slide, "Tantangan & solusi", "Pelajaran")

rows = [
    ("CORS pada segmen HLS", "Server mem-proxy manifest dan segmen lewat /api/segment dengan allowlist domain.", CYAN),
    ("Client ID sering berubah", "Auto-discovery dari bundle JS + cache 6 jam + override lewat .env.", ACCENT),
    ("Chart genre sering 404", "Fallback otomatis ke searchTracks() memakai query genre yang sama.", GOLD),
    ("Track SNIP hanya 30 detik", "Badge preview di UI dan pilih track ALLOW untuk demo durasi penuh.", ACCENT2),
]
for index, (problem, solution, color) in enumerate(rows):
    y = 1.92 + index * 1.22
    rect(slide, 0.75, y, 11.85, 1.05, fill=CARD, line=BORDER, radius=0.05)
    rect(slide, 0.77, y + 0.17, 0.05, 0.71, fill=color, radius=0.5)
    text(slide, problem, 1.05, y + 0.32, 3.55, 0.45, size=12.5, color=TEXT, bold=True)
    text(slide, solution, 4.95, y + 0.26, 7.4, 0.6, size=11, color=MUTED, spacing=1.12)
    chip(slide, f"0{index + 1}", 11.95, y + 0.35, 0.45, color=color, size=9, fill=CARD2)

footer(slide, 16)
notes(slide, "Empat tantangan terbesar dan solusinya. Yang paling teknis adalah CORS segmen HLS dan client_id yang berputar. "
             "Sisanya lebih ke ekspektasi pengguna terhadap preview 30 detik.")

# ---------------------------------------------------------------------------
# Slide 17 — Penutup
# ---------------------------------------------------------------------------

slide = new_slide()
decorative_orb(slide, 8.4, -1.8, 6.8, RGBColor(0x17, 0x10, 0x2E))
decorative_orb(slide, 2.0, 5.4, 5.2, RGBColor(0x1E, 0x0E, 0x20))
equalizer(slide, 0.9, 5.25, count=28, bar_w=0.15, gap=0.2, max_h=1.7)

text(slide, "DEMO & DISKUSI", 0.9, 0.95, 5.5, 0.3, size=12, color=ACCENT, bold=True)
text(slide, "Terima kasih.", 0.85, 1.55, 8.5, 0.95, size=54, color=TEXT, bold=True, font=HEAD)
text(slide, "SoundKu — streaming musik dari katalog publik SoundCloud.",
     0.9, 2.65, 8.6, 0.45, size=19, color=MUTED)

chips = [("localhost:8000", 2.1, ACCENT), ("admin@soundku.test", 2.55, ACCENT2), ("Tanya jawab", 1.65, CYAN)]
x = 0.9
for value, width, color in chips:
    chip(slide, value, x, 3.55, width, color=color, size=11, fill=CARD)
    x += width + 0.2

text(slide, "Laravel 12  ·  PHP 8.2  ·  SoundCloud API v2  ·  hls.js", 0.9, 4.3, 8.0, 0.3,
     size=11.5, color=DIM)
text(slide, "Alvin Yafiq Firas · 2026", 0.9, 6.45, 5.0, 0.3, size=11, color=MUTED)
footer(slide, 17)
notes(slide, "Tutup dengan demo singkat: buka beranda, putar satu track, tunjukkan pencarian dan panel admin. "
             "Lalu buka sesi tanya jawab.")

# ---------------------------------------------------------------------------

prs.save(OUT)
print("saved", OUT)
print("slides", len(prs.slides))
