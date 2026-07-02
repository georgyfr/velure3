#!/usr/bin/env python3
"""Generate a simple screenshot.png for the NutriVitaX Pro theme."""
from PIL import Image, ImageDraw, ImageFont

W, H = 1200, 900
img = Image.new('RGB', (W, H), '#FFFFFF')
d = ImageDraw.Draw(img)

# Background gradient effect
for y in range(H):
    r = int(255 - (y / H) * 15)
    g = int(255 - (y / H) * 5)
    b = int(255 - (y / H) * 5)
    d.line([(0, y), (W, y)], fill=(r, g, b))

# Top bar
d.rectangle([0, 0, W, 40], fill='#F8F9FA')
d.line([(0, 40), (W, 40)], fill='#E9ECEF', width=1)

# Header bar
d.rectangle([0, 40, W, 110], fill='#FFFFFF')
d.line([(0, 110), (W, 110)], fill='#E9ECEF', width=1)

# Logo area
d.ellipse([50, 55, 85, 90], fill='#F0F9F4', outline='#1A6B3A', width=2)
d.text((62, 62), "N", fill='#1A6B3A')
try:
    font = ImageFont.truetype("/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf", 22)
    font_sm = ImageFont.truetype("/usr/share/fonts/truetype/dejavu/DejaVuSans.ttf", 14)
    font_md = ImageFont.truetype("/usr/share/fonts/truetype/dejavu/DejaVuSans.ttf", 16)
    font_lg = ImageFont.truetype("/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf", 32)
    font_xl = ImageFont.truetype("/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf", 42)
except:
    font = font_sm = font_md = font_lg = font_xl = ImageFont.load_default()

d.text((95, 62), "NutriVitaX", fill='#12243A', font=font)
d.text((240, 66), "Pro", fill='#F4A900', font=font)

# Nav items
nav_items = ['Accueil', 'Produits', 'Quiz', 'Blog', 'Contact']
x = 500
for item in nav_items:
    d.text((x, 68), item, fill='#12243A', font=font_md)
    x += 100

# Hero section
d.rectangle([0, 110, W, 500], fill='#12243A')
# Overlay gradient
for y in range(110, 500):
    alpha = int((y - 110) / 390 * 80)
    d.line([(0, y), (W, y)], fill=(26, 107 + alpha // 3, 58 + alpha // 5))

# Hero content
d.text((W//2 - 280, 180), "NUTRACEUTIQUE PREMIUM", fill='#2ECC71', font=font_md)
d.text((W//2 - 350, 230), "Optimisez votre", fill='#FFFFFF', font=font_xl)
d.text((W//2 - 280, 290), "sant\u00e9 naturelle", fill='#FFFFFF', font=font_xl)
d.text((W//2 - 340, 360), "Compl\u00e9ments alimentaires formul\u00e9s par des chercheurs,", fill='#CCCCCC', font=font_md)
d.text((W//2 - 280, 385), "test\u00e9s en laboratoire, d\u00e9livr\u00e9s chez vous.", fill='#CCCCCC', font=font_md)

# CTA button
d.rounded_rectangle([W//2 - 180, 430, W//2 + 20, 475], radius=12, fill='#F4A900')
d.text((W//2 - 160, 445), "COMMENCER LE QUIZ", fill='#12243A', font=font_md)
d.rounded_rectangle([W//2 + 40, 430, W//2 + 230, 475], radius=12, outline='#FFFFFF', width=2)
d.text((W//2 + 60, 445), "BOUTIQUE", fill='#FFFFFF', font=font_md)

# Trust bar
d.rectangle([0, 500, W, 560], fill='#F0F9F4')
trusts = ['Certifi\u00e9 Bio', 'GMP', 'ISO 22000', 'HACCP', 'Livraison 50\u20ac+', 'Retour 30 jours']
x = 80
for t in trusts:
    d.text((x, 525), "\u2713 " + t, fill='#1A6B3A', font=font_sm)
    x += 185

# Counter
d.text((W - 250, 520), "15 847", fill='#1A6B3A', font=font_lg)
d.text((W - 230, 545), "clients satisfaits", fill='#6B7280', font=font_sm)

# Product cards section
d.text((W//2 - 200, 590), "Nos Formulations Vedettes", fill='#12243A', font=font_lg)

# 3 product cards
cards = [
    ('W', 'Whey Prot\u00e9ine', '49,99 \u20ac', '#F0F9F4'),
    ('C', 'Cr\u00e9atine', '24,99 \u20ac', '#E8F8F0'),
    ('P', 'Pre-Workout', '39,99 \u20ac', '#FFF8E1'),
]
for i, (letter, name, price, bg) in enumerate(cards):
    cx = 120 + i * 350
    d.rounded_rectangle([cx, 640, cx + 300, 850], radius=16, fill=bg, outline='#E9ECEF')
    d.ellipse([cx + 100, 660, cx + 200, 760], fill=bg)
    d.text((cx + 140, 690), letter, fill='#1A6B3A', font=font_xl)
    d.text((cx + 90, 775), name, fill='#12243A', font=font_md)
    d.text((cx + 100, 805), price, fill='#1A6B3A', font=font)
    # Stars
    d.text((cx + 100, 825), '\u2605\u2605\u2605\u2605\u2605 (124 avis)', fill='#F4A900', font=font_sm)

img.save('/home/z/my-project/lampstack-wordpress/wordpress/wp-content/themes/nutrivitax-pro/screenshot.png')
print("screenshot.png created (1200x900)")