#!/usr/bin/env python3
"""
Generate screenshot.png for NutriVitaX Pro WordPress theme.
1200x900 pixels (WordPress standard 4:3 ratio).
BioLab Luxe design system — rendered with PyCairo for professional text quality.

Cairo provides: sub-pixel anti-aliasing, proper font hinting,
smooth Bezier curves, and accurate text metrics.
"""

import cairo
import math
import os

# ── Config ──────────────────────────────────────────────────────────────────
W, H = 1200, 900
THEME_DIR = "/home/z/lampstack/wordpress/wp-content/themes/nutrivitax-pro"
OUTPUT = os.path.join(THEME_DIR, "screenshot.png")

# ── BioLab Luxe palette (0-1 float tuples for Cairo) ───────────────────────
def c(r, g, b):
    return (r / 255.0, g / 255.0, b / 255.0)

C_PRIMARY     = c(26, 107, 58)
C_PRIMARY_LT  = c(35, 145, 78)
C_SECONDARY   = c(46, 204, 113)
C_ACCENT      = c(244, 169, 0)
C_DARK        = c(18, 36, 58)
C_LIGHT       = c(240, 249, 244)
C_WHITE       = c(255, 255, 255)
C_GRAY        = c(140, 148, 160)
C_GRAY_DIM    = c(100, 108, 120)
C_LIGHT_GRAY  = c(229, 231, 235)
C_DANGER      = c(239, 68, 68)
C_NAV_BG      = c(10, 22, 40)
C_FOOT_BG     = c(10, 20, 38)
C_FOOT_DARK   = c(4, 10, 22)
C_GRID        = c(30, 48, 70)
C_HERO_TEXT   = c(180, 200, 210)
C_LINK_TEXT   = c(170, 180, 195)
C_SUBTLE      = c(80, 90, 105)

# ── Font families (Cairo uses family names, not file paths) ────────────────
# Carlito = Calibri-compatible, clean modern sans-serif
F_HEAD  = "Carlito"     # Bold headings
F_BODY  = "Carlito"     # Regular body text
F_MONO  = "Liberation Mono"
F_SERIF = "Nimbus Roman"

# ── Helper functions ───────────────────────────────────────────────────────

def rrect(ctx, x, y, w, h, r, fill=None, stroke=None, sw=1.0):
    """Rounded rectangle via Bezier arcs."""
    r = min(r, w / 2, h / 2)
    ctx.new_sub_path()
    ctx.move_to(x + r, y)
    ctx.arc(x + w - r, y + r, r, -math.pi / 2, 0)
    ctx.arc(x + w - r, y + h - r, r, 0, math.pi / 2)
    ctx.arc(x + r, y + h - r, r, math.pi / 2, math.pi)
    ctx.arc(x + r, y + r, r, math.pi, 1.5 * math.pi)
    ctx.close_path()
    if fill:
        ctx.set_source_rgb(*fill)
        ctx.fill_preserve()
    if stroke:
        ctx.set_source_rgb(*stroke)
        ctx.set_line_width(sw)
        ctx.stroke()


def grad_v(ctx, x, y, w, h, c1, c2):
    """Vertical linear gradient fill."""
    p = cairo.LinearGradient(x, y, x, y + h)
    p.add_color_stop_rgb(0, *c1)
    p.add_color_stop_rgb(1, *c2)
    ctx.set_source(p)
    ctx.rectangle(x, y, w, h)
    ctx.fill()


def grad_h(ctx, x, y, w, h, c1, c2):
    """Horizontal linear gradient fill."""
    p = cairo.LinearGradient(x, y, x + w, y)
    p.add_color_stop_rgb(0, *c1)
    p.add_color_stop_rgb(1, *c2)
    ctx.set_source(p)
    ctx.rectangle(x, y, w, h)
    ctx.fill()


def txt_c(ctx, text, cx, y, size, color, font=F_HEAD, bold=True):
    """Centered text — returns text_extents."""
    ctx.select_font_face(font, cairo.FONT_SLANT_NORMAL,
                         cairo.FONT_WEIGHT_BOLD if bold else cairo.FONT_WEIGHT_NORMAL)
    ctx.set_font_size(size)
    e = ctx.text_extents(text)
    ctx.move_to(cx - e.width / 2 - e.x_bearing, y + e.height * 0.75 - e.y_bearing)
    ctx.set_source_rgb(*color)
    ctx.show_text(text)
    return e


def txt(ctx, text, x, y, size, color, font=F_BODY, bold=False):
    """Left-aligned text — returns text_extents."""
    ctx.select_font_face(font, cairo.FONT_SLANT_NORMAL,
                         cairo.FONT_WEIGHT_BOLD if bold else cairo.FONT_WEIGHT_NORMAL)
    ctx.set_font_size(size)
    ctx.move_to(x, y)
    ctx.set_source_rgb(*color)
    ctx.show_text(text)
    return ctx.text_extents(text)


def txt_w(ctx, text, size, font=F_HEAD, bold=True):
    """Measure text width without drawing."""
    ctx.select_font_face(font, cairo.FONT_SLANT_NORMAL,
                         cairo.FONT_WEIGHT_BOLD if bold else cairo.FONT_WEIGHT_NORMAL)
    ctx.set_font_size(size)
    return ctx.text_extents(text).width


def draw_star(ctx, cx, cy, r, color):
    """5-pointed star."""
    ctx.set_source_rgb(*color)
    ctx.new_sub_path()
    for i in range(10):
        a = math.radians(i * 36 - 90)
        rad = r if i % 2 == 0 else r * 0.4
        px = cx + rad * math.cos(a)
        py = cy + rad * math.sin(a)
        if i == 0:
            ctx.move_to(px, py)
        else:
            ctx.line_to(px, py)
    ctx.close_path()
    ctx.fill()


def draw_pill(ctx, cx, cy, body_color):
    """Supplement pill bottle icon."""
    # Cap
    rrect(ctx, cx - 11, cy - 28, 22, 16, 3, fill=C_NAV_BG)
    # Rim
    rrect(ctx, cx - 14, cy - 13, 28, 8, 2, fill=C_NAV_BG)
    # Body
    rrect(ctx, cx - 18, cy - 6, 36, 40, 8, fill=body_color)
    # Label lines
    ctx.set_source_rgb(*C_WHITE)
    ctx.set_line_width(2)
    ctx.move_to(cx - 10, cy + 10)
    ctx.line_to(cx + 10, cy + 10)
    ctx.stroke()
    ctx.set_line_width(1.2)
    ctx.move_to(cx - 8, cy + 18)
    ctx.line_to(cx + 8, cy + 18)
    ctx.stroke()


def draw_arrow(ctx, x, y, size, color):
    """Small right-pointing chevron arrow."""
    ctx.set_source_rgb(*color)
    ctx.new_sub_path()
    ctx.move_to(x, y)
    ctx.line_to(x + size, y + size / 2)
    ctx.line_to(x, y + size)
    ctx.close_path()
    ctx.fill()


def draw_social(ctx, cx, cy, r, label):
    """Social media circle icon."""
    ctx.set_source_rgb(*C_GRAY_DIM)
    ctx.set_line_width(1)
    ctx.arc(cx, cy, r, 0, 2 * math.pi)
    ctx.stroke()
    ctx.select_font_face(F_BODY, cairo.FONT_SLANT_NORMAL, cairo.FONT_WEIGHT_BOLD)
    ctx.set_font_size(10)
    e = ctx.text_extents(label)
    ctx.move_to(cx - e.width / 2 - e.x_bearing, cy - e.height / 2 - e.y_bearing)
    ctx.set_source_rgb(*C_LIGHT_GRAY)
    ctx.show_text(label)


def draw_cert(ctx, cx, cy, r, label):
    """Certification badge circle with checkmark and label."""
    ctx.set_source_rgb(*C_PRIMARY)
    ctx.set_line_width(1.8)
    ctx.arc(cx, cy, r, 0, 2 * math.pi)
    ctx.stroke()
    ctx.set_source_rgb(*C_SECONDARY)
    ctx.set_line_width(0.8)
    ctx.arc(cx, cy, r - 4, 0, 2 * math.pi)
    ctx.stroke()
    # Check
    ctx.select_font_face(F_BODY, cairo.FONT_SLANT_NORMAL, cairo.FONT_WEIGHT_BOLD)
    ctx.set_font_size(14)
    e = ctx.text_extents("\u2713")
    ctx.move_to(cx - e.width / 2 - e.x_bearing, cy - e.height / 2 - e.y_bearing)
    ctx.set_source_rgb(*C_SECONDARY)
    ctx.show_text("\u2713")
    # Label
    ctx.select_font_face(F_BODY, cairo.FONT_SLANT_NORMAL, cairo.FONT_WEIGHT_NORMAL)
    ctx.set_font_size(8.5)
    e2 = ctx.text_extents(label)
    ctx.move_to(cx - e2.width / 2 - e2.x_bearing, cy + r + 10)
    ctx.set_source_rgb(*C_GRAY)
    ctx.show_text(label)


def draw_payment(ctx, x, y, label, bg_color):
    """Payment method badge."""
    ctx.select_font_face(F_BODY, cairo.FONT_SLANT_NORMAL, cairo.FONT_WEIGHT_BOLD)
    ctx.set_font_size(8)
    e = ctx.text_extents(label)
    pw = e.width + 14
    rrect(ctx, x, y, pw, 20, 4, fill=bg_color)
    txt_c(ctx, label, x + pw / 2, y + 4, 8, C_WHITE, F_BODY, bold=True)
    return pw


# ═══════════════════════════════════════════════════════════════════════════
# MAIN RENDERER
# ═══════════════════════════════════════════════════════════════════════════

def create_screenshot():
    surface = cairo.ImageSurface(cairo.FORMAT_ARGB32, W, H)
    ctx = cairo.Context(surface)

    # White background
    ctx.set_source_rgb(*C_WHITE)
    ctx.paint()

    # ═══════════════════════════════════════════════════════════════════
    # 1. HEADER / NAV BAR  (y: 0 → 56)
    # ═══════════════════════════════════════════════════════════════════
    ctx.set_source_rgb(*C_NAV_BG)
    ctx.rectangle(0, 0, W, 56)
    ctx.fill()

    # Bottom accent line on nav
    p = cairo.LinearGradient(0, 55, W, 55)
    p.add_color_stop_rgb(0, *C_PRIMARY)
    p.add_color_stop_rgb(1, *C_SECONDARY)
    ctx.set_source(p)
    ctx.set_line_width(2)
    ctx.move_to(0, 55.5)
    ctx.line_to(W, 55.5)
    ctx.stroke()

    # Logo mark — rounded pill
    rrect(ctx, 24, 16, 24, 24, 12, fill=C_SECONDARY)
    txt_c(ctx, "N", 36, 17, 15, C_NAV_BG, F_HEAD, bold=True)

    # Logo text
    txt(ctx, "NutriVitaX", 56, 36, 16, C_WHITE, F_HEAD, bold=True)
    nvx_w = txt_w(ctx, "NutriVitaX", 16, F_HEAD, True)
    txt(ctx, "PRO", 56 + nvx_w + 5, 37, 10, C_ACCENT, F_HEAD, bold=True)

    # Nav links — measure & right-align
    nav_items = ["Accueil", "Boutique", "Quiz Sante", "Stack Builder", "Blog"]
    spacing = 24
    total_nw = sum(txt_w(ctx, it, 12, F_BODY) + spacing for it in nav_items) - spacing
    nx = W - 60 - total_nw
    for item in nav_items:
        txt(ctx, item, nx, 34, 12, C_LIGHT_GRAY, F_BODY)
        nx += txt_w(ctx, item, 12, F_BODY) + spacing

    # Search icon (magnifying glass)
    ctx.set_source_rgb(*C_GRAY_DIM)
    ctx.set_line_width(1.5)
    ctx.arc(W - 78, 28, 7, 0, 2 * math.pi)
    ctx.stroke()
    ctx.set_line_width(2)
    ctx.move_to(W - 73, 33)
    ctx.line_to(W - 68, 38)
    ctx.stroke()

    # Cart badge
    rrect(ctx, W - 52, 18, 20, 20, 10, fill=C_ACCENT)
    txt_c(ctx, "3", W - 42, 18, 12, C_NAV_BG, F_HEAD, bold=True)

    # ═══════════════════════════════════════════════════════════════════
    # 2. HERO SECTION  (y: 56 → 290)
    # ═══════════════════════════════════════════════════════════════════
    grad_v(ctx, 0, 56, W, 234, C_DARK, (20, 90, 50))

    # Subtle grid
    ctx.set_source_rgb(*C_GRID)
    ctx.set_line_width(0.3)
    for gx in range(0, W + 1, 60):
        ctx.move_to(gx, 56)
        ctx.line_to(gx, 290)
        ctx.stroke()
    for gy in range(56, 291, 60):
        ctx.move_to(0, gy)
        ctx.line_to(W, gy)
        ctx.stroke()

    # Decorative radial glow (right)
    rg = cairo.RadialGradient(W - 120, 140, 10, W - 120, 140, 220)
    rg.add_color_stop_rgba(0, *C_SECONDARY, 0.08)
    rg.add_color_stop_rgba(1, *C_SECONDARY, 0)
    ctx.set_source(rg)
    ctx.rectangle(0, 56, W, 234)
    ctx.fill()

    # Badge pill
    badge = "THEME WOOCOMMERCE PREMIUM"
    bw = txt_w(ctx, badge, 11, F_HEAD, True) + 28
    bx = (W - bw) / 2
    rrect(ctx, bx, 78, bw, 22, 11, fill=C_SECONDARY)
    txt_c(ctx, badge, W / 2, 81, 11, C_WHITE, F_HEAD, bold=True)

    # Hero title
    t1 = "NutriVitaX"
    t2 = "Pro"
    ctx.select_font_face(F_HEAD, cairo.FONT_SLANT_NORMAL, cairo.FONT_WEIGHT_BOLD)
    ctx.set_font_size(50)
    e1 = ctx.text_extents(t1)
    e2 = ctx.text_extents(t2)
    tw = e1.width + 12 + e2.width
    tx = (W - tw) / 2
    # Text shadow
    ctx.set_source_rgba(0, 0, 0, 0.25)
    ctx.move_to(tx + 2, 131)
    ctx.show_text(t1)
    ctx.move_to(tx + e1.width + 14, 131)
    ctx.show_text(t2)
    # Main text
    txt(ctx, t1, tx, 129, 50, C_WHITE, F_HEAD, bold=True)
    txt(ctx, t2, tx + e1.width + 12, 129, 50, C_ACCENT, F_HEAD, bold=True)

    # Subtitle
    txt_c(ctx, "Design BioLab Luxe pour votre boutique de complements", W / 2, 192, 17, C_HERO_TEXT, F_BODY)
    txt_c(ctx, "alimentaires & nutraceutique premium", W / 2, 214, 17, C_HERO_TEXT, F_BODY)

    # CTA buttons
    btn1 = "Explorer la Boutique"
    btn2 = "Passer le Quiz"
    b1w = txt_w(ctx, btn1, 13, F_BODY, True) + 40
    b2w = txt_w(ctx, btn2, 13, F_BODY, True) + 40
    b1x = W / 2 - b1w - 12
    b2x = W / 2 + 12
    btn_y = 248
    btn_h = 38

    rrect(ctx, b1x, btn_y, b1w, btn_h, 10, fill=C_ACCENT)
    txt_c(ctx, btn1, b1x + b1w / 2, btn_y + 8, 13, C_NAV_BG, F_BODY, bold=True)

    rrect(ctx, b2x, btn_y, b2w, btn_h, 10, stroke=C_WHITE, sw=2)
    txt_c(ctx, btn2, b2x + b2w / 2, btn_y + 8, 13, C_WHITE, F_BODY, bold=True)

    # ═══════════════════════════════════════════════════════════════════
    # 3. TRUST BAR  (y: 290 → 328)
    # ═══════════════════════════════════════════════════════════════════
    ctx.set_source_rgb(*C_LIGHT)
    ctx.rectangle(0, 290, W, 38)
    ctx.fill()
    # Accent top line
    ctx.set_source_rgb(*C_PRIMARY)
    ctx.set_line_width(1.5)
    ctx.move_to(0, 290)
    ctx.line_to(W, 290)
    ctx.stroke()

    trust = ["\u2713 Certifie Bio", "\u2713 Livraison Gratuite 50\u20ac+",
             "\u2713 Retour 30 jours", "\u2713 Paiement Securise", "\u2713 Support 24/7"]
    sp = 28
    total_tw = sum(txt_w(ctx, it, 11, F_BODY, True) + sp for it in trust) - sp
    ttx = (W - total_tw) / 2
    for item in trust:
        txt(ctx, item, ttx, 314, 11, C_PRIMARY, F_BODY, bold=True)
        ttx += txt_w(ctx, item, 11, F_BODY, True) + sp

    # ═══════════════════════════════════════════════════════════════════
    # 4. SECTION TITLE  (y: 328 → 385)
    # ═══════════════════════════════════════════════════════════════════
    txt(ctx, "Nos Best-Sellers", 100, 354, 24, C_DARK, F_HEAD, bold=True)
    txt(ctx, "Les formulations les plus populaires, validees par la science", 100, 376, 13, C_GRAY, F_BODY)

    # ═══════════════════════════════════════════════════════════════════
    # 5. PRODUCT CARDS  (y: 392 → 642)
    # ═══════════════════════════════════════════════════════════════════
    products = [
        {"name": "Vitamine D3 5000 UI", "price": "29,90 \u20ac", "stars": 5,
         "tag": "Best-Seller", "tag_c": C_ACCENT, "pill_c": C_ACCENT},
        {"name": "Omega-3 Ultra Pure", "price": "34,50 \u20ac", "stars": 5,
         "tag": "Nouveau", "tag_c": C_SECONDARY, "pill_c": C_SECONDARY},
        {"name": "Complexe Magnesium", "price": "24,90 \u20ac", "stars": 4,
         "tag": "Promo -15%", "tag_c": C_DANGER, "pill_c": c(100, 180, 120)},
    ]

    cw, ch = 310, 248
    cy0 = 392

    for i, prod in enumerate(products):
        cx = 100 + i * (cw + 30)

        # Shadow
        rrect(ctx, cx + 3, cy0 + 3, cw, ch, 14, fill=c(235, 237, 240))
        # Card
        rrect(ctx, cx, cy0, cw, ch, 14, fill=C_WHITE, stroke=c(230, 233, 237), sw=1)

        # Product image area
        ix = cx + (cw - 120) // 2
        iy = cy0 + 18
        grad_v(ctx, ix, iy, 120, 100, C_PRIMARY, C_PRIMARY_LT)
        draw_pill(ctx, ix + 60, iy + 50, prod["pill_c"])

        # Tag badge
        tag_w = txt_w(ctx, prod["tag"], 10, F_BODY, True) + 16
        rrect(ctx, cx + cw - tag_w - 12, cy0 + 12, tag_w, 20, 6, fill=prod["tag_c"])
        txt(ctx, prod["tag"], cx + cw - tag_w - 4, cy0 + 27, 10, C_WHITE, F_BODY, bold=True)

        # Product name
        txt(ctx, prod["name"], cx + 20, cy0 + 134, 15, C_DARK, F_HEAD, bold=True)

        # Stars
        for s in range(5):
            sc = C_ACCENT if s < prod["stars"] else C_LIGHT_GRAY
            draw_star(ctx, cx + 26 + s * 16, cy0 + 155, 5.5, sc)

        # Rating text
        txt(ctx, "(124 avis)", cx + 110, cy0 + 159, 10, C_GRAY, F_BODY)

        # Price
        txt(ctx, prod["price"], cx + 20, cy0 + 184, 20, C_PRIMARY, F_HEAD, bold=True)

        # Add to cart button
        by = cy0 + ch - 46
        bw_btn = cw - 40
        grad_v(ctx, cx + 20, by, bw_btn, 34, c(22, 95, 52), C_PRIMARY)
        rrect(ctx, cx + 20, by, bw_btn, 34, 10, fill=C_PRIMARY)
        txt_c(ctx, "Ajouter au panier", cx + 20 + bw_btn / 2, by + 10, 12, C_WHITE, F_BODY, bold=True)

    # ═══════════════════════════════════════════════════════════════════
    # 6. NEWSLETTER CTA BAR  (y: 650 → 705)
    # ═══════════════════════════════════════════════════════════════════
    ny = 650
    nh = 52
    grad_h(ctx, 0, ny, W, nh, C_PRIMARY, C_PRIMARY_LT)
    # Accent top line
    ctx.set_source_rgb(*C_ACCENT)
    ctx.set_line_width(2)
    ctx.move_to(0, ny)
    ctx.line_to(W, ny)
    ctx.stroke()

    txt(ctx, "Recevez 10% de reduction sur votre 1ere commande", 100, ny + 19, 14, C_WHITE, F_HEAD, bold=True)
    txt(ctx, "Inscrivez-vous a notre newsletter sante \u2014 5 000+ abonnes", 100, ny + 38, 11, c(200, 230, 210), F_BODY)

    # Email input
    rrect(ctx, 710, ny + 13, 250, 28, 8, fill=C_WHITE)
    txt(ctx, "votre@email.com", 724, ny + 31, 12, C_LIGHT_GRAY, F_BODY)

    # Subscribe button
    rrect(ctx, 968, ny + 13, 110, 28, 8, fill=C_ACCENT)
    txt_c(ctx, "S'inscrire", 1023, ny + 19, 12, C_NAV_BG, F_BODY, bold=True)

    # ═══════════════════════════════════════════════════════════════════
    # 7. PREMIUM FOOTER  (y: 705 → 860)
    # ═══════════════════════════════════════════════════════════════════
    fy0 = 705
    grad_v(ctx, 0, fy0, W, H - fy0, C_FOOT_BG, C_FOOT_DARK)

    # Top accent lines
    ctx.set_source_rgb(*C_PRIMARY)
    ctx.set_line_width(2.5)
    ctx.move_to(0, fy0 + 1)
    ctx.line_to(W, fy0 + 1)
    ctx.stroke()
    ctx.set_source_rgb(*C_SECONDARY)
    ctx.set_line_width(0.5)
    ctx.move_to(0, fy0 + 5)
    ctx.line_to(W, fy0 + 5)
    ctx.stroke()

    # Column header y and link start y
    col_y = fy0 + 20
    lnk_y = fy0 + 46
    lnk_sp = 20

    # ── Col 1: Brand  (x: 80) ──────────────────────────────────────────
    bx = 80

    # Glow behind logo
    rg = cairo.RadialGradient(bx + 12, col_y + 12, 2, bx + 12, col_y + 12, 22)
    rg.add_color_stop_rgba(0, *C_SECONDARY, 0.25)
    rg.add_color_stop_rgba(1, *C_SECONDARY, 0)
    ctx.set_source(rg)
    ctx.arc(bx + 12, col_y + 12, 22, 0, 2 * math.pi)
    ctx.fill()

    rrect(ctx, bx, col_y + 2, 24, 24, 12, fill=C_SECONDARY)
    txt_c(ctx, "N", bx + 12, col_y + 1, 15, C_NAV_BG, F_HEAD, bold=True)

    txt(ctx, "NutriVitaX", bx + 32, col_y + 19, 16, C_WHITE, F_HEAD, bold=True)
    nw = txt_w(ctx, "NutriVitaX", 16, F_HEAD, True)
    txt(ctx, "Pro", bx + 32 + nw + 5, col_y + 20, 11, C_ACCENT, F_HEAD, bold=True)

    desc = ["Theme WooCommerce premium concu pour",
            "les boutiques de complements alimentaires",
            "et nutraceutique. Design BioLab Luxe."]
    ly = lnk_y
    for line in desc:
        txt(ctx, line, bx, ly, 10, C_GRAY, F_BODY)
        ly += 14

    # Social icons
    sy = ly + 8
    for label in ["f", "in", "X", "Yt", "Li"]:
        draw_social(ctx, bx + 12, sy, 10, label)
        bx += 26
    bx = 80  # reset

    # ── Col 2: Boutique  (x: 320) ──────────────────────────────────────
    c2x = 330
    txt(ctx, "Boutique", c2x, col_y + 19, 13, C_SECONDARY, F_HEAD, bold=True)
    ctx.set_source_rgb(*C_SECONDARY)
    ctx.set_line_width(2)
    ctx.move_to(c2x, col_y + 25)
    ctx.line_to(c2x + 52, col_y + 25)
    ctx.stroke()

    for item in ["Vitamines & Mineraux", "Proteines & BCAA",
                 "Omegas & Acides Gras", "Antioxydants", "Herboristerie", "Packs & Stacks"]:
        draw_arrow(ctx, c2x + 1, lnk_y + 3, 6, C_SECONDARY)
        txt(ctx, item, c2x + 14, lnk_y + 13, 11, C_LINK_TEXT, F_BODY)
        lnk_y += lnk_sp

    # ── Col 3: Informations  (x: 510) ──────────────────────────────────
    c3x = 520
    lnk_y = fy0 + 46
    txt(ctx, "Informations", c3x, col_y + 19, 13, C_SECONDARY, F_HEAD, bold=True)
    ctx.set_source_rgb(*C_SECONDARY)
    ctx.set_line_width(2)
    ctx.move_to(c3x, col_y + 25)
    ctx.line_to(c3x + 78, col_y + 25)
    ctx.stroke()

    for item in ["A propos de nous", "Notre laboratoire", "Blog Sante",
                 "Programme affiliate", "Temoignages clients", "Carrieres"]:
        draw_arrow(ctx, c3x + 1, lnk_y + 3, 6, C_SECONDARY)
        txt(ctx, item, c3x + 14, lnk_y + 13, 11, C_LINK_TEXT, F_BODY)
        lnk_y += lnk_sp

    # ── Col 4: Aide & Support  (x: 700) ────────────────────────────────
    c4x = 710
    lnk_y = fy0 + 46
    txt(ctx, "Aide & Support", c4x, col_y + 19, 13, C_SECONDARY, F_HEAD, bold=True)
    ctx.set_source_rgb(*C_SECONDARY)
    ctx.set_line_width(2)
    ctx.move_to(c4x, col_y + 25)
    ctx.line_to(c4x + 88, col_y + 25)
    ctx.stroke()

    for item in ["FAQ", "Suivi de commande", "Politique de livraison",
                 "Retours & remboursements", "Conditions generales", "Contactez-nous"]:
        draw_arrow(ctx, c4x + 1, lnk_y + 3, 6, C_SECONDARY)
        txt(ctx, item, c4x + 14, lnk_y + 13, 11, C_LINK_TEXT, F_BODY)
        lnk_y += lnk_sp

    # ── Col 5: Contact + Certifications  (x: 900) ─────────────────────
    c5x = 910
    lnk_y = fy0 + 46
    txt(ctx, "Contact", c5x, col_y + 19, 13, C_SECONDARY, F_HEAD, bold=True)
    ctx.set_source_rgb(*C_SECONDARY)
    ctx.set_line_width(2)
    ctx.move_to(c5x, col_y + 25)
    ctx.line_to(c5x + 48, col_y + 25)
    ctx.stroke()

    contacts = [
        ("\u2709", "contact@nutrivitax.com"),
        ("\u260E", "+33 1 23 45 67 89"),
        ("\u25CF", "Lun-Ven : 9h - 18h"),
        ("\u25A0", "14 Rue de la Sante, Paris"),
    ]
    for icon, text in contacts:
        txt(ctx, icon, c5x, lnk_y + 13, 12, C_SECONDARY, F_BODY)
        txt(ctx, text, c5x + 18, lnk_y + 13, 11, C_LINK_TEXT, F_BODY)
        lnk_y += 19

    # Certifications
    txt(ctx, "Certifications :", c5x, lnk_y + 6, 9, C_GRAY_DIM, F_BODY)
    cert_y = lnk_y + 22
    draw_cert(ctx, c5x + 16, cert_y + 14, 14, "Bio")
    draw_cert(ctx, c5x + 64, cert_y + 14, 14, "GMP")
    draw_cert(ctx, c5x + 112, cert_y + 14, 14, "ISO")
    draw_cert(ctx, c5x + 160, cert_y + 14, 14, "HACCP")

    # ═══════════════════════════════════════════════════════════════════
    # 8. BOTTOM BAR  (y: 862 → 900)
    # ═══════════════════════════════════════════════════════════════════
    by0 = 862
    ctx.set_source_rgb(*C_FOOT_DARK)
    ctx.rectangle(0, by0, W, H - by0)
    ctx.fill()
    # Top border
    ctx.set_source_rgb(*c(25, 45, 70))
    ctx.set_line_width(1)
    ctx.move_to(0, by0)
    ctx.line_to(W, by0)
    ctx.stroke()

    # Copyright
    txt(ctx, "\u00a9 2026 NutriVitaX Pro. Tous droits reserves.", 80, by0 + 24, 10, C_SUBTLE, F_BODY)

    # Payment badges
    px = 520
    for label, bg in [("Visa", c(20, 60, 140)), ("MC", c(230, 50, 50)),
                       ("PayPal", c(0, 100, 180)), ("Apple Pay", c(50, 50, 50)),
                       ("CB", c(20, 80, 160))]:
        pw = draw_payment(ctx, px, by0 + 12, label, bg)
        px += pw + 8

    # WP compat
    wp = "WordPress 6.7+ | WooCommerce 9.0+ | PHP 8.4"
    wp_w = txt_w(ctx, wp, 9, F_BODY)
    txt(ctx, wp, W - 80 - wp_w, by0 + 24, 9, C_SUBTLE, F_BODY)

    # ── SAVE ───────────────────────────────────────────────────────────
    surface.write_to_png(OUTPUT)
    fsize = os.path.getsize(OUTPUT)
    print(f"Screenshot saved: {OUTPUT}")
    print(f"Size: {W}x{H} | File: {fsize} bytes ({fsize / 1024:.1f} KB)")


if __name__ == "__main__":
    create_screenshot()