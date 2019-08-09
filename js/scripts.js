var phpfilebrowser = {
	'init': function() {
		var that = this, dirs = document.getElementsByClassName('directory'), l = dirs.length, i = 0;
		document.body.addEventListener('click', function() {
			that.closeContextMenus();
		});
		for (i = 0; i < l; i++) {
			phpfilebrowser_directory.init(dirs[i]);
		}
	},
	'selectfile': function() {
		top.dispatchEvent(new MessageEvent('message', {
			'data': {
				'sender': 'matfilemanager',
				'url': ''
			}
		}));
	},
	'showContextMenu': function(items, pos) {
		var l = items.length, i = 0, nav = null, ul = null, li = null, a = null, label = null;
		if (l > 0) {
			this.closeContextMenus();
			ul = document.createElement('ul');
			for (i = 0; i < l; i++) {
				li = document.createElement('li');
				label = document.createTextNode(items[i].label);
				if (items[i].href || items[i].function) {
					a = document.createElement('a');
					a.setAttribute('href', items[i].href || '#');
					if (items[i].function) {
						a.addEventListener('click', items[i].function);
					}
					a.appendChild(label);
					li.appendChild(a);
				} else {
					li.appendChild(label);
				}
				ul.appendChild(li);
			}
			nav = document.createElement('nav')
			nav.classList.add('context-menu');
			nav.appendChild(ul);
			if (pos) {
				if (!isNaN(pos.x)) {
					nav.style.left = pos.x + 'px';
				}
				if (!isNaN(pos.y)) {
					nav.style.top = pos.y + 'px';
				}
			}
			document.body.appendChild(nav);
		}
	},
	'closeContextMenus': function() {
		var m = document.getElementsByClassName('context-menu'), l = m.length, i = 0;
		for (i = 0; i < l; i++) {
			m[i].remove();
		}
	}
},
phpfilebrowser_directory = {
	'init': function(dir) {
		var that = this;
		dir.addEventListener('dragenter', this.onDragEnter, false);
		dir.addEventListener('dragover', this.onDragEnter, false);
		dir.addEventListener('dragleave', this.onDragLeave, false);
		dir.addEventListener('drop', this.onDragLeave, false);
		dir.addEventListener('drop', function(e) {
			var l = e.dataTransfer.files.length, i = 0;
			for (i = 0; i < l; i++) {
				that.uploadDirFile(this, e.dataTransfer.files[i]);
			}
		}, false);
		dir.addEventListener('contextmenu', function(e) {
			e.preventDefault();
			phpfilebrowser.showContextMenu([
				{
					'label': phpfilebrowser_lang.__('New directory'),
					'function': function(e) {
						e.preventDefault();
						console.log('Prova');
					}
				}
			],
			{
				'x': e.clientX,
				'y': e.clientY
			});
		});
	},
	'onDragEnter': function(e) {
		e.preventDefault();
		e.stopPropagation();
		this.classList.add('directory-drag-over');
	},
	'onDragLeave': function(e) {
		e.preventDefault();
		e.stopPropagation();
		this.classList.remove('directory-drag-over');
	},
	'uploadDirFile': function(d, f) {
		var xhr = new XMLHttpRequest(), formData = new FormData();
		xhr.open('POST', 'index.php?upload=1&dir=' + (d.getAttribute('data-path') || ''), true);
		xhr.addEventListener('readystatechange', function(e) {
			var el = null;
			if (xhr.readyState == 4 && xhr.status == 200) {
				el = document.createElement('div');
				el.innerHTML = xhr.response;
				rd = el.getElementsByClassName('directory');
				if (rd.length > 0) {
					d.innerHTML = rd[0].innerHTML;
				}
			} else if (xhr.readyState == 4 && xhr.status != 200) {
				phpfilebrowser_messenger.error(phpfilebrowser_lang.__('Error on upload'));
			}
		});
		formData.append('file', f);
		xhr.send(formData);
	}
},
phpfilebrowser_lang = {
	'texts': {},
	'__': function(t) {
		return this.texts[t] || t;
	}
},
phpfilebrowser_messenger = {
	'error': function(msg) {
		alert(msg);
	}
};

document.addEventListener('DOMContentLoaded', function() {
	document.removeEventListener('DOMContentLoaded', arguments.callee, false);
	phpfilebrowser.init();
}, false);