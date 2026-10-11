(() => {
  const form = document.getElementById('import-form');
  const fileInput = document.getElementById('csv-file');
  const fileNameEl = document.getElementById('file-name');
  const submitBtn = document.getElementById('submit-btn');
  const formError = document.getElementById('form-error');
  const progressSection = document.getElementById('progress-section');
  const progressBar = document.getElementById('progress-bar');
  const progressPercent = document.getElementById('progress-percent');
  const progressText = document.getElementById('progress-text');
  const progressTrack = document.querySelector('.progress-track');
  const resultsSection = document.getElementById('results-section');
  const statInserted = document.getElementById('stat-inserted');
  const statErrors = document.getElementById('stat-errors');
  const errorsBlock = document.getElementById('errors-block');
  const errorsList = document.getElementById('errors-list');
  const errorsTruncated = document.getElementById('errors-truncated');
  const usersTbody = document.getElementById('users-tbody');

  const t = (...args) => window.ImporterI18n.t(...args);

  fileInput.addEventListener('change', () => {
    const file = fileInput.files?.[0];
    if (!file) {
      fileNameEl.textContent = t('noFile');
      fileNameEl.setAttribute('data-i18n', 'noFile');
      return;
    }
    fileNameEl.removeAttribute('data-i18n');
    fileNameEl.textContent = file.name;
    hideError();
  });

  form.addEventListener('submit', async (event) => {
    event.preventDefault();
    hideError();
    resultsSection.hidden = true;

    const file = fileInput.files?.[0];
    if (!file) {
      showError(t('errNoFile'));
      return;
    }

    if (!isCsvFile(file)) {
      showError(t('errOnlyCsv'));
      return;
    }

    setBusy(true);
    showProgress(0, 0, 0);

    try {
      const upload = await uploadFile(file);
      let offset = 0;
      let insertedTotal = 0;
      let errorTotal = 0;
      const allErrors = [];
      const total = upload.totalRows;
      const batchSize = upload.batchSize || 1000;

      while (true) {
        const chunk = await processChunk(upload.token, offset, batchSize, total);
        insertedTotal += chunk.inserted;
        errorTotal += chunk.errorCount || 0;
        allErrors.push(...(chunk.errors || []));
        offset = chunk.processed;
        showProgress(chunk.percent, offset, total);

        if (chunk.done) {
          break;
        }
      }

      const latest = await fetchLatest();
      renderResults(insertedTotal, errorTotal, allErrors, latest.users || []);
    } catch (err) {
      showError(err.message || t('errGeneric'));
      progressSection.hidden = true;
    } finally {
      setBusy(false);
    }
  });

  function isCsvFile(file) {
    const name = file.name.toLowerCase();
    return name.endsWith('.csv');
  }

  async function uploadFile(file) {
    const body = new FormData();
    body.append('file', file);

    const res = await fetch('/api/upload.php', { method: 'POST', body });
    const data = await res.json();
    if (!res.ok || !data.ok) {
      throw new Error(data.error || t('errUpload'));
    }
    return data;
  }

  async function processChunk(token, offset, limit, totalRows) {
    const res = await fetch('/api/process.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ token, offset, limit, totalRows }),
    });
    const data = await res.json();
    if (!res.ok || !data.ok) {
      throw new Error(data.error || t('errProcess'));
    }
    return data;
  }

  async function fetchLatest() {
    const res = await fetch('/api/latest.php');
    const data = await res.json();
    if (!res.ok || !data.ok) {
      throw new Error(data.error || t('errGeneric'));
    }
    return data;
  }

  function showProgress(percent, processed, total) {
    progressSection.hidden = false;
    progressBar.style.width = `${percent}%`;
    progressPercent.textContent = `${percent}%`;
    progressText.textContent = `${processed.toLocaleString()} / ${total.toLocaleString()}`;
    progressTrack.setAttribute('aria-valuenow', String(percent));
  }

  function renderResults(inserted, errorTotal, errors, users) {
    resultsSection.hidden = false;
    statInserted.textContent = inserted.toLocaleString();
    statErrors.textContent = errorTotal.toLocaleString();

    errorsList.innerHTML = '';
    if (errors.length > 0) {
      errorsBlock.hidden = false;
      errors.forEach((err) => {
        const li = document.createElement('li');
        li.textContent = t('rowError', { row: err.row, message: err.message });
        errorsList.appendChild(li);
      });
      errorsTruncated.hidden = errorTotal <= errors.length;
    } else {
      errorsBlock.hidden = true;
    }

    usersTbody.innerHTML = '';
    users.forEach((user) => {
      const tr = document.createElement('tr');
      tr.innerHTML = `
        <td>${escapeHtml(user.id)}</td>
        <td>${escapeHtml(user.name)}</td>
        <td>${escapeHtml(user.email)}</td>
        <td>${escapeHtml(user.city ?? '')}</td>
        <td>${escapeHtml(user.country ?? '')}</td>
      `;
      usersTbody.appendChild(tr);
    });
  }

  function escapeHtml(value) {
    return String(value)
      .replaceAll('&', '&amp;')
      .replaceAll('<', '&lt;')
      .replaceAll('>', '&gt;')
      .replaceAll('"', '&quot;');
  }

  function showError(message) {
    formError.hidden = false;
    formError.textContent = message;
  }

  function hideError() {
    formError.hidden = true;
    formError.textContent = '';
  }

  function setBusy(busy) {
    submitBtn.disabled = busy;
    fileInput.disabled = busy;
    submitBtn.textContent = busy ? t('importing') : t('submit');
    if (!busy) {
      submitBtn.setAttribute('data-i18n', 'submit');
    } else {
      submitBtn.removeAttribute('data-i18n');
    }
  }
})();
