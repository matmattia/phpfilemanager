/*var FileBrowserDialogue = {
    init : function () {
        // Here goes your code for setting your custom things onLoad.
		console.log('init');
    },
    mySubmit : function () {
        var URL = 'https://www.matriz.it/files/image/gioele.jpg';
        var win = tinyMCEPopup.getWindowArg("window");

        // insert information now
        win.document.getElementById(tinyMCEPopup.getWindowArg("input")).value = URL;

        // are we an image browser
        if (typeof(win.ImageDialog) != "undefined") {
            // we are, so update image dimensions...
            if (win.ImageDialog.getImageData)
                win.ImageDialog.getImageData();

            // ... and preview if necessary
            if (win.ImageDialog.showPreviewImage)
                win.ImageDialog.showPreviewImage(URL);
        }

        // close popup window
        tinyMCEPopup.close();
    }
}

tinyMCEPopup.onInit.add(FileBrowserDialogue.init, FileBrowserDialogue);*/

var matfilebrowser = {
	'submit': function() {
		top.dispatchEvent(new MessageEvent('message', {
			'data': {
				'sender': 'matfilemanager',
				'url': 'https://www.matriz.it/files/image/gioele.jpg'
			}
		}));
	}
};