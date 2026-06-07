// Targets Firefox 52 ESR (last Firefox for Windows XP). preset-env down-compiles
// modern syntax (object spread, optional chaining, arrow fns, classes) to ES5 and
// core-js@3 (useBuiltIns: 'usage') injects only the polyfills actually referenced.
module.exports = {
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
    // Automatic runtime so .jsx files don't need an explicit `import React`.
    ['@babel/preset-react', { runtime: 'automatic' }],
  ],
};
