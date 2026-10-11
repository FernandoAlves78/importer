<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>CSV User Importer</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700;1,9..40,400&family=Instrument+Serif:ital@0;1&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="/assets/css/app.css">
</head>
<body>
  <div class="page">
    <header class="top">
      <div class="brand">
        <span class="brand-mark" aria-hidden="true"></span>
        <span class="brand-name">Importer</span>
      </div>
      <div class="lang" role="group" aria-label="Language">
        <button type="button" class="lang-btn" data-lang="en" aria-pressed="true">EN</button>
        <button type="button" class="lang-btn" data-lang="it" aria-pressed="false">IT</button>
      </div>
    </header>

    <main class="main">
      <p class="eyebrow" data-i18n="eyebrow">CSV bulk import</p>
      <h1 data-i18n="title">Import users</h1>
      <p class="lead" data-i18n="subtitle">Upload a CSV file to load users into MySQL. Download the sample of 50,000 rows, then import and watch progress live.</p>

      <a class="download" href="/api/sample.php" data-i18n="downloadSample">Download sample CSV (50,000 users)</a>

      <form id="import-form" class="import-form" novalidate>
        <label class="file-label" for="csv-file">
          <span class="file-title" data-i18n="chooseFile">Choose CSV file</span>
          <span class="file-hint" data-i18n="fileHint">Only .csv files are accepted</span>
          <span id="file-name" class="file-name" data-i18n="noFile">No file selected</span>
          <input id="csv-file" name="file" type="file" accept=".csv,text/csv" required>
        </label>
        <button type="submit" class="submit" id="submit-btn" data-i18n="submit">Import users</button>
      </form>

      <p id="form-error" class="form-error" hidden></p>

      <section id="progress-section" class="progress-section" hidden>
        <div class="progress-head">
          <span data-i18n="progressLabel">Importing…</span>
          <strong id="progress-percent">0%</strong>
        </div>
        <div class="progress-track" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0">
          <div id="progress-bar" class="progress-bar"></div>
        </div>
        <p id="progress-text" class="progress-text">0 / 0</p>
      </section>

      <section id="results-section" class="results-section" hidden>
        <h2 data-i18n="resultsTitle">Import complete</h2>
        <div class="stats">
          <div class="stat">
            <span class="stat-label" data-i18n="insertedLabel">Inserted successfully</span>
            <strong id="stat-inserted" class="stat-value">0</strong>
          </div>
          <div class="stat">
            <span class="stat-label" data-i18n="errorsLabel">Errors</span>
            <strong id="stat-errors" class="stat-value">0</strong>
          </div>
        </div>

        <div id="errors-block" class="errors-block" hidden>
          <h3 data-i18n="errorsTitle">Error details</h3>
          <ul id="errors-list" class="errors-list"></ul>
          <p id="errors-truncated" class="errors-truncated" hidden data-i18n="errorsTruncated">Showing the first errors only.</p>
        </div>

        <h3 data-i18n="latestTitle">Last 5 registered users</h3>
        <div class="table-wrap">
          <table class="users-table">
            <thead>
              <tr>
                <th data-i18n="colId">ID</th>
                <th data-i18n="colName">Name</th>
                <th data-i18n="colEmail">Email</th>
                <th data-i18n="colCity">City</th>
                <th data-i18n="colCountry">Country</th>
              </tr>
            </thead>
            <tbody id="users-tbody"></tbody>
          </table>
        </div>
      </section>
    </main>

    <footer class="footer">
      <span data-i18n="footer">PHP 8.4 · MySQL · Docker</span>
    </footer>
  </div>

  <script src="/assets/js/i18n.js"></script>
  <script src="/assets/js/app.js"></script>
</body>
</html>
