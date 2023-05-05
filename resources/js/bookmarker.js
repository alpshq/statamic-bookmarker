import setupForm from './lib/form';

const setupDocumentForms = (win) => {
  const doc = win.document;

  [...window.document.querySelectorAll('form.alps-bookmarker')]
    .forEach(form => setupForm(win, doc, form));
}

((win) => {
  setupDocumentForms(win);

  win.document.addEventListener('statamic:nocache.replaced', (ev) => {
    setTimeout(() => setupDocumentForms(win), 100);
  });
})(global);
