/**
 * Generate Velure3 screenshot.png (1200x900) using Playwright
 */
const { chromium } = require('playwright');

(async () => {
  const browser = await chromium.launch();
  const page = await browser.newPage({
    viewport: { width: 1200, height: 900 }
  });

  // Load the local HTML
  await page.goto('file:///home/z/my-project/scripts/screenshot.html', {
    waitUntil: 'networkidle',
    timeout: 30000
  });

  // Wait for fonts to load
  await page.waitForTimeout(3000);

  // Screenshot
  await page.screenshot({
    path: '/home/z/my-project/velure3/screenshot.png',
    width: 1200,
    height: 900,
    clip: { x: 0, y: 0, width: 1200, height: 900 }
  });

  console.log('Screenshot saved to /home/z/my-project/velure3/screenshot.png');
  await browser.close();
})();