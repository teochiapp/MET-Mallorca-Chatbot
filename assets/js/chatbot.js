/**
 * JavaScript del Chatbot MET Mallorca
 */

(function($) {
    'use strict';
    
    const MetChatbot = {
        // Estado del chatbot
        state: {
            isOpen: false,
            currentStep: 'welcome',
            conversationData: {
                language: (window.MetTranslations && window.MetTranslations.getLanguage && window.MetTranslations.getLanguage()) || 'es'
            },
            messages: []
        },

        policyLinks: {
            es: 'https://metmallorca.com/es/condiciones-contratacion/',
            en: 'https://metmallorca.com/en/condiciones-contratacion/',
            de: 'https://metmallorca.com/de/condiciones-contratacion/'
        },
        
        // Inicializar
        init: function() {
            this.syncLanguageWithTranslations();
            this.bindEvents();
            this.updateUILanguage();
            this.showInvitation();
            this.updateToggleButtonUI();
        },

        syncLanguageWithTranslations: function() {
            if (window.MetTranslations && typeof window.MetTranslations.getLanguage === 'function') {
                const lang = window.MetTranslations.getLanguage();
                this.state.conversationData.language = lang || 'es';
                $('.met-lang-btn').removeClass('active');
                $(`.met-lang-btn[data-lang="${this.state.conversationData.language}"]`).addClass('active');
            }
        },
        
        // Eventos
        bindEvents: function() {
            const self = this;
            
            // Toggle chatbot
            $('#met-chatbot-toggle').on('click', function() {
                self.toggleChatbot();
                self.hideInvitation();
            });
            
            // Minimizar
            $('#met-chatbot-minimize').on('click', function() {
                self.toggleChatbot();
            });
            
            // Cerrar diálogo de invitación
            $(document).on('click', '.met-chatbot-invitation-close', function() {
                self.hideInvitation();
            });
            
            // Clic en el diálogo abre el chatbot
            $(document).on('click', '.met-chatbot-invitation-content', function() {
                self.toggleChatbot();
                self.hideInvitation();
            });
            
            // Enviar mensaje con Ctrl+Enter o Shift+Enter
            $('#met-chatbot-input').on('keydown', function(e) {
                if (e.which === 13 && !e.shiftKey) {
                    e.preventDefault();
                    self.sendTextMessage();
                }
            });

            // Bloquear caracteres no numéricos cuando corresponda
            $('#met-chatbot-input').on('keypress', function(e) {
                const inputType = $(this).attr('data-input-type') || 'text';
                const shouldForceNumber = inputType === 'number' || self.state.currentStep === 'passengers';

                if (shouldForceNumber) {
                    const char = String.fromCharCode(e.which);
                    const isControl = e.ctrlKey || e.metaKey || e.altKey || e.which < 32;

                    if (!isControl && !/^[0-9]$/.test(char)) {
                        e.preventDefault();
                    }
                }
            });
            
            // Auto-ajustar altura del textarea
            $('#met-chatbot-input').on('input', function() {
                const inputType = $(this).attr('data-input-type') || 'text';
                const shouldForceNumber = inputType === 'number' || self.state.currentStep === 'passengers';

                if (shouldForceNumber) {
                    const sanitized = this.value.replace(/[^0-9]/g, '');
                    if (sanitized !== this.value) {
                        this.value = sanitized;
                    }
                }

                this.style.height = 'auto';
                this.style.height = Math.min(this.scrollHeight, 120) + 'px';
            });
            
            // Botón enviar
            $('#met-chatbot-send').on('click', function() {
                self.sendTextMessage();
            });
            
            // Delegación de eventos para botones de opciones
            $(document).on('click', '.met-option-button', function() {
                const value = $(this).data('value');
                const text = $(this).text();
                const url = $(this).data('url');
                
                if (url) {
                    window.open(url, '_blank');
                } else {
                    self.handleOptionClick(value, text);
                }
            });
            
            // Selector de idioma
            $(document).on('click', '.met-lang-btn', function() {
                const lang = $(this).data('lang');
                self.changeLanguage(lang);
            });
            
            // Escuchar cambios de idioma
            $(document).on('met-language-changed', function(e, lang) {
                self.updateUILanguage();
            });
        },
        
        // Toggle chatbot
        toggleChatbot: function() {
            this.state.isOpen = !this.state.isOpen;
            this.updateToggleButtonUI();
            
            if (this.state.isOpen && this.state.messages.length === 0) {
                this.startConversation();
            }
        },

        updateToggleButtonUI: function() {
            $('#met-chatbot-widget').toggleClass('open', this.state.isOpen);
            $('#met-chatbot-toggle')
                .toggleClass('is-open', this.state.isOpen)
                .attr('aria-label', this.state.isOpen ? 'Cerrar chat' : 'Abrir chat');
            $('#met-chatbot-toggle .met-chatbot-icon').toggle(!this.state.isOpen);
            $('#met-chatbot-toggle .met-chatbot-close').toggle(this.state.isOpen);
        },
        
        // Iniciar conversación
        startConversation: function() {
            this.sendMessage('', 'welcome', this.state.conversationData);
        },
        
        // Manejar clic en opción
        handleOptionClick: function(value, text) {
            // Agregar mensaje del usuario
            this.addMessage('user', text);
            
            // Enviar al servidor
            this.sendMessage(value, this.state.currentStep, this.state.conversationData);
        },
        
        // Enviar mensaje de texto
        sendTextMessage: function() {
            const input = $('#met-chatbot-input');
            const message = input.val().trim();
            const inputType = input.attr('data-input-type') || 'text';
            const shouldForceNumber = inputType === 'number' || this.state.currentStep === 'passengers';

            if (!message) return;

            let finalMessage = message;

            if (shouldForceNumber) {
                finalMessage = message.replace(/[^0-9]/g, '');

                if (!finalMessage) {
                    input.val('');
                    input.css('height', 'auto');
                    return;
                }
            }

            // Agregar mensaje del usuario
            this.addMessage('user', finalMessage);

            // Limpiar textarea y resetear altura
            input.val('');
            input.css('height', 'auto');

            // Enviar al servidor
            this.sendMessage(finalMessage, this.state.currentStep, this.state.conversationData);
        },
        
        // Enviar mensaje al servidor
        sendMessage: function(message, step, data) {
            const self = this;
            const payloadData = Object.assign({}, data);

            // Asegurar idioma
            if (!payloadData.language && window.MetTranslations) {
                payloadData.language = window.MetTranslations.getLanguage();
            }

            this.state.conversationData = payloadData;
            
            // Mostrar typing indicator
            this.showTyping();
            
            $.ajax({
                url: metChatbot.ajaxUrl,
                method: 'POST',
                data: {
                    action: 'met_chatbot_message',
                    nonce: metChatbot.nonce,
                    message: message,
                    step: step,
                    data: JSON.stringify(payloadData)
                },
                success: function(response) {
                    self.hideTyping();
                    
                    if (response.success) {
                        self.handleResponse(response.data);
                    } else {
                        self.addMessage('bot', 'Lo siento, ha ocurrido un error. Por favor, intenta de nuevo.');
                    }
                },
                error: function() {
                    self.hideTyping();
                    self.addMessage('bot', 'Lo siento, ha ocurrido un error de conexión. Por favor, intenta de nuevo.');
                }
            });
        },
        
        // Manejar respuesta del servidor
        handleResponse: function(data) {
            // Actualizar estado
            this.state.currentStep = data.nextStep;
            const previousLang = this.state.conversationData.language;
            const newData = data.data || {};
            if (previousLang && !newData.language) {
                newData.language = previousLang;
            }
            this.state.conversationData = newData;
            
            // Si es un formulario embebido, manejarlo de forma especial
            if (data.showForm) {
                this.addFormMessage('bot', data.message);
                this.hideOptions();
                this.hideTextInput();
            } else {
                // Agregar mensaje del bot
                this.addMessage('bot', data.message);
                
                // Mostrar opciones o input
                if (data.options && data.options.length > 0) {
                    this.showOptions(data.options);
                    this.hideTextInput();
                } else if (data.inputType === 'location') {
                    // Mostrar buscador de ubicaciones
                    this.showLocationSearcher(data.placeholder || 'Buscar ubicación...');
                    this.hideOptions();
                } else if (data.inputType === 'time_searcher') {
                    // Mostrar buscador de horarios
                    this.showTimeSearcher(data.placeholder || 'Buscar horario (ej: 14:30)');
                    this.hideOptions();
                } else if (data.inputType === 'extras_form') {
                    // Mostrar formulario de extras
                    this.showExtrasForm(data.extrasConfig || {});
                    this.hideOptions();
                } else if (data.inputType) {
                    this.showTextInput(data.inputType);
                    this.hideOptions();
                } else {
                    this.hideOptions();
                    this.hideTextInput();
                }
            }
            
            // Si hay una acción especial (como verificar reserva)
            if (data.action === 'verify_booking') {
                this.verifyBooking(data.data.booking_code, data.data.email);
            }
        },
        
        // Verificar reserva
        verifyBooking: function(bookingCode, email) {
            const self = this;
            
            $.ajax({
                url: metChatbot.ajaxUrl,
                method: 'POST',
                data: {
                    action: 'met_verify_booking',
                    nonce: metChatbot.nonce,
                    booking_code: bookingCode,
                    email: email
                },
                success: function(response) {
                    if (response.success) {
                        self.addMessage('bot', response.data.message);
                        
                        // Si se encontró la reserva, mostrar opciones
                        if (response.data.found) {
                            const options = [
                                {
                                    text: '📋 Ver detalles completos',
                                    value: 'view_details',
                                    url: '/mi-cuenta/orders/' + response.data.order_id
                                },
                                {
                                    text: '✏️ Modificar reserva',
                                    value: 'modify',
                                    url: '/contacto/?ref=' + bookingCode
                                },
                                {
                                    text: '🏠 Volver al inicio',
                                    value: 'restart'
                                }
                            ];
                            self.showOptions(options);
                        } else {
                            // No se encontró, ofrecer nueva reserva
                            const options = [
                                {
                                    text: '🆕 Hacer nueva reserva',
                                    value: 'restart'
                                },
                                {
                                    text: '📞 Contactar soporte',
                                    value: 'contact',
                                    url: '/contacto/'
                                }
                            ];
                            self.showOptions(options);
                        }
                    }
                },
                error: function() {
                    self.addMessage('bot', 'Error al verificar la reserva. Por favor, intenta de nuevo.');
                }
            });
        },
        
        // Agregar mensaje
        addMessage: function(type, content) {
            const messagesContainer = $('#met-chatbot-messages');
            
            const avatar = type === 'bot' 
                ? '<i class="fas fa-robot"></i>'
                : '<i class="fas fa-user"></i>';
            
            const messageHtml = `
                <div class="met-message ${type}">
                    <div class="met-message-avatar">${avatar}</div>
                    <div class="met-message-content">${content}</div>
                </div>
            `;
            
            messagesContainer.append(messageHtml);
            this.scrollToBottom();
            
            // Guardar en estado
            this.state.messages.push({ type, content });
        },
        
        // Agregar mensaje con formulario embebido
        addFormMessage: function(type, content) {
            const messagesContainer = $('#met-chatbot-messages');
            
            const avatar = type === 'bot' 
                ? '<i class="fas fa-robot"></i>'
                : '<i class="fas fa-user"></i>';
            
            const messageHtml = `
                <div class="met-message ${type} met-message-form">
                    <div class="met-message-avatar">${avatar}</div>
                    <div class="met-message-content met-form-content">${content}</div>
                </div>
            `;
            
            messagesContainer.append(messageHtml);
            
            // Esperar un momento para que el DOM se actualice
            setTimeout(() => {
                this.scrollToBottom();
                // Reinicializar scripts del formulario si es necesario
                this.initializeBookingForm();
            }, 100);
            
            // Guardar en estado
            this.state.messages.push({ type, content, isForm: true });
        },
        
        // Inicializar formulario de reservas
        initializeBookingForm: function() {
            // Si el plugin de reservas tiene scripts de inicialización, ejecutarlos aquí
            // Por ejemplo, si usa jQuery plugins o eventos personalizados
            if (typeof window.CHBSBookingForm !== 'undefined') {
                // Reinicializar el formulario del plugin
                window.CHBSBookingForm.init();
            }
            
            // Trigger evento para que otros scripts sepan que el formulario está listo
            $(document).trigger('met-booking-form-loaded');

            this.bindBookingVerifierForm();
        },

        bindBookingVerifierForm: function() {
            const self = this;

            $(document)
                .off('submit.metBookingVerifier')
                .on('submit.metBookingVerifier', '.met-chatbot-booking-verifier-form', function(e) {
                    e.preventDefault();

                    const $form = $(this);
                    const $wrapper = $form.closest('.met-chatbot-booking-verifier');
                    const $result = $wrapper.find('.met-chatbot-booking-verifier-result');
                    const $input = $form.find('input[name="met_booking_code"]');
                    const $button = $form.find('button[type="submit"]');
                    const bookingCode = $input.val().trim();
                    const originalLabel = $button.text();

                    if (!bookingCode) {
                        $result.html(self.renderInlineNotice('error', 'Ingresa un código válido.'));
                        return;
                    }

                    $button.prop('disabled', true).text('Verificando...');
                    $result.html(self.renderInlineNotice('info', 'Procesando tu reserva...'));

                    $.ajax({
                        url: metChatbot.ajaxUrl,
                        method: 'POST',
                        data: {
                            action: 'met_verify_booking_inline',
                            nonce: metChatbot.nonce,
                            booking_code: bookingCode
                        },
                        success: function(response) {
                            $button.prop('disabled', false).text(originalLabel);

                            if (response && response.success) {
                                $result.html(response.data.html);

                                if (response.data.verified) {
                                    self.showOptions([
                                        {
                                            text: '🏠 Volver al inicio',
                                            value: 'restart'
                                        }
                                    ]);
                                }
                            } else {
                                $result.html(self.renderInlineNotice('error', 'Error al verificar la reserva.'));
                            }
                        },
                        error: function() {
                            $button.prop('disabled', false).text(originalLabel);
                            $result.html(self.renderInlineNotice('error', 'No pudimos verificar la reserva. Intenta de nuevo.'));
                        }
                    });
                });
        },

        renderInlineNotice: function(type, message) {
            const styles = {
                success: 'border:1px solid #b0e9c1;background:#f2fff5;color:#135c1c;',
                error: 'border:1px solid #f5c6cb;background:#fff5f5;color:#721c24;',
                info: 'border:1px solid #bee5eb;background:#e8f7fb;color:#0c5460;'
            };

            const style = styles[type] || styles.info;
            return `<div style="margin-top:10px;padding:12px;border-radius:6px;${style}">${message}</div>`;
        },
        
        // Mostrar typing indicator
        showTyping: function() {
            const typingHtml = `
                <div class="met-message bot met-typing-message">
                    <div class="met-message-avatar">
                        <i class="fas fa-robot"></i>
                    </div>
                    <div class="met-message-content">
                        <div class="met-typing">
                            <span></span>
                            <span></span>
                            <span></span>
                        </div>
                    </div>
                </div>
            `;
            
            $('#met-chatbot-messages').append(typingHtml);
            this.scrollToBottom();
        },
        
        // Ocultar typing indicator
        hideTyping: function() {
            $('.met-typing-message').remove();
        },
        
        // Mostrar opciones
        showOptions: function(options) {
            const optionsContainer = $('#met-chatbot-options');
            optionsContainer.empty();
            
            options.forEach(function(option) {
                const url = option.url ? `data-url="${option.url}"` : '';
                const buttonHtml = `
                    <button class="met-option-button" data-value="${option.value}" ${url}>
                        ${option.text}
                    </button>
                `;
                optionsContainer.append(buttonHtml);
            });
            
            optionsContainer.show();
        },
        
        // Ocultar opciones
        hideOptions: function() {
            $('#met-chatbot-options').hide().empty();
        },
        
        // Mostrar input de texto
        showTextInput: function(type) {
            const input = $('#met-chatbot-input');
            $('#met-chatbot-location-container').hide().empty();
            $('#met-chatbot-time-container').hide().empty();
            const effectiveType = type || 'text';
            input.attr('data-input-type', effectiveType);
            input.data('input-type', effectiveType);
            if (effectiveType === 'number') {
                input.attr('inputmode', 'numeric');
                input.attr('pattern', '[0-9]*');
            } else {
                input.removeAttr('inputmode');
                input.removeAttr('pattern');
            }

            // Configurar placeholder según tipo
            if (effectiveType === 'number') {
                input.attr('placeholder', 'Escribe un número...');
            } else if (effectiveType === 'email') {
                input.attr('placeholder', 'Escribe tu email...');
            } else {
                input.attr('placeholder', 'Escribe tu mensaje...');
            }
            
            $('#met-chatbot-text-input').show();
            
            // Enfocar y resetear altura
            input.css('height', 'auto');
            input.focus();
        },
        
        // Ocultar input de texto
        hideTextInput: function() {
            $('#met-chatbot-text-input').hide();
            $('#met-chatbot-input').val('');
        },
        
        // Mostrar buscador de ubicaciones
        showLocationSearcher: function(placeholder) {
            const self = this;
            
            // Ocultar otros inputs
            this.hideTextInput();
            this.hideOptions();
            $('#met-chatbot-time-container').hide().empty();
            
            // Obtener contenedor
            const $container = $('#met-chatbot-location-container');
            
            // Limpiar contenedor
            $container.empty();
            
            // Generar HTML del buscador
            const searcherHtml = window.MetLocationSearcher.createSearcher(
                placeholder || 'Buscar ubicación...', 
                'met-location-input-chat'
            );
            $container.html(searcherHtml);
            
            // Cargar ubicaciones si no están cargadas
            if (window.MetLocationSearcher.locations.length === 0) {
                window.MetLocationSearcher.loadLocations(function() {
                    $container.show();
                    // Enfocar el input después de cargar
                    setTimeout(function() {
                        $('#met-location-input-chat').focus();
                    }, 100);
                });
            } else {
                $container.show();
                // Enfocar el input
                setTimeout(function() {
                    $('#met-location-input-chat').focus();
                }, 100);
            }
            
            // Manejar selección de ubicación
            $container.off('location-selected').on('location-selected', function(e, location) {
                // Agregar mensaje del usuario
                self.addMessage('user', location);
                
                // Ocultar buscador
                $container.hide().empty();
                
                // Enviar al servidor
                self.sendMessage(location, self.state.currentStep, self.state.conversationData);
            });
        },

        // Mostrar buscador de horarios
        showTimeSearcher: function(placeholder) {
            const self = this;

            this.hideTextInput();
            this.hideOptions();
            $('#met-chatbot-location-container').hide().empty();

            const $container = $('#met-chatbot-time-container');
            $container.empty();

            const searcherHtml = window.MetTimeSearcher.createSearcher(
                placeholder || 'Buscar horario (ej: 14:30)',
                'met-time-input-chat'
            );
            $container.html(searcherHtml);

            window.MetTimeSearcher.loadTimeSlots(function() {
                $container.show();
                setTimeout(function() {
                    $('#met-time-input-chat').focus();
                }, 100);
            });

            $container.off('time-selected').on('time-selected', function(e, time) {
                // Convert to 12h format for display
                const displayTime = window.MetTimeSearcher.convertTo12Hour ? 
                    window.MetTimeSearcher.convertTo12Hour(time) : time;
                
                self.addMessage('user', displayTime);
                $container.hide().empty();
                self.sendMessage(time, self.state.currentStep, self.state.conversationData);
            });
        },
        
        // Mostrar formulario de extras
        showExtrasForm: function(extrasConfig) {
            const self = this;
            
            // Ocultar otros inputs
            this.hideTextInput();
            this.hideOptions();
            
            // Crear el formulario
            const formHtml = window.MetExtrasSelector.createForm(extrasConfig);
            
            // Agregar como mensaje del bot
            const messagesContainer = $('#met-chatbot-messages');
            const messageHtml = `
                <div class="met-message bot met-extras-message">
                    <div class="met-message-avatar">
                        <i class="fas fa-robot"></i>
                    </div>
                    <div class="met-message-content">
                        ${formHtml}
                    </div>
                </div>
            `;
            
            messagesContainer.append(messageHtml);
            this.scrollToBottom();
            
            // Manejar confirmación de extras
            $(document).off('extras-selected').on('extras-selected', function(e, extrasData) {
                // Agregar resumen visual de lo seleccionado
                const summary = self.buildExtrasSummary(JSON.parse(extrasData), extrasConfig);
                self.addMessage('user', summary);
                
                // Enviar al servidor
                self.sendMessage(extrasData, self.state.currentStep, self.state.conversationData);
            });
        },
        
        // Construir resumen visual de extras seleccionados
        buildExtrasSummary: function(extrasData, extrasConfig) {
            const t = window.MetTranslations && typeof window.MetTranslations.t === 'function'
                ? window.MetTranslations.t.bind(window.MetTranslations)
                : (key) => key;
            let summary = `<strong>${t('extras_selected_summary') || 'Opciones extras seleccionadas'}</strong><br>`;
            let hasSelection = false;
            
            Object.keys(extrasData).forEach(key => {
                const quantity = extrasData[key];
                if (quantity > 0 && extrasConfig[key]) {
                    const config = extrasConfig[key];
                    summary += `${config.icon} ${config.label}: ${quantity}`;
                    if (config.price > 0) {
                        summary += ` (€${(quantity * config.price).toFixed(2)})`;
                    }
                    summary += '<br>';
                    hasSelection = true;
                }
            });
            
            if (!hasSelection) {
                summary = t('extras_none') || 'Sin opciones extras';
            }
            
            return summary;
        },
        
        // Scroll al final
        scrollToBottom: function() {
            const messagesContainer = $('#met-chatbot-messages');
            messagesContainer.animate({
                scrollTop: messagesContainer[0].scrollHeight
            }, 300);
        },
        
        // Mostrar diálogo de invitación
        showInvitation: function() {
            const self = this;
            
            // Mostrar después de 3 segundos
            setTimeout(function() {
                if (!self.state.isOpen) {
                    $('#met-chatbot-invitation').show();
                    
                    // Auto-ocultar después de 10 segundos
                    setTimeout(function() {
                        self.hideInvitation();
                    }, 10000);
                }
            }, 3000);
        },
        
        // Ocultar diálogo de invitación
        hideInvitation: function() {
            const invitation = $('#met-chatbot-invitation');
            
            if (!invitation.is(':visible')) return;
            
            // Agregar clase de animación de salida
            invitation.addClass('hiding');
            
            // Ocultar después de la animación
            setTimeout(function() {
                invitation.hide();
                invitation.removeClass('hiding');
            }, 300);
        },
        
        // Cambiar idioma
        changeLanguage: function(lang) {
            if (window.MetTranslations && window.MetTranslations.setLanguage(lang)) {
                // Actualizar botones activos
                $('.met-lang-btn').removeClass('active');
                $('.met-lang-btn[data-lang="' + lang + '"]').addClass('active');
                
                // Guardar idioma en datos de conversación
                this.state.conversationData.language = lang;
                
                // Actualizar UI inmediatamente
                this.updateUILanguage();
                
                // Limpiar mensajes y reiniciar conversación
                $('#met-chatbot-messages').empty();
                this.state.messages = [];
                this.state.currentStep = 'welcome';
                
                // Reiniciar conversación en el nuevo idioma
                this.startConversation();
            }
        },
        
        // Actualizar textos de la UI según idioma
        updateUILanguage: function() {
            if (!window.MetTranslations) return;
            
            const t = window.MetTranslations.t.bind(window.MetTranslations);
            const lang = this.state.conversationData.language || 'es';
            const policyUrl = this.policyLinks[lang] || this.policyLinks.es;
            
            // Header
            $('#met-chatbot-title').text(t('assistant_title'));
            $('#met-chatbot-status-text').text(t('status_online'));
            
            // Invitación
            $('#met-invitation-text').text(t('invitation_text'));
            
            // Footer
            $('#met-footer-privacy-text').text(t('footer_privacy'));
            $('#met-footer-privacy-link')
                .text(t('footer_privacy_link'))
                .attr('href', policyUrl);
            
            // Placeholder del input (si está visible)
            const $input = $('#met-chatbot-input');
            if ($input.is(':visible')) {
                const currentPlaceholder = $input.attr('placeholder');
                // Actualizar solo si es un placeholder conocido
                if (currentPlaceholder) {
                    $input.attr('placeholder', t('placeholder_message'));
                }
            }
        }
    };
    
    // Inicializar cuando el documento esté listo
    $(document).ready(function() {
        MetChatbot.init();
        $('#met-chatbot-toggle .met-chatbot-icon').show();
        $('#met-chatbot-toggle .met-chatbot-close').hide();
    });
    
})(jQuery);
