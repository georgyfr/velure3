#!/usr/bin/env python3
"""
Fix velure-skeleton placeholders in all Velure3 template files.
Replace gray divs with actual <img> tags using placehold.co images.
"""

import re
import os
import glob

THEME_DIR = "/home/z/my-project/velure3"

# Context-aware image labels based on surrounding HTML
CONTEXT_LABELS = {
    "produit": "Produit",
    "product": "Produit",
    "robe": "Robe",
    "chemise": "Chemise",
    "chaussure": "Chaussure",
    "sac": "Sac",
    "accessoire": "Accessoire",
    "manteau": "Manteau",
    "veste": "Veste",
    "pantalon": "Pantalon",
    "pull": "Pull",
    "collection": "Collection",
    "lookbook": "Lookbook",
    "femme": "Femme",
    "homme": "Homme",
    "nouvelle": "Nouvelle",
    "bestseller": "Best-seller",
    "blog": "Blog",
    "article": "Article",
    "temoignage": "Temoignage",
    "avis": "Avis",
    "instagram": "Instagram",
    "marque": "La Marque",
    "atelier": "Atelier",
    "equipe": "Equipe",
    "boutique": "Boutique",
    "vetement": "Vetement",
}

# Fashion-themed placeholder image seeds
IMAGE_SEEDS = [
    "velure-fashion-1", "velure-fashion-2", "velure-fashion-3", "velure-fashion-4",
    "velure-fashion-5", "velure-fashion-6", "velure-fashion-7", "velure-fashion-8",
    "velure-style-1", "velure-style-2", "velure-style-3", "velure-style-4",
    "velure-mode-1", "velure-mode-2", "velure-mode-3", "velure-mode-4",
    "velure-collection-1", "velure-collection-2", "velure-collection-3",
    "velure-look-1", "velure-look-2", "velure-look-3",
    "velure-editorial-1", "velure-editorial-2",
    "velure-atelier-1", "velure-atelier-2",
    "velure-model-1", "velure-model-2", "velure-model-3",
    "velure-detail-1", "velure-detail-2", "velure-detail-3",
    "velure-texture-1", "velure-texture-2",
    "velure-silk", "velure-linen", "velure-wool", "velure-cotton",
    "velure-heel", "velure-bag", "velure-jewelry", "velure-scarf",
    "velure-coat", "velure-dress", "velure-shirt", "velure-pants",
    "velure-autumn", "velure-winter", "velure-spring", "velure-summer",
    "velure-minimal", "velure-luxury", "velure-chic", "velure-casual",
    "velure-neutral-1", "velure-neutral-2", "velure-neutral-3",
    "velure-earth-1", "velure-earth-2", "velure-earth-3",
    "velure-gold-1", "velure-cream-1", "velure-beige-1",
    "velure-studio-1", "velure-studio-2",
    "velure-portrait-1", "velure-portrait-2", "velure-portrait-3",
    "velure-runway-1", "velure-runway-2",
    "velure-fabric-1", "velure-fabric-2", "velure-fabric-3",
    "velure-store-1", "velure-store-2",
    "velure-wishlist-1", "velure-wishlist-2", "velure-wishlist-3",
]

seed_counter = [0]

def get_context_label(html_before, html_after):
    """Extract a relevant label from surrounding HTML context."""
    combined = (html_before + " " + html_after).lower()
    for keyword, label in CONTEXT_LABELS.items():
        if keyword in combined:
            return label
    return "Velure"

def extract_dimensions(style_str):
    """Extract width and height from inline style."""
    h_match = re.search(r'height:\s*(\d+)px', style_str)
    w_match = re.search(r'width:\s*(\d+)%', style_str)
    height = int(h_match.group(1)) if h_match else 300
    # Width is typically percentage based, use a reasonable px value
    return (600, height)

def replace_skeleton(match, context_before="", context_after=""):
    """Replace a velure-skeleton div with an actual img tag."""
    style_str = match.group(1) if match.group(1) else ""
    w, h = extract_dimensions(style_str)
    label = get_context_label(context_before, context_after)
    seed = IMAGE_SEEDS[seed_counter[0] % len(IMAGE_SEEDS)]
    seed_counter[0] += 1
    
    # Use placehold.co with fashion-themed colors
    # Alternating between warm neutral tones
    colors = [
        ("E8E4DE", "1A1A1A"),  # Cream on dark
        ("D4CFC9", "1A1A1A"),  # Light gray on dark
        ("F5F0EB", "1A1A1A"),  # Soft cream on dark
        ("C8A97E", "FFFFFF"),  # Gold on white
        ("2C2C2C", "C8A97E"),  # Dark on gold
        ("3D3D3D", "FAFAF8"),  # Charcoal on base
    ]
    bg, fg = colors[seed_counter[0] % len(colors)]
    
    img_tag = (
        f'<img src="https://placehold.co/{w}x{h}/{bg}/{fg}?text={seed}" '
        f'alt="{label} - Velure" '
        f'style="width:100%;height:100%;object-fit:cover;"/>'
    )
    
    # Preserve the parent div style but replace the content
    return f'<div style="{style_str}overflow:hidden;border-radius:2px;">\n            {img_tag}\n          </div>'

def process_template(filepath):
    """Process a single template file."""
    with open(filepath, 'r', encoding='utf-8') as f:
        content = f.read()
    
    original_count = content.count('velure-skeleton')
    if original_count == 0:
        return 0
    
    # Find all skeleton divs and replace them
    # Pattern: <div class="velure-skeleton" style="...">
    lines = content.split('\n')
    new_lines = []
    replacements = 0
    
    for i, line in enumerate(lines):
        if 'velure-skeleton' in line:
            # Get context from surrounding lines
            context_before = '\n'.join(lines[max(0, i-5):i])
            context_after = '\n'.join(lines[i+1:min(len(lines), i+5)])
            
            # Extract the style
            style_match = re.search(r'style="([^"]*)"', line)
            style_str = style_match.group(1) if style_match else ""
            
            w, h = extract_dimensions(style_str)
            label = get_context_label(context_before, context_after)
            seed = IMAGE_SEEDS[seed_counter[0] % len(IMAGE_SEEDS)]
            seed_counter[0] += 1
            
            colors = [
                ("E8E4DE", "1A1A1A"),
                ("D4CFC9", "1A1A1A"),
                ("F5F0EB", "1A1A1A"),
                ("C8A97E", "FFFFFF"),
                ("2C2C2C", "C8A97E"),
                ("3D3D3D", "FAFAF8"),
            ]
            bg, fg = colors[seed_counter[0] % len(colors)]
            
            indent = len(line) - len(line.lstrip())
            indent_str = ' ' * indent
            
            new_lines.append(f'{indent_str}<div style="{style_str}overflow:hidden;">')
            new_lines.append(f'{indent_str}  <img src="https://placehold.co/{w}x{h}/{bg}/{fg}?text={label}" alt="{label} - Velure" style="width:100%;height:100%;object-fit:cover;" loading="lazy"/>')
            new_lines.append(f'{indent_str}</div>')
            replacements += 1
        else:
            new_lines.append(line)
    
    new_content = '\n'.join(new_lines)
    
    with open(filepath, 'w', encoding='utf-8') as f:
        f.write(new_content)
    
    return replacements

def main():
    total = 0
    templates_dir = os.path.join(THEME_DIR, "templates")
    
    for filepath in sorted(glob.glob(os.path.join(templates_dir, "*.html"))):
        count = process_template(filepath)
        if count > 0:
            print(f"  {os.path.basename(filepath)}: {count} placeholders remplaces")
            total += count
    
    print(f"\nTotal: {total} placeholders remplaces par des images")
    
    # Also update the skeleton CSS to be a fallback
    css_path = os.path.join(THEME_DIR, "assets", "css", "base.css")
    with open(css_path, 'r') as f:
        css = f.read()
    
    # Update skeleton style to be a proper loading state
    css = css.replace(
        '.velure-skeleton{',
        '.velure-skeleton{/* fallback for missing images */background:linear-gradient(110deg,#e8e4de 8%,#f0ebe5 18%,#e8e4de 33%);background-size:200% 100%;animation:velure-shimmer 1.5s linear infinite;}'
    )
    
    with open(css_path, 'w') as f:
        f.write(css)
    
    print("CSS skeleton mis a jour (shimmer animation)")

if __name__ == "__main__":
    main()