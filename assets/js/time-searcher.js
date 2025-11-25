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
            const normalized = query.toLowerCase();
            const matches = this.timeSlots.filter(function(slot) {
                return slot.toLowerCase().indexOf(normalized) !== -1;
            });

            if (matches.length === 0 && normalized.length === 0) {
                return this.timeSlots.slice(0, this.config.maxResults);
            }

            return matches.slice(0, this.config.maxResults);
        },

        showResults: function(results, $input) {
            const $searcher = $input.closest('.met-time-searcher');
            const $dropdown = $searcher.find('.met-time-dropdown');
            const $resultsContainer = $dropdown.find('.met-time-results');

            $resultsContainer.empty();

            if (results.length === 0) {
                $resultsContainer.html('<div class="met-time-no-results">Sin horarios disponibles</div>');
            } else {
                results.forEach(function(slot) {
                    const $item = $('<div class="met-time-result-item"></div>')
                        .text(slot)
                        .data('time', slot);
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

            $searcher.data('selected-time', time);
            $input.val('').hide();
            $selectedText.text(time);
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
