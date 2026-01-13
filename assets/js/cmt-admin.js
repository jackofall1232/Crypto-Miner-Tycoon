/**
 * Crypto Idle Game - Admin Styles
 * @since 0.3.0
 */

(function($) {
    'use strict';
    
    $(document).ready(function() {
        
        /**
         * Tab switching with smooth animation
         */
        function handleTabSwitching() {
            $('.cmt-tab-wrapper .nav-tab').on('click', function(e) {
                // Let the link work normally, but add animation
                const $content = $('.cmt-tab-content');
                
                // Fade out
                $content.css('opacity', '0');
                
                // Fade back in after page load (handled by browser)
                setTimeout(function() {
                    $content.css('opacity', '1');
                }, 100);
            });
            
            // Initial fade in
            $('.cmt-tab-content').css({
                'opacity': '0',
                'transition': 'opacity 0.3s ease'
            });
            
            setTimeout(function() {
                $('.cmt-tab-content').css('opacity', '1');
            }, 100);
        }
        
        /**
         * Handle cloud saves checkbox dependency
         */
        function handleCloudSavesDependency() {
            const $cloudSaves = $('input[name="cmt_enable_cloud_saves"]');
            const $leaderboard = $('input[name="cmt_enable_leaderboard"]');
            
            if (!$cloudSaves.length || !$leaderboard.length) {
                return;
            }
            
            function updateLeaderboardState() {
                if ($cloudSaves.is(':checked')) {
                    $leaderboard.prop('disabled', false);
                    $leaderboard.closest('label').css('opacity', '1');
                } else {
                    $leaderboard.prop('disabled', true);
                    $leaderboard.prop('checked', false);
                    $leaderboard.closest('label').css('opacity', '0.5');
                }
            }
            
            // Initial state
            updateLeaderboardState();
            
            // Update on change
            $cloudSaves.on('change', updateLeaderboardState);
        }
        
        /**
         * Confirm before disabling cloud saves if data exists
         */
        function handleCloudSavesDisableWarning() {
            const $cloudSaves = $('input[name="cmt_enable_cloud_saves"]');
            const $form = $cloudSaves.closest('form');
            
            if (!$cloudSaves.length) {
                return;
            }
            
            // Store initial state
            const initialState = $cloudSaves.is(':checked');
            
            $form.on('submit', function(e) {
                const currentState = $cloudSaves.is(':checked');
                
                // If changing from enabled to disabled
                if (initialState && !currentState) {
                    const confirmed = confirm(
                        'Warning: Disabling cloud saves will prevent users from saving/loading their games.\n\n' +
                        'Existing saved games will remain in the database but will be inaccessible.\n\n' +
                        'Are you sure you want to disable cloud saves?'
                    );
                    
                    if (!confirmed) {
                        e.preventDefault();
                        $cloudSaves.prop('checked', true);
                        return false;
                    }
                }
            });
        }
        
        /**
         * Leaderboard limit validation
         */
        function handleLeaderboardLimitValidation() {
            const $limitInput = $('input[name="cmt_leaderboard_limit"]');
            
            if (!$limitInput.length) {
                return;
            }
            
            $limitInput.on('change', function() {
                let value = parseInt($(this).val());
                
                if (isNaN(value) || value < 5) {
                    value = 5;
                } else if (value > 100) {
                    value = 100;
                }
                
                $(this).val(value);
            });
        }
        
        /**
         * Add helpful tooltips
         */
        function addTooltips() {
            // Add tooltip to cloud saves checkbox
            const $cloudSavesLabel = $('input[name="cmt_enable_cloud_saves"]').closest('label');
            if ($cloudSavesLabel.length && !$cloudSavesLabel.find('.cmt-help-icon').length) {
                $cloudSavesLabel.append(' <span class="cmt-help-icon dashicons dashicons-info" title="Saves game data to WordPress database. Requires users to be logged in."></span>');
            }
            
            // Add tooltip to leaderboard checkbox
            const $leaderboardLabel = $('input[name="cmt_enable_leaderboard"]').closest('label');
            if ($leaderboardLabel.length && !$leaderboardLabel.find('.cmt-help-icon').length) {
                $leaderboardLabel.append(' <span class="cmt-help-icon dashicons dashicons-info" title="Display top players using the [crypto_miner_leaderboard] shortcode."></span>');
            }
            
            // Make dashicons visible
            $('.cmt-help-icon').css({
                'cursor': 'help',
                'color': '#787c82',
                'font-size': '16px',
                'vertical-align': 'middle'
            });
        }
        
        /**
         * Copy shortcode to clipboard
         */
        function handleShortcodeCopy() {
            $('.cmt-info-box code').on('click', function() {
                const $code = $(this);
                const text = $code.text();
                
                // Create temporary input
                const $temp = $('<input>');
                $('body').append($temp);
                $temp.val(text).select();
                document.execCommand('copy');
                $temp.remove();
                
                // Visual feedback
                const originalBg = $code.css('background-color');
                $code.css('background-color', '#46b450');
                
                setTimeout(function() {
                    $code.css('background-color', originalBg);
                }, 200);
                
                // Show tooltip
                const $tooltip = $('<span class="cmt-copied-tooltip">Copied!</span>');
                $tooltip.css({
                    'position': 'absolute',
                    'background': '#1d2327',
                    'color': '#fff',
                    'padding': '4px 8px',
                    'border-radius': '3px',
                    'font-size': '11px',
                    'margin-left': '8px',
                    'z-index': '1000'
                });
                
                $code.after($tooltip);
                
                setTimeout(function() {
                    $tooltip.fadeOut(function() {
                        $(this).remove();
                    });
                }, 1500);
            });
            
            // Add cursor pointer to codes
            $('.cmt-info-box code').css('cursor', 'pointer');
        }
        
        /**
         * Animate stats on page load
         */
        function animateStats() {
            $('.cmt-stat-value').each(function() {
                const $this = $(this);
                const finalValue = parseInt($this.text());
                
                if (isNaN(finalValue)) {
                    return;
                }
                
                $this.text('0');
                
                $({ counter: 0 }).animate({ counter: finalValue }, {
                    duration: 1000,
                    easing: 'swing',
                    step: function() {
                        $this.text(Math.ceil(this.counter));
                    },
                    complete: function() {
                        $this.text(finalValue);
                    }
                });
            });
        }
        
        /**
         * Settings form unsaved changes warning
         */
        function handleUnsavedChanges() {
            const $form = $('form');
            let formChanged = false;
            
            if (!$form.length) {
                return;
            }
            
            // Track changes
            $form.on('change', 'input, select, textarea', function() {
                formChanged = true;
            });
            
            // Warn on page leave
            $(window).on('beforeunload', function() {
                if (formChanged) {
                    return 'You have unsaved changes. Are you sure you want to leave?';
                }
            });
            
            // Don't warn on form submit
            $form.on('submit', function() {
                formChanged = false;
            });
        }
        
        /**
         * Animate upgrade CTAs
         */
        function animateUpgradeCTAs() {
            $('.cmt-upgrade-cta, .cmt-upgrade-button').each(function() {
                const $this = $(this);
                
                // Pulse animation on hover
                $this.hover(
                    function() {
                        $(this).css('animation', 'pulse 0.5s ease-in-out');
                    },
                    function() {
                        $(this).css('animation', '');
                    }
                );
            });
        }
        
        /**
         * Initialize all admin functionality
         */
        function init() {
            handleTabSwitching();
            handleCloudSavesDependency();
            handleCloudSavesDisableWarning();
            handleLeaderboardLimitValidation();
            addTooltips();
            handleShortcodeCopy();
            animateStats();
            handleUnsavedChanges();
            animateUpgradeCTAs();
        }
        
        // Initialize
        init();
        
    });
    
})(jQuery);
