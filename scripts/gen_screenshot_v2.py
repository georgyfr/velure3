#!/usr/bin/env python3
"""
Generate a premium screenshot.png for the Velure3 WordPress theme.
Creates a realistic fashion e-commerce website mockup at 1200x900.
Uses the Velure3 design system: cream, black, gold, cognac palette.
"""

from PIL import Image, ImageDraw, ImageFont, ImageFilter
import math, random

# ── Palette Velure3 ──
C_BASE      = (250, 250, 248)   # #FAFAF8
C_CONTRAST  = (26, 26, 26)      # #1A1A1A
C_GOLD      = (200, 169, 126)   # #C8A97E
C_COGNAC    = (139, 94, 60)     # #8B5E3C
C_MUTED     = (232, 228, 222)   # #E8E4DE
C_SOFT      = (245, 240, 235)   # #F5F0EB
C_WHITE     = (255, 255, 255)
C_DARK      = (30, 30, 30)
C_MID_GRAY  = (120, 115, 108)
C_LIGHT_GRAY= (200, 196, 190)

W, H = 1200, 900

# ── Fonts ──
def load_font(path, size):
    try:
        return ImageFont.truetype(path, size)
    except:
        return ImageFont.load_default()

FONT_SANS    = "/usr/share/fonts/truetype/english/Carlito-Regular.ttf"
FONT_SANS_B  = "/usr/share/fonts/truetype/english/Carlito-Bold.ttf"
FONT_SERIF   = "/usr/share/fonts/truetype/english/Tinos-Regular.ttf"
FONT_SERIF_B = "/usr/share/fonts/truetype/english/Tinos-Bold.ttf"
FONT_SERIF_I = "/usr/share/fonts/truetype/english/Tinos-Italic.ttf"

f_logo     = load_font(FONT_SANS_B, 22)
f_nav      = load_font(FONT_SANS, 11)
f_eyebrow  = load_font(FONT_SANS, 11)
f_hero_h1  = load_font(FONT_SERIF_B, 52)
f_hero_sub = load_font(FONT_SERIF_I, 14)
f_section  = load_font(FONT_SERIF_B, 22)
f_card_t   = load_font(FONT_SANS, 11)
f_card_p   = load_font(FONT_SANS_B, 12)
f_btn      = load_font(FONT_SANS_B, 10)
f_footer_h = load_font(FONT_SANS_B, 11)
f_footer_l = load_font(FONT_SANS, 10)
f_topbar   = load_font(FONT_SANS, 9)
f_small    = load_font(FONT_SANS, 9)
f_badge    = load_font(FONT_SANS_B, 8)
f_feat     = load_font(FONT_SANS_B, 11)
f_feat_d   = load_font(FONT_SANS, 9)


def draw_rounded_rect(draw, xy, radius, fill=None, outline=None, width=1):
    """Draw a rounded rectangle."""
    x1, y1, x2, y2 = xy
    r = min(radius, (x2-x1)//2, (y2-y1)//2)
    if fill:
        draw.rectangle([x1+r, y1, x2-r, y2], fill=fill)
        draw.rectangle([x1, y1+r, x2, y2-r], fill=fill)
        draw.pieslice([x1, y1, x1+2*r, y1+2*r], 180, 270, fill=fill)
        draw.pieslice([x2-2*r, y1, x2, y1+2*r], 270, 360, fill=fill)
        draw.pieslice([x1, y2-2*r, x1+2*r, y2], 90, 180, fill=fill)
        draw.pieslice([x2-2*r, y2-2*r, x2, y2], 0, 90, fill=fill)
    if outline:
        draw.arc([x1, y1, x1+2*r, y1+2*r], 180, 270, fill=outline, width=width)
        draw.arc([x2-2*r, y1, x2, y1+2*r], 270, 360, fill=outline, width=width)
        draw.arc([x1, y2-2*r, x1+2*r, y2], 90, 180, fill=outline, width=width)
        draw.arc([x2-2*r, y2-2*r, x2, y2], 0, 90, fill=outline, width=width)
        draw.line([x1+r, y1, x2-r, y1], fill=outline, width=width)
        draw.line([x1+r, y2, x2-r, y2], fill=outline, width=width)
        draw.line([x1, y1+r, x1, y2-r], fill=outline, width=width)
        draw.line([x2, y1+r, x2, y2-r], fill=outline, width=width)


def draw_fashion_image(img, x, y, w, h, seed=0, style="warm"):
    """Draw a fashion-style placeholder image using gradients and shapes."""
    overlay = Image.new("RGBA", (w, h), (0, 0, 0, 0))
    od = ImageDraw.Draw(overlay)

    random.seed(seed)

    # Background gradient
    palettes = {
        "warm":   [(210, 195, 175), (185, 170, 150), (165, 148, 130)],
        "cool":   [(180, 185, 195), (160, 168, 180), (140, 150, 165)],
        "gold":   [(200, 169, 126), (175, 148, 110), (155, 130, 95)],
        "dark":   [(60, 55, 50), (45, 40, 38), (35, 32, 30)],
        "blush":  [(220, 200, 190), (200, 180, 168), (185, 165, 152)],
        "olive":  [(170, 178, 155), (152, 160, 138), (138, 145, 122)],
        "navy":   [(70, 75, 95), (55, 60, 80), (45, 48, 68)],
        "sage":   [(190, 195, 180), (172, 178, 162), (155, 162, 145)],
    }
    pal = palettes.get(style, palettes["warm"])

    # Vertical gradient
    for row in range(h):
        t = row / max(h - 1, 1)
        idx = min(int(t * (len(pal) - 1)), len(pal) - 2)
        lt = (t * (len(pal) - 1)) - idx
        c = tuple(int(pal[idx][i] * (1 - lt) + pal[idx+1][i] * lt) for i in range(3))
        od.line([(0, row), (w-1, row)], fill=(*c, 255))

    # Add abstract fashion silhouette shapes
    # Fabric drape curves
    for _ in range(random.randint(2, 4)):
        cx = random.randint(w//4, 3*w//4)
        cy = random.randint(h//4, 3*h//4)
        rx = random.randint(w//6, w//3)
        ry = random.randint(h//4, h//2)
        alpha = random.randint(15, 35)
        od.ellipse([cx-rx, cy-ry, cx+rx, cy+ry], fill=(255, 255, 255, alpha))

    # Subtle diagonal lines (fabric texture)
    for i in range(0, max(w, h), random.randint(20, 40)):
        alpha = random.randint(8, 20)
        x_start = random.randint(-w//2, w//2)
        od.line([(x_start, 0), (x_start + w, h)], fill=(255, 255, 255, alpha), width=1)

    # Mannequin/silhouette hint
    cx, cy = w // 2, h // 2
    # Head
    head_r = min(w, h) // 8
    od.ellipse([cx-head_r, cy-h//3-head_r, cx+head_r, cy-h//3+head_r],
               fill=(255, 255, 255, 20))
    # Body triangle
    body_pts = [
        (cx - w//5, h),
        (cx + w//5, h),
        (cx, cy - h//6)
    ]
    od.polygon(body_pts, fill=(255, 255, 255, 12))

    # Gentle vignette
    for i in range(30):
        alpha = int(3 * (30 - i) / 30)
        inset = i * 2
        if inset < w//2 and inset < h//2:
            draw_rounded_rect(od, [inset, inset, w-inset, h-inset], 4,
                              outline=(0, 0, 0, alpha), width=1)

    img.paste(Image.alpha_composite(Image.new("RGBA", (w, h), (0,0,0,0)), overlay).convert("RGB"),
              (x, y))


def draw_text_centered(draw, y, text, font, fill):
    bbox = draw.textbbox((0, 0), text, font=font)
    tw = bbox[2] - bbox[0]
    draw.text(((W - tw) // 2, y), text, font=font, fill=fill)


def draw_hero_section(img, y_start):
    """Draw a stunning hero section with fashion imagery."""
    draw = ImageDraw.Draw(img)
    hero_h = 420

    # Hero background with fashion image
    draw_fashion_image(img, 0, y_start, W, hero_h, seed=42, style="dark")

    # Dark gradient overlay for text readability
    overlay = Image.new("RGBA", (W, hero_h), (0, 0, 0, 0))
    od = ImageDraw.Draw(overlay)

    # Bottom gradient (stronger)
    for row in range(hero_h):
        t = row / hero_h
        if t > 0.3:
            alpha = int(180 * ((t - 0.3) / 0.7) ** 0.8)
        else:
            alpha = int(30 * t / 0.3)
        od.line([(0, row), (W-1, row)], fill=(20, 18, 16, alpha))

    # Side vignettes
    for i in range(80):
        alpha = int(60 * (80 - i) / 80)
        od.line([(i, 0), (i, hero_h-1)], fill=(0, 0, 0, alpha))
        od.line([(W-1-i, 0), (W-1-i, hero_h-1)], fill=(0, 0, 0, alpha))

    img.paste(Image.alpha_composite(Image.new("RGBA", (W, hero_h), (0,0,0,0)), overlay).convert("RGB"),
              (0, y_start))

    draw = ImageDraw.Draw(img)

    # Eyebrow
    eyebrow = "AUTOMNE  /  HIVER  2025"
    bbox = draw.textbbox((0, 0), eyebrow, font=f_eyebrow)
    tw = bbox[2] - bbox[0]
    ex = (W - tw) // 2
    # Decorative lines around eyebrow
    line_w = 40
    line_y = y_start + 140
    draw.line([(ex - line_w - 15, line_y + 7), (ex - 12, line_y + 7)],
              fill=C_GOLD, width=1)
    draw.line([(ex + tw + 12, line_y + 7), (ex + tw + line_w + 15, line_y + 7)],
              fill=C_GOLD, width=1)
    draw.text((ex, line_y), eyebrow, font=f_eyebrow, fill=C_GOLD)

    # Hero Title
    title = "L'Art du Style"
    bbox = draw.textbbox((0, 0), title, font=f_hero_h1)
    tw = bbox[2] - bbox[0]
    draw.text(((W - tw) // 2, y_start + 170), title, font=f_hero_h1, fill=C_WHITE)

    title2 = "Contemporain"
    bbox = draw.textbbox((0, 0), title2, font=f_hero_h1)
    tw2 = bbox[2] - bbox[0]
    draw.text(((W - tw2) // 2, y_start + 230), title2, font=f_hero_h1, fill=C_WHITE)

    # Subtitle
    sub = "Des pieces d'exception qui redefinissent l'elegance moderne"
    bbox = draw.textbbox((0, 0), sub, font=f_hero_sub)
    tw = bbox[2] - bbox[0]
    draw.text(((W - tw) // 2, y_start + 305), sub, font=f_hero_sub, fill=C_MUTED)

    # CTA Buttons
    btn_y = y_start + 350
    # Primary button
    btn1_text = "EXPLORER LA COLLECTION"
    bbox = draw.textbbox((0, 0), btn1_text, font=f_btn)
    bw1 = bbox[2] - bbox[0]
    btn1_x = (W // 2) - bw1 // 2 - 130
    draw_rounded_rect(draw, [btn1_x, btn_y, btn1_x + bw1 + 40, btn_y + 38],
                      radius=0, fill=C_WHITE)
    draw.text((btn1_x + 20, btn_y + 13), btn1_text, font=f_btn, fill=C_CONTRAST)

    # Outline button
    btn2_text = "VOIR LE LOOKBOOK"
    bbox = draw.textbbox((0, 0), btn2_text, font=f_btn)
    bw2 = bbox[2] - bbox[0]
    btn2_x = (W // 2) - bw2 // 2 + 130
    draw_rounded_rect(draw, [btn2_x, btn_y, btn2_x + bw2 + 40, btn_y + 38],
                      radius=0, outline=C_WHITE, width=1)
    draw.text((btn2_x + 20, btn_y + 13), btn2_text, font=f_btn, fill=C_WHITE)

    return y_start + hero_h


def draw_features_bar(img, y):
    """Draw trust/features bar."""
    draw = ImageDraw.Draw(img)
    bar_h = 48
    draw.rectangle([0, y, W, y + bar_h], fill=C_SOFT)
    draw.line([(0, y + bar_h - 1), (W, y + bar_h - 1)], fill=C_MUTED, width=1)

    features = [
        ("\u2714  Livraison gratuite", "d\u00e8s 75\u20ac"),
        ("\u21ba  Retours sous", "30 jours"),
        ("\u2605  Paiement", "s\u00e9curis\u00e9"),
        ("\u260e  Service client", "disponible"),
    ]

    spacing = W // len(features)
    for i, (line1, line2) in enumerate(features):
        cx = spacing * i + spacing // 2
        bbox1 = draw.textbbox((0, 0), line1, font=f_feat)
        bbox2 = draw.textbbox((0, 0), line2, font=f_feat)
        tw1 = bbox1[2] - bbox1[0]
        tw2 = bbox2[2] - bbox2[0]
        draw.text((cx - tw1//2, y + 7), line1, font=f_feat, fill=C_CONTRAST)
        draw.text((cx - tw2//2, y + 24), line2, font=f_feat, fill=C_COGNAC)

    return y + bar_h


def draw_categories_section(img, y):
    """Draw categories grid section."""
    draw = ImageDraw.Draw(img)
    sec_h = 230

    # Section title
    draw.rectangle([0, y, W, y + 10], fill=C_BASE)
    title = "NOS CATEGORIES"
    bbox = draw.textbbox((0, 0), title, font=f_section)
    tw = bbox[2] - bbox[0]
    draw.text(((W - tw) // 2, y + 18), title, font=f_section, fill=C_CONTRAST)

    # Underline decoration
    line_w = 50
    cx = W // 2
    draw.line([(cx - line_w, y + 48), (cx + line_w, y + 48)], fill=C_GOLD, width=1)

    # 4 category cards
    card_w = 250
    card_h = 140
    gap = 30
    total = 4 * card_w + 3 * gap
    start_x = (W - total) // 2
    card_y = y + 65

    categories = [
        ("FEMME", "warm"),
        ("HOMME", "navy"),
        ("ACCESSOIRES", "gold"),
        ("CHAUSSURES", "sage"),
    ]

    for i, (label, style) in enumerate(categories):
        cx = start_x + i * (card_w + gap)
        # Card image
        draw_fashion_image(img, cx, card_y, card_w, card_h, seed=i*7+3, style=style)

        # Hover overlay effect
        overlay = Image.new("RGBA", (card_w, card_h), (0, 0, 0, 0))
        od = ImageDraw.Draw(overlay)
        od.rectangle([0, card_h//2, card_w, card_h], fill=(0, 0, 0, 120))
        img.paste(Image.alpha_composite(
            Image.new("RGBA", (card_w, card_h), (0,0,0,0)), overlay).convert("RGB"),
            (cx, card_y))

        draw = ImageDraw.Draw(img)
        # Label on card
        bbox = draw.textbbox((0, 0), label, font=f_btn)
        lw = bbox[2] - bbox[0]
        draw.text((cx + (card_w - lw) // 2, card_y + card_h - 32), label,
                  font=f_btn, fill=C_WHITE)

    return y + sec_h


def draw_product_cards_section(img, y):
    """Draw a row of product cards."""
    draw = ImageDraw.Draw(img)
    sec_h = 205

    draw.rectangle([0, y, W, y + 5], fill=C_MUTED)

    # Section header
    title = "PRODUITS VEDETTES"
    bbox = draw.textbbox((0, 0), title, font=f_section)
    tw = bbox[2] - bbox[0]
    draw.text(((W - tw) // 2, y + 12), title, font=f_section, fill=C_CONTRAST)

    line_w = 50
    cx = W // 2
    draw.line([(cx - line_w, y + 42), (cx + line_w, y + 42)], fill=C_GOLD, width=1)

    # 4 product cards
    card_w = 240
    card_img_h = 100
    gap = 25
    total = 4 * card_w + 3 * gap
    start_x = (W - total) // 2
    card_y = y + 55

    products = [
        ("Robe en Soie", "189,00 \u20ac", "blush", "Nouveau"),
        ("Manteau Cognac", "345,00 \u20ac", "gold", ""),
        ("Chemise en Lin", "95,00 \u20ac", "warm", "Best-seller"),
        ("Pantalon Tailleur", "165,00 \u20ac", "olive", ""),
    ]

    for i, (name, price, style, badge) in enumerate(products):
        cx = start_x + i * (card_w + gap)

        # Product image
        draw_fashion_image(img, cx, card_y, card_w, card_img_h,
                           seed=i*11+20, style=style)

        # Bottom info area
        info_y = card_y + card_img_h
        draw.rectangle([cx, info_y, cx + card_w, info_y + 72], fill=C_WHITE)
        draw.line([(cx, info_y), (cx + card_w, info_y)], fill=(240, 237, 232), width=1)

        # Badge
        if badge:
            badge_w = 65 if badge == "Best-seller" else 55
            draw_rounded_rect(draw, [cx + 8, card_y + 8, cx + 8 + badge_w, card_y + 24],
                              radius=2, fill=C_GOLD)
            bbox = draw.textbbox((0, 0), badge.upper(), font=f_badge)
            bw = bbox[2] - bbox[0]
            draw.text((cx + 8 + (badge_w - bw) // 2, card_y + 9),
                      badge.upper(), font=f_badge, fill=C_WHITE)

        # Product name
        draw.text((cx + 10, info_y + 10), name, font=f_card_t, fill=C_CONTRAST)
        # Price
        draw.text((cx + 10, info_y + 28), price, font=f_card_p, fill=C_COGNAC)

        # Star rating
        star_y = info_y + 48
        draw.text((cx + 10, star_y), "\u2605 \u2605 \u2605 \u2605 \u2606",
                  font=f_small, fill=C_GOLD)

    return y + sec_h


def draw_newsletter_bar(img, y):
    """Draw newsletter subscription bar."""
    draw = ImageDraw.Draw(img)
    bar_h = 65

    draw.rectangle([0, y, W, y + bar_h], fill=C_CONTRAST)

    # Text
    text = "Rejoignez l'univers Velure  \u2014  Inscrivez-vous et recevez -15% sur votre premi\u00e8re commande"
    bbox = draw.textbbox((0, 0), text, font=f_nav)
    tw = bbox[2] - bbox[0]
    draw.text(((W - tw) // 2, y + 24), text, font=f_nav, fill=C_MUTED)

    return y + bar_h


def draw_mini_footer(img, y):
    """Draw a mini footer section."""
    draw = ImageDraw.Draw(img)
    bar_h = 55

    draw.rectangle([0, y, W, y + bar_h], fill=(35, 35, 35))

    # 4 columns of links
    cols = [
        "VELURE    Femme  Homme  Accessoires  Chaussures",
        "INFOS    Notre Histoire  Guide des Tailles  Contact",
        "AIDE    Livraison  Retours  Paiement S\u00e9curis\u00e9",
        "\u00a9 2025 Velure    Instagram  TikTok  Pinterest",
    ]
    col_w = W // 4
    for i, col in enumerate(cols):
        draw.text((col_w * i + 20, y + 14), col, font=f_small, fill=(160, 156, 150))

    # Gold accent line at top
    draw.line([(0, y), (W, y)], fill=C_GOLD, width=2)

    return y + bar_h


def draw_topbar(img, y):
    """Draw thin top info bar."""
    draw = ImageDraw.Draw(img)
    bar_h = 28

    draw.rectangle([0, y, W, y + bar_h], fill=C_CONTRAST)

    text_l = "Livraison gratuite d\u00e8s 75\u20ac  \u2022  Retours sous 30 jours"
    text_r = "Mon Compte  |  Aide"

    bbox_l = draw.textbbox((0, 0), text_l, font=f_topbar)
    draw.text((40, y + 8), text_l, font=f_topbar, fill=(180, 175, 168))

    bbox_r = draw.textbbox((0, 0), text_r, font=f_topbar)
    rw = bbox_r[2] - bbox_r[0]
    draw.text((W - rw - 40, y + 8), text_r, font=f_topbar, fill=(180, 175, 168))

    return y + bar_h


def draw_navbar(img, y):
    """Draw the main navigation bar."""
    draw = ImageDraw.Draw(img)
    bar_h = 52

    draw.rectangle([0, y, W, y + bar_h], fill=C_BASE)
    draw.line([(0, y + bar_h - 1), (W, y + bar_h - 1)], fill=C_MUTED, width=1)

    # Logo
    draw.text((50, y + 14), "VELURE", font=f_logo, fill=C_CONTRAST)

    # Nav links
    links = ["BOUTIQUE", "NOUVEAUT\u00c9S", "BEST-SELLERS", "LOOKBOOK", "LA MARQUE"]
    link_start = 260
    for i, link in enumerate(links):
        color = C_COGNAC if link == "NOUVEAUT\u00c9S" else C_CONTRAST
        draw.text((link_start + i * 130, y + 18), link, font=f_nav, fill=color)

    # Right icons (simplified)
    # Search icon
    draw.text((W - 180, y + 16), "\u2315", font=f_card_p, fill=C_CONTRAST)
    # Account
    draw.text((W - 140, y + 16), "\u263A", font=f_card_p, fill=C_CONTRAST)
    # Heart
    draw.text((W - 100, y + 16), "\u2665", font=f_card_p, fill=C_CONTRAST)
    # Cart
    draw.text((W - 60, y + 16), "\u2706", font=f_card_p, fill=C_CONTRAST)
    # Cart count badge
    draw_rounded_rect(draw, [W-48, y+10, W-36, y+24], radius=8, fill=C_GOLD)
    draw.text((W-46, y+11), "3", font=f_badge, fill=C_WHITE)

    return y + bar_h


def apply_subtle_texture(img):
    """Apply a very subtle noise texture for premium feel."""
    import random
    w, h = img.size
    overlay = Image.new("RGBA", (w, h), (0, 0, 0, 0))
    od = ImageDraw.Draw(overlay)
    random.seed(123)
    for _ in range(8000):
        x = random.randint(0, w-1)
        y = random.randint(0, h-1)
        alpha = random.randint(2, 6)
        od.point((x, y), fill=(0, 0, 0, alpha))
    return Image.alpha_composite(img.convert("RGBA"), overlay).convert("RGB")


def main():
    img = Image.new("RGB", (W, H), C_BASE)

    # 1. Top bar
    y = draw_topbar(img, 0)

    # 2. Navbar
    y = draw_navbar(img, y)

    # 3. Hero section
    y = draw_hero_section(img, y)

    # 4. Features bar
    y = draw_features_bar(img, y)

    # 5. Categories
    y = draw_categories_section(img, y)

    # 6. Products
    y = draw_product_cards_section(img, y)

    # 7. Newsletter bar
    y = draw_newsletter_bar(img, y)

    # 8. Mini footer
    draw_mini_footer(img, y)

    # Apply subtle texture
    img = apply_subtle_texture(img)

    # Save
    out_path = "/home/z/my-project/velure3/screenshot.png"
    img.save(out_path, "PNG", quality=95)
    print(f"screenshot.png genere: {img.size}")

    # Also save a copy for download
    img.save("/home/z/my-project/download/velure3-screenshot.png", "PNG", quality=95)
    print("Copie sauvegardee dans /home/z/my-project/download/")


if __name__ == "__main__":
    main()