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
            
            // Verificar si debe mostrarse
            if (this.shouldShow()) {
                setTimeout(() => {
                    this.showPopup();
                }, this.config.showDelay);
            }
            
            // Bind events
            this.bindEvents();
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
            
            if (this.state.isVisible) {
                return;
            }
            
            this.state.isVisible = true;
            $popup.addClass('met-popup-visible');
        },
        
        // Ocultar popup
        hidePopup: function() {
            const $popup = $('#met-homepage-popup');
            
            if (!this.state.isVisible) {
                return;
            }
            
            this.state.isVisible = false;
            $popup.removeClass('met-popup-visible');
            
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
        }
    };
    
    // Inicializar cuando el DOM esté listo
    $(document).ready(function() {
        MetHomepagePopup.init();
    });
    
})(jQuery);
