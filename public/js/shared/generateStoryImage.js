(function () {
  // Shared generator for admin preview. Exposes window.generateStoryCanvas(data)
  // Returns a Promise resolving to { canvas, dataUrl, blob }

  async function loadFonts() {
    try {
      await Promise.all([
        document.fonts.load('bold 64px "Playfair Display"'),
        document.fonts.load('32px "Buenard"'),
        document.fonts.load('bold 80px "Playfair Display"'),
        document.fonts.load('bold 42px "Playfair Display"'),
        document.fonts.load('36px "Buenard"'),
        document.fonts.load('56px "Chunkfive"')
      ]);
    } catch (e) {
      console.warn('Font loading failed, using fallbacks:', e);
    }
  }

  window.generateStoryCanvas = async function (data) {
    await loadFonts();

    return new Promise((resolve, reject) => {
      try {
        const canvas = document.createElement('canvas');
        canvas.width = 1080;
        canvas.height = 1600;
        const ctx = canvas.getContext('2d');

        // Background
        ctx.fillStyle = '#00452A';
        ctx.fillRect(0, 0, canvas.width, canvas.height);

        const img = new Image();
        img.crossOrigin = 'anonymous';
        img.onload = function () {
          // Top logo image
          const topLogo = new Image();
          topLogo.crossOrigin = 'anonymous';
          topLogo.onload = function () {
            // nothing here; drawn later in flow
          };
          topLogo.onerror = function () { console.warn('Top logo failed to load'); };
          topLogo.src = (window.PLANTSCAN_ASSETS && window.PLANTSCAN_ASSETS.topLogo) || '/assets/plantscan-logo.png';

          const imgWidth = 600 - 30; // 570
          const imgHeight = Math.round(imgWidth * 4 / 3);
          const imgX = (canvas.width - imgWidth) / 2;
          const imgY = 200;

          ctx.drawImage(img, imgX, imgY, imgWidth, imgHeight);

          // Border
          ctx.lineWidth = 10;
          ctx.strokeStyle = '#dcffd6';
          ctx.strokeRect(imgX - (ctx.lineWidth / 2), imgY - (ctx.lineWidth / 2), imgWidth + ctx.lineWidth, imgHeight + ctx.lineWidth);

          // Try to draw topLogo rotated (best-effort: draw once it's loaded)
          topLogo.onload = function () {
            const topLogoWidth = 450;
            const topLogoHeight = (topLogo.height / topLogo.width) * topLogoWidth;
            const topLogoX = imgX - 50;
            const topLogoY = imgY - 120;
            const centerX = topLogoX + topLogoWidth / 2;
            const centerY = topLogoY + topLogoHeight / 2;
            const angle = -10 * Math.PI / 180;
            ctx.save();
            ctx.translate(centerX, centerY);
            ctx.rotate(angle);
            ctx.drawImage(topLogo, -topLogoWidth / 2, -topLogoHeight / 2, topLogoWidth, topLogoHeight);
            ctx.restore();
          };

          // Grid & texts
          const gridTopY = imgY + imgHeight + 20;
          const petNameY = gridTopY + 15;

          ctx.fillStyle = '#fe8d2c';
          ctx.font = '56px "Chunkfive", "Playfair Display", Georgia, serif';
          ctx.textAlign = 'center';
          ctx.textBaseline = 'top';
          ctx.fillText(data.petName || data.pet_name || 'tu mascota', canvas.width / 2, petNameY);

          const inspirations = data.inspirations || data.inspirations_text || '';
          if (inspirations) {
            ctx.fillStyle = '#dcffd6';
            ctx.font = '28px "Buenard", Georgia, serif';
            ctx.textAlign = 'center';
            ctx.fillText((Array.isArray(inspirations) ? inspirations.join(' - ') : inspirations), canvas.width / 2, petNameY + 70);
          }

          const plantNameY = petNameY + 44 + 75;
          ctx.fillStyle = '#fe8d2c';
          ctx.font = 'bold 80px "Playfair Display", Georgia, serif';
          ctx.textAlign = 'center';
          ctx.fillText(data.plantName || data.plant_name || '', canvas.width / 2, plantNameY);

          // Description wrap
          ctx.fillStyle = '#dcffd6';
          ctx.font = '28px "Buenard", Georgia, serif';
          const descMaxWidth = 800;
          const descWords = (data.description || data.desc || '').split(' ');
          let descLine = '';
          let descY = gridTopY + 230;
          const descLineHeight = 36;
          let lineCount = 0;
          const maxLines = 3;

          for (let word of descWords) {
            if (lineCount >= maxLines) break;
            const testLine = descLine + word + ' ';
            const metrics = ctx.measureText(testLine);
            if (metrics.width > descMaxWidth && descLine !== '') {
              ctx.fillText(descLine.trim(), canvas.width / 2, descY);
              descLine = word + ' ';
              descY += descLineHeight;
              lineCount++;
            } else {
              descLine = testLine;
            }
          }
          if (lineCount < maxLines && descLine.trim()) {
            ctx.fillText(descLine.trim(), canvas.width / 2, descY);
          }

          // Bottom logo
          const logo = new Image();
          logo.crossOrigin = 'anonymous';
          logo.onload = function () {
            const logoWidth = 300;
            const logoHeight = (logo.height / logo.width) * logoWidth;
            const logoX = (canvas.width - logoWidth) / 2;
            const logoY = 1375;
            ctx.drawImage(logo, logoX, logoY, logoWidth, logoHeight);

            ctx.font = '36px "Buenard", Georgia, serif';
            ctx.fillStyle = '#dcffd6';
            ctx.textAlign = 'center';
            ctx.fillText('auroraurn.pet/plantscan', canvas.width / 2, logoY + logoHeight + 50);

            canvas.toBlob((blob) => {
              resolve({ canvas, dataUrl: canvas.toDataURL('image/png'), blob });
            }, 'image/png');
          };
          logo.onerror = function () {
            ctx.fillStyle = '#fe8d2c';
            ctx.font = 'bold 42px "Playfair Display", Georgia, serif';
            ctx.textAlign = 'center';
            ctx.fillText('Aurora Pets', canvas.width / 2, 1420);

            ctx.font = '36px "Buenard", Georgia, serif';
            ctx.fillStyle = '#dcffd6';
            ctx.fillText('auroraurn.pet/plantscan', canvas.width / 2, 1490);

            canvas.toBlob((blob) => {
              resolve({ canvas, dataUrl: canvas.toDataURL('image/png'), blob });
            }, 'image/png');
          };
          logo.src = (window.PLANTSCAN_ASSETS && window.PLANTSCAN_ASSETS.logo) || '/assets/plantscan/imgs/logo.png';
        };
        img.onerror = () => reject(new Error('Failed to load plant image'));
        img.src = data.image || data.plant_image_url || data.plant_image || '';
      } catch (err) {
        reject(err);
      }
    });
  };
})();