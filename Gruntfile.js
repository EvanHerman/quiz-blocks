module.exports = function (grunt) {
  'use strict';

  const pkg = grunt.file.readJSON('package.json');

  grunt.initConfig({

    pkg,

    replace: {
      php: {
        src: [
          'quiz-blocks.php',
          'includes/**/*.php',
        ],
        overwrite: true,
        replacements: [
          {
            from: /Version:(\s*?)[a-zA-Z0-9\.\-\+]+$/m,
            to: 'Version:$1' + pkg.version,
          },
          {
            from: /@since(.*?)NEXT/mg,
            to: '@since$1' + pkg.version,
          },
          {
            from: /Version:(\s*?)[a-zA-Z0-9\.\-\+]+$/m,
            to: 'Version:$1' + pkg.version,
          },
          {
            from: /define\(\s*'QUIZ_BLOCKS_VERSION',\s*'(.*)'\s*\);/,
            to: 'define( \'QUIZ_BLOCKS_VERSION\', \'<%= pkg.version %>\' );',
          },
          {
            from: /define\(\s*'QUIZ_BLOCKS_JQUERY_MODAL_VERSION',\s*'(.*)'\s*\);/,
            to: 'define( \'QUIZ_BLOCKS_JQUERY_MODAL_VERSION\', \'<%= pkg.quiz_blocks_jquery_modal_version %>\' );',
          },
          {
            from: /Tested up to:(\s*?)[a-zA-Z0-9\.\-\+]+$/m,
            to: 'Tested up to:$1' + pkg.tested_up_to,
          },
        ],
      },
      readme: {
        src: 'readme.*',
        overwrite: true,
        replacements: [
          {
            from: /^(\*\*|)Stable tag:(\*\*|)(\s*?)[a-zA-Z0-9.-]+(\s*?)$/mi,
            to: '$1Stable tag:$2$3<%= pkg.version %>$4',
          },
          {
            from: /Tested up to:(\s*?)[a-zA-Z0-9\.\-\+]+$/m,
            to: 'Tested up to:$1' + pkg.tested_up_to,
          },
        ],
      },
      readme_md: {
        overwrite: true,
        replacements: [
          {
            from: /quizBlocksVersion=&message=v[\w.+-]+&/,
            to: 'quizBlocksVersion=&message=v<%= pkg.version %>&'
          }
        ],
        src: ['readme.md']
      },
      tests: {
        src: '.dev/tests/phpunit/**/*.php',
        overwrite: true,
        replacements: [
          {
            from: /\'version\'(\s*?)\=\>(\s*?)\'(.*)\'/,
            to: '\'version\' \=\> \'<%= pkg.version %>\'',
          },
        ],
      },
      languages: {
        src: 'languages/quiz-blocks.pot',
        overwrite: true,
        replacements: [
          {
            from: /(Project-Id-Version: Lity )[0-9\.]+/,
            to: '$1' + pkg.version,
          },
        ],
      },
    }

  });

  grunt.loadNpmTasks( 'grunt-text-replace' );

  grunt.registerTask( 'version', [ 'replace' ] );

}