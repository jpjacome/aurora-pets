// Minimal helper: draw a simple image + text to canvas and return dataURL
document.addEventListener('DOMContentLoaded', function () {
  const previewCanvas = document.getElementById('previewCanvas');
  const previewPlaceholder = document.getElementById('previewPlaceholder');
  let lastResult = null; // store last generated result

  async function renderSharedPreview(data) {
    if (!window.generateStoryCanvas) throw new Error('Shared generator not available');
    const result = await window.generateStoryCanvas(data);
    previewCanvas.width = result.canvas.width;
    previewCanvas.height = result.canvas.height;
    const ctx = previewCanvas.getContext('2d');
    ctx.clearRect(0, 0, previewCanvas.width, previewCanvas.height);
    ctx.drawImage(result.canvas, 0, 0);
    previewCanvas.style.display = 'block';
    previewPlaceholder.style.display = 'none';
    lastResult = result;
  }

  document.getElementById('previewBtn').addEventListener('click', function () {
    const data = {
      petName: document.getElementById('pet_name').value,
      plantName: document.getElementById('plant_name').value,
      description: document.getElementById('description').value,
      image: document.getElementById('plant_image_url').value
    };
    renderSharedPreview(data).catch(e => alert('Preview failed: ' + (e.message || e)));
  });

  document.getElementById('downloadBtn').addEventListener('click', async function () {
    try {
      if (!lastResult) {
        const data = {
          petName: document.getElementById('pet_name').value,
          plantName: document.getElementById('plant_name').value,
          description: document.getElementById('description').value,
          image: document.getElementById('plant_image_url').value
        };
        lastResult = await window.generateStoryCanvas(data);
      }
      const dataURL = lastResult.dataUrl || lastResult.canvas.toDataURL('image/png');
      const fileName = `${(document.getElementById('pet_name').value || 'pet')}-${(document.getElementById('plant_name').value || 'plant')}-Aurora.png`.replace(/\s+/g, '-');
      const link = document.createElement('a');
      link.href = dataURL;
      link.download = fileName;
      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);
    } catch (e) { alert('Download failed: ' + (e.message || e)); }
  });

  document.getElementById('saveBtn').addEventListener('click', async function () {
    try {
      if (!lastResult) {
        const data = {
          petName: document.getElementById('pet_name').value,
          plantName: document.getElementById('plant_name').value,
          description: document.getElementById('description').value,
          image: document.getElementById('plant_image_url').value
        };
        lastResult = await window.generateStoryCanvas(data);
      }
      const dataURL = lastResult.dataUrl || lastResult.canvas.toDataURL('image/png');
      const payload = { dataURL, pet_name: document.getElementById('pet_name').value };
      const tokenMeta = document.querySelector('meta[name="csrf-token"]');
      const headers = { 'Content-Type': 'application/json' };
      if (tokenMeta) headers['X-CSRF-TOKEN'] = tokenMeta.getAttribute('content');
      const resp = await fetch('/admin/images/generator/upload', { method: 'POST', headers, body: JSON.stringify(payload) });
      const j = await resp.json();
      if (j.url) window.open(j.url, '_blank'); else alert('Save error');
    } catch (e) { alert('Save failed: ' + (e.message || e)); }
  });

  document.getElementById('serverRenderBtn').addEventListener('click', async function () {
    try {
      const payload = {
        plant_image_url: document.getElementById('plant_image_url').value,
        pet_name: document.getElementById('pet_name').value,
        description: document.getElementById('description').value,
        plant_name: document.getElementById('plant_name').value,
        mode: 'queued'
      };
      const tokenMeta = document.querySelector('meta[name="csrf-token"]');
      const headers = { 'Content-Type': 'application/json' };
      if (tokenMeta) headers['X-CSRF-TOKEN'] = tokenMeta.getAttribute('content');
      const resp = await fetch('/admin/images/generator/server', { method: 'POST', headers, body: JSON.stringify(payload) });
      const j = await resp.json();
      alert('Server render response: ' + JSON.stringify(j));
    } catch (e) { alert('Server render failed: ' + (e.message || e)); }
  });

});
