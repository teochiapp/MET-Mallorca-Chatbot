<?php
/**
 * Template del widget del chatbot
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<!-- Widget del Chatbot -->
<div id="met-chatbot-widget" class="met-chatbot-widget">
    <!-- Diálogo flotante de invitación -->
    <div id="met-chatbot-invitation" class="met-chatbot-invitation" style="display: none;">
        <div class="met-chatbot-invitation-content">
            <div class="met-chatbot-invitation-text" id="met-invitation-text">
                ¿Te ayudo con tu reserva?
            </div>
            <div class="met-chatbot-invitation-close">
                <i class="fas fa-times"></i>
            </div>
        </div>
        <div class="met-chatbot-invitation-arrow"></div>
    </div>
    
    <!-- Botón flotante -->
    <button id="met-chatbot-toggle" class="met-chatbot-toggle" aria-label="Abrir chat">
        <i class="fas fa-comments met-chatbot-icon"></i>
        <i class="fas fa-times met-chatbot-close"></i>
    </button>
    
    <!-- Ventana del chat -->
    <div id="met-chatbot-window" class="met-chatbot-window">
        <!-- Header -->
        <div class="met-chatbot-header">
            <div class="met-chatbot-header-top">
                <div class="met-chatbot-header-content">
                    <div class="met-chatbot-avatar">
                        <i class="fas fa-robot"></i>
                    </div>
                    <div class="met-chatbot-header-text">
                        <h3 id="met-chatbot-title">Asistente MET Mallorca</h3>
                        <span class="met-chatbot-status"><i class="fas fa-circle"></i> <span id="met-chatbot-status-text">En línea</span></span>
                    </div>
                </div>
                <button id="met-chatbot-minimize" class="met-chatbot-minimize" aria-label="Minimizar chat">
                    <i class="fas fa-chevron-down"></i>
                </button>
            </div>
            <!-- Selector de idioma en fila separada -->
            <div class="met-language-selector-wrapper">
                <div class="met-language-selector">
                    <button class="met-lang-btn active" data-lang="es" title="Español">
                        <span class="met-flag">🇪🇸</span>
                        <span class="met-lang-label">ES</span>
                    </button>
                    <button class="met-lang-btn" data-lang="en" title="English">
                        <span class="met-flag">🇬🇧</span>
                        <span class="met-lang-label">EN</span>
                    </button>
                    <button class="met-lang-btn" data-lang="de" title="Deutsch">
                        <span class="met-flag">🇩🇪</span>
                        <span class="met-lang-label">DE</span>
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Mensajes -->
        <div id="met-chatbot-messages" class="met-chatbot-messages">
            <!-- Los mensajes se cargarán aquí dinámicamente -->
        </div>
        
        <!-- Input area -->
        <div id="met-chatbot-input-area" class="met-chatbot-input-area">
            <div id="met-chatbot-options" class="met-chatbot-options">
                <!-- Los botones de opciones se cargarán aquí -->
            </div>
            
            <!-- Location Searcher Container -->
            <div id="met-chatbot-location-container" class="met-chatbot-location-wrapper" style="display: none;">
                <!-- El buscador de ubicaciones se cargará aquí -->
            </div>

            <!-- Time Searcher Container -->
            <div id="met-chatbot-time-container" class="met-chatbot-time-wrapper" style="display: none;">
                <!-- El buscador de horarios se cargará aquí -->
            </div>
            
            <div id="met-chatbot-text-input" class="met-chatbot-text-input" style="display: none;">
                <textarea id="met-chatbot-input" class="met-chatbot-input" placeholder="Escribe tu mensaje..." rows="2"></textarea>
                <button id="met-chatbot-send" class="met-chatbot-send" aria-label="Enviar mensaje">
                    <i class="fas fa-paper-plane"></i>
                </button>
            </div>
        </div>
        
        <!-- Footer con RGPD -->
        <div class="met-chatbot-footer">
            <small><span id="met-footer-privacy-text">Al continuar aceptas la</span> <a href="<?php echo home_url('/politica-de-privacidad/'); ?>" target="_blank" id="met-footer-privacy-link">Política de Privacidad</a></small>
        </div>
    </div>
</div>
