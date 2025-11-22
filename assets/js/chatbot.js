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
            conversationData: {},
            messages: []
        },
        
        // Inicializar
        init: function() {
            this.bindEvents();
            this.startConversation();
            this.showInvitation();
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
            
            // Auto-ajustar altura del textarea
            $('#met-chatbot-input').on('input', function() {
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
        },
        
        // Toggle chatbot
        toggleChatbot: function() {
            this.state.isOpen = !this.state.isOpen;
            $('#met-chatbot-widget').toggleClass('open', this.state.isOpen);
            
            if (this.state.isOpen && this.state.messages.length === 0) {
                this.startConversation();
            }
        },
        
        // Iniciar conversación
        startConversation: function() {
            this.sendMessage('', 'welcome', {});
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
            
            if (!message) return;
            
            // Agregar mensaje del usuario
            this.addMessage('user', message);
            
            // Limpiar textarea y resetear altura
            input.val('');
            input.css('height', 'auto');
            
            // Enviar al servidor
            this.sendMessage(message, this.state.currentStep, this.state.conversationData);
        },
        
        // Enviar mensaje al servidor
        sendMessage: function(message, step, data) {
            const self = this;
            
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
                    data: JSON.stringify(data)
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
            this.state.conversationData = data.data || {};
            
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
            
            // Configurar placeholder según tipo
            if (type === 'number') {
                input.attr('placeholder', 'Escribe un número...');
            } else if (type === 'email') {
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
            let summary = '<strong>Opciones extras seleccionadas:</strong><br>';
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
                summary = 'Sin opciones extras';
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
        }
    };
    
    // Inicializar cuando el documento esté listo
    $(document).ready(function() {
        MetChatbot.init();
    });
    
})(jQuery);
