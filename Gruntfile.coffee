"use strict"

# # Globbing
module.exports = (grunt) ->

	# Load grunt tasks automatically
	require("load-grunt-tasks") grunt

	# Time how long tasks take. Can help when optimizing build times
	# require("time-grunt") grunt

	# Define the configuration for all the tasks
	grunt.initConfig

		# Watches files for changes and runs tasks based on the changed files
		watch :
			php :
				files : [ "*.php", "**/*.php", "tests/**/*.php"]
				tasks : ["shell:phpunit"]

		shell :
			phpunit :
				command : 'phpunit'


	grunt.registerTask "dev", "Start development", (target)->
		grunt.task.run [
			"shell:phpunit"
			"watch"
		]
