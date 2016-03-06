module.exports = function (grunt) {

    var package = grunt.file.readJSON('package.json');

    var less_custom_directories = [];
    less_custom_directories.push(package.sandstone_directory + "/css/less/config.less");
    less_custom_directories.push(package.custom_directory + '/css/less/{**/*,*}.less');

    var js_directories = [];
    js_directories.push(package.custom_directory + "/js/{**/*,*}.js");

    grunt.initConfig({
        pkg: package,


        concat: {
            custom_less: {
                options: {
                    separator: ''
                },
                src: less_custom_directories,
                dest: 'public/css/custom.cat.less'
            },
            custom_js: {
                options: {
                    separator: ''
                },
                src: js_directories,
                dest: 'public/js/global.cat.js'
            }


        },
        //Minify JS
        uglify: {
            main: {
                options: {
                    beautify: package.debug_mode
                },
                files: {
                    'public/js/global.min.js': ['public/js/global.cat.js']
                }
            }
        },
        less: {

            custom_development: {
                options: {
                    compress: false
                },
                files: {
                        "public/css/custom.css": "public/css/custom.cat.less"
                }
            },
            custom_production: {
                options: {
                    compress: true,
                    plugins: [

                    ],
                },
                files: {
                    "public/css/custom.css": "public/css/custom.cat.less"
                }
            }

        },

        clean: {
            js: ["public/js/global.cat.js"],
            less: ["public/css/custom.cat.less"]
        },

        watch: {
            less: {
                files: less_custom_directories,
                tasks: ['concat:custom_less', 'less:custom_development', 'less:custom_production', 'clean:less']
            },
            js: {
                files: js_directories,
                tasks: ['concat:custom_js', 'uglify:main', 'clean:js']
            }
        }
    });

    grunt.loadNpmTasks('grunt-contrib-concat');
    grunt.loadNpmTasks('grunt-contrib-uglify');
    grunt.loadNpmTasks('grunt-contrib-less');
    grunt.loadNpmTasks('grunt-contrib-clean');
    grunt.loadNpmTasks('grunt-contrib-watch');

    grunt.registerTask('default', ['concat', 'uglify', 'less', 'clean', 'watch']);
};