// Loaded first, before React. core-js (via babel preset-env useBuiltIns) covers
// most ES gaps for Firefox 52, but a few host APIs aren't part of core-js.

// Firefox added queueMicrotask in 69; React 18 falls back without it, but some
// libs assume it exists. Provide a Promise-based shim.
if (typeof window !== 'undefined' && typeof window.queueMicrotask !== 'function') {
  window.queueMicrotask = function (cb) {
    Promise.resolve()
      .then(cb)
      .catch(function (e) {
        setTimeout(function () {
          throw e;
        });
      });
  };
}
