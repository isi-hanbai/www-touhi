/**
 * @license Copyright (c) 2003-2013, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see LICENSE.html or http://ckeditor.com/license
 */
CKEDITOR.editorConfig = function( config ) {
	// Define changes to default configuration here. For example:
	// config.language = 'fr';
	// config.uiColor = '#AADC6E';
	config.language = 'ja';
	config.filebrowserBrowseUrl = beseurl+'/theme2/vendors/kcfinder/browse.php?type=files';
	config.filebrowserImageBrowseUrl = beseurl+'/theme2/vendors/kcfinder/browse.php?type=images';
	config.filebrowserFlashBrowseUrl = beseurl+'/theme2/vendors/kcfinder/browse.php?type=flash';
	config.filebrowserUploadUrl = beseurl+'/theme2/vendors/kcfinder/upload.php?type=files';
	config.filebrowserImageUploadUrl = beseurl+'/theme2/vendors/kcfinder/upload.php?type=images';
	config.filebrowserFlashUploadUrl = beseurl+'/theme2/vendors/kcfinder/upload.php?type=flash';
};
