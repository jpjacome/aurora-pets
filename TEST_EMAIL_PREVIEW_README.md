# 🌿 PlantScan Email Image Preview Tool

## Quick Start

Open this file in your browser to preview the email image:

```
http://localhost/test-email-preview.html
```

Or directly open the file:
```
public/test-email-preview.html
```

## What It Does

This standalone HTML page lets you preview the exact image that users will receive via email when they complete the PlantScan test, **without** needing to:
- Fill out the entire PlantScan form
- Actually send an email
- Use the database
- Run the Laravel server

## How to Use

1. **Open the page** in any modern browser
2. **Enter test data:**
   - Pet name (e.g., Luna, Max, Milo)
   - Select a plant from the dropdown (all 19 plants available)
   - Optionally customize the description
3. **Click "🎨 Generar Vista Previa"**
4. See the generated 1080x1920px image
5. **Click "💾 Descargar Imagen"** to save it

## Features

- ✅ **Exact replica** - Uses same canvas generation code as production
- ✅ **All plants** - All 19 available plants in dropdown
- ✅ **Real fonts** - Loads Playfair Display & Buenard from Google Fonts
- ✅ **Production colors** - Uses exact Aurora brand colors (#00452A, #fe8d2c, #dcffd6)
- ✅ **Instant preview** - No server needed, runs entirely in browser
- ✅ **Download** - Save the generated PNG
- ✅ **Responsive** - Works on desktop and mobile

## Perfect For

- 🎨 **Designers**: Preview layout and typography
- 🧪 **QA Testing**: Verify all plants render correctly
- 📊 **Stakeholders**: Show email attachment example
- 🐛 **Debugging**: Test long pet/plant names
- 📱 **Mobile Testing**: Check image scales properly

## Image Specifications

- **Size**: 1080 x 1920 pixels (Instagram Story format)
- **Format**: PNG
- **Fonts**: 
  - Playfair Display (titles)
  - Buenard (body text)
- **Colors**:
  - Background: #00452A (dark green)
  - Primary: #fe8d2c (orange)
  - Text: #dcffd6 (light green)

## Example Output

The generated image includes:
1. **Top Title**: "¡La planta perfecta para [Pet Name]!"
2. **Plant Image**: 600x800px centered
3. **Plant Name**: Large title below image
4. **Description**: Wrapped text (max 3 lines)
5. **Branding**: "Aurora Pets" + website URL

## Troubleshooting

**Problem**: Plant image not loading
- **Solution**: Make sure the plant image exists in `assets/plantscan/imgs/plants/`

**Problem**: Fonts not loading
- **Solution**: Check internet connection (fonts load from Google Fonts CDN)

**Problem**: Canvas is blank
- **Solution**: Check browser console for errors, make sure JavaScript is enabled

## Technical Notes

This tool uses the **exact same `generateStoryImage()` logic** from `prevention.js`, ensuring 100% parity between preview and production.

The canvas generation is client-side only - no server communication, no database queries, no authentication needed.

---

**Created as part of PlantScan Email Implementation**
See `PLANTSCAN_EMAIL_IMPLEMENTATION.md` for full documentation.
