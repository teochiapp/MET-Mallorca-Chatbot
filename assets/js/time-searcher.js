/**
 * Time Searcher Component
 * Selector de horarios en intervalos de 30 minutos
 */
(function($) {
    'use strict';

    const TimeSearcher = {
        timeSlots: [],
        config: {
            maxResults: 12,
            debounceTime: 150
        },
        searchTimer: null,

        init: function() {
            this.bindEvents();
        },

        /**
         * Convert 24-hour format to 12-hour AM/PM format
         * @param {string} time24 - Time in HH:MM format
         * @returns {string} Time in h:MM AM/PM format
         */
        convertTo12Hour: function(time24) {
            const [hourStr, minute] = time24.split(':');
            let hour = parseInt(hourStr, 10);
            const ampm = hour >= 12 ? 'PM' : 'AM';
            
            // Convert hour to 12-hour format
            if (hour === 0) {
                hour = 12; // Midnight
            } else if (hour > 12) {
                hour = hour - 12;
            }
            
            return `${hour}:${minute} ${ampm}`;
        },

        /**
         * Convert 12-hour AM/PM format back to 24-hour format
         * @param {string} time12 - Time in h:MM AM/PM format
         * @returns {string} Time in HH:MM format
         */
        convertTo24Hour: function(time12) {
            const match = time12.match(/^(\d{1,2}):(\d{2})\s*(AM|PM)$/i);
            if (!match) return time12;
            
            let hour = parseInt(match[1], 10);
            const minute = match[2];
            const ampm = match[3].toUpperCase();
            
            if (ampm === 'PM' && hour !== 12) {
                hour += 12;
            } else if (ampm === 'AM' && hour === 12) {
                hour = 0;
            }
            
            return `${hour.toString().padStart(2, '0')}:${minute}`;
        },

        createSearcher: function(placeholder, inputId) {
            const html = `
                <div class="met-time-searcher">
                    <div class="met-time-input-wrapper">
                        <input
                            type="text"
                            id="${inputId || 'met-time-input'}"
                            class="met-time-input"
                            placeholder="${placeholder || 'Buscar horario...'}"
                            autocomplete="off"
                        />
                        <i class="fas fa-clock met-time-search-icon"></i>
                    </div>
                    <div class="met-time-dropdown" style="display:none;">
                        <div class="met-time-results"></div>
                    </div>
                    <div class="met-time-selected" style="display:none;">
                        <span class="met-time-selected-text"></span>
                        <button class="met-time-clear" type="button">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            `;
            return html;
        },

        bindEvents: function() {
            const self = this;

            $(document).on('input', '.met-time-input', function() {
                const $input = $(this);
                const query = $input.val().trim();

                clearTimeout(self.searchTimer);
                self.searchTimer = setTimeout(function() {
                    self.search(query, $input);
                }, self.config.debounceTime);
            });

            $(document).on('focus', '.met-time-input', function() {
                const $input = $(this);
                self.search($input.val().trim(), $input);
            });

            $(document).on('click', '.met-time-result-item', function() {
                const value = $(this).data('time');
                const $searcher = $(this).closest('.met-time-searcher');
                self.selectTime(value, $searcher);
            });

            $(document).on('click', '.met-time-clear', function() {
                const $searcher = $(this).closest('.met-time-searcher');
                self.clearSelection($searcher);
            });

            $(document).on('click', function(event) {
                if (!$(event.target).closest('.met-time-searcher').length) {
                    $('.met-time-dropdown').hide();
                }
            });

            $(document).on('keydown', '.met-time-input', function(e) {
                const $dropdown = $(this).closest('.met-time-searcher').find('.met-time-dropdown');
                if (!$dropdown.is(':visible')) return;

                const $items = $dropdown.find('.met-time-result-item');
                const $active = $items.filter('.active');

                if (e.key === 'ArrowDown') {
                    e.preventDefault();
                    if ($active.length === 0) {
                        $items.first().addClass('active');
                    } else {
                        const $next = $active.removeClass('active').next('.met-time-result-item');
                        ($next.length ? $next : $items.first()).addClass('active');
                    }
                } else if (e.key === 'ArrowUp') {
                    e.preventDefault();
                    if ($active.length === 0) {
                        $items.last().addClass('active');
                    } else {
                        const $prev = $active.removeClass('active').prev('.met-time-result-item');
                        ($prev.length ? $prev : $items.last()).addClass('active');
                    }
                } else if (e.key === 'Enter' && $active.length) {
                    e.preventDefault();
                    $active.click();
                } else if (e.key === 'Escape') {
                    $dropdown.hide();
                }
            });
        },

        search: function(query, $input) {
            const results = this.filterSlots(query);
            this.showResults(results, $input);
        },

        filterSlots: function(query) {
            const self = this;
            const normalized = query.toLowerCase().trim();
            
            // Normalize search query by removing spaces between number and AM/PM
            // e.g., "2 am" or "2am" both become "2am"
            const normalizedQuery = normalized.replace(/\s+/g, '');
            
            // Filter by matching either 24h or 12h format
            const matches = this.timeSlots.filter(function(slot) {
                const time12 = self.convertTo12Hour(slot).toLowerCase();
                const slotLower = slot.toLowerCase();
                
                // Remove spaces from slot for comparison
                const time12NoSpace = time12.replace(/\s+/g, '');
                const slotNoSpace = slotLower.replace(/\s+/g, '');
                
                // Check various patterns:
                // 1. Direct match with original formats
                // 2. Match without spaces (e.g., "2am" matches "2:00 AM")
                // 3. Partial match (e.g., "2" matches "2:00 AM" and "2:30 AM")
                return slotLower.indexOf(normalized) !== -1 || 
                       time12.indexOf(normalized) !== -1 ||
                       slotNoSpace.indexOf(normalizedQuery) !== -1 ||
                       time12NoSpace.indexOf(normalizedQuery) !== -1;
            });

            if (matches.length === 0 && normalized.length === 0) {
                return this.timeSlots.slice(0, this.config.maxResults);
            }

            return matches.slice(0, this.config.maxResults);
        },

        showResults: function(results, $input) {
            const self = this;
            const $searcher = $input.closest('.met-time-searcher');
            const $dropdown = $searcher.find('.met-time-dropdown');
            const $resultsContainer = $dropdown.find('.met-time-results');

            $resultsContainer.empty();

            if (results.length === 0) {
                $resultsContainer.html('<div class="met-time-no-results">Sin horarios disponibles</div>');
            } else {
                results.forEach(function(slot) {
                    // Check if already in 12h format (contains AM or PM)
                    const isAlready12h = /AM|PM/i.test(slot);
                    const displayTime = isAlready12h ? slot : self.convertTo12Hour(slot);
                    const timeValue = isAlready12h ? self.convertTo24Hour(slot) : slot;
                    
                    const $item = $('<div class="met-time-result-item"></div>')
                        .text(displayTime)
                        .data('time', timeValue)
                        .data('display-time', displayTime);
                    $resultsContainer.append($item);
                });
            }

            $dropdown.show();
        },

        selectTime: function(time, $searcher) {
            const $input = $searcher.find('.met-time-input');
            const $dropdown = $searcher.find('.met-time-dropdown');
            const $selected = $searcher.find('.met-time-selected');
            const $selectedText = $selected.find('.met-time-selected-text');

            // Store 24-hour format for backend compatibility
            $searcher.data('selected-time', time);
            
            // Display 12-hour format to user
            const displayTime = this.convertTo12Hour(time);
            $input.val('').hide();
            $selectedText.text(displayTime);
            $selected.show();
            $dropdown.hide();

            $searcher.trigger('time-selected', [time]);
        },

        clearSelection: function($searcher) {
            const $input = $searcher.find('.met-time-input');
            const $selected = $searcher.find('.met-time-selected');

            $searcher.removeData('selected-time');
            $input.val('').show().focus();
            $selected.hide();

            $searcher.trigger('time-cleared');
        },

        loadTimeSlots: function(callback) {
            const self = this;

            if (self.timeSlots.length > 0) {
                if (callback) callback(self.timeSlots);
                return;
            }

            if (typeof metChatbot === 'undefined' || !metChatbot.ajaxUrl) {
                if (callback) callback(self.timeSlots);
                return;
            }

            $.ajax({
                url: metChatbot.ajaxUrl,
                method: 'POST',
                data: {
                    action: 'met_get_time_slots',
                    nonce: metChatbot.nonce
                },
                dataType: 'json'
            }).done(function(response) {
                if (response && response.success && Array.isArray(response.data.time_slots)) {
                    self.timeSlots = response.data.time_slots;
                } else {
                    self.generateFallbackSlots();
                }
            }).fail(function() {
                self.generateFallbackSlots();
            }).always(function() {
                if (callback) callback(self.timeSlots);
            });
        },

        generateFallbackSlots: function() {
            if (this.timeSlots.length > 0) return;
            for (let hour = 0; hour < 24; hour++) {
                ['00', '30'].forEach(min => {
                    // Store in 24-hour format for backend compatibility
                    this.timeSlots.push(`${hour.toString().padStart(2, '0')}:${min}`);
                });
            }
        }
    };

    window.MetTimeSearcher = TimeSearcher;

    $(document).ready(function() {
        TimeSearcher.init();
        TimeSearcher.loadTimeSlots();
    });
})(jQuery);
