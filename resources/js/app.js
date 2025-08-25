import './bootstrap';
import "flyonui/flyonui"
import OverType from 'overtype';
import { Livewire, Alpine } from '../../vendor/livewire/livewire/dist/livewire.esm';
import chartBar from './components/chart-bar';
import chartLine from './components/chart-line';
import chartPie from './components/chart-pie';

Alpine.data('chartBar', chartBar);
Alpine.data('chartLine', chartLine);
Alpine.data('chartPie', chartPie);

// OverType markdown editor initialization
Alpine.data('markdownEditor', () => ({
    editor: null,
    init() {
        this.$nextTick(() => {
            if (this.$refs.editor) {
                // Correct OverType initialization according to documentation
                const [instance] = new OverType(this.$refs.editor, {
                    value: this.$wire.get('description_draft') || '',
                    placeholder: 'マークダウンで記入してください...',
                    theme: 'solar',
                    toolbar: true,
                    showStats: true,
                    autoResize: true,
                    minHeight: '200px',
                    maxHeight: '500px',
                    onChange: (value, instance) => {
                        this.$wire.set('description_draft', value);
                    }
                });
                this.editor = instance;
            }
        });
    },
    destroy() {
        if (this.editor && typeof this.editor.destroy === 'function') {
            this.editor.destroy();
            this.editor = null;
        }
    }
}));

Livewire.start()
