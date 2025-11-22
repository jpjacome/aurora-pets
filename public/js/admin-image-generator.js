// Admin wrapper that uses shared generator
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
      inspirations: document.getElementById('inspirations') ? document.getElementById('inspirations').value : undefined,
      image: document.getElementById('plant_image_url').value
    };
    renderSharedPreview(data).catch(e => alert('Preview failed: ' + (e.message || e)));
  });

  // Auto-fill when selecting a plant
  const plantSelect = document.getElementById('plant_select');
  if (plantSelect) {
    plantSelect.addEventListener('change', function () {
      const id = this.value;
      if (!id) return;
      const item = (window.PLANT_DATA || []).find(p => String(p.id) === String(id));
      if (!item) return;
      document.getElementById('plant_name').value = item.name || '';
      // Support multiple possible description keys (defensive) and log for debugging
      const desc = item.description || item.desc || item.plant_description || item.plantDescription || '';
      const descEl = document.getElementById('description');
      if (descEl) descEl.value = desc;
      // Populate inspirations if present on the item (support different keys)
      const inspVal = item.inspirations || item.inspirations_text || item.virtues || item.inspirationsText || '';
      const inspEl = document.getElementById('inspirations');
      if (inspEl && inspVal) inspEl.value = Array.isArray(inspVal) ? inspVal.join(', ') : inspVal;
      if (item.image) document.getElementById('plant_image_url').value = item.image;
      // Auto-preview when selecting (use the resolved description)
      renderSharedPreview({ petName: document.getElementById('pet_name').value, plantName: item.name, description: desc, inspirations: (inspEl ? inspEl.value : ''), image: item.image });
    });
  }

  // Auto-fill when selecting a pet
  const petSelect = document.getElementById('pet_select');
  if (petSelect) {
    petSelect.addEventListener('change', function () {
      const id = this.value;
      if (!id) return;
      const item = (window.PET_DATA || []).find(p => String(p.id) === String(id));
      if (!item) return;
      document.getElementById('pet_name').value = item.name || '';
      // Auto-preview when selecting
      renderSharedPreview({ petName: item.name, plantName: document.getElementById('plant_name').value, description: document.getElementById('description').value, image: document.getElementById('plant_image_url').value });
    });
  }

  document.getElementById('downloadBtn').addEventListener('click', async function () {
    try {
      if (!lastResult) {
        const data = {
          petName: document.getElementById('pet_name').value,
          plantName: document.getElementById('plant_name').value,
          description: document.getElementById('description').value,
          inspirations: document.getElementById('inspirations') ? document.getElementById('inspirations').value : undefined,
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
          inspirations: document.getElementById('inspirations') ? document.getElementById('inspirations').value : undefined,
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
        inspirations: document.getElementById('inspirations') ? document.getElementById('inspirations').value : undefined,
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