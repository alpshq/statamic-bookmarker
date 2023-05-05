import setupForm from './lib/form';

const setupOnDocumentForms = (win) => {
  const doc = win.document;

  [...window.document.querySelectorAll('form.alps-bookmarker')]
    .forEach(form => setupForm(win, doc, form));
}

((win) => {
  setupOnDocumentForms(win);

  win.document.addEventListener('statamic:nocache.replaced', (ev) => {
    setupOnDocumentForms(win);
  });
})(global);
