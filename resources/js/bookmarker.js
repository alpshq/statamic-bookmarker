import setupForm from './lib/form';

((win) => {
  const doc = win.document;

  [...doc.querySelectorAll('form.alps-bookmarker')]
    .forEach(form => setupForm(win, doc, form));
})(global);
