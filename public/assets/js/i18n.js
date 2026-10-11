const I18N = {
  en: {
    eyebrow: 'CSV bulk import',
    title: 'Import users',
    subtitle:
      'Upload a CSV file to load users into MySQL. Download the sample of 50,000 rows, then import and watch progress live.',
    downloadSample: 'Download sample CSV (50,000 users)',
    chooseFile: 'Choose CSV file',
    fileHint: 'Only .csv files are accepted',
    noFile: 'No file selected',
    submit: 'Import users',
    importing: 'Importing…',
    progressLabel: 'Importing…',
    resultsTitle: 'Import complete',
    insertedLabel: 'Inserted successfully',
    errorsLabel: 'Errors',
    errorsTitle: 'Error details',
    errorsTruncated: 'Showing the first errors only.',
    latestTitle: 'Last 5 registered users',
    colId: 'ID',
    colName: 'Name',
    colEmail: 'Email',
    colCity: 'City',
    colCountry: 'Country',
    footer: 'PHP 8.4 · MySQL · Docker',
    errOnlyCsv: 'Only CSV files are accepted.',
    errNoFile: 'Please select a CSV file.',
    errUpload: 'Upload failed.',
    errProcess: 'Import failed.',
    errGeneric: 'Something went wrong.',
    rowError: 'Row {row}: {message}',
  },
  it: {
    eyebrow: 'Importazione CSV',
    title: 'Importa utenti',
    subtitle:
      'Carica un file CSV per inserire gli utenti in MySQL. Scarica il campione da 50.000 righe, poi importa e segui l’avanzamento in tempo reale.',
    downloadSample: 'Scarica CSV di esempio (50.000 utenti)',
    chooseFile: 'Scegli file CSV',
    fileHint: 'Sono accettati solo file .csv',
    noFile: 'Nessun file selezionato',
    submit: 'Importa utenti',
    importing: 'Importazione…',
    progressLabel: 'Importazione…',
    resultsTitle: 'Importazione completata',
    insertedLabel: 'Inseriti con successo',
    errorsLabel: 'Errori',
    errorsTitle: 'Dettaglio errori',
    errorsTruncated: 'Vengono mostrati solo i primi errori.',
    latestTitle: 'Ultimi 5 utenti registrati',
    colId: 'ID',
    colName: 'Nome',
    colEmail: 'Email',
    colCity: 'Città',
    colCountry: 'Paese',
    footer: 'PHP 8.4 · MySQL · Docker',
    errOnlyCsv: 'Sono accettati solo file CSV.',
    errNoFile: 'Seleziona un file CSV.',
    errUpload: 'Caricamento non riuscito.',
    errProcess: 'Importazione non riuscita.',
    errGeneric: 'Si è verificato un errore.',
    rowError: 'Riga {row}: {message}',
  },
};

const LANG_KEY = 'importer_lang';

function detectLang() {
  const saved = localStorage.getItem(LANG_KEY);
  if (saved === 'en' || saved === 'it') {
    return saved;
  }
  return navigator.language.toLowerCase().startsWith('it') ? 'it' : 'en';
}

let currentLang = detectLang();

function t(key, vars = {}) {
  const dict = I18N[currentLang] || I18N.en;
  let text = dict[key] ?? I18N.en[key] ?? key;
  Object.entries(vars).forEach(([k, v]) => {
    text = text.replace(`{${k}}`, String(v));
  });
  return text;
}

function applyLang(lang) {
  currentLang = lang === 'it' ? 'it' : 'en';
  localStorage.setItem(LANG_KEY, currentLang);
  document.documentElement.lang = currentLang;

  document.querySelectorAll('[data-i18n]').forEach((el) => {
    const key = el.getAttribute('data-i18n');
    if (key) {
      el.textContent = t(key);
    }
  });

  document.querySelectorAll('.lang-btn').forEach((btn) => {
    const active = btn.getAttribute('data-lang') === currentLang;
    btn.setAttribute('aria-pressed', active ? 'true' : 'false');
  });
}

document.addEventListener('DOMContentLoaded', () => {
  applyLang(currentLang);
  document.querySelectorAll('.lang-btn').forEach((btn) => {
    btn.addEventListener('click', () => applyLang(btn.getAttribute('data-lang')));
  });
});

window.ImporterI18n = { t, applyLang, getLang: () => currentLang };
