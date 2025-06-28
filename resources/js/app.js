import './bootstrap';
import "flyonui/flyonui"
import { Livewire, Alpine } from '../../vendor/livewire/livewire/dist/livewire.esm';
import chartBar from './components/chart-bar';
import chartLine from './components/chart-line';
import chartPie from './components/chart-pie';

Alpine.data('chartBar', chartBar);
Alpine.data('chartLine', chartLine);
Alpine.data('chartPie', chartPie);

Livewire.start()
