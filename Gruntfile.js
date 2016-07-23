module.exports = function(grunt) {

    grunt.initConfig({
        compress: {
            main: {
                options: {
                    archive: 'mpsendtoafriend.zip'
                },
                files: [
                    {src: ['controllers/**'], dest: 'mpsendtoafriend/', filter: 'isFile'},
                    {src: ['classes/**'], dest: 'mpsendtoafriend/', filter: 'isFile'},
                    {src: ['docs/**'], dest: 'mpsendtoafriend/', filter: 'isFile'},
                    {src: ['override/**'], dest: 'mpsendtoafriend/', filter: 'isFile'},
                    {src: ['logs/**'], dest: 'mpsendtoafriend/', filter: 'isFile'},
                    {src: ['vendor/**'], dest: 'mpsendtoafriend/', filter: 'isFile'},
                    {src: ['mails/**'], dest: 'mpsendtoafriend/', filter: 'isFile'},
                    {src: ['translations/**'], dest: 'mpsendtoafriend/', filter: 'isFile'},
                    {src: ['upgrade/**'], dest: 'mpsendtoafriend/', filter: 'isFile'},
                    {src: ['optionaloverride/**'], dest: 'mpsendtoafriend/', filter: 'isFile'},
                    {src: ['oldoverride/**'], dest: 'mpsendtoafriend/', filter: 'isFile'},
                    {src: ['sql/**'], dest: 'mpsendtoafriend/', filter: 'isFile'},
                    {src: ['lib/**'], dest: 'mpsendtoafriend/', filter: 'isFile'},
                    {src: ['defaultoverride/**'], dest: 'mpsendtoafriend/', filter: 'isFile'},
                    {src: ['views/**'], dest: 'mpsendtoafriend/', filter: 'isFile'},
                    {src: 'config.xml', dest: 'mpsendtoafriend/'},
                    {src: 'index.php', dest: 'mpsendtoafriend/'},
                    {src: 'mpsendtoafriend.php', dest: 'mpsendtoafriend/'},
                    {src: 'mpsendtoafriend_ajax.php', dest: 'mpsendtoafriend/'},
                    {src: 'logo.png', dest: 'mpsendtoafriend/'},
                    {src: 'logo.gif', dest: 'mpsendtoafriend/'},
                    {src: 'LICENSE.md', dest: 'mpsendtoafriend/'},
                    {src: 'README.md', dest: 'mpsendtoafriend/'}
                ]
            }
        }
    });

    grunt.loadNpmTasks('grunt-contrib-compress');

    grunt.registerTask('default', ['compress']);
};