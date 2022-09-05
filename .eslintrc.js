module.exports = {
  "extends": "plugin:@wordpress/eslint-plugin/recommended",
  "env": {
    "browser": true,
    "es2021": true,
    "node": true
  },
  "ignorePatterns": [
    "Gruntfile.js",
    "src/js/*.min.js",
    "src/thirdparty/*"
  ],
  "plugins": [
    'react'
  ],
  "globals": {
    "wp": true,
    "quizBlocks": true,
    "quizBlocksQuiz": true,
    "jQuery": true
  },
  "parserOptions": {
    "ecmaVersion": "latest",
    "sourceType": "module"
  },
  "rules": {
    "prettier/prettier": "off",
    "jsdoc/require-param": "off",
    "valid-jsdoc": "off",
    "jsdoc/check-line-alignment": "off",
    "jsdoc/check-alignment": "off",
    "no-extra-boolean-cast": "off",
    "no-unused-vars": "off",
    "object-shorthand": "off",
    "@wordpress/no-unsafe-wp-apis": "off"
  }
}