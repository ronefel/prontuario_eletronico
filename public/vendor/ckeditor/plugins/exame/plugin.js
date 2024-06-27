/**
 * Copyright (c) 2014-2023, CKSource Holding sp. z o.o. All rights reserved.
 * Licensed under the terms of the MIT License (see LICENSE.md).
 *
 * Basic sample plugin inserting current date and time into the CKEditor editing area.
 *
 * Created out of the CKEditor Plugin SDK:
 * https://ckeditor.com/docs/ckeditor4/latest/guide/plugin_sdk_intro.html
 */

// Register the plugin within the editor.
CKEDITOR.plugins.add( 'exame', {

	// Register the icons. They must match command names.
	icons: 'exame',

	// The plugin initialization logic goes inside this method.
	init: function( editor ) {

		// Define the editor command that inserts a exame.
		editor.addCommand( 'insertExame', {

			// Define the function that will be fired when the command is executed.
			exec: function( editor ) {
				var now = new Date();

				// Insert the exame into the document.
				editor.insertHtml( 'The current date and time is: <em>' + now.toString() + '</em>' );
			}
		});

		// Create the toolbar button that executes the above command.
		editor.ui.addButton( 'Exame', {
			label: 'Insert Exame',
			command: 'insertExame',
			toolbar: 'insert'
		});
	}
});
