/**
 * Location Searcher Component
 * Componente de búsqueda de ubicaciones con dropdown
 */

(function($) {
    'use strict';
    
    const LocationSearcher = {
        // Todas las ubicaciones disponibles
        locations: [],
        
        // Local street database
        streetDatabase: null,
        
        // Configuración
        config: {
            minChars: 2,
            maxResults: 10,
            debounceTime: 300
        },
        
        // Timer para debounce
        searchTimer: null,
        
        /**
         * Crear variantes de búsqueda para términos equivalentes
         * Genera todas las posibles variantes (Puerto <-> Pto)
         */
        createSearchVariants: function(term) {
            if (!term) return [term];
            
            const variants = [];
            const lower = term.toLowerCase();
            
            // Agregar el término original en minúsculas
            variants.push(lower);
            
            // Si contiene "pto" (con punto o sin punto), agregar variante con "puerto"
            if (/\bpto\.?\b/i.test(lower)) {
                variants.push(lower.replace(/\bpto\.?\b/g, 'puerto'));
            }
            
            // Si contiene "puerto", agregar variante con "pto"
            if (/\bpuerto\b/i.test(lower)) {
                variants.push(lower.replace(/\bpuerto\b/g, 'pto'));
            }
            
            return variants;
        },
        
        /**
         * Inicializar el buscador
         */
        init: function(locations) {
            this.locations = locations || [];
            this.bindEvents();
            this.loadStreetDatabase();
        },
        
        /**
         * Cargar base de datos local de calles
         */
        loadStreetDatabase: function() {
            const self = this;
            const dbPath = metChatbot.pluginUrl + '/mallorca_streets.json';
            
            $.ajax({
                url: dbPath,
                dataType: 'json',
                cache: true,
                success: function(data) {
                    self.streetDatabase = data;
                    console.log('Street database loaded:', Object.keys(data).length, 'locations');
                },
                error: function() {
                    console.warn('Could not load street database');
                    self.streetDatabase = {};
                }
            });
        },
        
        /**
         * Crear el HTML del buscador
         */
        createSearcher: function(placeholder, inputId) {
            const html = `
                <div class="met-location-searcher">
                    <div class="met-location-input-wrapper">
                        <input 
                            type="text" 
                            id="${inputId || 'met-location-input'}"
                            class="met-location-input" 
                            placeholder="${placeholder || 'Buscar ubicación...'}"
                            autocomplete="off"
                        />
                        <i class="fas fa-search met-location-search-icon"></i>
                    </div>
                    <div class="met-location-dropdown" style="display: none;">
                        <div class="met-location-results"></div>
                    </div>
                    <div class="met-location-selected" style="display: none;">
                        <span class="met-location-selected-text"></span>
                        <button class="met-location-clear" type="button">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            `;
            return html;
        },
        
        /**
         * Vincular eventos
         */
        bindEvents: function() {
            const self = this;
            
            // Evento de input con debounce
            $(document).on('input', '.met-location-input', function() {
                const $input = $(this);
                const query = $input.val().trim();
                
                clearTimeout(self.searchTimer);
                
                if (query.length >= self.config.minChars) {
                    self.searchTimer = setTimeout(function() {
                        self.search(query, $input);
                    }, self.config.debounceTime);
                } else {
                    self.hideDropdown($input);
                }
            });
            
            // Focus en input
            $(document).on('focus', '.met-location-input', function() {
                const $input = $(this);
                const query = $input.val().trim();
                
                if (query.length >= self.config.minChars) {
                    self.search(query, $input);
                }
            });
            
            // Click en resultado
            $(document).on('click', '.met-location-result-item', function() {
                const location = $(this).data('location');
                const $searcher = $(this).closest('.met-location-searcher');
                const $input = $searcher.find('.met-location-input');
                
                self.selectLocation(location, $searcher);
            });
            
            // Limpiar selección
            $(document).on('click', '.met-location-clear', function() {
                const $searcher = $(this).closest('.met-location-searcher');
                self.clearSelection($searcher);
            });
            
            // Cerrar dropdown al hacer click fuera
            $(document).on('click', function(e) {
                if (!$(e.target).closest('.met-location-searcher').length) {
                    $('.met-location-dropdown').hide();
                }
            });
            
            // Navegación con teclado
            $(document).on('keydown', '.met-location-input', function(e) {
                const $dropdown = $(this).closest('.met-location-searcher').find('.met-location-dropdown');
                
                if (!$dropdown.is(':visible')) return;
                
                const $items = $dropdown.find('.met-location-result-item');
                const $active = $items.filter('.active');
                
                if (e.key === 'ArrowDown') {
                    e.preventDefault();
                    if ($active.length === 0) {
                        $items.first().addClass('active');
                    } else {
                        $active.removeClass('active');
                        const $next = $active.next('.met-location-result-item');
                        if ($next.length) {
                            $next.addClass('active');
                        } else {
                            $items.first().addClass('active');
                        }
                    }
                } else if (e.key === 'ArrowUp') {
                    e.preventDefault();
                    if ($active.length === 0) {
                        $items.last().addClass('active');
                    } else {
                        $active.removeClass('active');
                        const $prev = $active.prev('.met-location-result-item');
                        if ($prev.length) {
                            $prev.addClass('active');
                        } else {
                            $items.last().addClass('active');
                        }
                    }
                } else if (e.key === 'Enter') {
                    e.preventDefault();
                    if ($active.length) {
                        $active.click();
                    }
                } else if (e.key === 'Escape') {
                    $dropdown.hide();
                }
            });
        },
        
        /**
         * Buscar ubicaciones
         */
        search: function(query, $input) {
            const self = this;
            const results = this.filterLocations(query);
            
            // If no results, try local street database first (fastest)
            if (results.length === 0 && this.streetDatabase) {
                const localResult = this.searchInLocalDatabase(query);
                if (localResult) {
                    this.showGeocodedResult(localResult, query, $input);
                    return;
                }
            }
            
            // If still no results and query is long enough, try geocoding (reduced to 3 chars)
            if (results.length === 0 && query.length >= 3) {
                this.showLoadingMessage($input);
                this.geocodeAddress(query).then(function(district) {
                    if (district) {
                        self.showGeocodedResult(district, query, $input);
                    } else {
                        self.showNoResults($input);
                    }
                }).catch(function() {
                    self.showResults(results, $input);
                });
            } else {
                this.showResults(results, $input);
            }
        },
        
        /**
         * Buscar en la base de datos local de calles
         */
        searchInLocalDatabase: function(query) {
            if (!this.streetDatabase) return null;
            
            const queryVariants = this.createSearchVariants(query);
            
            // Buscar en cada distrito
            for (const [location, data] of Object.entries(this.streetDatabase)) {
                const streets = data.streets || [];
                
                // Buscar coincidencia en calles
                for (const street of streets) {
                    const streetVariants = this.createSearchVariants(street);
                    
                    // Comprobar si alguna variante coincide
                    for (const qv of queryVariants) {
                        for (const sv of streetVariants) {
                            if (sv.includes(qv) || qv.includes(sv)) {
                                console.log('✓ Found in local DB:', street, '→', data.district);
                                return data.district;
                            }
                        }
                    }
                }
            }
            
            return null;
        },
        
        /**
         * Filtrar ubicaciones
         */
        filterLocations: function(query) {
            const queryVariants = this.createSearchVariants(query);
            const results = [];
            
            for (let i = 0; i < this.locations.length && results.length < this.config.maxResults; i++) {
                const location = this.locations[i];
                const locationVariants = this.createSearchVariants(location);
                
                // Comprobar si alguna variante de query coincide con alguna variante de location
                let found = false;
                for (const qv of queryVariants) {
                    for (const lv of locationVariants) {
                        if (lv.indexOf(qv) !== -1) {
                            found = true;
                            break;
                        }
                    }
                    if (found) break;
                }
                
                if (found) {
                    results.push(location);
                }
            }
            
            return results;
        },
        
        /**
         * Mostrar resultados
         */
        showResults: function(results, $input) {
            const $searcher = $input.closest('.met-location-searcher');
            const $dropdown = $searcher.find('.met-location-dropdown');
            const $resultsContainer = $dropdown.find('.met-location-results');
            
            $resultsContainer.empty();
            
            if (results.length === 0) {
                $resultsContainer.html('<div class="met-location-no-results">No se encontraron ubicaciones</div>');
            } else {
                results.forEach(function(location) {
                    const $item = $('<div class="met-location-result-item"></div>')
                        .text(location)
                        .data('location', location);
                    $resultsContainer.append($item);
                });
            }
            
            $dropdown.show();
        },
        
        /**
         * Ocultar dropdown
         */
        hideDropdown: function($input) {
            const $searcher = $input.closest('.met-location-searcher');
            $searcher.find('.met-location-dropdown').hide();
        },
        
        /**
         * Seleccionar ubicación
         */
        selectLocation: function(location, $searcher) {
            const $input = $searcher.find('.met-location-input');
            const $dropdown = $searcher.find('.met-location-dropdown');
            const $selected = $searcher.find('.met-location-selected');
            const $selectedText = $selected.find('.met-location-selected-text');
            
            // Guardar la ubicación seleccionada
            $searcher.data('selected-location', location);
            
            // Actualizar UI
            $input.val('').hide();
            $selectedText.text(location);
            $selected.show();
            $dropdown.hide();
            
            // Trigger evento personalizado
            $searcher.trigger('location-selected', [location]);
        },
        
        /**
         * Limpiar selección
         */
        clearSelection: function($searcher) {
            const $input = $searcher.find('.met-location-input');
            const $selected = $searcher.find('.met-location-selected');
            
            // Limpiar datos
            $searcher.removeData('selected-location');
            
            // Actualizar UI
            $input.val('').show().focus();
            $selected.hide();
            
            // Trigger evento personalizado
            $searcher.trigger('location-cleared');
        },
        
        /**
         * Obtener ubicación seleccionada
         */
        getSelectedLocation: function($searcher) {
            return $searcher.data('selected-location') || null;
        },
        
        /**
         * Cargar ubicaciones desde el servidor
         */
        loadLocations: function(callback) {
            const self = this;
            console.log('MetLocationSearcher: Iniciando carga de ubicaciones...');

            if (typeof metChatbot === 'undefined' || !metChatbot.ajaxUrl) {
                const errorMsg = 'MetLocationSearcher: metChatbot no está disponible. Verifica que el script se cargue correctamente.';
                console.error(errorMsg, {
                    metChatbotDefined: typeof metChatbot !== 'undefined',
                    ajaxUrl: metChatbot ? metChatbot.ajaxUrl : 'no definido',
                    nonce: metChatbot ? (metChatbot.nonce ? 'definido' : 'no definido') : 'no aplicable'
                });
                if (callback) callback(self.locations);
                return;
            }

            console.log('MetLocationSearcher: Solicitando ubicaciones a', metChatbot.ajaxUrl);
            
            $.ajax({
                url: metChatbot.ajaxUrl,
                method: 'POST',
                data: {
                    action: 'met_get_locations',
                    nonce: metChatbot.nonce
                },
                dataType: 'json',
                success: function(response) {
                    console.group('MetLocationSearcher: Respuesta del servidor');
                    console.log('Respuesta completa:', response);
                    
                    if (response && response.success) {
                        console.log('Estado: Éxito');
                        console.log('Datos recibidos:', response.data);
                        
                        if (response.data && Array.isArray(response.data.locations)) {
                            self.locations = response.data.locations;
                            console.log('Ubicaciones cargadas:', self.locations.length);
                            console.log('Primeras 5 ubicaciones:', self.locations.slice(0, 5));
                            
                            // Mostrar mensaje de depuración si existe
                            if (response.data.debug) {
                                console.log('Mensaje de depuración:', response.data.debug);
                            }
                            
                            if (self.locations.length === 0) {
                                console.warn('ADVERTENCIA: Se recibió un array de ubicaciones vacío');
                            }
                            
                            if (callback) callback(self.locations);
                        } else {
                            console.error('ERROR: La propiedad data.locations no es un array o no existe', {
                                data: response.data,
                                locationsIsArray: response.data ? Array.isArray(response.data.locations) : 'data es nulo'
                            });
                            if (callback) callback(self.locations);
                        }
                    } else {
                        console.error('ERROR: La respuesta no indica éxito', {
                            response: response,
                            success: response ? response.success : 'respuesta nula',
                            data: response ? response.data : 'no disponible'
                        });
                        if (callback) callback(self.locations);
                    }
                    console.groupEnd();
                },
                error: function(xhr, status, error) {
                    console.group('MetLocationSearcher: Error en la petición AJAX');
                    console.error('Estado:', status);
                    console.error('Error:', error);
                    console.error('Respuesta del servidor:', xhr.responseText);
                    console.error('Estado de la petición:', xhr.status, xhr.statusText);
                    console.groupEnd();
                    
                    if (callback) callback(self.locations);
                },
                complete: function(xhr, status) {
                    console.log('MetLocationSearcher: Petición completada con estado:', status);
                }
            });
        }
    };
    
    // Exponer globalmente
    window.MetLocationSearcher = LocationSearcher;
    
    // Auto-inicializar cuando el documento esté listo
    $(document).ready(function() {
        LocationSearcher.init([]);

        const preloadLocations = function(attempts) {
            if (typeof metChatbot !== 'undefined' && metChatbot.ajaxUrl) {
                LocationSearcher.loadLocations();
            } else if (attempts > 0) {
                setTimeout(function() {
                    preloadLocations(attempts - 1);
                }, 100);
            }
        };

        preloadLocations(30); // Reintentar durante ~3s
    });
    
    // Extend LocationSearcher with geocoding methods
    LocationSearcher.geocodeAddress = function(address) {
        return new Promise(function(resolve, reject) {
            $.ajax({
                url: metChatbot.ajaxUrl,
                method: 'POST',
                data: {
                    action: 'met_geocode_location',
                    nonce: metChatbot.nonce,
                    address: address
                },
                timeout: 5000
            }).done(function(response) {
                if (response.success && response.data.district) {
                    resolve(response.data.district);
                } else {
                    resolve(null);
                }
            }).fail(function() {
                resolve(null);
            });
        });
    };
    
    LocationSearcher.showLoadingMessage = function($input) {
        const $searcher = $input.closest('.met-location-searcher');
        const $dropdown = $searcher.find('.met-location-dropdown');
        const $resultsContainer = $dropdown.find('.met-location-results');
        
        $resultsContainer.html(
            '<div class="met-location-loading">' +
            '<i class="fas fa-spinner fa-spin"></i> Buscando dirección...' +
            '</div>'
        );
        $dropdown.show();
    };
    
    LocationSearcher.showGeocodedResult = function(district, originalQuery, $input) {
        const $searcher = $input.closest('.met-location-searcher');
        const $dropdown = $searcher.find('.met-location-dropdown');
        const $resultsContainer = $dropdown.find('.met-location-results');
        
        $resultsContainer.html(
            '<div class="met-location-geocoded">' +
            '<div class="met-location-result-item" data-location="' + district + '">' +
            '<i class="fas fa-map-marker-alt"></i> ' + district +
            '<div class="met-location-hint">📍 Desde: ' + originalQuery + '</div>' +
            '</div>' +
            '</div>'
        );
        $dropdown.show();
    };
    
    LocationSearcher.showNoResults = function($input) {
        const $searcher = $input.closest('.met-location-searcher');
        const $dropdown = $searcher.find('.met-location-dropdown');
        const $resultsContainer = $dropdown.find('.met-location-results');
        
        $resultsContainer.html(
            '<div class="met-location-no-results">' +
            'No se encontró esta dirección.<br>' +
            '<small style="margin-top:8px;display:block;">💡 <strong>Tip:</strong> Escribe solo el nombre del pueblo o ciudad<br>' +
            '(ej: Andratx, Alcudia, Pollença, Santa Ponsa)</small>' +
            '</div>'
        );
        $dropdown.show();
    };

})(jQuery);
