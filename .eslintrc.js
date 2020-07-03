module.exports = {
  extends: [
    "plugin:prettier/recommended",
    "eslint:recommended",
    "plugin:react/recommended",
  ],
  rules: {
    // Prevent warnings for webpack resolve aliases.
    "import/no-unresolved": "off",
    // Prevent warnings for webpack extension resolution.
    "import/extensions": "off",
    // Prevent warnings for import statements with aliases.
    "import/first": "off",
    "prettier/prettier": ["error"],
  },
  settings: {
    react: {
      pragma: "wp",
      version: "latest",
    },
  },
};
