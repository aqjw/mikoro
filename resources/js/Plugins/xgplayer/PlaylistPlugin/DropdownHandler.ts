import { computed, nextTick, ref, Ref, watch } from 'vue';
import { Util } from 'xgplayer';
import Emitter from '../Emitter';

interface DropdownConfig {
  container: string;
  class?: string;
  invisible?: boolean;
  recentChangeThreshold?: number;
  filter?: (options: any[]) => any[];
  formatOptionLabel: (option: any, isSelected?: boolean) => string;
}

interface DropdownOption {
  id: number;
  label: string;
  [key: string]: any;
}

export default class DropdownHandler {
  private emitter: Emitter;
  private plugin: any;
  private config: DropdownConfig;

  public isOpen: Ref<boolean>;
  public selected: Ref<number | string | null>;
  public options: Ref<DropdownOption[]>;
  private lastChanged: Ref<number>;
  public filteredOptions: Ref<DropdownOption[]>;
  public selectedOption: Ref<DropdownOption | undefined>;

  constructor(plugin: any, config: DropdownConfig) {
    this.emitter = new Emitter();
    this.plugin = plugin;
    this.config = this.initializeConfig(config);

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

  private initializeConfig(config: DropdownConfig): DropdownConfig {
    return {
      class: '',
      filter: null,
      invisible: false,
      recentChangeThreshold: 500,
      formatOptionLabel: (option: any) => option.label,
      ...config,
    };
  }

  refreshOptions(): void {
    this.options.value = [...this.options.value];
  }

  getDropdownStatus(): boolean {
    return this.isOpen.value;
  }

  suspendEmitter(autoResume = true): this {
    try {
      this.emitter.stop();
      return this;
    } finally {
      if (autoResume) {
        nextTick(() => this.emitter.start());
      }
    }
  }

  wasRecentlyChanged(): boolean {
    return Date.now() - this.lastChanged.value <= this.config.recentChangeThreshold;
  }

  closeDropdown(): void {
    this.isOpen.value = false;
  }

  setOptions(options: DropdownOption[]): void {
    this.options.value = options;
  }

  getOptions(): DropdownOption[] {
    return this.options.value;
  }

  hasSelected(): boolean {
    return Boolean(this.selected.value);
  }

  setSelected(selected: number | string): void {
    this.selected.value = selected;
    this.lastChanged.value = Date.now();
  }

  getSelectedOption(): DropdownOption | undefined {
    return this.selectedOption.value;
  }

  getSelected(): number | null {
    return this.selected.value;
  }

  first(): DropdownOption | undefined {
    return this.filteredOptions.value[0];
  }

  last(): DropdownOption | undefined {
    return this.filteredOptions.value[this.filteredOptions.value.length - 1];
  }

  selector(selector = ''): string {
    return `${this.config.container} ${selector}`.trim();
  }

  el(selector = ''): HTMLElement | null {
    return this.plugin.find(this.selector(selector));
  }

  scrollToSelected(): void {
    const selectedValue = this.getSelected();
    if (!selectedValue) return;

    const optionNode = this.el(`.dropdown li[data-value="${selectedValue}"]`);
    const dropdownNode = this.el('.dropdown');

    if (optionNode && dropdownNode) {
      const offset = optionNode.offsetTop - dropdownNode.offsetTop - optionNode.offsetHeight;
      dropdownNode.scrollTop = Math.max(0, offset);
    }
  }

  initWatchers(): void {
    if (!this.config.invisible) {
      watch(this.isOpen, (val) => {
        this.emitter.emit('open-toggle', val);
        if (val) {
          this.el('.select-container')?.classList.add('open');
          this.scrollToSelected();
        } else {
          this.el('.select-container')?.classList.remove('open');
        }
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

  initEventListeners(): void {
    if (this.isInvisible()) return;

    this.handleContainerClick = (e: Event) => {
      e.preventDefault();
      e.stopPropagation();
      this.isOpen.value = !this.isOpen.value;
    };

    this.handleOptionClick = (e: any) => {
      e.preventDefault();
      e.stopPropagation();
      this.setSelected(+e.delegateTarget.dataset.value);
    };

    this.plugin.bind(this.selector('.select-container'), 'click', this.handleContainerClick);
    this.plugin.bind(this.selector('.dropdown-option'), 'click', this.handleOptionClick);
  }

  destroy(): void {
    if (this.isInvisible()) return;

    this.plugin.unbind(this.selector('.select-container'), 'click', this.handleContainerClick);
    this.plugin.unbind(this.selector('.dropdown-option'), 'click', this.handleOptionClick);
  }

  isInvisible(): boolean {
    return !!this.config.invisible;
  }

  highlightSelectedOption(): void {
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

  renderOptions(): void {
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

  renderDropdown(): void {
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
