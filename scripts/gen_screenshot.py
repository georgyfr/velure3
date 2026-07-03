#!/usr/bin/env python3
"""Generate Velure3 theme screenshot.png (1200x900)"""

from PIL import Image, ImageDraw, ImageFont
import os

W, H = 1200, 900
img = Image.new('RGB', (W, H), '#FAFAF8')
draw = ImageDraw.Draw(img)

try:
    font_serif = ImageFont.truetype('/usr/share/fonts/truetype/english/Tinos-Bold.ttf', 42)
    font_sans = ImageFont.truetype('/usr/share/fonts/truetype/english/Carlito-Bold.ttf', 14)
    font_sans_sm = ImageFont.truetype('/usr/share/fonts/truetype/english/Carlito.ttf', 12)
except:
    font_serif = ImageFont.load_default()
    font_sans = font_serif
    font_sans_sm = font_serif

# Top bar
draw.rectangle([(0, 0), (W, 32)], fill='#1A1A1A')
draw.text((W//2 - 200, 8), "Livraison gratuite a partir de 75 EUR  |  Retours sous 30 jours", fill='#FAFAF8', font=font_sans_sm)

# Navbar
draw.rectangle([(0, 32), (W, 100)], fill='#FAFAF8')
draw.line([(0, 100), (W, 100)], fill='#E5E0D8', width=1)

# Logo
draw.text((W//2 - 60, 52), "VELURE", fill='#1A1A1A', font=font_serif)

# Hero section
hero_top = 180
hero_bottom = 620
draw.rectangle([(0, hero_top), (W, hero_bottom)], fill='#E8E4DE')
draw.rectangle([(0, hero_top), (500, hero_bottom)], fill='#1A1A1A')

draw.text((60, hero_top + 40), "NOUVELLE COLLECTION", fill='#C8A97E', font=font_sans)

try:
    font_title = ImageFont.truetype('/usr/share/fonts/truetype/english/Tinos-Bold.ttf', 52)
except:
    font_title = font_serif

draw.text((60, hero_top + 75), "L'Elegance", fill='#FAFAF8', font=font_title)
draw.text((60, hero_top + 140), "Minimaliste", fill='#FAFAF8', font=font_title)

draw.rectangle([(60, hero_top + 220), (260, hero_top + 265)], fill='#1A1A1A')
draw.text((85, hero_top + 232), "DECOUVRIR", fill='#FAFAF8', font=font_sans)

# Features bar
fy = 630
draw.rectangle([(0, fy), (W, fy + 70)], fill='#FAFAF8')
draw.line([(0, fy), (W, fy)], fill='#E5E0D8', width=1)
draw.line([(0, fy + 70), (W, fy + 70)], fill='#E5E0D8', width=1)

features = ["Livraison Express", "Retours Gratuits", "Paiement Securise", "Service Client 7j/7"]
descs = ["Sous 24-48h en France", "Sous 30 jours, sans frais", "SSL & cryptage 256 bits", "Par chat, email ou tel"]
for i in range(4):
    x = 60 + i * 280
    draw.text((x, fy + 12), features[i], fill='#1A1A1A', font=font_sans)
    draw.text((x, fy + 35), descs[i], fill='#888888', font=font_sans_sm)
    if i < 3:
        draw.line([(x + 260, fy + 15), (x + 260, fy + 60)], fill='#E5E0D8', width=1)

# Product section
py = 730
draw.text((W//2 - 60, py), "Pieces Vedettes", fill='#1A1A1A', font=font_sans)

card_y = py + 35
for i in range(4):
    x = 60 + i * 270
    draw.rectangle([(x, card_y), (x + 240, card_y + 100)], fill='#E8E4DE')
    draw.text((x + 10, card_y + 108), "Nom du produit", fill='#1A1A1A', font=font_sans_sm)
    draw.text((x + 10, card_y + 126), "195,00 EUR", fill='#888888', font=font_sans_sm)

output_path = '/home/z/my-project/velure3/screenshot.png'
img.save(output_path, 'PNG')
print(f"Screenshot saved: {output_path}")
print(f"Size: {os.path.getsize(output_path)} bytes")