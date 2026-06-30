#!/usr/bin/env python3
"""
Generate screenshot.png for NutriVitaX Pro WordPress theme.
1200x900 pixels (WordPress standard 4:3 ratio).
BioLab Luxe design system mockup.
"""

from PIL import Image, ImageDraw, ImageFont, ImageFilter
import os

# ── Config ──────────────────────────────────────────────────────────────────
W, H = 1200, 900
THEME_DIR = "/home/z/lampstack/wordpress/wp-content/themes/nutrivitax-pro"
OUTPUT = os.path.join(THEME_DIR, "screenshot.png")

# BioLab Luxe palette
C_PRIMARY   = (26, 107, 58)    # #1A6B3A
C_SECONDARY = (46, 204, 113)   # #2ECC71
C_ACCENT    = (244, 169, 0)    # #F4A900
C_DARK      = (18, 36, 58)     # #12243A
C_LIGHT     = (240, 249, 244)  # #F0F9F4
C_WHITE     = (255, 255, 255)
C_GRAY      = (107, 114, 128)
C_LIGHT_GRAY = (229, 231, 235)
C_DANGER    = (239, 68, 68)

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


def create_screenshot():
    img = Image.new("RGB", (W, H), C_WHITE)
    draw = ImageDraw.Draw(img)

    # ── HERO SECTION ─────────────────────────────────────────────────────
    # Gradient background hero
    draw_gradient_v(img, (0, 0, W, 330), C_DARK, (20, 90, 50))

    # Subtle grid overlay on hero
    for gx in range(0, W, 80):
        draw.line([(gx, 0), (gx, 330)], fill=(30, 48, 70), width=1)
    for gy in range(0, 330, 80):
        draw.line([(0, gy), (W, gy)], fill=(30, 48, 70), width=1)

    # Nav bar
    draw.rectangle([0, 0, W, 56], fill=(10, 22, 40))
    f_nav = load_font(14, bold=True)
    f_nav_sm = load_font(12)

    # Logo
    draw.ellipse([24, 14, 48, 38], fill=C_SECONDARY)
    draw.text((56, 16), "NutriVitaX", fill=C_WHITE, font=f_nav)
    draw.text((184, 19), "PRO", fill=C_ACCENT, font=f_nav_sm)

    # Nav items
    nav_items = ["Accueil", "Boutique", "Quiz Sante", "Stack Builder", "Blog"]
    nav_x = 680
    for item in nav_items:
        draw.text((nav_x, 20), item, fill=C_LIGHT_GRAY, font=f_nav_sm)
        nav_x += 95

    # Cart
    draw.ellipse([W-52, 14, W-38, 28], fill=C_ACCENT)
    draw.text((W-33, 14), "3", fill=C_DARK, font=f_nav_sm)

    # Badge
    f_hero_sm = load_font(13, bold=True)
    badge_text = "Theme WooCommerce Premium"
    badge_bbox = f_hero_sm.getbbox(badge_text)
    badge_w = badge_bbox[2] - badge_bbox[0] + 24
    badge_x = (W - badge_w) // 2
    rounded_rect(draw, (badge_x, 75, badge_x + badge_w, 100), 12, fill=C_SECONDARY)
    draw.text((badge_x + 12, 79), badge_text, fill=C_WHITE, font=f_hero_sm)

    # Hero title
    f_hero = load_font(52, bold=True)
    f_hero_sub = load_font(19)

    title1 = "NutriVitaX"
    title2 = "Pro"
    t1_bbox = f_hero.getbbox(title1)
    total_w = t1_bbox[2] - t1_bbox[0] + 10
    t2_bbox = f_hero.getbbox(title2)
    total_w += t2_bbox[2] - t2_bbox[0] + 10
    tx = (W - total_w) // 2
    draw.text((tx, 115), title1, fill=C_WHITE, font=f_hero)
    draw.text((tx + t1_bbox[2] - t1_bbox[0] + 10, 115), title2, fill=C_ACCENT, font=f_hero)

    # Subtitle
    sub1 = "Design BioLab Luxe pour votre boutique de complements"
    sub2 = "alimentaires & nutraceutique premium"
    s1_bbox = f_hero_sub.getbbox(sub1)
    draw.text(((W - s1_bbox[2] + s1_bbox[0]) // 2, 185), sub1, fill=(180, 200, 210), font=f_hero_sub)
    s2_bbox = f_hero_sub.getbbox(sub2)
    draw.text(((W - s2_bbox[2] + s2_bbox[0]) // 2, 210), sub2, fill=(180, 200, 210), font=f_hero_sub)

    # CTA buttons
    f_btn = load_font(14, bold=True)
    btn1_text = "Explorer la Boutique"
    btn2_text = "Passer le Quiz"

    b1_bbox = f_btn.getbbox(btn1_text)
    b1_w = b1_bbox[2] - b1_bbox[0] + 40
    b1_x = W // 2 - b1_w - 20
    rounded_rect(draw, (b1_x, 258, b1_x + b1_w, 296), 12, fill=C_ACCENT)
    draw.text((b1_x + 20, 268), btn1_text, fill=C_DARK, font=f_btn)

    b2_bbox = f_btn.getbbox(btn2_text)
    b2_w = b2_bbox[2] - b2_bbox[0] + 40
    b2_x = W // 2 + 20
    rounded_rect(draw, (b2_x, 258, b2_x + b2_w, 296), 12, outline=C_WHITE, width=2)
    draw.text((b2_x + 20, 268), btn2_text, fill=C_WHITE, font=f_btn)

    # ── TRUST BAR ─────────────────────────────────────────────────────────
    draw.rectangle([0, 296, W, 338], fill=C_LIGHT)
    f_trust = load_font(12, bold=True)

    trust_items = [
        "Certifie Bio",
        "Livraison Gratuite 50 EUR+",
        "Retour 30 jours",
        "Paiement Securise",
        "Support 24/7"
    ]
    trust_spacing = W // (len(trust_items) + 1)
    for i, item in enumerate(trust_items):
        tx = trust_spacing * (i + 1)
        item_bbox = f_trust.getbbox(item)
        item_w = item_bbox[2] - item_bbox[0]
        draw.text((tx - item_w // 2, 312), item, fill=C_PRIMARY, font=f_trust)
        # Green dot before each
        draw.ellipse([tx - item_w // 2 - 14, 316, tx - item_w // 2 - 4, 326], fill=C_SECONDARY)

    # ── SECTION: Best-Sellers ────────────────────────────────────────────
    f_section = load_font(28, bold=True)
    f_section_sub = load_font(14)

    draw.text((100, 358), "Nos Best-Sellers", fill=C_DARK, font=f_section)
    draw.text((100, 394), "Les formulations les plus populaires, validees par la science", fill=C_GRAY, font=f_section_sub)

    # ── PRODUCT CARDS ───────────────────────────────────────────────────
    card_w, card_h = 310, 300
    card_y = 430
    products = [
        {"name": "Vitamine D3 5000 UI", "price": "29,90 EUR", "stars": 5, "tag": "Best-Seller", "tag_color": C_ACCENT, "pill_c": C_ACCENT},
        {"name": "Omega-3 Ultra Pure", "price": "34,50 EUR", "stars": 5, "tag": "Nouveau", "tag_color": C_SECONDARY, "pill_c": C_SECONDARY},
        {"name": "Complexe Magnesium", "price": "24,90 EUR", "stars": 4, "tag": "Promo -15%", "tag_color": C_DANGER, "pill_c": (100, 180, 120)},
    ]

    f_prod_name = load_font(16, bold=True)
    f_prod_price = load_font(22, bold=True)
    f_prod_btn = load_font(13, bold=True)
    f_tag = load_font(11, bold=True)

    for i, prod in enumerate(products):
        cx = 100 + i * (card_w + 30)

        # Card shadow
        rounded_rect(draw, (cx+4, card_y+4, cx+card_w+4, card_y+card_h+4), 20, fill=C_LIGHT_GRAY)
        # Card
        rounded_rect(draw, (cx, card_y, cx+card_w, card_y+card_h), 20, fill=C_WHITE, outline=(230, 233, 237), width=1)

        # Product image area (pill bottle illustration)
        img_x = cx + (card_w - 130) // 2
        img_y = card_y + 22
        draw_gradient_v(img, (img_x, img_y, img_x + 130, img_y + 120), C_PRIMARY, (30, 130, 75))
        # Pill shape
        pill_cx = img_x + 65
        pill_cy = img_y + 60
        # Cap
        draw.rectangle([pill_cx-12, pill_cy-30, pill_cx+12, pill_cy-10], fill=C_DARK, width=0)
        draw.rectangle([pill_cx-14, pill_cy-12, pill_cx+14, pill_cy-5], fill=C_DARK, width=0)
        # Body
        draw.rounded_rectangle([pill_cx-18, pill_cy-5, pill_cx+18, pill_cy+35], radius=8, fill=prod["pill_c"])
        draw.rounded_rectangle([pill_cx-18, pill_cy-5, pill_cx+18, pill_cy+5], radius=4, fill=prod["pill_c"])
        # Label line
        draw.line([(pill_cx-10, pill_cy+10), (pill_cx+10, pill_cy+10)], fill=C_WHITE, width=2)
        draw.line([(pill_cx-8, pill_cy+18), (pill_cx+8, pill_cy+18)], fill=C_WHITE, width=1)

        # Tag badge
        tag_text = prod["tag"]
        tag_bbox = f_tag.getbbox(tag_text)
        tag_w = tag_bbox[2] - tag_bbox[0] + 16
        rounded_rect(draw, (cx + card_w - tag_w - 12, card_y + 12, cx + card_w - 12, card_y + 32), 8, fill=prod["tag_color"])
        draw.text((cx + card_w - tag_w - 4, card_y + 14), tag_text, fill=C_WHITE, font=f_tag)

        # Product name
        draw.text((cx + 22, card_y + 152), prod["name"], fill=C_DARK, font=f_prod_name)

        # Stars
        star_y = card_y + 178
        for s in range(prod["stars"]):
            sx = cx + 22 + s * 18
            draw.ellipse([sx, star_y, sx+12, star_y+12], fill=C_ACCENT)
        if prod["stars"] < 5:
            for s in range(prod["stars"], 5):
                sx = cx + 22 + s * 18
                draw.ellipse([sx, star_y, sx+12, star_y+12], fill=C_LIGHT_GRAY)

        # Rating text
        f_rating = load_font(11)
        draw.text((cx + 22 + 5 * 18 + 4, star_y - 1), "(124 avis)", fill=C_GRAY, font=f_rating)

        # Price
        draw.text((cx + 22, card_y + 200), prod["price"], fill=C_PRIMARY, font=f_prod_price)

        # Add to cart button
        btn_y = card_y + card_h - 48
        rounded_rect(draw, (cx + 22, btn_y, cx + card_w - 22, btn_y + 36), 12, fill=C_PRIMARY)
        cart_text = "Ajouter au panier"
        cart_bbox = f_prod_btn.getbbox(cart_text)
        cart_w = cart_bbox[2] - cart_bbox[0]
        draw.text((cx + (card_w - cart_w) // 2, btn_y + 9), cart_text, fill=C_WHITE, font=f_prod_btn)

    # ── FOOTER ───────────────────────────────────────────────────────────
    footer_y = H - 165
    draw.rectangle([0, footer_y, W, H], fill=C_DARK)

    f_footer_title = load_font(18, bold=True)
    f_footer_link = load_font(13)
    f_footer_copy = load_font(11)

    # Logo in footer
    fy = footer_y + 22
    draw.ellipse([104, fy, 128, fy + 24], fill=C_SECONDARY)
    draw.text((138, fy - 2), "NutriVitaX Pro", fill=C_SECONDARY, font=f_footer_title)
    draw.text((100, footer_y + 52), "Theme WooCommerce premium pour boutiques de", fill=C_GRAY, font=f_footer_link)
    draw.text((100, footer_y + 72), "complements alimentaires et nutraceutique.", fill=C_GRAY, font=f_footer_link)

    # Footer columns
    cols = [
        ("Produits", ["Vitamines", "Mineraux", "Proteines", "Antioxydants"]),
        ("Entreprise", ["A propos", "Blog", "Carrieres", "Contact"]),
        ("Support", ["FAQ", "Livraison", "Retours", "CGV"])
    ]
    col_x = 450
    for title, items in cols:
        draw.text((col_x, footer_y + 22), title, fill=C_WHITE, font=f_nav)
        iy = footer_y + 48
        for item in items:
            draw.text((col_x, iy), item, fill=C_GRAY, font=f_footer_link)
            iy += 20
        col_x += 180

    # Bottom bar
    draw.rectangle([0, H - 35, W, H], fill=(8, 16, 30))
    draw.text((100, H - 25), "2026 NutriVitaX Pro. Tous droits reserves. | Design BioLab Luxe | FSE Block Theme v0.1.0", fill=C_GRAY, font=f_footer_copy)

    # WordPress.org badge area (bottom right)
    f_wp = load_font(10)
    draw.text((W - 260, H - 25), "Compatible WordPress 6.7+ | WooCommerce 9.0+", fill=(80, 90, 100), font=f_wp)

    # ── SAVE ──────────────────────────────────────────────────────────────
    os.makedirs(os.path.dirname(OUTPUT), exist_ok=True)
    img.save(OUTPUT, "PNG")
    print(f"Screenshot saved: {OUTPUT}")
    print(f"Size: {img.size}")
    print(f"File size: {os.path.getsize(OUTPUT)} bytes")


if __name__ == "__main__":
    create_screenshot()
