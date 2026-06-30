#!/usr/bin/env python3
"""
Generate screenshot.png for NutriVitaX Pro WordPress theme.
1200x900 pixels (WordPress standard 4:3 ratio).
BioLab Luxe design system mockup — with premium footer.
"""

from PIL import Image, ImageDraw, ImageFont
import math, os

# ── Config ──────────────────────────────────────────────────────────────────
W, H = 1200, 900
THEME_DIR = "/home/z/lampstack/wordpress/wp-content/themes/nutrivitax-pro"
OUTPUT = os.path.join(THEME_DIR, "screenshot.png")

# BioLab Luxe palette
C_PRIMARY    = (26, 107, 58)    # #1A6B3A  Forêt Profonde
C_SECONDARY  = (46, 204, 113)   # #2ECC71  Émeraude Vif
C_ACCENT     = (244, 169, 0)    # #F4A900  Or Scientifique
C_DARK       = (18, 36, 58)     # #12243A  Nuit Marine
C_LIGHT      = (240, 249, 244)  # #F0F9F4  Brume Verte
C_WHITE      = (255, 255, 255)
C_GRAY       = (107, 114, 128)
C_LIGHT_GRAY = (229, 231, 235)
C_DANGER     = (239, 68, 68)
C_DARK_FOOT  = (10, 20, 38)     # darker than C_DARK for footer bg
C_MID_FOOT   = (15, 28, 48)     # mid tone for footer columns bg

# Fonts
FONT_DIR = "/usr/share/fonts/truetype"
def load_font(size, bold=False, mono=False, serif=False):
    if mono:
        path = f"{FONT_DIR}/dejavu/DejaVuSansMono.ttf"
    elif serif:
        path = f"{FONT_DIR}/noto-serif-sc/NotoSerifSC-Bold.ttf" if bold else f"{FONT_DIR}/noto-serif-sc/NotoSerifSC-Regular.ttf"
    elif bold:
        path = f"{FONT_DIR}/dejavu/DejaVuSans-Bold.ttf"
    else:
        path = f"{FONT_DIR}/dejavu/DejaVuSans.ttf"
    try:
        return ImageFont.truetype(path, size)
    except Exception:
        return ImageFont.load_default()


def rounded_rect(draw, bbox, radius, fill=None, outline=None, width=1):
    x1, y1, x2, y2 = bbox
    r = radius
    draw.ellipse([x1, y1, x1 + 2*r, y1 + 2*r], fill=fill, outline=outline, width=width)
    draw.ellipse([x2 - 2*r, y1, x2, y1 + 2*r], fill=fill, outline=outline, width=width)
    draw.ellipse([x1, y2 - 2*r, x1 + 2*r, y2], fill=fill, outline=outline, width=width)
    draw.ellipse([x2 - 2*r, y2 - 2*r, x2, y2], fill=fill, outline=outline, width=width)
    draw.rectangle([x1 + r, y1, x2 - r, y2], fill=fill)
    draw.rectangle([x1, y1 + r, x2, y2 - r], fill=fill)
    if outline:
        draw.line([x1 + r, y1, x2 - r, y1], fill=outline, width=width)
        draw.line([x1 + r, y2, x2 - r, y2], fill=outline, width=width)
        draw.line([x1, y1 + r, x1, y2 - r], fill=outline, width=width)
        draw.line([x2, y1 + r, x2, y2 - r], fill=outline, width=width)


def draw_gradient_v(img, bbox, color1, color2):
    x1, y1, x2, y2 = bbox
    draw = ImageDraw.Draw(img)
    for y in range(y1, y2):
        ratio = (y - y1) / max(1, (y2 - y1))
        r = int(color1[0] + (color2[0] - color1[0]) * ratio)
        g = int(color1[1] + (color2[1] - color1[1]) * ratio)
        b = int(color1[2] + (color2[2] - color1[2]) * ratio)
        draw.line([(x1, y), (x2, y)], fill=(r, g, b))


def draw_gradient_h(img, bbox, color1, color2):
    x1, y1, x2, y2 = bbox
    draw = ImageDraw.Draw(img)
    for x in range(x1, x2):
        ratio = (x - x1) / max(1, (x2 - x1))
        r = int(color1[0] + (color2[0] - color1[0]) * ratio)
        g = int(color1[1] + (color2[1] - color1[1]) * ratio)
        b = int(color1[2] + (color2[2] - color1[2]) * ratio)
        draw.line([(x, y1), (x, y2)], fill=(r, g, b))


def draw_icon_circle(draw, cx, cy, r, color, icon_type="check"):
    """Draw a small circular icon with a symbol inside."""
    draw.ellipse([cx - r, cy - r, cx + r, cy + r], fill=color)
    f = load_font(int(r * 1.3), bold=True)
    symbols = {
        "check": "\u2713", "star": "\u2605", "lock": "\u25C9",
        "truck": "\u25B2", "heart": "\u2665", "phone": "\u2706",
    }
    sym = symbols.get(icon_type, "\u2713")
    sb = f.getbbox(sym)
    sw = sb[2] - sb[0]
    draw.text((cx - sw // 2, cy - r + 1), sym, fill=C_WHITE, font=f)


def draw_social_icon(draw, cx, cy, size, icon_type):
    """Draw a simple social media icon (circle with letter)."""
    letters = {"facebook": "f", "instagram": "in", "twitter": "X", "youtube": "Yt", "linkedin": "Li", "tiktok": "Tk"}
    letter = letters.get(icon_type, "?")
    draw.ellipse([cx - size, cy - size, cx + size, cy + size], outline=C_GRAY, width=1)
    f = load_font(int(size * 1.0), bold=True)
    sb = f.getbbox(letter)
    sw = sb[2] - sb[0]
    draw.text((cx - sw // 2, cy - size + 2), letter, fill=C_LIGHT_GRAY, font=f)


def draw_payment_badge(draw, x, y, w, h, label, color):
    """Draw a payment method badge."""
    rounded_rect(draw, (x, y, x + w, y + h), 4, fill=color)
    f = load_font(9, bold=True)
    sb = f.getbbox(label)
    lw = sb[2] - sb[0]
    draw.text((x + (w - lw) // 2, y + (h - 12) // 2), label, fill=C_WHITE, font=f)


def draw_certification_badge(draw, cx, cy, r, label):
    """Draw a certification circle badge with text below."""
    draw.ellipse([cx - r, cy - r, cx + r, cy + r], outline=C_PRIMARY, width=2)
    # Inner circle
    draw.ellipse([cx - r + 4, cy - r + 4, cx + r - 4, cy + r - 4], outline=C_SECONDARY, width=1)
    # Check mark inside
    f = load_font(int(r * 1.1), bold=True)
    sb = f.getbbox("\u2713")
    sw = sb[2] - sb[0]
    draw.text((cx - sw // 2, cy - r + 3), "\u2713", fill=C_SECONDARY, font=f)
    # Label below
    f_lbl = load_font(9)
    sb2 = f_lbl.getbbox(label)
    lw = sb2[2] - sb2[0]
    draw.text((cx - lw // 2, cy + r + 4), label, fill=C_GRAY, font=f_lbl)


def create_screenshot():
    img = Image.new("RGB", (W, H), C_WHITE)
    draw = ImageDraw.Draw(img)

    # ═══════════════════════════════════════════════════════════════════════
    # LAYOUT PLAN (900px total height)
    # Nav bar:        0 - 54     (54px)
    # Hero:          54 - 280   (226px)
    # Trust bar:    280 - 318   (38px)
    # Section title: 318 - 372  (54px)
    # Product cards: 372 - 622  (250px)
    # Newsletter:    622 - 685  (63px)
    # Footer:        685 - 900  (215px)  ← PREMIUM FOOTER
    # ═══════════════════════════════════════════════════════════════════════

    # ── NAV BAR ────────────────────────────────────────────────────────
    draw.rectangle([0, 0, W, 54], fill=(10, 22, 40))
    f_nav = load_font(14, bold=True)
    f_nav_sm = load_font(11)

    # Logo
    draw.ellipse([24, 13, 46, 35], fill=C_SECONDARY)
    draw.text((54, 14), "NutriVitaX", fill=C_WHITE, font=f_nav)
    draw.text((175, 17), "PRO", fill=C_ACCENT, font=f_nav_sm)

    # Nav items
    nav_items = ["Accueil", "Boutique", "Quiz Sante", "Stack Builder", "Blog"]
    nav_x = 660
    for item in nav_items:
        draw.text((nav_x, 20), item, fill=C_LIGHT_GRAY, font=f_nav_sm)
        nav_x += 90

    # Cart icon
    draw.ellipse([W - 50, 14, W - 36, 28], fill=C_ACCENT)
    draw.text((W - 32, 14), "3", fill=C_DARK, font=f_nav_sm)

    # ── HERO SECTION ───────────────────────────────────────────────────
    draw_gradient_v(img, (0, 54, W, 280), C_DARK, (20, 90, 50))

    # Subtle grid
    for gx in range(0, W, 80):
        draw.line([(gx, 54), (gx, 280)], fill=(30, 48, 70), width=1)
    for gy in range(54, 280, 80):
        draw.line([(0, gy), (W, gy)], fill=(30, 48, 70), width=1)

    # Badge
    f_hero_sm = load_font(12, bold=True)
    badge_text = "Theme WooCommerce Premium"
    badge_bbox = f_hero_sm.getbbox(badge_text)
    badge_w = badge_bbox[2] - badge_bbox[0] + 24
    badge_x = (W - badge_w) // 2
    rounded_rect(draw, (badge_x, 72, badge_x + badge_w, 96), 12, fill=C_SECONDARY)
    draw.text((badge_x + 12, 75), badge_text, fill=C_WHITE, font=f_hero_sm)

    # Title
    f_hero = load_font(48, bold=True)
    f_hero_sub = load_font(17)
    title1 = "NutriVitaX"
    title2 = "Pro"
    t1_bbox = f_hero.getbbox(title1)
    t2_bbox = f_hero.getbbox(title2)
    total_w = (t1_bbox[2] - t1_bbox[0]) + 10 + (t2_bbox[2] - t2_bbox[0])
    tx = (W - total_w) // 2
    draw.text((tx, 108), title1, fill=C_WHITE, font=f_hero)
    draw.text((tx + (t1_bbox[2] - t1_bbox[0]) + 10, 108), title2, fill=C_ACCENT, font=f_hero)

    # Subtitle
    sub1 = "Design BioLab Luxe pour votre boutique de complements"
    sub2 = "alimentaires & nutraceutique premium"
    s1_bbox = f_hero_sub.getbbox(sub1)
    draw.text(((W - s1_bbox[2] + s1_bbox[0]) // 2, 172), sub1, fill=(180, 200, 210), font=f_hero_sub)
    s2_bbox = f_hero_sub.getbbox(sub2)
    draw.text(((W - s2_bbox[2] + s2_bbox[0]) // 2, 195), sub2, fill=(180, 200, 210), font=f_hero_sub)

    # CTA buttons
    f_btn = load_font(13, bold=True)
    btn1_text = "Explorer la Boutique"
    btn2_text = "Passer le Quiz"
    b1_bbox = f_btn.getbbox(btn1_text)
    b1_w = b1_bbox[2] - b1_bbox[0] + 36
    b1_x = W // 2 - b1_w - 15
    rounded_rect(draw, (b1_x, 235, b1_x + b1_w, 268), 10, fill=C_ACCENT)
    draw.text((b1_x + 18, 245), btn1_text, fill=C_DARK, font=f_btn)

    b2_bbox = f_btn.getbbox(btn2_text)
    b2_w = b2_bbox[2] - b2_bbox[0] + 36
    b2_x = W // 2 + 15
    rounded_rect(draw, (b2_x, 235, b2_x + b2_w, 268), 10, outline=C_WHITE, width=2)
    draw.text((b2_x + 18, 245), btn2_text, fill=C_WHITE, font=f_btn)

    # ── TRUST BAR ──────────────────────────────────────────────────────
    draw.rectangle([0, 280, W, 318], fill=C_LIGHT)
    f_trust = load_font(11, bold=True)

    trust_items = [
        "Certifie Bio", "Livraison Gratuite 50\u20ac+", "Retour 30 jours", "Paiement Securise", "Support 24/7"
    ]
    trust_spacing = W // (len(trust_items) + 1)
    for i, item in enumerate(trust_items):
        tx_pos = trust_spacing * (i + 1)
        item_bbox = f_trust.getbbox(item)
        item_w = item_bbox[2] - item_bbox[0]
        draw.text((tx_pos - item_w // 2, 294), item, fill=C_PRIMARY, font=f_trust)
        draw.ellipse([tx_pos - item_w // 2 - 12, 298, tx_pos - item_w // 2 - 4, 306], fill=C_SECONDARY)

    # ── SECTION: Best-Sellers ──────────────────────────────────────────
    f_section = load_font(24, bold=True)
    f_section_sub = load_font(13)
    draw.text((100, 330), "Nos Best-Sellers", fill=C_DARK, font=f_section)
    draw.text((100, 360), "Les formulations les plus populaires, validees par la science", fill=C_GRAY, font=f_section_sub)

    # ── PRODUCT CARDS ──────────────────────────────────────────────────
    card_w, card_h = 310, 248
    card_y = 388
    products = [
        {"name": "Vitamine D3 5000 UI", "price": "29,90 \u20ac", "stars": 5, "tag": "Best-Seller", "tag_color": C_ACCENT, "pill_c": C_ACCENT},
        {"name": "Omega-3 Ultra Pure", "price": "34,50 \u20ac", "stars": 5, "tag": "Nouveau", "tag_color": C_SECONDARY, "pill_c": C_SECONDARY},
        {"name": "Complexe Magnesium", "price": "24,90 \u20ac", "stars": 4, "tag": "Promo -15%", "tag_color": C_DANGER, "pill_c": (100, 180, 120)},
    ]

    f_prod_name = load_font(15, bold=True)
    f_prod_price = load_font(20, bold=True)
    f_prod_btn = load_font(12, bold=True)
    f_tag = load_font(10, bold=True)

    for i, prod in enumerate(products):
        cx = 100 + i * (card_w + 30)

        # Card shadow + card
        rounded_rect(draw, (cx+3, card_y+3, cx+card_w+3, card_y+card_h+3), 16, fill=C_LIGHT_GRAY)
        rounded_rect(draw, (cx, card_y, cx+card_w, card_y+card_h), 16, fill=C_WHITE, outline=(230, 233, 237), width=1)

        # Product image area
        img_x = cx + (card_w - 120) // 2
        img_y = card_y + 18
        draw_gradient_v(img, (img_x, img_y, img_x + 120, img_y + 100), C_PRIMARY, (30, 130, 75))

        # Pill bottle
        pill_cx = img_x + 60
        pill_cy = img_y + 50
        draw.rectangle([pill_cx-11, pill_cy-25, pill_cx+11, pill_cy-8], fill=C_DARK)
        draw.rectangle([pill_cx-13, pill_cy-10, pill_cx+13, pill_cy-4], fill=C_DARK)
        draw.rounded_rectangle([pill_cx-16, pill_cy-4, pill_cx+16, pill_cy+28], radius=6, fill=prod["pill_c"])
        draw.line([(pill_cx-8, pill_cy+8), (pill_cx+8, pill_cy+8)], fill=C_WHITE, width=2)
        draw.line([(pill_cx-6, pill_cy+15), (pill_cx+6, pill_cy+15)], fill=C_WHITE, width=1)

        # Tag
        tag_text = prod["tag"]
        tag_bbox = f_tag.getbbox(tag_text)
        tag_w = tag_bbox[2] - tag_bbox[0] + 14
        rounded_rect(draw, (cx + card_w - tag_w - 10, card_y + 10, cx + card_w - 10, card_y + 28), 6, fill=prod["tag_color"])
        draw.text((cx + card_w - tag_w - 3, card_y + 12), tag_text, fill=C_WHITE, font=f_tag)

        # Product name
        draw.text((cx + 20, card_y + 128), prod["name"], fill=C_DARK, font=f_prod_name)

        # Stars
        star_y = card_y + 150
        for s in range(prod["stars"]):
            sx = cx + 20 + s * 16
            draw.ellipse([sx, star_y, sx+10, star_y+10], fill=C_ACCENT)
        if prod["stars"] < 5:
            for s in range(prod["stars"], 5):
                sx = cx + 20 + s * 16
                draw.ellipse([sx, star_y, sx+10, star_y+10], fill=C_LIGHT_GRAY)
        f_rating = load_font(10)
        draw.text((cx + 20 + 5 * 16 + 4, star_y - 1), "(124 avis)", fill=C_GRAY, font=f_rating)

        # Price
        draw.text((cx + 20, card_y + 168), prod["price"], fill=C_PRIMARY, font=f_prod_price)

        # Add to cart
        btn_y = card_y + card_h - 42
        rounded_rect(draw, (cx + 20, btn_y, cx + card_w - 20, btn_y + 32), 10, fill=C_PRIMARY)
        cart_text = "Ajouter au panier"
        cart_bbox = f_prod_btn.getbbox(cart_text)
        cart_w = cart_bbox[2] - cart_bbox[0]
        draw.text((cx + (card_w - cart_w) // 2, btn_y + 8), cart_text, fill=C_WHITE, font=f_prod_btn)

    # ═══════════════════════════════════════════════════════════════════════
    # NEWSLETTER CTA BAR (622 - 685)
    # ═══════════════════════════════════════════════════════════════════════
    nl_y = 632
    nl_h = 50
    draw_gradient_h(img, (0, nl_y, W, nl_y + nl_h), C_PRIMARY, (35, 145, 78))

    # Decorative line top
    draw.rectangle([0, nl_y, W, nl_y + 2], fill=C_ACCENT)

    f_nl_title = load_font(15, bold=True)
    f_nl_sub = load_font(11)
    f_nl_btn = load_font(12, bold=True)

    nl_title = "Recevez 10% de reduction sur votre 1ere commande"
    nl_title_bbox = f_nl_title.getbbox(nl_title)
    draw.text((100, nl_y + 8), nl_title, fill=C_WHITE, font=f_nl_title)

    nl_sub = "Inscrivez-vous a notre newsletter sante + 5000 abonnes"
    nl_sub_bbox = f_nl_sub.getbbox(nl_sub)
    draw.text((100, nl_y + 30), nl_sub, fill=(200, 230, 210), font=f_nl_sub)

    # Email input field
    input_x = 700
    input_w = 260
    rounded_rect(draw, (input_x, nl_y + 10, input_x + input_w, nl_y + nl_h - 10), 8, fill=C_WHITE)
    f_input = load_font(11)
    draw.text((input_x + 12, nl_y + 18), "votre@email.com", fill=C_LIGHT_GRAY, font=f_input)

    # Subscribe button
    sub_x = input_x + input_w + 10
    sub_w = 120
    rounded_rect(draw, (sub_x, nl_y + 10, sub_x + sub_w, nl_y + nl_h - 10), 8, fill=C_ACCENT)
    sub_text = "S'inscrire"
    sub_bbox = f_nl_btn.getbbox(sub_text)
    sw = sub_bbox[2] - sub_bbox[0]
    draw.text((sub_x + (sub_w - sw) // 2, nl_y + 18), sub_text, fill=C_DARK, font=f_nl_btn)

    # ═══════════════════════════════════════════════════════════════════════
    # PREMIUM FOOTER (685 - 900)
    # ═══════════════════════════════════════════════════════════════════════
    foot_y = 685

    # Main footer background
    draw_gradient_v(img, (0, foot_y, W, H), C_DARK_FOOT, (6, 14, 28))

    # Top decorative accent line
    draw.rectangle([0, foot_y, W, foot_y + 3], fill=C_PRIMARY)

    # Subtle decorative elements — faint DNA helix dots in background
    for dy in range(0, 200, 25):
        for dx in [50, 1150]:
            alpha_color = (20, 45, 75)
            draw.ellipse([dx - 2, foot_y + dy - 2, dx + 2, foot_y + dy + 2], fill=alpha_color)

    # ── Footer layout: 5 columns ──────────────────────────────────────
    f_ft_title = load_font(14, bold=True)
    f_ft_link = load_font(11)
    f_ft_desc = load_font(10)
    f_ft_small = load_font(9)
    f_ft_logo = load_font(16, bold=True)

    col_start_y = foot_y + 16
    link_start_y = foot_y + 38
    link_spacing = 18

    # ─── Column 1: Brand (x: 80-280) ──────────────────────────────────
    brand_x = 80

    # Logo with glow effect
    for gr in range(3, 0, -1):
        glow_color = (30, 80 + gr * 20, 50 + gr * 15)
        draw.ellipse([brand_x - gr, col_start_y + 2 - gr, brand_x + 20 + gr, col_start_y + 22 + gr], fill=glow_color)
    draw.ellipse([brand_x, col_start_y + 2, brand_x + 20, col_start_y + 22], fill=C_SECONDARY)
    draw.text((brand_x + 28, col_start_y + 2), "NutriVitaX", fill=C_WHITE, font=f_ft_logo)
    pro_bbox = f_ft_title.getbbox("Pro")
    draw.text((brand_x + 28 + f_ft_logo.getbbox("NutriVitaX")[2] - f_ft_logo.getbbox("NutriVitaX")[0] + 6, col_start_y + 5), "Pro", fill=C_ACCENT, font=f_ft_title)

    # Description
    desc_lines = [
        "Theme WooCommerce premium concu",
        "pour les boutiques de complements",
        "alimentaires et nutraceutique.",
        "Design BioLab Luxe | FSE Block Theme"
    ]
    ly = link_start_y
    for line in desc_lines:
        draw.text((brand_x, ly), line, fill=C_GRAY, font=f_ft_desc)
        ly += 15

    # Social media icons row
    social_y = ly + 6
    social_icons = ["facebook", "instagram", "twitter", "youtube", "linkedin"]
    social_x = brand_x
    for sicon in social_icons:
        draw_social_icon(draw, social_x + 10, social_y, 10, sicon)
        social_x += 28

    # ─── Column 2: Boutique (x: 310-440) ──────────────────────────────
    col2_x = 320
    draw.text((col2_x, col_start_y), "Boutique", fill=C_SECONDARY, font=f_ft_title)
    # Underline
    draw.rectangle([col2_x, col_start_y + 20, col2_x + 55, col_start_y + 22], fill=C_SECONDARY)

    boutique_links = [
        ("Vitamines & Mineraux", "icons/vitamins"),
        ("Proteines & BCAA", "icons/protein"),
        ("Omegas & Acides Gras", "icons/omega"),
        ("Antioxydants", "icons/antioxydant"),
        ("Herboristerie", "icons/herbs"),
        ("Packs & Stacks", "icons/stack"),
    ]
    ly = link_start_y + 6
    for link_text, _ in boutique_links:
        # Small green arrow before each link
        draw.polygon([(col2_x, ly + 5), (col2_x + 6, ly + 9), (col2_x, ly + 13)], fill=C_SECONDARY)
        draw.text((col2_x + 12, ly), link_text, fill=(170, 180, 195), font=f_ft_link)
        ly += link_spacing

    # ─── Column 3: Informations (x: 470-610) ──────────────────────────
    col3_x = 490
    draw.text((col3_x, col_start_y), "Informations", fill=C_SECONDARY, font=f_ft_title)
    draw.rectangle([col3_x, col_start_y + 20, col3_x + 80, col_start_y + 22], fill=C_SECONDARY)

    info_links = [
        ("A propos de nous", ""),
        ("Notre laboratoire", ""),
        ("Blog Sante", ""),
        ("Programme affiliate", ""),
        ("Temoignages clients", ""),
        ("Carrieres", ""),
    ]
    ly = link_start_y + 6
    for link_text, _ in info_links:
        draw.polygon([(col3_x, ly + 5), (col3_x + 6, ly + 9), (col3_x, ly + 13)], fill=C_SECONDARY)
        draw.text((col3_x + 12, ly), link_text, fill=(170, 180, 195), font=f_ft_link)
        ly += link_spacing

    # ─── Column 4: Aide & Support (x: 650-790) ────────────────────────
    col4_x = 660
    draw.text((col4_x, col_start_y), "Aide & Support", fill=C_SECONDARY, font=f_ft_title)
    draw.rectangle([col4_x, col_start_y + 20, col4_x + 95, col_start_y + 22], fill=C_SECONDARY)

    help_links = [
        ("FAQ", ""),
        ("Suivi de commande", ""),
        ("Politique de livraison", ""),
        ("Retours & remboursements", ""),
        ("Conditions generales", ""),
        ("Contactez-nous", ""),
    ]
    ly = link_start_y + 6
    for link_text, _ in help_links:
        draw.polygon([(col4_x, ly + 5), (col4_x + 6, ly + 9), (col4_x, ly + 13)], fill=C_SECONDARY)
        draw.text((col4_x + 12, ly), link_text, fill=(170, 180, 195), font=f_ft_link)
        ly += link_spacing

    # ─── Column 5: Contact & Certifications (x: 840-1140) ─────────────
    col5_x = 840
    draw.text((col5_x, col_start_y), "Contact", fill=C_SECONDARY, font=f_ft_title)
    draw.rectangle([col5_x, col_start_y + 20, col5_x + 55, col_start_y + 22], fill=C_SECONDARY)

    # Contact info items
    contact_items = [
        ("\u2709", "contact@nutrivitax-pro.com"),
        ("\u260E", "+33 1 23 45 67 89"),
        ("\u25CB", "Lun-Ven: 9h-18h"),
        ("\u25A0", "14 Rue de la Sante, Paris"),
    ]
    ly = link_start_y + 6
    for icon, text in contact_items:
        draw.text((col5_x, ly), icon, fill=C_SECONDARY, font=f_ft_link)
        draw.text((col5_x + 16, ly), text, fill=(170, 180, 195), font=f_ft_link)
        ly += link_spacing

    # Certification badges
    cert_y = ly + 8
    draw.text((col5_x, cert_y - 2), "Certifications:", fill=(130, 140, 155), font=f_ft_small)
    cert_y += 14
    draw_certification_badge(draw, col5_x + 14, cert_y + 14, 14, "Bio")
    draw_certification_badge(draw, col5_x + 60, cert_y + 14, 14, "GMP")
    draw_certification_badge(draw, col5_x + 106, cert_y + 14, 14, "ISO")
    draw_certification_badge(draw, col5_x + 152, cert_y + 14, 14, "HACCP")

    # ── BOTTOM BAR ─────────────────────────────────────────────────────
    bottom_y = H - 40
    draw.rectangle([0, bottom_y, W, H], fill=(4, 10, 22))

    # Top border of bottom bar
    draw.rectangle([0, bottom_y, W, bottom_y + 1], fill=(25, 45, 70))

    # Copyright left
    f_copy = load_font(10)
    draw.text((80, bottom_y + 12), "\u00a9 2026 NutriVitaX Pro. Tous droits reserves.", fill=(80, 90, 105), font=f_copy)

    # Payment method badges (center)
    payment_x = 520
    payments = [
        ("Visa", (20, 60, 140)),
        ("MC", (230, 50, 50)),
        ("PayPal", (0, 100, 180)),
        ("Apple Pay", (40, 40, 40)),
        ("CB", (20, 80, 160)),
    ]
    for label, color in payments:
        draw_payment_badge(draw, payment_x, bottom_y + 8, 46, 20, label, color)
        payment_x += 52

    # WordPress compat right
    f_wp = load_font(9)
    wp_text = "WordPress 6.7+ | WooCommerce 9.0+ | PHP 8.4"
    wp_bbox = f_wp.getbbox(wp_text)
    draw.text((W - 80 - (wp_bbox[2] - wp_bbox[0]), bottom_y + 13), wp_text, fill=(60, 70, 85), font=f_wp)

    # ── SAVE ───────────────────────────────────────────────────────────
    os.makedirs(os.path.dirname(OUTPUT), exist_ok=True)
    img.save(OUTPUT, "PNG")
    print(f"Screenshot saved: {OUTPUT}")
    print(f"Size: {img.size}")
    print(f"File size: {os.path.getsize(OUTPUT)} bytes")


if __name__ == "__main__":
    create_screenshot()