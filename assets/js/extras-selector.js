/**
 * Extras Selector Component
 * Componente para seleccionar opciones extras con contadores numéricos
 */

(function($) {
    'use strict';
    
    const ExtrasSelector = {
        // Configuración de extras
        extrasConfig: {},
        
        // Estado actual
        currentValues: {},
        
        /**
         * Crear el formulario de extras
         */
        createForm: function(config) {
            this.extrasConfig = config || {};
            this.currentValues = {};
            
            // Inicializar valores en 0
            Object.keys(this.extrasConfig).forEach(key => {
                this.currentValues[key] = 0;
            });
            
            let html = '<div class="met-extras-form">';
            
            // Crear selector para cada extra
            Object.keys(this.extrasConfig).forEach(key => {
                const extra = this.extrasConfig[key];
                html += this.createExtraSelector(key, extra);
            });
            
            // Total dinámico
            html += '<div class="met-extras-total">';
            html += '<strong>💰 Total extras: <span id="met-extras-total-amount">€0.00</span></strong>';
            html += '</div>';
            
            // Botón confirmar
            html += '<div class="met-extras-actions">';
            html += '<button type="button" class="met-extras-confirm" id="met-extras-confirm-btn">';
            html += '<i class="fas fa-check"></i> Confirmar opciones';
            html += '</button>';
            html += '</div>';
            
            html += '</div>';
            
            return html;
        },
        
        /**
         * Crear selector individual
         */
        createExtraSelector: function(key, extra) {
            const priceText = extra.price > 0 ? extra.info : '<span class="met-extras-free">Gratis</span>';
            
            return `
                <div class="met-extra-item" data-extra-key="${key}">
                    <div class="met-extra-header">
                        <span class="met-extra-icon">${extra.icon}</span>
                        <span class="met-extra-label">${extra.label}</span>
                        <span class="met-extra-price">${priceText}</span>
                    </div>
                    <div class="met-extra-counter">
                        <button type="button" class="met-counter-btn met-counter-minus" data-action="minus" data-key="${key}">
                            <i class="fas fa-minus"></i>
                        </button>
                        <input 
                            type="number" 
                            class="met-counter-input" 
                            id="met-extra-${key}" 
                            value="0" 
                            min="0" 
                            max="20"
                            data-key="${key}"
                            readonly
                        />
                        <button type="button" class="met-counter-btn met-counter-plus" data-action="plus" data-key="${key}">
                            <i class="fas fa-plus"></i>
                        </button>
                    </div>
                </div>
            `;
        },
        
        /**
         * Inicializar eventos
         */
        bindEvents: function() {
            const self = this;
            
            // Botones + y -
            $(document).on('click', '.met-counter-btn', function() {
                const action = $(this).data('action');
                const key = $(this).data('key');
                
                self.updateCounter(key, action);
            });
            
            // Input directo (aunque está readonly, por si se cambia)
            $(document).on('change', '.met-counter-input', function() {
                const key = $(this).data('key');
                const value = parseInt($(this).val()) || 0;
                
                self.currentValues[key] = Math.max(0, Math.min(20, value));
                self.updateDisplay(key);
                self.updateTotal();
            });
            
            // Confirmar selección
            $(document).on('click', '#met-extras-confirm-btn', function() {
                self.confirmSelection();
            });
        },
        
        /**
         * Actualizar contador
         */
        updateCounter: function(key, action) {
            const currentValue = this.currentValues[key] || 0;
            
            if (action === 'plus') {
                this.currentValues[key] = Math.min(20, currentValue + 1);
            } else if (action === 'minus') {
                this.currentValues[key] = Math.max(0, currentValue - 1);
            }
            
            this.updateDisplay(key);
            this.updateTotal();
        },
        
        /**
         * Actualizar visualización
         */
        updateDisplay: function(key) {
            const value = this.currentValues[key];
            $('#met-extra-' + key).val(value);
        },
        
        /**
         * Calcular y actualizar total
         */
        updateTotal: function() {
            let total = 0;
            
            Object.keys(this.currentValues).forEach(key => {
                const quantity = this.currentValues[key];
                const price = this.extrasConfig[key]?.price || 0;
                total += quantity * price;
            });
            
            $('#met-extras-total-amount').text('€' + total.toFixed(2));
        },
        
        /**
         * Confirmar selección
         */
        confirmSelection: function() {
            // Crear objeto con los datos
            const extrasData = {};
            
            Object.keys(this.currentValues).forEach(key => {
                extrasData[key] = this.currentValues[key];
            });
            
            // Trigger evento con los datos
            $(document).trigger('extras-selected', [JSON.stringify(extrasData)]);
        },
        
        /**
         * Obtener valores actuales
         */
        getValues: function() {
            return this.currentValues;
        }
    };
    
    // Exponer globalmente
    window.MetExtrasSelector = ExtrasSelector;
    
    // Auto-inicializar eventos cuando el documento esté listo
    $(document).ready(function() {
        ExtrasSelector.bindEvents();
    });
    
})(jQuery);
