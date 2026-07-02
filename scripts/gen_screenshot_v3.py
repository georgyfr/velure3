#!/usr/bin/env python3
"""
Generate a premium screenshot.png for Velure3 - Optimized to fit 1200x900.
"""

from PIL import Image, ImageDraw, ImageFont
import math, random

# Palette Velure3
C_BASE     = (250, 250, 248)
C_CONTRAST = (26, 26, 26)
C_GOLD     = (200, 169, 126)
C_COGNAC   = (139, 94, 60)
C_MUTED    = (232, 228, 222)
C_SOFT     = (245, 240, 235)
C_WHITE    = (255, 255, 255)
C_DARK     = (35, 35, 35)

W, H = 1200, 900

# Fonts
def lf(path, size):
    try: return ImageFont.truetype(path, size)
    except: return ImageFont.load_default()

FS    = "/usr/share/fonts/truetype/english/Carlito-Regular.ttf"
FSB   = "/usr/share/fonts/truetype/english/Carlito-Bold.ttf"
FSR   = "/usr/share/fonts/truetype/english/Tinos-Regular.ttf"
FSRB  = "/usr/share/fonts/truetype/english/Tinos-Bold.ttf"
FSRI  = "/usr/share/fonts/truetype/english/Tinos-Italic.ttf"

f_logo    = lf(FSB, 20)
f_nav     = lf(FS, 10.5)
f_eyebrow = lf(FS, 10)
f_h1      = lf(FSRB, 44)
f_h1b     = lf(FSRB, 44)
f_sub     = lf(FSRI, 13)
f_sec     = lf(FSRB, 18)
f_card_t  = lf(FS, 10)
f_card_p  = lf(FSB, 11)
f_btn     = lf(FSB, 9)
f_topbar  = lf(FS, 8.5)
f_small   = lf(FS, 8)
f_badge   = lf(FSB, 7.5)
f_feat    = lf(FSB, 9.5)
f_ft      = lf(FSB, 9)
f_ft_l    = lf(FS, 8.5)


def rrect(draw, xy, r, fill=None, outline=None, width=1):
    x1, y1, x2, y2 = xy
    r = min(r, (x2-x1)//2, (y2-y1)//2)
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


def fashion_img(img, x, y, w, h, seed=0, style="warm"):
    overlay = Image.new("RGBA", (w, h), (0, 0, 0, 0))
    od = ImageDraw.Draw(overlay)
    random.seed(seed)

    pals = {
        "warm":   [(215, 200, 180), (190, 175, 155), (170, 153, 135)],
        "cool":   [(185, 190, 200), (165, 172, 185), (145, 155, 170)],
        "gold":   [(200, 169, 126), (180, 152, 115), (160, 135, 100)],
        "dark":   [(62, 57, 52), (47, 42, 40), (37, 34, 32)],
        "blush":  [(225, 205, 195), (205, 185, 172), (190, 170, 157)],
        "olive":  [(175, 182, 158), (157, 165, 142), (142, 150, 128)],
        "navy":   [(72, 78, 98), (57, 62, 82), (47, 50, 70)],
        "sage":   [(195, 200, 182), (177, 183, 165), (160, 167, 148)],
        "taupe":  [(195, 185, 172), (178, 168, 155), (162, 152, 140)],
        "camel":  [(210, 180, 140), (192, 162, 122), (175, 145, 108)],
    }
    pal = pals.get(style, pals["warm"])

    # Vertical gradient
    for row in range(h):
        t = row / max(h - 1, 1)
        idx = min(int(t * (len(pal) - 1)), len(pal) - 2)
        lt = (t * (len(pal) - 1)) - idx
        c = tuple(int(pal[idx][i] * (1 - lt) + pal[idx+1][i] * lt) for i in range(3))
        od.line([(0, row), (w-1, row)], fill=(*c, 255))

    # Abstract fabric folds
    for _ in range(random.randint(3, 6)):
        cx = random.randint(w//6, 5*w//6)
        cy = random.randint(h//6, 5*h//6)
        rx = random.randint(w//8, w//3)
        ry = random.randint(h//6, h//3)
        alpha = random.randint(12, 28)
        od.ellipse([cx-rx, cy-ry, cx+rx, cy+ry], fill=(255, 255, 255, alpha))

    # Diagonal fabric lines
    for i in range(0, max(w, h)*2, random.randint(18, 35)):
        alpha = random.randint(6, 16)
        od.line([(i, 0), (i + h, h)], fill=(255, 255, 255, alpha), width=1)

    # Subtle silhouette
    cx, cy = w // 2, h // 2
    # Body shape
    pts = [
        (cx - w//6, h),
        (cx + w//6, h),
        (cx + w//10, h//3),
        (cx, h//5),
        (cx - w//10, h//3),
    ]
    od.polygon(pts, fill=(255, 255, 255, 10))

    # Vignette
    for i in range(10):
        alpha = int(2.5 * (10 - i) / 10)
        inset = i * 2
        if w - 2*inset > 4 and h - 2*inset > 4:
            rrect(od, [inset, inset, w-inset, h-inset], 3,
                  outline=(0, 0, 0, alpha), width=1)

    img.paste(Image.alpha_composite(
        Image.new("RGBA", (w, h), (0,0,0,0)), overlay).convert("RGB"), (x, y))


def tcenter(draw, y, text, font, fill, w=W):
    bb = draw.textbbox((0, 0), text, font=font)
    tw = bb[2] - bb[0]
    draw.text(((w - tw) // 2, y), text, font=font, fill=fill)


def main():
    img = Image.new("RGB", (W, H), C_BASE)
    draw = ImageDraw.Draw(img)
    y = 0

    # ═══ TOPBAR (26px) ═══
    draw.rectangle([0, y, W, y+26], fill=C_CONTRAST)
    draw.text((45, y+8), "Livraison gratuite d\u00e8s 75\u20ac  \u2022  Retours sous 30 jours",
              font=f_topbar, fill=(175, 170, 162))
    tr = "Mon Compte  |  Aide"
    bb = draw.textbbox((0, 0), tr, font=f_topbar)
    draw.text((W - (bb[2]-bb[0]) - 45, y+8), tr, font=f_topbar, fill=(175, 170, 162))
    y += 26

    # ═══ NAVBAR (48px) ═══
    draw.rectangle([0, y, W, y+48], fill=C_BASE)
    draw.line([(0, y+47), (W, y+47)], fill=C_MUTED)
    draw.text((50, y+13), "VELURE", font=f_logo, fill=C_CONTRAST)

    links = ["BOUTIQUE", "NOUVEAUT\u00c9S", "BEST-SELLERS", "LOOKBOOK", "LA MARQUE"]
    lx = 255
    for link in links:
        c = C_COGNAC if "NOUVEAUT" in link else C_CONTRAST
        bb = draw.textbbox((0, 0), link, font=f_nav)
        draw.text((lx, y+18), link, font=f_nav, fill=c)
        lx += (bb[2]-bb[0]) + 28

    # Cart badge
    draw.text((W-55, y+15), "\u263A", font=f_card_p, fill=C_CONTRAST)
    draw.text((W-85, y+15), "\u2665", font=f_card_p, fill=C_CONTRAST)
    draw.text((W-115, y+15), "\u2315", font=f_card_p, fill=C_CONTRAST)
    rrect(draw, [W-42, y+9, W-30, y+23], r=7, fill=C_GOLD)
    draw.text((W-40, y+10), "3", font=f_badge, fill=C_WHITE)
    y += 48

    # ═══ HERO (320px) ═══
    hero_h = 320
    fashion_img(img, 0, y, W, hero_h, seed=42, style="dark")

    # Overlay for readability
    ov = Image.new("RGBA", (W, hero_h), (0,0,0,0))
    od = ImageDraw.Draw(ov)
    for row in range(hero_h):
        t = row / hero_h
        if t > 0.25:
            a = int(190 * ((t - 0.25) / 0.75) ** 0.7)
        else:
            a = int(35 * t / 0.25)
        od.line([(0, row), (W-1, row)], fill=(22, 20, 18, a))
    for i in range(60):
        a = int(50 * (60-i)/60)
        od.line([(i, 0), (i, hero_h-1)], fill=(0, 0, 0, a))
        od.line([(W-1-i, 0), (W-1-i, hero_h-1)], fill=(0, 0, 0, a))
    img.paste(Image.alpha_composite(
        Image.new("RGBA", (W, hero_h), (0,0,0,0)), ov).convert("RGB"), (0, y))
    draw = ImageDraw.Draw(img)

    # Eyebrow + decorative lines
    eb = "AUTOMNE  /  HIVER  2025"
    bb = draw.textbbox((0, 0), eb, font=f_eyebrow)
    etw = bb[2] - bb[0]
    ex = (W - etw) // 2
    ey = y + 85
    draw.line([(ex-55, ey+6), (ex-10, ey+6)], fill=C_GOLD, width=1)
    draw.line([(ex+etw+10, ey+6), (ex+etw+55, ey+6)], fill=C_GOLD, width=1)
    draw.text((ex, ey), eb, font=f_eyebrow, fill=C_GOLD)

    # Title line 1
    t1 = "L'Art du Style"
    bb = draw.textbbox((0, 0), t1, font=f_h1)
    draw.text(((W-(bb[2]-bb[0]))//2, y+105), t1, font=f_h1, fill=C_WHITE)

    # Title line 2
    t2 = "Contemporain"
    bb = draw.textbbox((0, 0), t2, font=f_h1b)
    draw.text(((W-(bb[2]-bb[0]))//2, y+155), t2, font=f_h1b, fill=C_WHITE)

    # Subtitle
    st = "Des pi\u00e8ces d'exception qui red\u00e9finissent l'\u00e9l\u00e9gance moderne"
    bb = draw.textbbox((0, 0), st, font=f_sub)
    draw.text(((W-(bb[2]-bb[0]))//2, y+210), st, font=f_sub, fill=C_MUTED)

    # Two CTA buttons
    by = y + 248
    b1t = "EXPLORER LA COLLECTION"
    bb = draw.textbbox((0, 0), b1t, font=f_btn)
    b1w = bb[2]-bb[0]
    b1x = W//2 - b1w//2 - 120
    rrect(draw, [b1x, by, b1x+b1w+36, by+34], r=0, fill=C_WHITE)
    draw.text((b1x+18, by+12), b1t, font=f_btn, fill=C_CONTRAST)

    b2t = "VOIR LE LOOKBOOK"
    bb = draw.textbbox((0, 0), b2t, font=f_btn)
    b2w = bb[2]-bb[0]
    b2x = W//2 - b2w//2 + 120
    rrect(draw, [b2x, by, b2x+b2w+36, by+34], r=0, outline=C_WHITE, width=1)
    draw.text((b2x+18, by+12), b2t, font=f_btn, fill=C_WHITE)

    y += hero_h

    # ═══ FEATURES BAR (40px) ═══
    draw.rectangle([0, y, W, y+40], fill=C_SOFT)
    draw.line([(0, y+39), (W, y+39)], fill=C_MUTED)
    feats = [
        ("\u2713  Livraison gratuite d\u00e8s 75\u20ac", ""),
        ("\u21ba  Retours sous 30 jours", ""),
        ("\u2605  Paiement 100% s\u00e9curis\u00e9", ""),
        ("\u260e  Service client disponible", ""),
    ]
    sp = W // 4
    for i, (l1, l2) in enumerate(feats):
        bb = draw.textbbox((0, 0), l1, font=f_feat)
        tw = bb[2]-bb[0]
        draw.text((sp*i + (sp-tw)//2, y+13), l1, font=f_feat, fill=C_CONTRAST)
    y += 40

    # ═══ CATEGORIES SECTION (165px) ═══
    sec_title = "NOS CAT\u00c9GORIES"
    tcenter(draw, y+8, sec_title, f_sec, C_CONTRAST)
    cx = W // 2
    draw.line([(cx-40, y+32), (cx+40, y+32)], fill=C_GOLD, width=1)

    cats = [("FEMME", "warm"), ("HOMME", "navy"), ("ACCESSOIRES", "gold"), ("CHAUSSURES", "sage")]
    cw, ch, gap = 240, 100, 30
    total = 4*cw + 3*gap
    sx = (W - total) // 2
    cy = y + 44

    for i, (label, style) in enumerate(cats):
        cx = sx + i*(cw+gap)
        fashion_img(img, cx, cy, cw, ch, seed=i*7+3, style=style)

        # Bottom gradient overlay
        ov = Image.new("RGBA", (cw, ch), (0,0,0,0))
        od = ImageDraw.Draw(ov)
        for row in range(ch//2):
            a = int(140 * row / (ch//2))
            od.line([(0, ch//2+row), (cw-1, ch//2+row)], fill=(0, 0, 0, a))
        img.paste(Image.alpha_composite(
            Image.new("RGBA", (cw, ch), (0,0,0,0)), ov).convert("RGB"), (cx, cy))

        draw = ImageDraw.Draw(img)
        bb = draw.textbbox((0, 0), label, font=f_btn)
        draw.text((cx + (cw-(bb[2]-bb[0]))//2, cy + ch - 28), label,
                  font=f_btn, fill=C_WHITE)

    y += 165

    # ═══ PRODUCTS SECTION (180px) ═══
    draw.rectangle([0, y, W, y+4], fill=C_MUTED)

    pt = "PRODUITS VEDETTES"
    tcenter(draw, y+8, pt, f_sec, C_CONTRAST)
    cx = W // 2
    draw.line([(cx-40, y+32), (cx+40, y+32)], fill=C_GOLD, width=1)

    products = [
        ("Robe en Soie", "189,00 \u20ac", "blush", "Nouveau"),
        ("Manteau Cognac", "345,00 \u20ac", "camel", ""),
        ("Chemise en Lin", "95,00 \u20ac", "taupe", "Best-seller"),
        ("Pantalon Tailleur", "165,00 \u20ac", "olive", ""),
        ("Pull Oversize", "125,00 \u20ac", "sage", "Promo"),
    ]

    cw2, ch2, gap2 = 192, 78, 18
    total2 = 5*cw2 + 4*gap2
    sx2 = (W - total2) // 2
    py = y + 42

    for i, (name, price, style, badge) in enumerate(products):
        px = sx2 + i*(cw2+gap2)

        # Product image
        fashion_img(img, px, py, cw2, 55, seed=i*11+20, style=style)

        # Info area
        iy = py + 55
        draw.rectangle([px, iy, px+cw2, iy+48], fill=C_WHITE)
        draw.line([(px, iy), (px+cw2, iy)], fill=(242, 239, 234))

        # Badge
        if badge:
            bw = 50 if "Nouveau" in badge or "Promo" in badge else 62
            rrect(draw, [px+6, py+5, px+6+bw, py+18], r=2, fill=C_GOLD)
            bb = draw.textbbox((0, 0), badge.upper(), font=f_badge)
            draw.text((px+6+(bw-(bb[2]-bb[0]))//2, py+6), badge.upper(),
                      font=f_badge, fill=C_WHITE)

        draw.text((px+8, iy+7), name, font=f_card_t, fill=C_CONTRAST)
        draw.text((px+8, iy+22), price, font=f_card_p, fill=C_COGNAC)
        draw.text((px+8, iy+36), "\u2605 \u2605 \u2605 \u2605 \u2606", font=f_small, fill=C_GOLD)

    y += 180

    # ═══ NEWSLETTER BAR (48px) ═══
    draw.rectangle([0, y, W, y+48], fill=C_CONTRAST)
    nt = "Rejoignez l'univers Velure  \u2014  -15% sur votre premi\u00e8re commande"
    tcenter(draw, y+17, nt, f_nav, C_MUTED)
    y += 48

    # ═══ FOOTER (52px) ═══
    draw.rectangle([0, y, W, y+52], fill=C_DARK)
    draw.line([(0, y), (W, y)], fill=C_GOLD, width=2)

    ft_cols = [
        "VELURE     Femme  Homme  Accessoires  Chaussures",
        "INFOS     Histoire  Tailles  Contact  FAQ",
        "AIDE     Livraison  Retours  Paiement  CGV",
        "\u00a9 2025 Velure     Instagram  TikTok  Pinterest",
    ]
    cfw = W // 4
    for i, col in enumerate(ft_cols):
        draw.text((cfw*i + 18, y+14), col, font=f_ft_l, fill=(150, 146, 140))

    # Subtle texture
    random.seed(42)
    ov = Image.new("RGBA", (W, H), (0,0,0,0))
    od = ImageDraw.Draw(ov)
    for _ in range(5000):
        ox = random.randint(0, W-1)
        oy = random.randint(0, H-1)
        od.point((ox, oy), fill=(0, 0, 0, random.randint(2, 5)))
    img = Image.alpha_composite(img.convert("RGBA"), ov).convert("RGB")

    out = "/home/z/my-project/velure3/screenshot.png"
    img.save(out, "PNG")
    img.save("/home/z/my-project/download/velure3-screenshot.png", "PNG")
    print(f"OK: {W}x{H} - {H - y - 52}px remaining (y={y})")


if __name__ == "__main__":
    main()