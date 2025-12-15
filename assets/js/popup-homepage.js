/**
 * Homepage Popup para MET Chatbot
 * Muestra un popup invitando a usar el chatbot cada 1 hora
 */

(function($) {
    'use strict';
    
    const MetHomepagePopup = {
        // Configuración
        config: {
            storageKey: 'met_popup_last_shown',
            intervalMinutes: 60, // 1 hora
            showDelay: 3000 // 3 segundos después de cargar la página
        },
        
        // Estado
        state: {
            isVisible: false
        },
        
        // Inicializar
        init: function() {
            // Solo ejecutar si estamos en homepage y el popup existe
            if (!$('#met-homepage-popup').length) {
                return;
            }

            // Sincronizar idioma con el chatbot
            this.syncLanguageWithChatbot();
            
            // Verificar si debe mostrarse
            if (this.shouldShow()) {
                setTimeout(() => {
                    this.showPopup();
                }, this.config.showDelay);
            }
            
            // Bind events
            this.bindEvents();
        },

        // Sincronizar idioma con el chatbot
        syncLanguageWithChatbot: function() {
            // Obtener idioma guardado del chatbot
            if (window.MetTranslations && typeof window.MetTranslations.getLanguage === 'function') {
                const savedLang = window.MetTranslations.getLanguage();
                
                // Actualizar botones activos
                $('.met-popup-lang-btn').removeClass('active');
                $('.met-popup-lang-btn[data-lang="' + savedLang + '"]').addClass('active');
                
                // Actualizar textos del popup
                this.updatePopupLanguage(savedLang);
            }
        },
        
        // Verificar si debe mostrarse el popup
        shouldShow: function() {
            const lastShown = localStorage.getItem(this.config.storageKey);
            
            if (!lastShown) {
                return true;
            }
            
            const lastShownTime = parseInt(lastShown, 10);
            const currentTime = new Date().getTime();
            const minutesPassed = (currentTime - lastShownTime) / (1000 * 60);
            
            return minutesPassed >= this.config.intervalMinutes;
        },
        
        // Mostrar popup
        showPopup: function() {
            const $popup = $('#met-homepage-popup');
            const $backdrop = $('#met-popup-backdrop');
            
            if (this.state.isVisible) {
                return;
            }
            
            this.state.isVisible = true;
            $popup.addClass('met-popup-visible');
            $backdrop.addClass('met-backdrop-visible');
        },
        
        // Ocultar popup
        hidePopup: function() {
            const $popup = $('#met-homepage-popup');
            const $backdrop = $('#met-popup-backdrop');
            
            if (!this.state.isVisible) {
                return;
            }
            
            this.state.isVisible = false;
            $popup.removeClass('met-popup-visible');
            $backdrop.removeClass('met-backdrop-visible');
            
            // Guardar timestamp
            this.saveTimestamp();
        },
        
        // Guardar timestamp en localStorage
        saveTimestamp: function() {
            const currentTime = new Date().getTime();
            localStorage.setItem(this.config.storageKey, currentTime.toString());
        },
        
        // Abrir chatbot
        openChatbot: function() {
            // Ocultar popup primero
            this.hidePopup();
            
            // Abrir el chatbot
            if (!$('#met-chatbot-widget').hasClass('open')) {
                $('#met-chatbot-toggle').trigger('click');
            }
        },
        
        // Bind events
        bindEvents: function() {
            const self = this;
            
            // Botón de cierre
            $(document).on('click', '#met-popup-close', function(e) {
                e.preventDefault();
                self.hidePopup();
            });
            
            // Botón de acción principal
            $(document).on('click', '#met-popup-action', function(e) {
                e.preventDefault();
                self.openChatbot();
            });
            
            // Cerrar con tecla ESC
            $(document).on('keydown', function(e) {
                if (e.key === 'Escape' && self.state.isVisible) {
                    self.hidePopup();
                }
            });

            // Language selector
            $(document).on('click', '.met-popup-lang-btn', function() {
                const lang = $(this).data('lang');
                self.changeLanguage(lang);
            });

            // Escuchar cambios de idioma desde el chatbot
            $(document).on('met-language-changed', function(e, lang) {
                // Actualizar botones activos del popup
                $('.met-popup-lang-btn').removeClass('active');
                $('.met-popup-lang-btn[data-lang="' + lang + '"]').addClass('active');
                
                // Actualizar textos del popup
                self.updatePopupLanguage(lang);
            });
        },

        // Cambiar idioma
        changeLanguage: function(lang) {
            // Actualizar botones activos del popup
            $('.met-popup-lang-btn').removeClass('active');
            $('.met-popup-lang-btn[data-lang="' + lang + '"]').addClass('active');

            // Actualizar botones activos del chatbot
            $('.met-lang-btn').removeClass('active');
            $('.met-lang-btn[data-lang="' + lang + '"]').addClass('active');

            // Cambiar idioma en el sistema de traducciones
            if (window.MetTranslations && window.MetTranslations.setLanguage) {
                window.MetTranslations.setLanguage(lang);
            }

            // Actualizar textos del popup según idioma
            this.updatePopupLanguage(lang);

            // Reiniciar conversación del chatbot si está disponible
            if (window.MetChatbot && typeof window.MetChatbot === 'object') {
                // Actualizar idioma en datos de conversación
                if (window.MetChatbot.state && window.MetChatbot.state.conversationData) {
                    window.MetChatbot.state.conversationData.language = lang;
                }

                // Actualizar UI del chatbot
                if (typeof window.MetChatbot.updateUILanguage === 'function') {
                    window.MetChatbot.updateUILanguage();
                }

                // Limpiar mensajes y reiniciar conversación
                $('#met-chatbot-messages').empty();
                if (window.MetChatbot.state) {
                    window.MetChatbot.state.messages = [];
                    window.MetChatbot.state.currentStep = 'welcome';
                }

                // Reiniciar conversación si el chatbot está abierto
                if ($('#met-chatbot-widget').hasClass('open')) {
                    if (typeof window.MetChatbot.startConversation === 'function') {
                        window.MetChatbot.startConversation();
                    }
                }
            }
        },

        // Actualizar textos del popup según idioma
        updatePopupLanguage: function(lang) {
            const translations = {
                es: {
                    title_orange: '¿NECESITAS UN ',
                    title_white: 'TRASLADO?',
                    button_white: 'HABLA ',
                    button_blue: 'POR CHAT'
                },
                en: {
                    title_orange: 'NEED A ',
                    title_white: 'TRANSFER?',
                    button_white: 'CHAT ',
                    button_blue: 'WITH US'
                },
                de: {
                    title_orange: 'BENÖTIGEN SIE EINEN ',
                    title_white: 'TRANSFER?',
                    button_white: 'CHATTEN ',
                    button_blue: 'SIE MIT UNS'
                }
            };

            const t = translations[lang] || translations.es;

            // Actualizar título
            $('.met-popup-title-orange').text(t.title_orange);
            $('.met-popup-title-white').text(t.title_white);

            // Actualizar botón
            $('.met-popup-btn-white').text(t.button_white);
            $('.met-popup-btn-blue').text(t.button_blue);
        }
    };
    
    // Inicializar cuando el DOM esté listo
    $(document).ready(function() {
        MetHomepagePopup.init();
    });
    
})(jQuery);
