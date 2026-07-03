#!/usr/bin/env python3
"""
Velure3 Premium Screenshot Generator - Clean approach.
Draws a realistic fashion website mockup at 1200x900.
"""
from PIL import Image, ImageDraw, ImageFont, ImageFilter
import random, math

W, H = 1200, 900
C = {
    'base': (250, 250, 248), 'dark': (26, 26, 26), 'gold': (200, 169, 126),
    'cognac': (139, 94, 60), 'muted': (232, 228, 222), 'soft': (245, 240, 235),
    'white': (255, 255, 255), 'footer': (35, 35, 35), 'mid': (120, 115, 108),
    'txt_light': (175, 170, 162), 'txt_foot': (150, 146, 140),
    'border': (242, 239, 234),
}

def lf(p, s):
    try: return ImageFont.truetype(p, s)
    except: return ImageFont.load_default()

FB = "/usr/share/fonts/truetype/english/Carlito-Bold.ttf"
FR = "/usr/share/fonts/truetype/english/Carlito-Regular.ttf"
TB = "/usr/share/fonts/truetype/english/Tinos-Bold.ttf"
TR = "/usr/share/fonts/truetype/english/Tinos-Regular.ttf"
TI = "/usr/share/fonts/truetype/english/Tinos-Italic.ttf"

f = {
    'logo': lf(FB, 20), 'nav': lf(FR, 10.5), 'eyebrow': lf(FR, 10),
    'h1': lf(TB, 44), 'sub': lf(TI, 13), 'sec': lf(TB, 18),
    'card_t': lf(FR, 10), 'card_p': lf(FB, 11), 'btn': lf(FB, 9),
    'topbar': lf(FR, 8.5), 'small': lf(FR, 8), 'badge': lf(FB, 7.5),
    'feat': lf(FB, 9.5), 'ft_l': lf(FR, 8.5),
}


def rrect(d, xy, r, **kw):
    x1, y1, x2, y2 = xy
    r = min(r, (x2-x1)//2, (y2-y1)//2)
    fill = kw.get('fill'); outline = kw.get('outline'); width = kw.get('width', 1)
    if fill:
        d.rectangle([x1+r, y1, x2-r, y2], fill=fill)
        d.rectangle([x1, y1+r, x2, y2-r], fill=fill)
        for cx, cy, s, e in [(x1,y1,180,270),(x2,y1,270,360),(x1,y2,90,180),(x2,y2,0,90)]:
            d.pieslice([cx-r, cy-r, cx+r, cy+r], s, e, fill=fill)
    if outline:
        for cx, cy, s, e in [(x1,y1,180,270),(x2,y1,270,360),(x1,y2,90,180),(x2,y2,0,90)]:
            d.arc([cx-r, cy-r, cx+r, cy+r], s, e, fill=outline, width=width)
        d.line([x1+r,y1, x2-r,y1], fill=outline, width=width)
        d.line([x1+r,y2, x2-r,y2], fill=outline, width=width)
        d.line([x1,y1+r, x1,y2-r], fill=outline, width=width)
        d.line([x2,y1+r, x2,y2-r], fill=outline, width=width)


def tc(d, y, text, font, fill):
    bb = d.textbbox((0, 0), text, font=font)
    d.text(((W - bb[2] + bb[0]) // 2, y), text, font=font, fill=fill)


def make_fashion_tile(w, h, seed, style):
    """Create a fashion-style tile as RGB image."""
    random.seed(seed)
    pals = {
        "warm": [(215,200,180),(190,175,155),(170,153,135)],
        "cool": [(185,190,200),(165,172,185),(145,155,170)],
        "gold": [(200,169,126),(180,152,115),(160,135,100)],
        "dark": [(62,57,52),(47,42,40),(37,34,32)],
        "blush": [(225,205,195),(205,185,172),(190,170,157)],
        "olive": [(175,182,158),(157,165,142),(142,150,128)],
        "navy": [(72,78,98),(57,62,82),(47,50,70)],
        "sage": [(195,200,182),(177,183,165),(160,167,148)],
        "taupe": [(195,185,172),(178,168,155),(162,152,140)],
        "camel": [(210,180,140),(192,162,122),(175,145,108)],
    }
    pal = pals.get(style, pals["warm"])
    tile = Image.new("RGB", (w, h))
    px = tile.load()

    # Vertical gradient
    for row in range(h):
        t = row / max(h-1, 1)
        i = min(int(t * (len(pal)-1)), len(pal)-2)
        lt = t * (len(pal)-1) - i
        c = tuple(int(pal[i][j]*(1-lt) + pal[i+1][j]*lt) for j in range(3))
        for col in range(w):
            px[col, row] = c

    # Fabric fold ellipses
    td = ImageDraw.Draw(tile)
    for _ in range(random.randint(3, 5)):
        cx = random.randint(w//6, 5*w//6)
        cy = random.randint(h//6, 5*h//6)
        rx = random.randint(w//8, w//3)
        ry = random.randint(h//6, h//3)
        alpha_color = tuple(min(c + random.randint(15, 30), 255) for c in pal[0])
        td.ellipse([cx-rx, cy-ry, cx+rx, cy+ry], fill=alpha_color)

    # Diagonal lines (fabric texture)
    for i in range(0, max(w, h)*2, random.randint(20, 40)):
        lc = tuple(min(c + random.randint(5, 15), 255) for c in pal[1])
        td.line([(i, 0), (i + h, h)], fill=lc, width=1)

    # Body silhouette hint
    cx2, cy2 = w // 2, h // 2
    pts = [(cx2-w//6, h), (cx2+w//6, h), (cx2+w//10, h//3), (cx2, h//5), (cx2-w//10, h//3)]
    sc = tuple(min(c + 8, 255) for c in pal[0])
    td.polygon(pts, fill=sc)

    # Subtle blur for smoothness
    tile = tile.filter(ImageFilter.GaussianBlur(radius=1))

    # Dark gradient overlay for bottom (for label readability)
    ov = Image.new("RGBA", (w, h), (0,0,0,0))
    od = ImageDraw.Draw(ov)
    for row in range(h//2):
        a = int(140 * row / (h//2))
        od.line([(0, h//2+row), (w-1, h//2+row)], fill=(0,0,0,a))

    tile = Image.alpha_composite(tile.convert("RGBA"), ov).convert("RGB")
    return tile


def main():
    img = Image.new("RGB", (W, H), C['base'])
    d = ImageDraw.Draw(img)

    # ════════════════════════════════════════
    # TOPBAR (26px)
    # ════════════════════════════════════════
    d.rectangle([0, 0, W, 26], fill=C['dark'])
    d.text((45, 8), "Livraison gratuite des 75 EUR  |  Retours sous 30 jours",
           font=f['topbar'], fill=C['txt_light'])
    tr = "Mon Compte  |  Aide"
    bb = d.textbbox((0,0), tr, font=f['topbar'])
    d.text((W-(bb[2]-bb[0])-45, 8), tr, font=f['topbar'], fill=C['txt_light'])

    # ════════════════════════════════════════
    # NAVBAR (48px) - y=26
    # ════════════════════════════════════════
    ny = 26
    d.rectangle([0, ny, W, ny+48], fill=C['base'])
    d.line([(0, ny+47), (W, ny+47)], fill=C['muted'])
    d.text((50, ny+13), "VELURE", font=f['logo'], fill=C['dark'])

    links = ["BOUTIQUE", "NOUVEAUTES", "BEST-SELLERS", "LOOKBOOK", "LA MARQUE"]
    lx = 255
    for link in links:
        c = C['cognac'] if "NOUVEA" in link else C['dark']
        bb = d.textbbox((0,0), link, font=f['nav'])
        d.text((lx, ny+18), link, font=f['nav'], fill=c)
        lx += (bb[2]-bb[0]) + 25

    # Cart icon area
    d.text((W-115, ny+15), "Search", font=f['small'], fill=C['dark'])
    d.text((W-80, ny+15), "Wish", font=f['small'], fill=C['dark'])
    rrect(d, [W-55, ny+10, W-25, ny+38], r=0, fill=C['dark'])
    d.text((W-48, ny+15), "Cart", font=f['small'], fill=C['white'])
    rrect(d, [W-22, ny+8, W-10, ny+22], r=6, fill=C['gold'])
    d.text((W-20, ny+9), "3", font=f['badge'], fill=C['white'])

    # ════════════════════════════════════════
    # HERO (320px) - y=74
    # ════════════════════════════════════════
    hy = 74
    hh = 320
    hero_tile = make_fashion_tile(W, hh, seed=42, style="dark")

    # Dark overlay for text readability (bottom heavier)
    ov = Image.new("RGBA", (W, hh), (0,0,0,0))
    od = ImageDraw.Draw(ov)
    for row in range(hh):
        t = row / hh
        a = int(190 * max(0, (t - 0.2) / 0.8) ** 0.6) if t > 0.2 else int(25 * t / 0.2)
        od.line([(0, row), (W-1, row)], fill=(22, 20, 18, a))
    # Side vignette
    for i in range(50):
        a = int(45 * (50-i) / 50)
        od.line([(i, 0), (i, hh-1)], fill=(0, 0, 0, a))
        od.line([(W-1-i, 0), (W-1-i, hh-1)], fill=(0, 0, 0, a))

    hero_final = Image.alpha_composite(hero_tile.convert("RGBA"), ov).convert("RGB")
    img.paste(hero_final, (0, hy))

    # Re-create draw after paste
    d = ImageDraw.Draw(img)

    # Eyebrow text with gold decorative lines
    eb = "AUTOMNE  /  HIVER  2025"
    bb = d.textbbox((0,0), eb, font=f['eyebrow'])
    etw = bb[2] - bb[0]
    ex = (W - etw) // 2
    ey = hy + 80
    d.line([(ex-50, ey+5), (ex-8, ey+5)], fill=C['gold'], width=1)
    d.line([(ex+etw+8, ey+5), (ex+etw+50, ey+5)], fill=C['gold'], width=1)
    d.text((ex, ey), eb, font=f['eyebrow'], fill=C['gold'])

    # Hero Title
    t1 = "L'Art du Style"
    bb = d.textbbox((0,0), t1, font=f['h1'])
    d.text(((W-(bb[2]-bb[0]))//2, hy+100), t1, font=f['h1'], fill=C['white'])

    t2 = "Contemporain"
    bb = d.textbbox((0,0), t2, font=f['h1'])
    d.text(((W-(bb[2]-bb[0]))//2, hy+152), t2, font=f['h1'], fill=C['white'])

    # Subtitle
    st = "Des pieces d'exception qui redefinissent l'elegance moderne"
    bb = d.textbbox((0,0), st, font=f['sub'])
    d.text(((W-(bb[2]-bb[0]))//2, hy+208), st, font=f['sub'], fill=C['muted'])

    # CTA Buttons
    by = hy + 248
    b1t = "EXPLORER LA COLLECTION"
    bb = d.textbbox((0,0), b1t, font=f['btn'])
    b1w = bb[2]-bb[0]
    b1x = W//2 - b1w//2 - 115
    rrect(d, [b1x, by, b1x+b1w+34, by+32], r=0, fill=C['white'])
    d.text((b1x+17, by+11), b1t, font=f['btn'], fill=C['dark'])

    b2t = "VOIR LE LOOKBOOK"
    bb = d.textbbox((0,0), b2t, font=f['btn'])
    b2w = bb[2]-bb[0]
    b2x = W//2 - b2w//2 + 115
    rrect(d, [b2x, by, b2x+b2w+34, by+32], r=0, outline=C['white'], width=1)
    d.text((b2x+17, by+11), b2t, font=f['btn'], fill=C['white'])

    # ════════════════════════════════════════
    # FEATURES BAR (40px) - y=394
    # ════════════════════════════════════════
    fy = 394
    d.rectangle([0, fy, W, fy+40], fill=C['soft'])
    d.line([(0, fy+39), (W, fy+39)], fill=C['muted'])
    feats = [
        "Livraison gratuite des 75 EUR",
        "Retours sous 30 jours",
        "Paiement 100% securise",
        "Service client disponible",
    ]
    sp = W // 4
    for i, ft in enumerate(feats):
        bb = d.textbbox((0,0), ft, font=f['feat'])
        tw = bb[2]-bb[0]
        d.text((sp*i + (sp-tw)//2, fy+14), ft, font=f['feat'], fill=C['dark'])

    # ════════════════════════════════════════
    # CATEGORIES (160px) - y=434
    # ════════════════════════════════════════
    cy0 = 434
    tc(d, cy0+6, "NOS CATEGORIES", f['sec'], C['dark'])
    cx = W // 2
    d.line([(cx-40, cy0+28), (cx+40, cy0+28)], fill=C['gold'], width=1)

    cats = [("FEMME", "warm"), ("HOMME", "navy"), ("ACCESSOIRES", "gold"), ("CHAUSSURES", "sage")]
    cw, ch, gap = 240, 95, 30
    total = 4*cw + 3*gap
    sx = (W - total) // 2
    cty = cy0 + 40

    for i, (label, style) in enumerate(cats):
        cx = sx + i*(cw+gap)
        tile = make_fashion_tile(cw, ch, seed=i*7+3, style=style)
        img.paste(tile, (cx, cty))
        d = ImageDraw.Draw(img)  # re-create draw
        bb = d.textbbox((0,0), label, font=f['btn'])
        d.text((cx + (cw-(bb[2]-bb[0]))//2, cty + ch - 26), label,
               font=f['btn'], fill=C['white'])

    # ════════════════════════════════════════
    # PRODUCTS (195px) - y=594
    # ════════════════════════════════════════
    py0 = 594
    d.rectangle([0, py0, W, py0+3], fill=C['muted'])
    tc(d, py0+8, "PRODUITS VEDETTES", f['sec'], C['dark'])
    d.line([(cx-40, py0+30), (cx+40, py0+30)], fill=C['gold'], width=1)

    products = [
        ("Robe en Soie", "189,00 EUR", "blush", "Nouveau"),
        ("Manteau Cognac", "345,00 EUR", "camel", ""),
        ("Chemise Lin", "95,00 EUR", "taupe", "Best-seller"),
        ("Pantalon Tailleur", "165,00 EUR", "olive", ""),
        ("Pull Oversize", "125,00 EUR", "sage", "Promo"),
    ]

    cw2, ch2, gap2 = 192, 55, 18
    total2 = 5*cw2 + 4*gap2
    sx2 = (W - total2) // 2
    pty = py0 + 40

    for i, (name, price, style, badge) in enumerate(products):
        px = sx2 + i*(cw2+gap2)
        tile = make_fashion_tile(cw2, ch2, seed=i*11+20, style=style)
        img.paste(tile, (px, pty))
        d = ImageDraw.Draw(img)

        # Info area
        iy = pty + ch2
        d.rectangle([px, iy, px+cw2, iy+52], fill=C['white'])
        d.line([(px, iy), (px+cw2, iy)], fill=C['border'])

        if badge:
            bw = 48 if len(badge) <= 6 else 60
            rrect(d, [px+5, pty+4, px+5+bw, pty+17], r=2, fill=C['gold'])
            bb = d.textbbox((0,0), badge.upper(), font=f['badge'])
            d.text((px+5+(bw-(bb[2]-bb[0]))//2, pty+5), badge.upper(),
                   font=f['badge'], fill=C['white'])

        d.text((px+8, iy+6), name, font=f['card_t'], fill=C['dark'])
        d.text((px+8, iy+21), price, font=f['card_p'], fill=C['cognac'])
        # Stars
        d.text((px+8, iy+37), "* * * * o", font=f['small'], fill=C['gold'])

    # ════════════════════════════════════════
    # NEWSLETTER (45px) - y=789
    # ════════════════════════════════════════
    ny2 = 789
    d.rectangle([0, ny2, W, ny2+45], fill=C['dark'])
    nt = "Rejoignez l'univers Velure  --  -15% sur votre premiere commande"
    tc(d, ny2+15, nt, f['nav'], C['muted'])

    # ════════════════════════════════════════
    # FOOTER (66px) - y=834
    # ════════════════════════════════════════
    fy2 = 834
    d.rectangle([0, fy2, W, H], fill=C['footer'])
    d.line([(0, fy2), (W, fy2)], fill=C['gold'], width=2)

    cols = [
        "VELURE   Femme  Homme  Accessoires  Chaussures",
        "INFOS   Histoire  Tailles  Contact  FAQ",
        "AIDE   Livraison  Retours  Paiement  CGV",
        "2025 Velure   Instagram  TikTok  Pinterest",
    ]
    cfw = W // 4
    for i, col in enumerate(cols):
        d.text((cfw*i + 18, fy2+12), col, font=f['ft_l'], fill=C['txt_foot'])

    # Payment icons hint
    d.text((cfw*i + 18, fy2+30), "Visa  Mastercard  PayPal  Apple Pay",
           font=f['ft_l'], fill=C['txt_foot'])

    # Subtle noise texture
    random.seed(42)
    ov = Image.new("RGBA", (W, H), (0,0,0,0))
    od = ImageDraw.Draw(ov)
    for _ in range(4000):
        ox = random.randint(0, W-1)
        oy = random.randint(0, H-1)
        od.point((ox, oy), fill=(0, 0, 0, random.randint(2, 5)))
    img = Image.alpha_composite(img.convert("RGBA"), ov).convert("RGB")

    out = "/home/z/my-project/velure3/screenshot.png"
    img.save(out, "PNG")
    img.save("/home/z/my-project/download/velure3-screenshot.png", "PNG")

    # Verify
    d2 = ImageDraw.Draw(img)
    used = H - fy2
    print(f"Screenshot: {W}x{H}")
    print(f"Footer starts at y={fy2}, uses {used}px ({H-fy2}px available)")
    print(f"Total layout: topbar=26 + nav=48 + hero=320 + feat=40 + cats=160 + prods=195 + news=45 + footer={H-fy2} = {26+48+320+40+160+195+45+(H-fy2)}")


if __name__ == "__main__":
    main()