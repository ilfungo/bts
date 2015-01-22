'use strict';
module.exports = function(grunt) {

    grunt.initConfig({
        // setting folder templates
        dirs: {
            css: 'assets/css',
            less: 'assets/less',
            fonts: 'assets/fonts',
            images: 'assets/images',
            js: 'assets/js'
        },

        // Compile all .less files.
        less: {

            // one to one
            core: {
                options: {
                    sourceMap: true,
                    sourceMapFilename: '<%= dirs.css %>/style.css.map',
                    sourceMapURL: 'style.css.map',
                    sourceMapRootpath: '../../'
                },
                files: {
                    '<%= dirs.css %>/style.css': '<%= dirs.less %>/style.less'
                }
            },

            admin: {
                files: {
                    '<%= dirs.css %>/admin.css': ['<%= dirs.less %>/admin.less', '<%= dirs.less %>/admin-report.less']
                }
            }
        },

        uglify: {
            minify: {
                expand: true,
                cwd: '<%= dirs.js %>',
                src: [
                    '*.js',
                ],
                dest: '<%= dirs.js %>/',
                ext: '.min.js'
            }
        },

        jshint: {
            options: {
                jshintrc: '.jshintrc'
            },
            all: [
                'Gruntfile.js',
                '<%= dirs.js %>/*.js',
                '!<%= dirs.js %>/*.min.js',
            ]
        },

        concat: {
            '<%= dirs.js %>/all.js': [
                '<%= dirs.js %>/admin.js',
                '<%= dirs.js %>/orders.js',
                '<%= dirs.js %>/product-editor.js',
                '<%= dirs.js %>/reviews.js',
                '<%= dirs.js %>/script.js',
                '<%= dirs.js %>/settings.js',
            ],
            '<%= dirs.js %>/flot-all.min.js': [
                '<%= dirs.js %>/jquery.flot.min.js',
                '<%= dirs.js %>/jquery.flot.pie.min.js',
                '<%= dirs.js %>/jquery.flot.resize.min.js',
                '<%= dirs.js %>/jquery.flot.stack.min.js',
                '<%= dirs.js %>/jquery.flot.time.min.js',
            ],
            '<%= dirs.css %>/dokan-extra.css': [
                '<%= dirs.css %>/chosen.min.css',
                '<%= dirs.css %>/icomoon.css',
                '<%= dirs.css %>/tabulous.css'
            ]
        },

        // Generate POT files.
        makepot: {
            target: {
                options: {
                    domainPath: '/languages/', // Where to save the POT file.
                    potFilename: 'dokan.pot', // Name of the POT file.
                    type: 'wp-plugin', // Type of project (wp-plugin or wp-theme).
                    potHeaders: {
                        'report-msgid-bugs-to': 'http://wedevs.com/support/forum/theme-support/dokan/',
                        'language-team': 'LANGUAGE <EMAIL@ADDRESS>'
                    }
                }
            }
        },

        watch: {
            less: {
                files: ['<%= dirs.less %>/*.less'],
                tasks: ['less:core', 'less:admin'],
                options: {
                    livereload: true
                }
            }
        }
    });

    // Load NPM tasks to be used here
    grunt.loadNpmTasks( 'grunt-contrib-less' );
    grunt.loadNpmTasks( 'grunt-contrib-concat' );
    grunt.loadNpmTasks( 'grunt-contrib-jshint' );
    grunt.loadNpmTasks( 'grunt-wp-i18n' );
    grunt.loadNpmTasks( 'grunt-contrib-uglify' );
    grunt.loadNpmTasks( 'grunt-contrib-watch' );

    grunt.registerTask( 'default', [
        'less',
        'concat',
        // 'uglify'
    ]);

    grunt.registerTask('release', [
        'makepot',
        'less',
        'concat',
        // 'uglify'
    ]);
};