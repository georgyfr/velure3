import ZAI from 'z-ai-web-dev-sdk';
import fs from 'fs';
import path from 'path';

const images = [
  { prompt: "Fashion editorial photography, autumn winter 2025 trends, minimalist chic style, warm earth tones, camel coat and cashmere sweater, soft natural lighting, high fashion magazine style", size: "1344x768", name: "blog-tendances-aw25.jpg" },
  { prompt: "Fashion editorial photography, luxury cashmere care, folded cashmere sweaters in neutral tones on marble surface, soft moody lighting, premium lifestyle", size: "1344x768", name: "blog-cachemire-entretien.jpg" },
  { prompt: "Fashion editorial photography, capsule wardrobe concept, curated minimalist clothing pieces on wooden rack, neutral palette beige white black, clean aesthetic", size: "1344x768", name: "blog-capsule-garderobe.jpg" },
  { prompt: "Instagram fashion flat lay, luxury fashion accessories on cream linen, sunglasses handbag jewelry watch, warm golden light, aesthetic minimalist", size: "1024x1024", name: "instagram-01.jpg" },
  { prompt: "Instagram fashion photo, elegant woman walking in paris street wearing camel coat, autumn leaves, golden hour lighting, street style photography", size: "1024x1024", name: "instagram-02.jpg" },
  { prompt: "Instagram fashion detail shot, close up of luxury leather bag with gold hardware on marble table, warm lighting, premium product photography", size: "1024x1024", name: "instagram-03.jpg" },
  { prompt: "Instagram fashion photo, man wearing tailored navy blazer and white shirt, clean background, sharp lighting, modern menswear style", size: "1024x1024", name: "instagram-04.jpg" },
  { prompt: "Instagram fashion lifestyle, woman in silk midi dress in art gallery, soft natural lighting, elegant feminine, luxury fashion", size: "1024x1024", name: "instagram-05.jpg" },
  { prompt: "Instagram fashion flat lay, luxury shoes collection arranged artfully, leather boots and heels on textured paper, warm tones, minimalist", size: "1024x1024", name: "instagram-06.jpg" },
];

const outputDir = "/home/z/my-project/velure3/assets/images";
const zai = await ZAI.create();

for (const img of images) {
  const outputPath = path.join(outputDir, img.name);
  try {
    console.log(`Generating ${img.name}...`);
    const response = await zai.images.generations.create({
      prompt: img.prompt,
      size: img.size
    });
    const buffer = Buffer.from(response.data[0].base64, 'base64');
    fs.writeFileSync(outputPath, buffer);
    console.log(`  ✓ ${img.name} (${buffer.length} bytes)`);
  } catch (err) {
    console.error(`  ✗ ${img.name}: ${err.message}`);
  }
}
console.log("Done!");