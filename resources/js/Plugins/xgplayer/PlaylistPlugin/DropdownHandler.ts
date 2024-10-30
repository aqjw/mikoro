import { computed, nextTick, ref, watch } from 'vue';
import { Util } from 'xgplayer';
import Emitter from '../Emitter';

export default class DropdownHandler {
  constructor(plugin, config) {
    this.emitter = new Emitter();
    this.plugin = plugin;
    this.config = config;

    this.isOpen = ref(false);
    this.selected = ref(null);
    this.options = ref([]);
    this.lastChanged = ref(0);

    this.filteredOptions = computed(() => {
      if (typeof this.config.filter === 'function') {
        return this.config.filter(this.options.value);
      }
      return this.options.value;
    });

    this.selectedOption = computed(() =>
      this.options.value.find((option) => option.id === this.selected.value)
    );

    this.renderDropdown();
    this.initWatchers();
    this.initEventListeners();
  }

  refreshOptions() {
    this.options.value = [...this.options.value];
  }

  getDropdownStatus() {
    return this.isOpen.value;
  }

  suspendEmitter(autoResume = true) {
    try {
      this.emitter.stop();
      return this;
    } finally {
      if (autoResume) {
        nextTick(() => this.emitter.start());
      }
    }
  }

  wasRecentlyChanged() {
    return (
      Date.now() - this.lastChanged.value <= (this.config.recentChangeThreshold || 500)
    );
  }

  closeDropdown() {
    this.isOpen.value = false;
  }

  setOptions(options) {
    this.options.value = options;
  }

  getOptions() {
    return this.options.value;
  }

  hasSelected() {
    return Boolean(this.selected.value);
  }

  setSelected(selected) {
    this.selected.value = selected;
  }

  getSelectedOption() {
    return this.selectedOption.value;
  }

  getSelected() {
    return this.selected.value;
  }

  first() {
    return this.filteredOptions.value[0];
  }

  last() {
    return this.filteredOptions.value[this.filteredOptions.value.length - 1];
  }

  selector(selector = '') {
    return `${this.config.container} ${selector}`.trim();
  }

  el(selector = '') {
    return this.plugin.find(this.selector(selector));
  }

  initWatchers() {
    if (!this.config.invisible) {
      watch(this.isOpen, (val) => {
        this.emitter.emit('open-toggle', val);
        val
          ? this.el('.select-container').classList.add('open')
          : this.el('.select-container').classList.remove('open');
      });

      watch(this.selected, () => {
        this.emitter.emit('selected-updated');
        this.lastChanged.value = Date.now();
        this.highlightSelectedOption();
      });

      watch(this.filteredOptions, () => {
        this.emitter.emit('options-updated');
        this.renderOptions();
      });
    }
  }

  initEventListeners() {
    if (this.isInvisible()) return;

    this.handleContainerClick = (e) => {
      e.preventDefault();
      e.stopPropagation();
      this.isOpen.value = !this.isOpen.value;
    };

    this.handleOptionClick = (e) => {
      e.preventDefault();
      e.stopPropagation();
      this.setSelected(+e.delegateTarget.dataset.value);
    };

    this.plugin.bind(
      this.selector('.select-container'),
      'click',
      this.handleContainerClick
    );
    this.plugin.bind(this.selector('.dropdown-option'), 'click', this.handleOptionClick);
  }

  destroy() {
    if (this.isInvisible()) return;

    this.plugin.unbind(
      this.selector('.select-container'),
      'click',
      this.handleContainerClick
    );
    this.plugin.unbind(
      this.selector('.dropdown-option'),
      'click',
      this.handleOptionClick
    );
  }

  isInvisible() {
    return this.config.invisible;
  }

  highlightSelectedOption() {
    if (this.isInvisible()) return;

    this.el('.dropdown-option.selected')?.classList.remove('selected');
    this.el(`.dropdown-option[data-value="${this.selected.value}"]`)?.classList.add(
      'selected'
    );

    const el = this.el('.selected-option');
    if (el && this.selectedOption.value) {
      el.innerHTML = this.config.formatOptionLabel(this.selectedOption.value);
    }
  }

  renderOptions() {
    if (this.isInvisible()) return;

    const html = this.filteredOptions.value
      .map((option) => {
        const selectedClass = this.selected.value === option.id ? 'selected' : '';
        return Util.createDom('li', this.config.formatOptionLabel(option, false), {
          class: `dropdown-option ${selectedClass}`,
          'data-value': option.id,
        }).outerHTML;
      })
      .join('');

    this.el('.dropdown').innerHTML = html;
  }

  renderDropdown() {
    if (this.isInvisible()) return;

    const html = `<div class="selected-option-container">
        <div class="selected-option"></div>
        <svg class="icon" viewBox="0 0 330 330"><path d="m250.606 154.389-150-149.996c-5.857-5.858-15.355-5.858-21.213.001-5.857 5.858-5.857 15.355.001 21.213l139.393 139.39L79.393 304.394c-5.857 5.858-5.857 15.355.001 21.213C82.322 328.536 86.161 330 90 330s7.678-1.464 10.607-4.394l149.999-150.004a14.996 14.996 0 0 0 0-21.213"/></svg>
    </div>
    <div class="dropdown-container"><ul class="dropdown"></ul></div>`;

    const node = Util.createDom('div', html, {}, `select-container ${this.config.class}`);
    this.plugin.appendChild(this.config.container, node);
  }
}
