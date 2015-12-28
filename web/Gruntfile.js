'use strict';
module.exports = function(grunt) {

    var jsFiles = [
        'js/plugins/*.js',
        'js/scripts.dev.js',
        'js/scripts.min.js'
    ];

    grunt.initConfig({
        pkg: grunt.file.readJSON('package.json'),

        concat: {
            dev: {
                files: {
                    '!js/scripts.dev.js': jsFiles
                }
            }
        },

        uglify: {
            prod: {
                files: {
                    'js/build.js': 'js/scripts.js'
                }
            }
        },

        less: {
            dev: {
                files: {
                    'css/main-dev.css': [
                        'less/main.less'
                    ],
                    'css/desktop.css': [
                        'less/desktop.less'
                    ]
                }
            },
            prod: {
                files: {
                    'css/main.css': [
                        'less/main.less'
                    ],
                    'css/desktop.css': [
                        'less/desktop.less'
                    ]
                },
                options: {
                    compress: true,
                    yuicompress: true
                }
            }
        },

        autoprefixer: {
            dev: {
                src: [
                    'css/main-dev.css'
                ]
            },
            prod: {
                src: [
                    'css/main.css'
                ]
            }
        },

        watch: {
            js: {
                files: [
                    'js/vendors/*.js',
                    'js/plugins/*.js',

                    'js/scripts.js',
                    '!js/scripts.min.js'
                ],
                tasks: [
                    'concat',
                    'uglify'
                ]
            },
            less: {
                files: [
                    'less/themes/desktop.less',
                    'less/*.less',
                    'less/base/*.less',
                    'less/components/*.less',
                    'less/layout/*.less',
                    'less/pages/*.less',
                    'less/themes/*.less',
                    'less/utils/*.less',
                    'less/vendors/*.less',


                ],
                tasks: [
                    'less',
                    'autoprefixer'
                ]
            }
        }
    });

    grunt.loadNpmTasks('grunt-contrib-watch');
    grunt.loadNpmTasks('grunt-contrib-concat');
    grunt.loadNpmTasks('grunt-contrib-uglify');
    grunt.loadNpmTasks('grunt-contrib-less');
    grunt.loadNpmTasks('grunt-autoprefixer');

    grunt.registerTask('default', [
        'concat',
        'uglify',
        'less',
        'autoprefixer'
    ]);

    grunt.registerTask('dev', [
        'concat:dev',
        'less:dev',
        'autoprefixer:dev'
    ]);

    grunt.registerTask('prod', [
        'uglify:prod',
        'less:prod',
        'autoprefixer:prod'
    ]);

    grunt.registerTask('observe', [
        'watch'
    ]);
};
