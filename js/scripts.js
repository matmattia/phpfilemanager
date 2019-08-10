var phpfilebrowser = {
	'init': function() {
		var that = this, dirs = document.getElementsByClassName('directory'), l = dirs.length, i = 0;
		document.body.addEventListener('click', function() {
			that.closeContextMenus();
		});
		for (i = 0; i < l; i++) {
			phpfilebrowser_directory.init(dirs[i]);
		}
		phpfilebrowser_toolbar.init();
	},
	'selectfile': function() {
		top.dispatchEvent(new MessageEvent('message', {
			'data': {
				'sender': 'matfilemanager',
				'url': ''
			}
		}));
	},
	'ajaxJson': function(url, method, data, success, error) {
		var xhr = new XMLHttpRequest(), formData = null, k = null;
		if (method == 'GET' && data) {
			url += (url.indexOf('?') >= 0 ? '&' : '?') + Object.keys(data).reduce(function(a, k) {
				a.push(k + '=' + encodeURIComponent(data[k]));
				return a;
			}, []).join('&');
			data = null;
		}
		xhr.open(method || 'POST', url, true);
		xhr.addEventListener('readystatechange', function() {
			var json = null;
			if (xhr.readyState == 4 && xhr.status == 200) {
				try {
					json = JSON.parse(xhr.response);
					if (json.ok && json.ok == 1) {
						success(json);
					} else {
						error(json);
					}
				} catch (e) {
					error();
				}
			} else if (xhr.readyState == 4 && xhr.status != 200) {
				error();
			}
		});
		if (data) {
			formData = new FormData();
			for (k in data) {
				formData.append(k, data[k]);
			}
		}
		xhr.send(formData);
	},
	'showContextMenu': function(items, pos) {
		var l = items.length, i = 0, j = 0, nav = null, ul = null, li = null, a = null, icon = null, label = null;
		if (l > 0) {
			this.closeContextMenus();
			ul = document.createElement('ul');
			for (i = 0; i < l; i++) {
				li = document.createElement('li');
				icon = document.createElement('span');
				icon.classList.add('context-menu-icon');
				if (items[i].fa_icon) {
					items[i].fa_icon = items[i].fa_icon.split(' ');
					for (j = 0; j < items[i].fa_icon.length; j++) {
						icon.classList.add(items[i].fa_icon[j]);
					}
				}
				label = document.createTextNode(items[i].label);
				if (items[i].href || items[i].function) {
					a = document.createElement('a');
					a.setAttribute('href', items[i].href || '#');
					if (items[i].function) {
						a.addEventListener('click', items[i].function);
					}
					a.appendChild(icon);
					a.appendChild(label);
					li.appendChild(a);
				} else {
					li.appendChild(icon);
					li.appendChild(label);
					li.classList.add('context-menu-separator');
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
					'fa_icon': 'fas fa-folder-plus',
					'function': function(e) {
						e.preventDefault();
						that.newDir(dir);
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
	'newDir': function(dir) {
		var n = prompt(phpfilebrowser_lang.__('New directory name?'), phpfilebrowser_lang.__('New directory'));
		if (n !== null) {
			this.ajaxJsonUpdateDirectory(dir, 'index.php', 'GET', {
				'dir': dir.getAttribute('data-path') || '',
				'new_dir': n
			}, 'Error on creating directory.');
		}
	},
	'uploadDirFile': function(d, f) {
		this.ajaxJsonUpdateDirectory(d, 'index.php?upload=1&dir=' + (d.getAttribute('data-path') || ''), 'POST', {
			'file': f
		}, 'Error on upload');
	},
	'ajaxJsonUpdateDirectory': function(d, url, method, data, error_msg) {
		phpfilebrowser.ajaxJson(url, method, data, function(r) {
			var el = document.createElement('div'), rd = null;
			el.innerHTML = r.html;
			rd = el.getElementsByClassName('directory');
			if (rd.length > 0) {
				d.innerHTML = rd[0].innerHTML;
			}
		}, function(r) {
			phpfilebrowser_messenger.error(r && r.msg ? r.msg : phpfilebrowser_lang.__(error_msg));
		});
	},
	'getDivByPath': function(path) {
		return document.querySelector('.directory[data-path="' + (path || '') +'"]');
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
},
phpfilebrowser_toolbar = {
	'init': function() {
		var b = document.querySelectorAll('.toolbar [data-operation]'), l = b.length, i = 0;
		for (i = 0; i < l; i++) {
			switch (b[i].getAttribute('data-operation')) {
				case 'new-directory':
					b[i].addEventListener('click', function(e) {
						var dir = phpfilebrowser_directory.getDivByPath(this.getAttribute('data-path'));
						if (dir) {
							phpfilebrowser_directory.newDir(dir);
						}
						e.preventDefault();
					});
				break;
			}
		}
	}
};

document.addEventListener('DOMContentLoaded', function() {
	document.removeEventListener('DOMContentLoaded', arguments.callee, false);
	phpfilebrowser.init();
}, false);