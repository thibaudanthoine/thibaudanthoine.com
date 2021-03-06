var pkgjson = require('./package.json');

var config = {
  pkg: pkgjson,
  app: 'src',
  dist: 'dist'
};

module.exports = function (grunt) {

  require("matchdep").filterDev("grunt-*").forEach(grunt.loadNpmTasks);

  grunt.initConfig({
    config: config,
    pkg: config.pkg,
    bower: grunt.file.readJSON('./.bowerrc'),
    deployableFiles: [
      '*.{html,txt,pdf,png}',
      '<%= config.dist %>/css/*',
      '<%= config.dist %>/js/**/*',
      '<%= config.app %>/img/**/*',
      '.htaccess'
    ],

    copy: {
      dist: {
       files: [{
         expand: true,
         cwd: '<%= config.app %>',
         src: 'img/*',
         dest: '<%= config.dist %>'
       }]
      }
    },

    htmlhint: {
      build: {
        options: {
          'tag-pair': true,
          'tagname-lowercase': true,
          'attr-lowercase': true,
          'attr-value-double-quotes': true,
          'doctype-first': true,
          'spec-char-escape': true,
          'id-unique': true,
          'style-disabled': true
        },
        src: ['*.html']
      }
    },

    jshint: {
      options: {
        reporter: require('jshint-stylish')
      },
      all: [
        'Gruntfile.js',
        '<%= config.app %>/js/{,*/}*.js'
      ]
    },

    uglify: {
      options: {
        banner: '/*! <%= pkg.name %> lib - v<%= pkg.version %> - <%= grunt.template.today("yyyy-mm-dd") %> */'
      },
      dist: {
        files: {
          '<%= config.dist %>/js/script.min.js': [
            '<%= bower.directory %>/jquery/dist/jquery.js',
            '<%= bower.directory %>/bootstrap/dist/js/bootstrap.js',
            '<%= bower.directory %>/alertify/alertify.js'
          ]
        }
      }
    },

    compass: {
      dist: {
        options: {
          sassDir: '<%= config.app %>/sass',
          cssDir: '<%= config.app %>/css',
          environment: 'production',
          outputStyle: 'compressed'
        }
      },
      dev: {
        options: {
          sassDir: '<%= config.app %>/sass',
          cssDir: '<%= config.app %>/css',
          outputStyle: 'expanded'
        }
      }
    },

    concat: {
      css: {
        src: [
          '<%= config.app %>/css/*',
          '<%= config.app %>/vendor/alertify/themes/alertify.core.css',
          '<%= config.app %>/vendor/alertify/themes/alertify.bootstrap.css'
        ],
        dest: '<%= config.dist %>/css/style.css'
      }
    },

    cssmin: {
      css: {
        src: '<%= config.dist %>/css/style.css',
        dest: '<%= config.dist %>/css/style.min.css'
      }
    },

    watch: {
      html: {
        files: '<%= htmlhint.build.src %>',
        tasks: ['htmlhint']
      },
      scripts: {
        files: '<%= deployableFiles %>',
        tasks: ['jshint'],
        options: {
            spawn: false
        }
      },
      css: {
        files: ['<%= config.app %>/sass/**/*.scss'],
        tasks: ['compass:dist', 'concat:css', 'cssmin:css'],
        options: {
            spawn: false
        }
      }
    }
  });

  grunt.registerTask('default', ['htmlhint', 'copy', 'jshint', 'uglify', 'compass', 'concat:css', 'cssmin:css', 'watch']);
};