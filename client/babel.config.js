// Targets Firefox 52 ESR (last Firefox for Windows XP). preset-env down-compiles
// modern syntax (object spread, optional chaining, arrow fns, classes) to ES5 and
// core-js@3 (useBuiltIns: 'usage') injects only the polyfills actually referenced.
module.exports = {
  // Detect CommonJS vs ESM per file. Without this, useBuiltIns:'usage' injects
  // `import` for core-js into CommonJS node_modules (e.g. react-dom), turning
  // them into ES modules with a read-only `exports` — their `module.exports =`
  // then throws "Cannot assign to read only property 'exports'" in the browser.
  sourceType: 'unambiguous',
  presets: [
    [
      '@babel/preset-env',
      {
        targets: { firefox: '52' },
        useBuiltIns: 'usage',
        corejs: 3,
        // Don't assume native ES modules in the browser — let webpack handle modules.
        modules: false,
      },
    ],
    // Classic runtime: React 16.12.0 predates react/jsx-runtime (added in 16.14),
    // so the automatic runtime isn't available. Every .jsx file imports React.
    ['@babel/preset-react', { runtime: 'classic' }],
  ],
};
