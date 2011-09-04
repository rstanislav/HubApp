if(jQuery) (function($) {
	
	$.extend($.fn, {
		
		selectBox: function(method, data) {
			
			var typeTimer, typeSearch = '';
			
			
			//
			// Private methods
			//
			
			
			var init = function(select, data) {
				
				// Disable for iOS devices (their native controls are more suitable for a touch device)
				if( navigator.userAgent.match(/iPad|iPhone/i) ) return false;
				
				// Element must be a select control
				if( select.tagName.toLowerCase() !== 'select' ) return false;
				
				select = $(select);
				if( select.data('selectBox-control') ) return false;
				
				var control = $('<a class="selectBox" />'),
					inline = select.attr('multiple') || parseInt(select.attr('size')) > 1;
				
				var settings = data || {};
				
				// Inherit class names, style, and title attributes
				control
					.addClass(select.attr('class'))
					.attr('style', select.attr('style') || '')
					.attr('title', select.attr('title') || '')
					.attr('tabindex', parseInt(select.attr('tabindex')))
					.css('display', 'inline-block')
					.bind('focus.selectBox', function() {
						if( this !== document.activeElement ) $(document.activeElement).blur();
						if( control.hasClass('selectBox-active') ) return;
						control.addClass('selectBox-active');
						select.trigger('focus');
					})
					.bind('blur.selectBox', function() {
						if( !control.hasClass('selectBox-active') ) return;
						control.removeClass('selectBox-active');
						select.trigger('blur');
					});
				
				if( select.attr('disabled') ) control.addClass('selectBox-disabled');
				
				// Generate control
				if( inline ) {
					
					//
					// Inline controls
					//
					var options = getOptions(select, 'inline');
					
					control
						.append(options)
						.data('selectBox-options', options)
						.addClass('selectBox-inline')
						.addClass('selectBox-menuShowing')
						.bind('keydown.selectBox', function(event) {
							handleKeyDown(select, event);
						})
						.bind('keypress.selectBox', function(event) {
							handleKeyPress(select, event);
						})
						.bind('mousedown.selectBox', function(event) {
							if( $(event.target).is('A.selectBox-inline') ) event.preventDefault();
							if( !control.hasClass('selectBox-focus') ) control.focus();
						})
						.insertAfter(select);
					
					// Auto-height based on size attribute
					if( !select[0].style.height ) {
						
						var size = select.attr('size') ? parseInt(select.attr('size')) : 5;
						
						// Draw a dummy control off-screen, measure, and remove it
						var tmp = control
							.clone()
							.removeAttr('id')
							.css({
								position: 'absolute',
								top: '-9999em'
							})
							.show()
							.appendTo('body');
						tmp.find('.selectBox-options').html('<li><a>\u00A0</a></li>');
						optionHeight = parseInt(tmp.find('.selectBox-options A:first').html('&nbsp;').outerHeight());
						tmp.remove();
						
						control.height(optionHeight * size);
						
					}
					
					disableSelection(control);
					
				} else {
					
					//
					// Dropdown controls
					//
					
					var label = $('<span class="selectBox-label" />'),
						arrow = $('<span class="selectBox-arrow" />');
					
					label.text( $(select).find('OPTION:selected').text() || '\u00A0' );
					
					var options = getOptions(select, 'dropdown');
					options.appendTo('BODY');
					
					control
						.data('selectBox-options', options)
						.addClass('selectBox-dropdown')
						.append(label)
						.append(arrow)
						.bind('mousedown.selectBox', function(event) {
							if( control.hasClass('selectBox-menuShowing') ) {
								hideMenus();
							} else {
								event.stopPropagation();
								// Webkit fix to prevent premature selection of options
								options.data('selectBox-down-at-x', event.screenX).data('selectBox-down-at-y', event.screenY);
								showMenu(select);
							}
						})
						.bind('keydown.selectBox', function(event) {
							handleKeyDown(select, event);
						})
						.bind('keypress.selectBox', function(event) {
							handleKeyPress(select, event);
						})
						.insertAfter(select);
					
					disableSelection(control);
						
				}
				
				// Store data for later use and show the control
				select
					.addClass('selectBox')
					.data('selectBox-control', control)
					.data('selectBox-settings', settings)
					.hide();
				
			};
			
			
			var getOptions = function(select, type) {
				
				var options;
				
				switch( type ) {
					
					case 'inline':
						

						options = $('<ul class="selectBox-options" />');
						
						if( select.find('OPTGROUP').length ) {
							
							select.find('OPTGROUP').each( function() {
								
								var optgroup = $('<li class="selectBox-optgroup" />');
								optgroup.text($(this).attr('label'));
								options.append(optgroup);
								
								$(this).find('OPTION').each( function() {
									var li = $('<li />'),
										a = $('<a />');
									li.addClass( $(this).attr('class') );
									a.attr('rel', $(this).val()).text( $(this).text() );
									li.append(a);
									if( $(this).attr('disabled') ) li.addClass('selectBox-disabled');
									if( $(this).attr('selected') ) li.addClass('selectBox-selected');
									options.append(li);
								});
								
							});
						
						} else {
						
							select.find('OPTION').each( function() {
								var li = $('<li />'),
									a = $('<a />');
								li.addClass( $(this).attr('class') );
								a.attr('rel', $(this).val()).text( $(this).text() );
								li.append(a);
								if( $(this).attr('disabled') ) li.addClass('selectBox-disabled');
								if( $(this).attr('selected') ) li.addClass('selectBox-selected');
								options.append(li);
							});
							
						}
						
						options
							.find('A')
								.bind('mouseover.selectBox', function(event) {
									addHover(select, $(this).parent());
								})
								.bind('mouseout.selectBox', function(event) {
									removeHover(select, $(this).parent());
								})
								.bind('mousedown.selectBox', function(event) {
									event.preventDefault(); // Prevent options from being "dragged"
									if( !select.selectBox('control').hasClass('selectBox-active') ) select.selectBox('control').focus();
								})
								.bind('mouseup.selectBox', function(event) {
									hideMenus();
									selectOption(select, $(this).parent(), event);
								});
						
						disableSelection(options);
						
						return options;
					
					case 'dropdown':
						
						options = $('<ul class="selectBox-dropdown-menu selectBox-options" />');
						
						if( select.find('OPTGROUP').length ) {
							
							select.find('OPTGROUP').each( function() {
								
								var optgroup = $('<li class="selectBox-optgroup" />');
								optgroup.text($(this).attr('label'));
								options.append(optgroup);
								
								$(this).find('OPTION').each( function() {
									var li = $('<li />'),
										a = $('<a />');
									li.addClass( $(this).attr('class') );
									a.attr('rel', $(this).val()).text( $(this).text() );
									li.append(a);
									if( $(this).attr('disabled') ) li.addClass('selectBox-disabled');
									if( $(this).attr('selected') ) li.addClass('selectBox-selected');
									options.append(li);
								});
								
							});
							
						} else {
							
							if( select.find('OPTION').length > 0 ) {
								select.find('OPTION').each( function() {
									var li = $('<li />'),
										a = $('<a />');
									li.addClass( $(this).attr('class') );
									a.attr('rel', $(this).val()).text( $(this).text() );
									li.append(a);
									if( $(this).attr('disabled') ) li.addClass('selectBox-disabled');
									if( $(this).attr('selected') ) li.addClass('selectBox-selected');
									options.append(li);
								});
							} else {
								options.append('<li>\u00A0</li>');
							}
							
						}
						
						options
							.data('selectBox-select', select)
							.css('display', 'none')
							.appendTo('BODY')
							.find('A')
								.bind('mousedown.selectBox', function(event) {
									event.preventDefault(); // Prevent options from being "dragged"
									if( event.screenX === options.data('selectBox-down-at-x') && event.screenY === options.data('selectBox-down-at-y') ) {
										options.removeData('selectBox-down-at-x').removeData('selectBox-down-at-y');
										hideMenus();
									}
								})
								.bind('mouseup.selectBox', function(event) {
									if( event.screenX === options.data('selectBox-down-at-x') && event.screenY === options.data('selectBox-down-at-y') ) {
										return;
									} else {
										options.removeData('selectBox-down-at-x').removeData('selectBox-down-at-y');
									}
									selectOption(select, $(this).parent());
									hideMenus();
								}).bind('mouseover.selectBox', function(event) {
									addHover(select, $(this).parent());
								})
								.bind('mouseout.selectBox', function(event) {
									removeHover(select, $(this).parent());
								});
						
						disableSelection(options);
						
						return options;
					
				}
				
			};
			
			
			var destroy = function(select) {
				
				select = $(select);
				
				var control = select.data('selectBox-control');
				if( !control ) return;
				var options = control.data('selectBox-options');
				
				options.remove();
				control.remove();
				select
					.removeClass('selectBox')
					.removeData('selectBox-control')
					.removeData('selectBox-settings')
					.show();
				
			};
			
			
			var showMenu = function(select) {
				
				select = $(select);
				var control = select.data('selectBox-control'),
					settings = select.data('selectBox-settings'),
					options = control.data('selectBox-options');
				if( control.hasClass('selectBox-disabled') ) return false;
				
				hideMenus();
				
				// Show menu
				options.css({
					width: control.outerWidth() - (parseInt(control.css('borderLeftWidth')) + parseInt(control.css('borderLeftWidth'))),
					top: control.offset().top + control.outerHeight() - (parseInt(control.css('borderBottomWidth'))),
					left: control.offset().left
				});
				
				switch( settings.menuTransition ) {
					
					case 'fade':
						options.fadeIn(settings.menuSpeed);
						break;
					
					case 'slide':
						options.slideDown(settings.menuSpeed);
						break;
					
					default:
						options.show(settings.menuSpeed);
						break;
					
				}
				
				// Center on selected option
				var li = options.find('.selectBox-selected:first');
				keepOptionInView(select, li, true);
				addHover(select, li);
				
				control.addClass('selectBox-menuShowing');
				
				$(document).bind('mousedown.selectBox', function(event) {
					if( $(event.target).parents().andSelf().hasClass('selectBox-options') ) return;
					hideMenus();
				});
				
			};
			
			
			var hideMenus = function() {
				
				if( $(".selectBox-dropdown-menu").length === 0 ) return;
				$(document).unbind('mousedown.selectBox');
				
				$(".selectBox-dropdown-menu").each( function() {
					
					var options = $(this),
						select = options.data('selectBox-select'),
						control = select.data('selectBox-control'),
						settings = select.data('selectBox-settings');
					
					switch( settings.menuTransition ) {
						
						case 'fade':
							options.fadeOut(settings.menuSpeed);
							break;
						
						case 'slide':
							options.slideUp(settings.menuSpeed);
							break;
							
						default:
							options.hide(settings.menuSpeed);
							break;
						
					}
					
					control.removeClass('selectBox-menuShowing');
					
				});
				
			};
			
			
			var selectOption = function(select, li, event) {
				
				select = $(select);
				li = $(li);
				var control = select.data('selectBox-control'),
					settings = select.data('selectBox-settings');
				
				if( control.hasClass('selectBox-disabled') ) return false;
				if( li.length === 0 || li.hasClass('selectBox-disabled') ) return false;
				
				if( select.attr('multiple') ) {
					
					// If event.shiftKey is true, this will select all options between li and the last li selected
					if( event.shiftKey && control.data('selectBox-last-selected') ) {
						
						li.toggleClass('selectBox-selected');
						
						var affectedOptions;
						if( li.index() > control.data('selectBox-last-selected').index() ) {
							affectedOptions = li.siblings().slice(control.data('selectBox-last-selected').index(), li.index());
						} else {
							affectedOptions = li.siblings().slice(li.index(), control.data('selectBox-last-selected').index());
						}
						
						affectedOptions = affectedOptions.not('.selectBox-optgroup, .selectBox-disabled');
						
						if( li.hasClass('selectBox-selected') ) {
							affectedOptions.addClass('selectBox-selected');
						} else {
							affectedOptions.removeClass('selectBox-selected');
						}
						
					} else if( event.metaKey ) {
						li.toggleClass('selectBox-selected');
					} else {
						li.siblings().removeClass('selectBox-selected');
						li.addClass('selectBox-selected');
					}
					
				} else {
					li.siblings().removeClass('selectBox-selected');
					li.addClass('selectBox-selected');
				}
				
				if( control.hasClass('selectBox-dropdown') ) {
					control.find('.selectBox-label').text(li.text());
				}
				
				// Update original control's value
				var i = 0, selection = [];
				if( select.attr('multiple') ) {
					control.find('.selectBox-selected A').each( function() {
						selection[i++] = $(this).attr('rel');
					});
				} else {
					selection = li.find('A').attr('rel');
				}
				
				// Remember most recently selected item
				control.data('selectBox-last-selected', li);
				
				// Change callback
				if( select.val() !== selection ) {
					select.val(selection);
					select.trigger('change');
				}
				
				return true;
				
			};
			
			
			var addHover = function(select, li) {
				select = $(select);
				li = $(li);
				var control = select.data('selectBox-control'),
					options = control.data('selectBox-options');
				
				options.find('.selectBox-hover').removeClass('selectBox-hover');
				li.addClass('selectBox-hover');
			};
			
			
			var removeHover = function(select, li) {
				select = $(select);
				li = $(li);
				var control = select.data('selectBox-control'),
					options = control.data('selectBox-options');
				options.find('.selectBox-hover').removeClass('selectBox-hover');
			};
			
			
			var keepOptionInView = function(select, li, center) {
				
				if( !li || li.length === 0 ) return;
				
				select = $(select);
				var control = select.data('selectBox-control'),
					options = control.data('selectBox-options'),
					scrollBox = control.hasClass('selectBox-dropdown') ? options : options.parent(),
					top = parseInt(li.offset().top - scrollBox.position().top),
					bottom = parseInt(top + li.outerHeight());
				
				if( center ) {
					scrollBox.scrollTop( li.offset().top - scrollBox.offset().top + scrollBox.scrollTop() - (scrollBox.height() / 2) );
				} else {
					if( top < 0 ) {
						scrollBox.scrollTop( li.offset().top - scrollBox.offset().top + scrollBox.scrollTop() );
					}
					if( bottom > scrollBox.height() ) {
						scrollBox.scrollTop( (li.offset().top + li.outerHeight()) - scrollBox.offset().top + scrollBox.scrollTop() - scrollBox.height() );
					}
				}
				
			};
			
			
			var handleKeyDown = function(select, event) {
				
				//
				// Handles open/close and arrow key functionality
				//
				
				select = $(select);
				var control = select.data('selectBox-control'),
					options = control.data('selectBox-options'),
					totalOptions = 0,
					i = 0;
				
				if( control.hasClass('selectBox-disabled') ) return;
				
				switch( event.keyCode ) {
					
					case 8: // backspace
						event.preventDefault();
						typeSearch = '';
						break;
					
					case 9: // tab
					case 27: // esc
						hideMenus();
						removeHover(select);
						break;
					
					case 13: // enter
						if( control.hasClass('selectBox-menuShowing') ) {
							selectOption(select, options.find('LI.selectBox-hover:first'), event);
							if( control.hasClass('selectBox-dropdown') ) hideMenus();
						} else {
							showMenu(select);
						}
						break;
						
					case 38: // up
					case 37: // left
						
						event.preventDefault();
						
						if( control.hasClass('selectBox-menuShowing') ) {
							
							var prev = options.find('.selectBox-hover').prev('LI');
							totalOptions = options.find('LI:not(.selectBox-optgroup)').length;
							i = 0;
							
							while( prev.length === 0 || prev.hasClass('selectBox-disabled') || prev.hasClass('selectBox-optgroup') ) {
								prev = prev.prev('LI');
								if( prev.length === 0 ) prev = options.find('LI:last');
								if( ++i >= totalOptions ) break;
							}
							
							addHover(select, prev);
							keepOptionInView(select, prev);
							
						} else {
							showMenu(select);
						}
						
						break;
						
					case 40: // down
					case 39: // right
					
						event.preventDefault();
						
						if( control.hasClass('selectBox-menuShowing') ) {
							
							var next = options.find('.selectBox-hover').next('LI');
							totalOptions = options.find('LI:not(.selectBox-optgroup)').length;
							i = 0;
							
							while( next.length === 0 || next.hasClass('selectBox-disabled') || next.hasClass('selectBox-optgroup') ) {
								next = next.next('LI');
								if( next.length === 0 ) next = options.find('LI:first');
								if( ++i >= totalOptions ) break;
							}
							
							addHover(select, next);
							keepOptionInView(select, next);
							
						} else {
							showMenu(select);
						}
						
						break;
						
				}
				
			};
			
			
			var handleKeyPress = function(select, event) {
				
				//
				// Handles type-to-find functionality
				//
				
				select = $(select);
				var control = select.data('selectBox-control'),
					options = control.data('selectBox-options');
				
				if( control.hasClass('selectBox-disabled') ) return;
				
				switch( event.keyCode ) {
					
					case 9: // tab
					case 27: // esc
					case 13: // enter
					case 38: // up
					case 37: // left
					case 40: // down
					case 39: // right
						// Don't interfere with the keydown event!
						break;
					
					default: // Type to find
						
						if( !control.hasClass('selectBox-menuShowing') ) showMenu(select);
						
						event.preventDefault();
						
						clearTimeout(typeTimer);
						typeSearch += String.fromCharCode(event.charCode || event.keyCode);
						
						options.find('A').each( function() {
							if( $(this).text().substr(0, typeSearch.length).toLowerCase() === typeSearch.toLowerCase() ) {
								addHover(select, $(this).parent());
								keepOptionInView(select, $(this).parent());
								return false;
							}
						});
						
						// Clear after a brief pause
						typeTimer = setTimeout( function() { typeSearch = ''; }, 1000);
						
						break;
						
				}
				
			};
			
			
			var enable = function(select) {
				select = $(select);
				select.attr('disabled', false);
				var control = select.data('selectBox-control');
				if( !control ) return;
				control.removeClass('selectBox-disabled');
			};
			
			
			var disable = function(select) {
				select = $(select);
				select.attr('disabled', true);
				var control = select.data('selectBox-control');
				if( !control ) return;
				control.addClass('selectBox-disabled');
			};
			
			
			var setValue = function(select, value) {
				select = $(select);
				select.val(value);
				value = select.val();
				var control = select.data('selectBox-control');
				if( !control ) return;
				var settings = select.data('selectBox-settings'),
					options = control.data('selectBox-options');
				
				// Update label
				control.find('.selectBox-label').text( $(select).find('OPTION:selected').text() || '\u00A0' );
				
				// Update control values
				options.find('.selectBox-selected').removeClass('selectBox-selected');
				options.find('A').each( function() {
					if( typeof(value) === 'object' ) {
						for( var i = 0; i < value.length; i++ ) {
							if( $(this).attr('rel') == value[i] ) {
								$(this).parent().addClass('selectBox-selected');
							}
						}
					} else {
						if( $(this).attr('rel') == value ) {
							$(this).parent().addClass('selectBox-selected');
						}
					}
				});
				
				if( settings.change ) settings.change.call(select);
				
			};
			
			
			var setOptions = function(select, options) {
				
				select = $(select);
				var control = select.data('selectBox-control'),
					settings = select.data('selectBox-settings');
				
				switch( typeof(data) ) {
					
					case 'string':
						select.html(data);
						break;
						
					case 'object':
						select.html('');
						for( var i in data ) {
							if( data[i] === null ) continue;
							if( typeof(data[i]) === 'object' ) {
								var optgroup = $('<optgroup label="' + i + '" />');
								for( var j in data[i] ) {
									optgroup.append('<option value="' + j + '">' + data[i][j] + '</option>');
								}
								select.append(optgroup);
							} else {
								var option = $('<option value="' + i + '">' + data[i] + '</option>');
								select.append(option);
							}
						}
						break;
					
				}
				
				if( !control ) return;
				
				// Remove old options
				control.data('selectBox-options').remove();
				
				// Generate new options
				var type = control.hasClass('selectBox-dropdown') ? 'dropdown' : 'inline',
					options = getOptions(select, type);
				control.data('selectBox-options', options);
				
				switch( type ) {
					case 'inline':
						control.append(options);
						break;
					case 'dropdown':
						control.find('.selectBox-label').text( $(select).find('OPTION:selected').text() || '\u00A0' );
						$("BODY").append(options);
						break;
				}
				
			};
			
			
			var disableSelection = function(selector) {
				$(selector)
					.css('MozUserSelect', 'none')
					.bind('selectstart', function(event) {
						event.preventDefault();
					});
			};
			
			
			//
			// Public methods
			//
			
			
			switch( method ) {
				
				case 'control':
					return $(this).data('selectBox-control');
					break;
				
				case 'settings':
					if( !data ) return $(this).data('selectBox-settings');
					$(this).each( function() {
						$(this).data('selectBox-settings', $.extend(true, $(this).data('selectBox-settings'), data));
					});
					break;
				
				case 'options':
					$(this).each( function() {
						setOptions(this, data);
					});
					break;
				
				case 'value':
					if( !data ) return $(this).val();
					$(this).each( function() {
						setValue(this, data);
					});
					break;
				
				case 'enable':
					$(this).each( function() {
						enable(this);
					});
					break;
				
				case 'disable':
					$(this).each( function() {
						disable(this);
					});
					break;
				
				case 'destroy':
					$(this).each( function() {
						destroy(this);
					});
					break;
				
				default:
					$(this).each( function() {
						init(this, method);
					});
					break;
				
			}
			
			return $(this);
			
		}
		
	});
	
})(jQuery);