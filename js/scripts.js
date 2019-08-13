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
		this.initLoad(dir);
	},
	'initLoad': function(dir) {
		var that = this, files = dir.getElementsByClassName('directory-file'), l = files.length, i = 0;
		for (i = 0; i < l; i++) {
			files[i].addEventListener('contextmenu', function(e) {
				var file = this;
				e.stopPropagation();
				e.preventDefault();
				phpfilebrowser.showContextMenu([
					{
						'label': phpfilebrowser_lang.__('Delete'),
						'fa_icon': 'far fa-trash-alt',
						'function': function(e) {
							e.preventDefault();
							that.deleteDirFile(dir, file);
						}
					},
					{
						'label': phpfilebrowser_lang.__('Rename'),
						'fa_icon': 'far fa-edit',
						'function': function(e) {
							e.preventDefault();
							that.renameDirFile(dir, file);
						}
					}
				],
				{
					'x': e.clientX,
					'y': e.clientY
				});
			});
		}
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
			this.ajaxJsonUpdateDirectory(dir, 'index.php', 'POST', {
				'operation': 'new_dir',
				'path': dir.getAttribute('data-path') || '',
				'name': n
			}, 'Error on creating directory.');
		}
	},
	'uploadDirFile': function(d, f) {
		this.ajaxJsonUpdateDirectory(d, 'index.php', 'POST', {
			'operation': 'upload',
			'path': d.getAttribute('data-path') || '',
			'file': f
		}, 'Error on upload.');
	},
	'deleteDirFile': function(d, f) {
		if (confirm(phpfilebrowser_lang.__('Are you sure to delete this element?'))) {
			this.ajaxJsonUpdateDirectory(d, 'index.php', 'POST', {
				'operation': 'delete',
				'path': f.getAttribute('data-path')
			}, 'Error on delete.');
		}
	},
	'renameDirFile': function(d, f) {
		var n = prompt(phpfilebrowser_lang.__('New name?'), f.getAttribute('data-name') || '');
		if (n !== null) {
			this.ajaxJsonUpdateDirectory(d, 'index.php', 'POST', {
				'operation': 'rename',
				'path': f.getAttribute('data-path'),
				'name': n
			}, 'Error on renaming.');
		}
	},
	'ajaxJsonUpdateDirectory': function(d, url, method, data, error_msg) {
		var that = this;
		phpfilebrowser.ajaxJson(url, method, data, function(r) {
			var el = document.createElement('div'), rd = null;
			el.innerHTML = r.html;
			rd = el.getElementsByClassName('directory');
			if (rd.length > 0) {
				d.innerHTML = rd[0].innerHTML;
				that.initLoad(d);
			}
		}, function(r) {
			if (r && r.msg) {
				switch (typeof r.msg) {
					case 'object':
						if (Array.isArray(r.msg)) {
							r.msg = r.msg.join("\r\n");
						} else {
							r.msg = null;
						}
					break;
					case 'string':
						if (r.msg == '') {
							r.msg = null;
						}
					break;
					default:
						r.msg = null;
					break;
				}
			}
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
				case 'upload':
					b[i].addEventListener('click', function(e) {
						var dir = phpfilebrowser_directory.getDivByPath(this.getAttribute('data-path')), inp = null;
						if (dir) {
							inp = document.createElement('input');
							inp.setAttribute('type', 'file');
							inp.classList.add('fake-input');
							document.body.append(inp);
							inp.dispatchEvent(new MouseEvent('click', {
								'view': window,
								'bubbles': true,
								'cancelable': true
							}));
							inp.addEventListener('change', function(e) {
								var l = this.files.length, i = 0;
								for (i = 0; i < l; i++) {
									phpfilebrowser_directory.uploadDirFile(dir, this.files[i]);
								}
							});
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