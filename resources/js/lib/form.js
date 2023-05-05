const setupForm = (win, doc, form) => {
  if (form.dataset.bookmarker) {
    return;
  }

  form.addEventListener('submit', createSubmitHandler());
  form.dataset.bookmarker = 'true';
};

const createSubmitHandler = () => async ev => {
  ev.preventDefault();

  const form = ev.target;
  const slotRoot = form.getElementsByTagName('div')[0];
  const url = form.getAttribute('action');

  const params = createRequestParams(ev);

  const beforeEvent = dispatchBeforeEvent(slotRoot, {
    url,
    params,
  });

  if (beforeEvent.defaultPrevented) {
    return;
  }

  const response = await fetch(url, {
    method: params.method ?? 'post',
    headers: {
      'Content-Type': 'application/json',
      'X-Requested-With': 'XMLHttpRequest',
    },
    credentials: 'same-origin',
    body: JSON.stringify(params),
  });

  slotRoot.innerHTML = await response.text();

  dispatchAfterEvent(slotRoot, {
    url,
    params,
    response,
  });
};

const createRequestParams = (ev) => {
  const data = new FormData(ev.target);

  if (ev.submitter?.name) {
    data.append(ev.submitter.name, ev.submitter.value);
  }

  const params = {
    _method: 'post',
  };

  [...data.entries()].forEach(([key, value]) => {
    params[key] = value;
  });

  return params;
}

const dispatchBeforeEvent = (element, detail) => {
  const event = new CustomEvent('beforeBookmark', {
    bubbles: true,
    cancelable: true,
    detail,
  });

  element.dispatchEvent(event);

  return event;
}

const dispatchAfterEvent = (element, detail) => {
  const event = new CustomEvent('afterBookmark', {
    bubbles: true,
    cancelable: false,
    detail,
  });

  element.dispatchEvent(event);

  return event;
}

export default setupForm;
