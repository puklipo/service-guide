// resources/js/components/markdown-editor.js

import OverType from 'overtype';

/**
 * OverType markdown editor component for Alpine.js
 * Provides a clean integration between OverType and Livewire
 */
export default function markdownEditor() {
    return {
        // --- State Management ---
        editor: null, // OverType instance
        initialized: false, // Initialization flag

        /**
         * Initialize the OverType markdown editor
         */
        init() {
            this.$nextTick(() => {
                if (this.$refs.editor && !this.initialized) {
                    try {
                        // Initialize OverType with Japanese-friendly configuration
                        const [instance] = new OverType(this.$refs.editor, {
                            value: this.$wire.get('description_draft') || '',
                            placeholder: 'マークダウンで記入してください...',
                            theme: 'solar',
                            toolbar: true,
                            showStats: false,
                            autoResize: false,
                            minHeight: '200px',
                            maxHeight: '500px',
                            onChange: (value, instance) => {
                                // Sync with Livewire component
                                this.$wire.set('description_draft', value);
                            }
                        });

                        this.editor = instance;
                        this.initialized = true;
                        //console.log('OverType markdown editor initialized successfully');
                    } catch (error) {
                        console.error('OverType initialization failed:', error);
                    }
                }
            });
        },

        /**
         * Destroy the editor instance and cleanup
         */
        destroy() {
            if (this.editor && typeof this.editor.destroy === 'function') {
                this.editor.destroy();
                this.editor = null;
                this.initialized = false;
                //console.log('OverType markdown editor destroyed');
            }
        },

        /**
         * Get the current markdown content
         * @returns {string}
         */
        getValue() {
            return this.editor ? this.editor.getValue() : '';
        },

        /**
         * Set the markdown content
         * @param {string} value
         */
        setValue(value) {
            if (this.editor) {
                this.editor.setValue(value || '');
            }
        },

        /**
         * Focus the editor
         */
        focus() {
            if (this.editor) {
                this.editor.focus();
            }
        },

        /**
         * Check if the editor is initialized
         * @returns {boolean}
         */
        isInitialized() {
            return this.initialized && this.editor !== null;
        }
    };
}
